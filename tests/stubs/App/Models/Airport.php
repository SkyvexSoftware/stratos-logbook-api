<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Test-only stub of phpVMS's Airport Eloquent model.
 * Real class: app/Models/Airport.php in https://github.com/nabeelio/phpvms.
 */
class Airport extends Model
{
    public $table = 'airports';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
