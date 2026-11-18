@extends('layout')
@section('cim', 'Tervem')
@section('tartalom')
    <h1>Tervem</h1>

    @include('partials.kapu-banner')

    @if (session('kapu_hiba'))
        <div class="banner banner-tiltva">
            <strong>{{ session('kapu_hiba') }}</strong>
            @if (session('kapu_teendok'))
                <ul>
                    @foreach (session('kapu_teendok') as $t)
                        <li>{{ $t }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if (session('terv_hiba'))
        <div class="banner banner-tiltva">{{ session('terv_hiba') }}</div>
    @endif

    @if ($terv)
        <div class="terv-fejlec">
            <h2>{{ $terv->nev }}</h2>
            @if ($terv->rehab)
                <span class="cimke cimke-rehab">Rehabilitációs terv</span>
            @endif
        </div>

        @foreach ($terv->napok as $nap)
            <section class="edzesnap-kartya">
                <h3>{{ $nap->nap_sorszam }}. nap — {{ $nap->nev }}</h3>
                <table class="gyakorlat-tablazat">
                    <thead>
                        <tr><th>#</th><th>Gyakorlat</th><th>Adag</th><th>Pihenő</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($nap->tetelek as $t)
                            <tr>
                                <td>{{ $t->sorrend }}</td>
                                <td><a href="{{ route('gyakorlatok.show', $t->gyakorlat_id) }}">{{ $t->gyakorlat->nev }}</a></td>
                                <td>
                                    @if ($t->idoAlapu())
                                        {{ $t->szett_szam }} × {{ $t->ido_mp }} mp
                                    @else
                                        {{ $t->szett_szam }} × {{ $t->ism_min }}–{{ $t->ism_max }} ism.
                                    @endif
                                </td>
                                <td>{{ $t->piheno_mp }} mp</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach

        @unless ($dontes->tiltva())
            <a href="{{ route('terv.uj') }}" class="gomb">Új terv generálása</a>
        @endunless
    @else
        <p>Még nincs edzésterved.</p>
        @unless ($dontes->tiltva())
            <a href="{{ route('terv.uj') }}" class="gomb gomb-elsodleges">Első terv elkészítése</a>
        @endunless
    @endif
@endsection
