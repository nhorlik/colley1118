<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edzesterv extends Model
{
    protected $table = 'edzesterv';
    protected $primaryKey = 'edzesterv_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'kezdo_datum' => 'date',
        'aktiv' => 'boolean',
        'rehab' => 'boolean',
        'letrehozva' => 'datetime',
    ];

    public function felhasznalo(): BelongsTo
    {
        return $this->belongsTo(Felhasznalo::class, 'felhasznalo_id');
    }

    public function napok(): HasMany
    {
        return $this->hasMany(Edzesnap::class, 'edzesterv_id')->orderBy('nap_sorszam');
    }

    public function fokuszok(): BelongsToMany
    {
        return $this->belongsToMany(Izomcsoport::class, 'edzesterv_fokusz', 'edzesterv_id', 'izomcsoport_id')
            ->withPivot('fo_fokusz');
    }
}
