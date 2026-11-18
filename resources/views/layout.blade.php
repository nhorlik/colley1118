<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('cim', 'Colley') — Colley</title>
    <link rel="stylesheet" href="{{ asset('css/colley.css') }}">
    @stack('fej')
</head>
<body>
<header class="fejlec">
    <div class="fejlec-belso">
        <a href="{{ route(auth()->check() ? 'terv.tervem' : 'login') }}" class="logo">Colley</a>

        @auth
            <nav class="fonav">
                <div class="fonav-gorgeto">
                    <a href="{{ route('terv.tervem') }}" class="{{ request()->routeIs('terv.tervem') ? 'aktiv' : '' }}">Tervem</a>
                    <span class="hamarosan" title="Hamarosan elérhető (4. fázis)">Rehabilitáció</span>
                    <a href="{{ route('terv.uj') }}" class="{{ request()->routeIs('terv.uj') ? 'aktiv' : '' }}">Új terv</a>
                    <span class="hamarosan" title="Hamarosan elérhető (4. fázis)">Naptár</span>
                    <a href="{{ route('gyakorlatok') }}" class="{{ request()->routeIs('gyakorlatok*') ? 'aktiv' : '' }}">Gyakorlatok</a>
                    <a href="{{ route('avatar') }}" class="{{ request()->routeIs('avatar') ? 'aktiv' : '' }}">Avatár</a>
                </div>
            </nav>
            <div class="fejlec-jobb">
                <span class="felh-nev">{{ auth()->user()->nev }}</span>
                <form method="POST" action="{{ route('kijelentkezes') }}">
                    @csrf
                    <button type="submit" class="gomb gomb-szoveges">Kijelentkezés</button>
                </form>
            </div>
        @endauth
    </div>
</header>

<main class="tartalom">
    @if (session('siker'))
        <div class="uzenet uzenet-siker">{{ session('siker') }}</div>
    @endif

    @if ($errors->any())
        <div class="uzenet uzenet-hiba">
            <ul>
                @foreach ($errors->all() as $hiba)
                    <li>{{ $hiba }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('tartalom')
</main>
</body>
</html>
