<?php

namespace App\Models;

use App\Enums\SessionStatusz;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EdzesSession extends Model
{
    protected $table = 'edzes_session';
    protected $primaryKey = 'edzes_session_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'datum' => 'date',
        'kezdes' => 'datetime',
        'befejezes' => 'datetime',
        'statusz' => SessionStatusz::class,
    ];

    public function felhasznalo(): BelongsTo
    {
        return $this->belongsTo(Felhasznalo::class, 'felhasznalo_id');
    }

    public function nap(): BelongsTo
    {
        return $this->belongsTo(Edzesnap::class, 'edzesnap_id');
    }

    public function szettek(): HasMany
    {
        return $this->hasMany(NaploSzett::class, 'edzes_session_id');
    }
}
