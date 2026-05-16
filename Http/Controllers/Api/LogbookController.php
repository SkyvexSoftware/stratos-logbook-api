<?php

namespace Modules\StratosLogbook\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Acars;
use App\Models\Enums\AcarsType;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function pireps(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit'  => 'sometimes|integer|min:1|max:100',
            'offset' => 'sometimes|integer|min:0',
            'sort'   => 'sometimes|string|in:date,route,aircraft,duration,distance,landing_rate,status',
            'order'  => 'sometimes|string|in:asc,desc',
            'q'      => 'sometimes|string|max:80',
            'status' => 'sometimes|string|in:pending,accepted,rejected',
        ]);

        $limit  = (int) ($validated['limit'] ?? 25);
        $offset = (int) ($validated['offset'] ?? 0);
        $sort   = $validated['sort'] ?? 'date';
        $order  = $validated['order'] ?? 'desc';

        $statusMap = [
            'pending'  => PirepState::PENDING,
            'accepted' => PirepState::ACCEPTED,
            'rejected' => PirepState::REJECTED,
        ];

        $sortMap = [
            'date'         => 'submitted_at',
            'aircraft'     => 'aircraft_id',
            'duration'     => 'flight_time',
            'distance'     => 'distance',
            'landing_rate' => 'landing_rate',
            'status'       => 'state',
        ];

        $query = Pirep::query()
            ->with(['aircraft', 'dpt_airport', 'arr_airport'])
            ->where('user_id', Auth::id())
            ->whereIn('state', [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED]);

        if (! empty($validated['status'])) {
            $query->where('state', $statusMap[$validated['status']]);
        }

        if (! empty($validated['q'])) {
            $term = '%'.$validated['q'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('flight_number', 'LIKE', $term)
                    ->orWhere('dpt_airport_id', 'LIKE', $term)
                    ->orWhere('arr_airport_id', 'LIKE', $term);
            });
        }

        $total = (clone $query)->count();

        if ($sort === 'route') {
            $query->orderBy('dpt_airport_id', $order)->orderBy('arr_airport_id', $order);
        } else {
            $query->orderBy($sortMap[$sort], $order);
        }

        $items = $query->offset($offset)->limit($limit)->get();

        return response()->json([
            'items'  => $items->map(fn (Pirep $p) => $this->toListItem($p))->all(),
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function pirep(string $id): JsonResponse
    {
        $pirep = Pirep::query()
            ->with(['aircraft', 'airline'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->whereIn('state', [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED])
            ->first();

        if (! $pirep) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $pathRows = Acars::query()
            ->where('pirep_id', $pirep->id)
            ->where('type', AcarsType::FLIGHT_PATH)
            ->orderBy('created_at')
            ->orderBy('order')
            ->get();

        $logRows = Acars::query()
            ->where('pirep_id', $pirep->id)
            ->where('type', AcarsType::LOG)
            ->orderBy('created_at')
            ->orderBy('order')
            ->get();

        $route = $this->buildRoute($pathRows);
        $log   = $this->buildLog($logRows);

        $durationMin = (int) $pirep->flight_time;

        return response()->json([
            'id'               => $pirep->id,
            'date'             => optional($pirep->submitted_at ?? $pirep->created_at)->toIso8601String(),
            'dep_icao'         => $pirep->dpt_airport_id,
            'arr_icao'         => $pirep->arr_airport_id,
            'callsign'         => ($pirep->airline->icao ?? '').($pirep->flight_number ?? ''),
            'aircraft_icao'    => $pirep->aircraft?->icao,
            'aircraft_reg'     => $pirep->aircraft?->registration,
            'status'           => $this->statusSlug((int) $pirep->state),
            'duration_min'     => $durationMin,
            'block_time_min'   => (int) ($pirep->block_time ?? $durationMin),
            'air_time_min'     => $durationMin,
            'distance_nm'      => (int) ($pirep->distance?->internal ?? 0),
            'fuel_used_kg'     => (int) round((float) ($pirep->fuel_used?->internal ?? 0)),
            'cruise_alt_ft'    => $this->cruiseAlt($route),
            'max_speed_kt'     => $this->maxSpeed($route),
            'landing_rate_fpm' => (int) round((float) ($pirep->landing_rate ?? 0)),
            'violations'       => 0,
            'simulator'        => (string) ($pirep->source_name ?? ''),
            'network'          => '',
            'route'            => $route,
            'log'              => $log,
        ]);
    }

    public function stats(): JsonResponse
    {
        $user   = Auth::user();
        $userId = $user->id;
        $now    = Carbon::now();

        $totalFlights    = (int) ($user->flights ?? 0);
        $totalHoursFlown = round(((int) ($user->flight_time ?? 0)) / 60, 1);

        $base = Pirep::query()
            ->where('user_id', $userId)
            ->where('state', PirepState::ACCEPTED);

        $flightsThisMonth = (clone $base)
            ->whereYear('submitted_at', $now->year)
            ->whereMonth('submitted_at', $now->month)
            ->count();

        $minutesThisYear = (int) (clone $base)
            ->whereYear('submitted_at', $now->year)
            ->sum('flight_time');

        $distance = (int) (clone $base)->sum('distance');

        $avgLandingFpm = (int) floor((float) ((clone $base)
            ->whereNotNull('landing_rate')
            ->avg('landing_rate') ?? 0));

        return response()->json([
            'total_flights'      => $totalFlights,
            'flights_this_month' => $flightsThisMonth,
            'hours_flown'        => $totalHoursFlown,
            'hours_this_year'    => round($minutesThisYear / 60, 1),
            'distance_nm'        => $distance,
            'avg_landing_fpm'    => $avgLandingFpm,
            'rank'               => (string) ($user->rank?->name ?? ''),
            'rank_image'         => (string) ($user->rank?->image_url ?? ''),
        ]);
    }

    private function toListItem(Pirep $p): array
    {
        return [
            'id'               => $p->id,
            'date'             => optional($p->submitted_at ?? $p->created_at)->toIso8601String(),
            'dep_icao'         => $p->dpt_airport_id,
            'arr_icao'         => $p->arr_airport_id,
            'callsign'         => ($p->airline->icao ?? '').($p->flight_number ?? ''),
            'aircraft_icao'    => $p->aircraft?->icao,
            'aircraft_reg'     => $p->aircraft?->registration,
            'duration_min'    => (int) $p->flight_time,
            'distance_nm'     => (int) ($p->distance?->internal ?? 0),
            'landing_rate_fpm' => (int) round((float) ($p->landing_rate ?? 0)),
            'status'           => $this->statusSlug((int) $p->state),
        ];
    }

    private function statusSlug(int $state): string
    {
        return match ($state) {
            PirepState::ACCEPTED => 'accepted',
            PirepState::REJECTED => 'rejected',
            default              => 'pending',
        };
    }

    private function buildRoute(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $first = $rows->first();
        $firstT = $first->created_at ? $first->created_at->timestamp : null;

        $out = [];
        foreach ($rows as $i => $r) {
            $t = $firstT !== null && $r->created_at
                ? max(0, ($r->created_at->timestamp - $firstT) * 1000)
                : $i * 1000;

            $out[] = [
                't'       => (int) $t,
                'lat'     => (float) $r->lat,
                'lon'     => (float) $r->lon,
                'alt_ft'  => (int) round((float) ($r->altitude_msl ?? $r->altitude_agl ?? 0)),
                'spd_kt'  => (int) round((float) ($r->gs ?? 0)),
                'hdg_deg' => (int) round((float) ($r->heading ?? 0)),
            ];
        }

        return $out;
    }

    private function buildLog(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $first = $rows->first();
        $firstT = $first->created_at ? $first->created_at->timestamp : null;

        $out = [];
        foreach ($rows as $i => $r) {
            $message = (string) ($r->log ?? '');
            if ($message === '') {
                continue;
            }

            $t = $firstT !== null && $r->created_at
                ? max(0, ($r->created_at->timestamp - $firstT) * 1000)
                : $i * 1000;

            $out[] = [
                't'       => (int) $t,
                'level'   => $this->inferLevel($message),
                'message' => $message,
            ];
        }

        return $out;
    }

    private function inferLevel(string $message): string
    {
        $h = strtolower($message);
        if (str_contains($h, 'overspeed')
            || str_contains($h, 'stall')
            || str_contains($h, 'hard landing')
            || str_contains($h, 'connection lost')
            || str_contains($h, 'exceeded')) {
            return 'error';
        }
        if (str_contains($h, 'slew')
            || str_contains($h, 'simulation rate')
            || str_contains($h, 'reconnected')
            || str_contains($h, 'early')) {
            return 'warning';
        }
        return 'info';
    }

    private function cruiseAlt(array $route): int
    {
        if ($route === []) {
            return 0;
        }
        return (int) max(array_column($route, 'alt_ft'));
    }

    private function maxSpeed(array $route): int
    {
        if ($route === []) {
            return 0;
        }
        return (int) max(array_column($route, 'spd_kt'));
    }
}
