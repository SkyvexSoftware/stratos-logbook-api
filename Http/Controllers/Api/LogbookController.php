<?php

namespace Modules\StratosLogbook\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->where('user_id', Auth::id());

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
        return response()->json(['message' => 'Not found'], 404);
    }

    public function stats(): JsonResponse
    {
        return response()->json([]);
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
}
