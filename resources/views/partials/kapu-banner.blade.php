{{-- Várja: $dontes (App\Services\Kapu\KapuDontes) --}}
@if ($dontes->tiltva())
    <div class="banner banner-tiltva">
        <strong>A terv generálása jelenleg nem elérhető.</strong>
        <ul>
            @foreach ($dontes->indokok as $i)
                <li>{{ $i }}</li>
            @endforeach
        </ul>
        @if ($dontes->teendok)
            <p class="banner-teendo">Teendő:</p>
            <ul>
                @foreach ($dontes->teendok as $t)
                    <li>{{ $t }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@elseif ($dontes->csakRehab())
    <div class="banner banner-info">
        <strong>Rehabilitációs mód aktív.</strong>
        <p>{{ $dontes->uzenet() }}</p>
    </div>
@endif
