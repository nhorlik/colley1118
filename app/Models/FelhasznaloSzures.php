<?php

namespace App\Models;

use App\Enums\SzuresEredmeny;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FelhasznaloSzures extends Model
{
    protected $table = 'felhasznalo_szures';
    protected $primaryKey = 'felhasznalo_szures_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['kitoltve' => 'datetime', 'eredmeny' => SzuresEredmeny::class];

    public function felhasznalo(): BelongsTo
    {
        return $this->belongsTo(Felhasznalo::class, 'felhasznalo_id');
    }

    public function testterulet(): BelongsTo
    {
        return $this->belongsTo(Testterulet::class, 'testterulet_id');
    }

    public function valaszok(): BelongsToMany
    {
        return $this->belongsToMany(SzuresValasz::class, 'felhasznalo_szures_valasz', 'felhasznalo_szures_id', 'szures_valasz_id')
            ->withPivot('szures_kerdes_id');
    }
}
