<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Testterulet extends Model
{
    protected $table = 'testterulet';
    protected $primaryKey = 'testterulet_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['aktiv' => 'boolean'];

    public function szuresKerdesek(): HasMany
    {
        return $this->hasMany(SzuresKerdes::class, 'testterulet_id')->where('aktiv', true)->orderBy('sorrend');
    }

    public function izomcsoportok(): HasMany
    {
        return $this->hasMany(Izomcsoport::class, 'testterulet_id');
    }
}
