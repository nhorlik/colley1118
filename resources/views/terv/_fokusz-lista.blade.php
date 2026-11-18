{{-- Várja: $izomcsoportok (App\Models\Izomcsoport kollekció, sorrend szerint) --}}
<div class="izom-lista" id="izom-lista">
    @foreach ($izomcsoportok as $i)
        <label class="izom-checkbox" data-mesh="{{ $i->mesh_nev }}">
            <input
                type="checkbox"
                name="fokusz_izmok[]"
                value="{{ $i->izomcsoport_id }}"
                data-mesh="{{ $i->mesh_nev }}"
                data-nev="{{ $i->nev }}"
            >
            {{ $i->nev }}
        </label>
    @endforeach
</div>
