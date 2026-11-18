<?php

namespace App\Services\Terv;

use App\Enums\Viszony;
use App\Exceptions\EdzesTiltvaException;
use App\Exceptions\TervGeneralasiHiba;
use App\Models\Edzesterv;
use App\Models\Felhasznalo;
use App\Models\Gyakorlat;
use App\Services\Kapu\EdzesKapu;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * A projekt szellemi magja: UI nélkül is hívható szolgáltatás (web, API, artisan, teszt).
 *
 *   1. EdzesKapu  → TILTVA: kivétel indoklással | REHAB: rehab-sablonok | SZABAD: normál sablonok
 *   2. jelöltlista a v_valaszthato_gyakorlat nézetből (szint, otthoni eszköz, ellenjavallat szűrve)
 *   3. Valaszto   → pontozásos kiválasztás + paraméterezés (DB-független)
 *   4. TervValidator → független utóellenőrzés; hiba esetén semmi nem mentődik
 *   5. mentés egy tranzakcióban; a korábbi aktív terv aktiv = 0-ra vált
 */
final class TervGenerator
{
    public function __construct(
        private readonly EdzesKapu $kapu = new EdzesKapu(),
        private readonly Valaszto $valaszto = new Valaszto(),
        private readonly TervValidator $validator = new TervValidator(),
    ) {
    }

    /**
     * @param list<int> $fokuszIzmok kijelölt izomcsoport_id-k (pl. a 3D avatarról)
     * @param ?int      $foFokusz    a fő fókusz; ha nincs megadva, a lista első eleme
     * @param ?int      $mag         véletlen-mag (tesztekhez reprodukálható tervhez)
     *
     * @throws EdzesTiltvaException
     * @throws TervGeneralasiHiba
     */
    public function general(
        Felhasznalo $f,
        array $fokuszIzmok = [],
        ?int $foFokusz = null,
        ?int $mag = null,
        ?CarbonImmutable $kezdoDatum = null,
    ): Edzesterv {
        $dontes = $this->kapu->ellenoriz($f);
        if ($dontes->tiltva()) {
            throw new EdzesTiltvaException($dontes);
        }
        $rehabTerv = $dontes->csakRehab();

        $fokuszIzmok = array_values(array_unique(array_map('intval', $fokuszIzmok)));
        $foFokusz ??= $fokuszIzmok[0] ?? null;
        if ($foFokusz !== null && ! in_array($foFokusz, $fokuszIzmok, true)) {
            array_unshift($fokuszIzmok, $foFokusz);
        }

        $panaszok = $f->panaszok()->pluck('panasz_tipus.panasz_tipus_id')->map(fn ($id) => (int) $id)->all();
        $heti = (int) $f->heti_edzesszam;

        $vazlat = $this->valaszto->osszeallit(new ValasztoBemenet(
            napok: $rehabTerv ? Sablonok::rehab($heti) : Sablonok::normal($heti, $f->cel),
            jeloltekMintankent: $this->jeloltek($f),
            foFokusz: $foFokusz,
            fokuszok: $fokuszIzmok,
            panaszok: $panaszok,
            rehabFelhasznalo: (bool) $f->rehab_mod,
            rehabTerv: $rehabTerv,
            cel: $f->cel,
            szint: $f->szint,
            mag: $mag,
        ));

        $hibak = $this->validator->hibak($vazlat, new ValidaciosKorlatok(
            tiltottGyakorlatok: $this->tiltottGyakorlatok($f),
            otthonEdz: (bool) $f->otthon_edz,
            maxNehezseg: $rehabTerv ? min(Valaszto::REHAB_MAX_NEHEZSEG, $f->szint->maxNehezseg()) : $f->szint->maxNehezseg(),
        ));
        if ($hibak !== []) {
            throw new TervGeneralasiHiba($hibak);
        }

        return $this->ment($f, $vazlat, $fokuszIzmok, $foFokusz, $kezdoDatum ?? CarbonImmutable::today());
    }

