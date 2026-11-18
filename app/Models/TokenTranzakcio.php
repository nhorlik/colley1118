<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Append-only: soha nem módosítjuk/töröljük; egyenleg = SUM(mennyiseg). */
class TokenTranzakcio extends Model
{
    protected $table = 'token_tranzakcio';
    protected $primaryKey = 'token_tranzakcio_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['letrehozva' => 'datetime', 'mennyiseg' => 'integer'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('A token-napló append-only, sor nem módosítható.'));
        static::deleting(fn () => throw new \LogicException('A token-napló append-only, sor nem törölhető.'));
    }
}
