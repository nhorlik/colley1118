@extends('layout')
@section('cim', 'Regisztráció')
@section('tartalom')
    <div class="auth-doboz auth-doboz-szeles">
        <h1>Regisztráció</h1>
        <form method="POST" action="{{ route('regisztracio.kuld') }}" class="auth-forma">
            @csrf

            <fieldset>
                <legend>Fiók</legend>
                <label>
                    E-mail cím
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>
                <label>
                    Jelszó
                    <input type="password" name="jelszo" required minlength="8">
                </label>
                <label>
                    Jelszó megerősítése
                    <input type="password" name="jelszo_confirmation" required minlength="8">
                </label>
                <label>
                    Név
                    <input type="text" name="nev" value="{{ old('nev') }}" required>
                </label>
            </fieldset>

            <fieldset>
                <legend>Edzési profil</legend>
                <label>
                    Cél
                    <select name="cel" required>
                        <option value="">Válassz…</option>
                        @foreach (App\Enums\Cel::cases() as $c)
                            <option value="{{ $c->value }}" @selected(old('cel') === $c->value)>{{ $c->cimke() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Szint
                    <select name="szint" required>
                        <option value="">Válassz…</option>
                        @foreach (App\Enums\Szint::cases() as $s)
                            <option value="{{ $s->value }}" @selected(old('szint') === $s->value)>{{ $s->cimke() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Heti edzésszám
                    <select name="heti_edzesszam" required>
                        @for ($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected(old('heti_edzesszam') == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </label>
                <label class="checkbox-sor">
                    <input type="checkbox" name="otthon_edz" value="1" @checked(old('otthon_edz'))>
                    Otthon edzek (eszköz nélkül / minimális eszközzel)
                </label>
            </fieldset>

            <fieldset>
                <legend>Testi adatok (nem kötelező)</legend>
                <label>
                    Testalkat
                    <select name="testalkat">
                        <option value="">Nem tudom / kihagyom</option>
                        <option value="EKTOMORF" @selected(old('testalkat') === 'EKTOMORF')>Ektomorf</option>
                        <option value="MEZOMORF" @selected(old('testalkat') === 'MEZOMORF')>Mezomorf</option>
                        <option value="ENDOMORF" @selected(old('testalkat') === 'ENDOMORF')>Endomorf</option>
                    </select>
                </label>
                <label>
                    Nem
                    <select name="nem">
                        <option value="">Nem adom meg</option>
                        <option value="ferfi" @selected(old('nem') === 'ferfi')>Férfi</option>
                        <option value="no" @selected(old('nem') === 'no')>Nő</option>
                        <option value="egyeb" @selected(old('nem') === 'egyeb')>Egyéb</option>
                    </select>
                </label>
                <label>
                    Születési év
                    <input type="number" name="szuletesi_ev" value="{{ old('szuletesi_ev') }}" min="1920" max="{{ now()->year }}">
                </label>
                <label>
                    Magasság (cm)
                    <input type="number" name="magassag_cm" value="{{ old('magassag_cm') }}" min="100" max="250">
                </label>
                <label>
                    Testsúly (kg)
                    <input type="number" step="0.1" name="testsuly_kg" value="{{ old('testsuly_kg') }}" min="30" max="300">
                </label>
            </fieldset>

            <fieldset>
                <legend>Diagnosztizált állapotok</legend>
                <p class="sugo">Jelöld, ha orvos ezt korábban már megállapította nálad — ez segít a biztonságos terv összeállításában.</p>
                @foreach ($diagnozisok as $p)
                    <label class="checkbox-sor">
                        <input type="checkbox" name="diagnozis_panaszok[]" value="{{ $p->panasz_tipus_id }}"
                            @checked(in_array($p->panasz_tipus_id, old('diagnozis_panaszok', [])))>
                        {{ $p->nev }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>Egészségügyi kérdések</legend>
                <p class="sugo">Ezek a tünetek/állapotok orvosi engedélyhez köthetik az edzéstervet.</p>
                @foreach ($orvosiak as $p)
                    <label class="checkbox-sor">
                        <input type="checkbox" name="orvosi_panaszok[]" value="{{ $p->panasz_tipus_id }}"
                            @checked(in_array($p->panasz_tipus_id, old('orvosi_panaszok', [])))>
                        {{ $p->nev }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>Hozzájárulások</legend>
                <label class="checkbox-sor">
                    <input type="checkbox" name="egeszseg_hozzajarulas" value="1" @checked(old('egeszseg_hozzajarulas'))>
                    Hozzájárulok a fent megadott egészségügyi adataim (különleges adatkategória) kezeléséhez a
                    személyre szabott edzésterv összeállítása céljából. Csak akkor kötelező, ha fentebb bármilyen
                    állapotot/tünetet megjelöltél.
                </label>
                <label class="checkbox-sor">
                    <input type="checkbox" name="gdpr_hozzajarulas" value="1" required @checked(old('gdpr_hozzajarulas'))>
                    Elfogadom az adatkezelési tájékoztatót és hozzájárulok a fiókadataim kezeléséhez. *
                </label>
            </fieldset>

            <button type="submit" class="gomb gomb-elsodleges">Regisztráció</button>
        </form>
        <p class="sugo">Már van fiókod? <a href="{{ route('login') }}">Jelentkezz be.</a></p>
    </div>
@endsection
