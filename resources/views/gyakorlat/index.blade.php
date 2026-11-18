@extends('layout')
@section('cim', 'Gyakorlatok')
@section('tartalom')
    <h1>Gyakorlatböngésző</h1>

    <form method="GET" class="szuro-forma">
        <input type="text" name="kereses" value="{{ $szuro['kereses'] ?? '' }}" placeholder="Keresés név szerint…">
        <select name="izomcsoport_id">
            <option value="">Minden izomcsoport</option>
            @foreach ($izomcsoportok as $i)
                <option value="{{ $i->izomcsoport_id }}" @selected(($szuro['izomcsoport_id'] ?? null) == $i->izomcsoport_id)>{{ $i->nev }}</option>
            @endforeach
        </select>
        <label class="checkbox-sor">
            <input type="checkbox" name="otthon" value="1" @checked(($szuro['otthon'] ?? false))>
            Csak otthon végezhető
        </label>
        <button type="submit" class="gomb">Szűrés</button>
    </form>

    <div class="gyakorlat-racs">
        @foreach ($gyakorlatok as $g)
            <a href="{{ route('gyakorlatok.show', $g->gyakorlat_id) }}" class="gyakorlat-kartya">
                <h3>{{ $g->nev }}</h3>
                <p class="gyakorlat-meta">
                    {{ $g->fo_izomcsoport ?? '—' }} · nehézség: {{ $g->nehezseg }}/5
                    @if ($g->otthon_vegezheto) · otthon is @endif
                </p>
            </a>
        @endforeach
    </div>

    {{ $gyakorlatok->links() }}
@endsection
