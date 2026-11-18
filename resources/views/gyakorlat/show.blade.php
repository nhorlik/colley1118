@extends('layout')
@section('cim', $g->nev)
@section('tartalom')
    <a href="{{ route('gyakorlatok') }}" class="vissza">← Vissza a gyakorlatokhoz</a>
    <h1>{{ $g->nev }}</h1>

    <div class="profil-osszegzo">
        <strong>Típus:</strong> {{ $g->tipus }} ·
        <strong>Mozgásminta:</strong> {{ $g->mozgasminta ?? '—' }} ·
        <strong>Eszköz:</strong> {{ $g->eszkoz ?? 'nincs' }} ·
        <strong>Nehézség:</strong> {{ $g->nehezseg }}/5
        @if ($g->otthon_vegezheto) · <strong>Otthon is végezhető</strong> @endif
        @if ($g->terapias) · <span class="cimke cimke-rehab">Terápiás</span> @endif
    </div>

    <h2>Érintett izomcsoportok</h2>
    <ul>
        @foreach ($izomcsoportok as $i)
            <li>{{ $i->nev }} @if ($i->elsodleges) <span class="cimke">elsődleges</span> @endif</li>
        @endforeach
    </ul>

    <h2>Alap adagolás</h2>
    <p>
        {{ $g->alap_szett }} szett ×
        @if ($g->alap_ido_mp)
            {{ $g->alap_ido_mp }} mp
        @else
            {{ $g->alap_ism_min }}–{{ $g->alap_ism_max }} ismétlés
        @endif
        , {{ $g->alap_piheno_mp }} mp pihenővel.
    </p>
@endsection
