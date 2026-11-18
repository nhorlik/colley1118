<?php

namespace App\Http\Controllers;

use App\Models\Felhasznalo;
use App\Models\Izomcsoport;
use App\Services\Kapu\EdzesKapu;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * A 3D izomcsoport-választó. A tényleges terv-generálás ugyanazt az útvonalat
 * (route('terv.general')) hívja, mint az "Új terv" egyszerű űrlapja — a 3D jelenet
 * csak be-/kipipálja a valódi checkboxokat (data-mesh = izomcsoport.mesh_nev),
 * szerveroldalon semmi nem változik emiatt.
 */
class AvatarController extends Controller
{
    public function __invoke(EdzesKapu $kapu): View
    {
        /** @var Felhasznalo $f */
        $f = Auth::user();

        return view('avatar.index', [
            'dontes' => $kapu->ellenoriz($f),
            'izomcsoportok' => Izomcsoport::orderBy('sorrend')->get(['izomcsoport_id', 'kod', 'nev', 'mesh_nev', 'testoldal']),
        ]);
    }
}
