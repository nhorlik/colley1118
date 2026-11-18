<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edzesnap extends Model
{
    protected $table = 'edzesnap';
    protected $primaryKey = 'edzesnap_id';
    public $timestamps = false;
    protected $guarded = [];

    public function terv(): BelongsTo
    {
        return $this->belongsTo(Edzesterv::class, 'edzesterv_id');
    }

    public function tetelek(): HasMany
    {
        return $this->hasMany(EdzesnapGyakorlat::class, 'edzesnap_id')->orderBy('sorrend');
    }
}
