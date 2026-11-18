<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisztraciosKerdoivRequest;
use App\Models\Felhasznalo;
use App\Models\PanaszTipus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisztracioController extends Controller
{
    public function mutat(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('terv.tervem');
        }

        return view('auth.regisztracio', [
            // Csak diagnózis / orvosi engedélyhez kötött panaszok — a terhelési jelzők (13–16)
            // a régiós szűrésből származnak (4. fázis), a regisztrációban nem választhatók.
            'diagnozisok' => PanaszTipus::where('eredet', 'diagnozis')->orderBy('panasz_tipus_id')->get(),
            'orvosiak' => PanaszTipus::where('eredet', 'orvosi_engedely')->orderBy('panasz_tipus_id')->get(),
        ]);
    }

    public function tarol(RegisztraciosKerdoivRequest $request): RedirectResponse
    {
        $adat = $request->validated();
        $panaszok = array_values(array_unique(array_merge(
            $adat['diagnozis_panaszok'] ?? [],
            $adat['orvosi_panaszok'] ?? [],
        )));
        $most = now();

        $felhasznalo = Felhasznalo::create([
            'email' => $adat['email'],
            'jelszo_hash' => Hash::make($adat['jelszo']),
            'nev' => $adat['nev'],
            'cel' => $adat['cel'],
            'szint' => $adat['szint'],
            'testalkat' => $adat['testalkat'] ?? null,
            'nem' => $adat['nem'] ?? null,
            'szuletesi_ev' => $adat['szuletesi_ev'] ?? null,
            'magassag_cm' => $adat['magassag_cm'] ?? null,
            'testsuly_kg' => $adat['testsuly_kg'] ?? null,
            'heti_edzesszam' => $adat['heti_edzesszam'],
            'otthon_edz' => $request->boolean('otthon_edz'),
            'gdpr_hozzajarulas' => $most,
            'egeszseg_hozzajarulas' => $panaszok !== [] ? $most : null,
        ]);

        if ($panaszok !== []) {
            $felhasznalo->panaszok()->attach($panaszok);
        }
        $felhasznalo->rehabModFrissit();

        Auth::login($felhasznalo);
        $request->session()->regenerate();

        return redirect()->route('terv.tervem')->with(
            'siker',
            'Sikeres regisztráció! Generálj egy edzéstervet, vagy válassz fókuszt az Avatár fülön.',
        );
    }
}
