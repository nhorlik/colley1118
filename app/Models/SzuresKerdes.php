<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SzuresKerdes extends Model
{
    protected $table = 'szures_kerdes';
    protected $primaryKey = 'szures_kerdes_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['aktiv' => 'boolean'];

    public function testterulet(): BelongsTo
    {
        return $this->belongsTo(Testterulet::class, 'testterulet_id');
    }

    public function valaszok(): HasMany
    {
        return $this->hasMany(SzuresValasz::class, 'szures_kerdes_id')->orderBy('sorrend');
    }
}
