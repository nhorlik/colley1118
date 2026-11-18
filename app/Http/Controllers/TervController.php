<?php

namespace App\Http\Controllers;

use App\Exceptions\EdzesTiltvaException;
use App\Exceptions\TervGeneralasiHiba;
use App\Models\Felhasznalo;
use App\Models\Izomcsoport;
use App\Services\Kapu\EdzesKapu;
use App\Services\Terv\TervGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TervController extends Controller
{
    public function __construct(
        private readonly EdzesKapu $kapu,
        private readonly TervGenerator $generator,
    ) {
    }

    public function tervem(): View
    {
        /** @var Felhasznalo $f */
        $f = Auth::user();

        $terv = $f->aktivTerv()->with('napok.tetelek.gyakorlat', 'fokuszok')->first();
        $dontes = $this->kapu->ellenoriz($f);

        return view('terv.tervem', compact('terv', 'dontes'));
    }

    public function ujForma(): View
    {
        /** @var Felhasznalo $f */
        $f = Auth::user();

        return view('terv.uj', [
            'dontes' => $this->kapu->ellenoriz($f),
            'izomcsoportok' => Izomcsoport::orderBy('sorrend')->get(),
        ]);
    }

    public function general(Request $request): RedirectResponse
    {
        /** @var Felhasznalo $f */
        $f = Auth::user();

        $adat = $request->validate([
            'fokusz_izmok' => ['array'],
            'fokusz_izmok.*' => ['integer', 'exists:izomcsoport,izomcsoport_id'],
        ]);

        try {
            $this->generator->general($f, $adat['fokusz_izmok'] ?? []);
        } catch (EdzesTiltvaException $e) {
            return back()->with('kapu_hiba', $e->getMessage())->with('kapu_teendok', $e->dontes->teendok);
        } catch (TervGeneralasiHiba $e) {
            report($e);

            return back()->with('terv_hiba', 'A terv összeállítása közben hiba történt. Próbáld újra, esetleg más fókusszal.');
        }

        return redirect()->route('terv.tervem')->with('siker', 'Új edzésterved elkészült.');
    }
}
