<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Test-only stub of phpVMS's Acars Eloquent model.
 * The real class lives in `app/Models/Acars.php` inside phpVMS —
 * see https://github.com/nabeelio/phpvms.
 *
 * Only the surface area the logbook controller reads is declared here.
 */
class Acars extends Model
{
    public $table = 'acars';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $fillable = [
        'pirep_id',
        'type',
        'lat',
        'lon',
        'altitude_msl',
        'altitude_agl',
        'gs',
        'heading',
        'log',
        'order',
        'created_at',
    ];
}
