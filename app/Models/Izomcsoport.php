<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** mesh_nev = a 3D modell felület-neve; a választó checkbox data-mesh attribútuma ezzel egyezik. */
class Izomcsoport extends Model
{
    protected $table = 'izomcsoport';
    protected $primaryKey = 'izomcsoport_id';
    public $timestamps = false;
    protected $guarded = [];

    public function testterulet(): BelongsTo
    {
        return $this->belongsTo(Testterulet::class, 'testterulet_id');
    }

    public function gyakorlatok(): BelongsToMany
    {
        return $this->belongsToMany(Gyakorlat::class, 'gyakorlat_izomcsoport', 'izomcsoport_id', 'gyakorlat_id')
            ->withPivot('elsodleges');
    }
}
