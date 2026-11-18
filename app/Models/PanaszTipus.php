<?php

namespace App\Models;

use App\Enums\PanaszEredet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PanaszTipus extends Model
{
    protected $table = 'panasz_tipus';
    protected $primaryKey = 'panasz_tipus_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['eredet' => PanaszEredet::class];

    public function gyakorlatok(): BelongsToMany
    {
        return $this->belongsToMany(Gyakorlat::class, 'gyakorlat_panasz', 'panasz_tipus_id', 'gyakorlat_id')
            ->withPivot('viszony', 'indoklas', 'helyette');
    }
}
