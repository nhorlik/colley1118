<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdzesnapGyakorlat extends Model
{
    protected $table = 'edzesnap_gyakorlat';
    protected $primaryKey = 'edzesnap_gyakorlat_id';
    public $timestamps = false;
    protected $guarded = [];

    public function nap(): BelongsTo
    {
        return $this->belongsTo(Edzesnap::class, 'edzesnap_id');
    }

    public function gyakorlat(): BelongsTo
    {
        return $this->belongsTo(Gyakorlat::class, 'gyakorlat_id');
    }

    /** Az ido_mp mező dönti el: visszaszámlálós (idő alapú) vagy ismétlésszám alapú tétel. */
    public function idoAlapu(): bool
    {
        return $this->ido_mp !== null;
    }
}
