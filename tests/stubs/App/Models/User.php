<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Test-only stub of phpVMS's User Eloquent model.
 * The real class lives in `app/Models/User.php` inside phpVMS —
 * see https://github.com/nabeelio/phpvms.
 *
 * Only the surface area the logbook controller reads is declared here.
 * In particular, `flights` and `flight_time` are scalar columns on the user
 * record in phpVMS — denormalised counters — so they're plain fillables here.
 */
class User extends Model
{
    public $table = 'users';

    protected $guarded = [];

    protected $fillable = [
        'id',
        'api_key',
        'flights',
        'flight_time',
        'rank_id',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