    /** @return array<string, list<Jelolt>> mozgásminta => jelöltek */
    private function jeloltek(Felhasznalo $f): array
    {
        $ids = DB::table('v_valaszthato_gyakorlat')
            ->where('felhasznalo_id', $f->felhasznalo_id)
            ->pluck('gyakorlat_id');

        $gyakorlatok = Gyakorlat::query()
            ->whereIn('gyakorlat_id', $ids)
            ->with([
                'izomcsoportok',
                'panaszok' => fn ($q) => $q->where('gyakorlat_panasz.viszony', Viszony::AJANLOTT->value),
            ])
            ->orderBy('gyakorlat_id')
            ->get();

        $eredmeny = [];
        foreach ($gyakorlatok as $g) {
            $eredmeny[$g->mozgasminta][] = new Jelolt(
                id: $g->gyakorlat_id,
                kod: $g->kod,
                nev: $g->nev,
                tipus: $g->tipus->value,
                mozgasminta: $g->mozgasminta,
                nehezseg: $g->nehezseg,
                terapias: $g->terapias,
                otthonVegezheto: $g->otthon_vegezheto,
                izmok: $g->izomcsoportok->mapWithKeys(fn ($i) => [$i->izomcsoport_id => (bool) $i->pivot->elsodleges])->all(),
                ajanlottPanaszok: $g->panaszok->pluck('panasz_tipus_id')->map(fn ($id) => (int) $id)->all(),
                alapSzett: (int) $g->alap_szett,
                alapIsmMin: $g->alap_ism_min,
                alapIsmMax: $g->alap_ism_max,
                alapIdoMp: $g->alap_ido_mp,
                alapPihenoMp: (int) $g->alap_piheno_mp,
            );
        }

        return $eredmeny;
    }

    /** @return list<int> */
    private function tiltottGyakorlatok(Felhasznalo $f): array
    {
        return DB::table('gyakorlat_panasz as gp')
            ->join('felhasznalo_panasz as fp', 'fp.panasz_tipus_id', '=', 'gp.panasz_tipus_id')
            ->where('fp.felhasznalo_id', $f->felhasznalo_id)
            ->where('gp.viszony', Viszony::TILTOTT->value)
            ->distinct()
            ->pluck('gp.gyakorlat_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ment(Felhasznalo $f, TervVazlat $v, array $fokuszok, ?int $foFokusz, CarbonImmutable $kezdo): Edzesterv
    {
        return DB::transaction(function () use ($f, $v, $fokuszok, $foFokusz, $kezdo) {
            Edzesterv::where('felhasznalo_id', $f->felhasznalo_id)->where('aktiv', true)->update(['aktiv' => false]);

            $terv = Edzesterv::create([
                'felhasznalo_id' => $f->felhasznalo_id,
                'nev' => ($v->rehab ? 'Rehab terv – ' : 'Heti terv – ') . $kezdo->format('Y. m. d.'),
                'kezdo_datum' => $kezdo->toDateString(),
                'aktiv' => true,
                'rehab' => $v->rehab,
            ]);

            foreach ($fokuszok as $izom) {
                $terv->fokuszok()->attach($izom, ['fo_fokusz' => $izom === $foFokusz]);
            }

            foreach ($v->napok as $nap) {
                $napModel = $terv->napok()->create(['nap_sorszam' => $nap->sorszam, 'nev' => $nap->nev]);
                foreach ($nap->tetelek as $t) {
                    $napModel->tetelek()->create([
                        'gyakorlat_id' => $t->jelolt->id,
                        'sorrend' => $t->sorrend,
                        'szett_szam' => $t->parameterek->szett,
                        'ism_min' => $t->parameterek->ismMin,
                        'ism_max' => $t->parameterek->ismMax,
                        'ido_mp' => $t->parameterek->idoMp,
                        'piheno_mp' => $t->parameterek->pihenoMp,
                    ]);
                }
            }

            return $terv->load('napok.tetelek.gyakorlat', 'fokuszok');
        });
    }
}
