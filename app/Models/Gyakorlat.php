<?php

namespace App\Models;

use App\Enums\GyakorlatTipus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gyakorlat extends Model
{
    protected $table = 'gyakorlat';
    protected $primaryKey = 'gyakorlat_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'tipus' => GyakorlatTipus::class,
        'otthon_vegezheto' => 'boolean',
        'terapias' => 'boolean',
        'aktiv' => 'boolean',
        'nehezseg' => 'integer',
    ];

    public function izomcsoportok(): BelongsToMany
    {
        return $this->belongsToMany(Izomcsoport::class, 'gyakorlat_izomcsoport', 'gyakorlat_id', 'izomcsoport_id')
            ->withPivot('elsodleges');
    }

    public function panaszok(): BelongsToMany
    {
        return $this->belongsToMany(PanaszTipus::class, 'gyakorlat_panasz', 'gyakorlat_id', 'panasz_tipus_id')
            ->withPivot('viszony', 'indoklas', 'helyette');
    }
}
