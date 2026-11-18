<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NaploSzett extends Model
{
    protected $table = 'naplo_szett';
    protected $primaryKey = 'naplo_szett_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'datum' => 'date',
        'suly_kg' => 'decimal:2',
        'rogzitve' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(EdzesSession::class, 'edzes_session_id');
    }

    public function gyakorlat(): BelongsTo
    {
        return $this->belongsTo(Gyakorlat::class, 'gyakorlat_id');
    }
}
