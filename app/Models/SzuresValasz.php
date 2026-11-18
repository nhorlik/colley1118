<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SzuresValasz extends Model
{
    protected $table = 'szures_valasz';
    protected $primaryKey = 'szures_valasz_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['vorosjelzes' => 'boolean'];

    public function kerdes(): BelongsTo
    {
        return $this->belongsTo(SzuresKerdes::class, 'szures_kerdes_id');
    }

    public function panaszTipus(): BelongsTo
    {
        return $this->belongsTo(PanaszTipus::class, 'panasz_tipus_id');
    }
}
