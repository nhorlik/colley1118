@extends('layout')
@section('cim', 'Avatár')
@section('tartalom')
    <h1>Avatár — izomcsoport-választó</h1>
    <p class="sugo">
        Kattints a modell egy testrészére, vagy jelöld be a listában — mindkettő ugyanazt a mezőt állítja.
        A modell lassan forog, hogy a hátsó izmokhoz (hát, trapéz, tricepsz, farizom stb.) is hozzáférj; húzással
        magad is forgathatod.
    </p>

    @include('partials.kapu-banner')

    @unless ($dontes->tiltva())
        <div class="avatar-elrendezes">
            <div id="avatar-canvas" class="avatar-canvas" role="img" aria-label="Interaktív 3D testmodell izomcsoport-választáshoz"></div>

            <form method="POST" action="{{ route('terv.general') }}" class="avatar-forma">
                @csrf
                @include('terv._fokusz-lista')
                <button type="submit" class="gomb gomb-elsodleges">Terv generálása a kiválasztott fókusszal</button>
            </form>
        </div>

        <script id="izomcsoport-adat" type="application/json">{{ Js::from($izomcsoportok) }}</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="{{ asset('js/avatar.js') }}" defer></script>
    @endunless
@endsection
