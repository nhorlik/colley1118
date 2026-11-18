<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BejelentkezesController extends Controller
{
    public function mutat(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('terv.tervem');
        }

        return view('auth.bejelentkezes');
    }

    public function bejelentkeztet(Request $request): RedirectResponse
    {
        $adat = $request->validate([
            'email' => ['required', 'email'],
            'jelszo' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $adat['email'], 'password' => $adat['jelszo']], $request->boolean('maradjon_bejelentkezve'))) {
            return back()->withErrors(['email' => 'Hibás e-mail cím vagy jelszó.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('terv.tervem'));
    }

    public function kijelentkeztet(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
