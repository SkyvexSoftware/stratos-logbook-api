<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Test-only stub of phpVMS's Pirep Eloquent model.
 * The real class lives in `app/Models/Pirep.php` inside phpVMS —
 * see https://github.com/nabeelio/phpvms.
 *
 * Only the surface area the logbook controller touches is declared here.
 */
class Pirep extends Model
{
    public $table = 'pireps';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $fillable = [
        'id',
        'user_id',
        'airline_id',
        'aircraft_id',
        'flight_number',
        'dpt_airport_id',
        'arr_airport_id',
        'flight_time',
        'block_time',
        'distance',
        'fuel_used',
        'landing_rate',
        'state',
        'source',
        'source_name',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function dpt_airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'dpt_airport_id');
    }

    public function arr_airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'arr_airport_id');
    }
}
