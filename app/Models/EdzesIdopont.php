<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdzesIdopont extends Model
{
    protected $table = 'edzes_idopont';
    protected $primaryKey = 'edzes_idopont_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'datum' => 'date',
        'ertesitve' => 'boolean',
        'letrehozva' => 'datetime',
    ];

    public function nap(): BelongsTo
    {
        return $this->belongsTo(Edzesnap::class, 'edzesnap_id');
    }
}
