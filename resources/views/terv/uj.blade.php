@extends('layout')
@section('cim', 'Új terv')
@section('tartalom')
    <h1>Új terv</h1>

    @include('partials.kapu-banner')

    <div class="profil-osszegzo">
        <strong>Cél:</strong> {{ auth()->user()->cel->cimke() }} ·
        <strong>Szint:</strong> {{ auth()->user()->szint->cimke() }} ·
        <strong>Heti edzésszám:</strong> {{ auth()->user()->heti_edzesszam }} ·
        <strong>Helyszín:</strong> {{ auth()->user()->otthon_edz ? 'Otthon' : 'Edzőterem' }}
    </div>

    @unless ($dontes->tiltva())
        <form method="POST" action="{{ route('terv.general') }}">
            @csrf
            <h2>Fókuszizmok (nem kötelező)</h2>
            @include('terv._fokusz-lista')
            <button type="submit" class="gomb gomb-elsodleges">Terv generálása</button>
        </form>
        <p class="sugo">A fókuszizmokat a 3D modellen is kiválaszthatod: <a href="{{ route('avatar') }}">Avatár</a>.</p>
    @endunless
@endsection
