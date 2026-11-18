<?php

namespace Tests\Unit\Terv;

use App\Enums\Cel;
use App\Enums\Szint;
use App\Services\Terv\Sablonok;
use App\Services\Terv\TervValidator;
use App\Services\Terv\TervVazlat;
use App\Services\Terv\Valaszto;
use App\Services\Terv\ValasztoBemenet;
use App\Services\Terv\ValidaciosKorlatok;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\SeedJeloltek;

/** A generátor-mag valós seed-adatokon, adatbázis nélkül. */
class ValasztoTest extends TestCase
{
    private function terv(Cel $cel, Szint $szint, int $heti, bool $otthon, array $panaszok, bool $rehab, array $fokusz = [], int $mag = 1): array
    {
        [$pool, $tiltott] = SeedJeloltek::pool($szint, $otthon, $panaszok);
        $v = (new Valaszto())->osszeallit(new ValasztoBemenet(
            $rehab ? Sablonok::rehab($heti) : Sablonok::normal($heti, $cel), $pool, $fokusz[0] ?? null, $fokusz,
            $panaszok, $rehab, $rehab, $cel, $szint, $mag,
        ));
        $hibak = (new TervValidator())->hibak($v, new ValidaciosKorlatok(
            $tiltott, $otthon, $rehab ? min(2, $szint->maxNehezseg()) : $szint->maxNehezseg(),
        ));

        return [$v, $hibak];
    }

    private function idk(TervVazlat $v): array
    {
        return array_map(fn ($t) => $t->jelolt->id, $v->osszesTetel());
    }

    public static function profilok(): array
    {
        $esetek = [];
        foreach (Cel::cases() as $cel) {
            foreach (Szint::cases() as $szint) {
                foreach ([1, 2, 3, 4, 5, 6] as $heti) {
                    foreach ([true, false] as $otthon) {
                        $rehab = $cel === Cel::REHABILITACIO;
                        $esetek["{$cel->value}/{$szint->value}/{$heti}/" . ($otthon ? 'otthon' : 'terem')] =
                            [$cel, $szint, $heti, $otthon, $rehab ? [2, 4] : [], $rehab];
                    }
                }
            }
        }

        return $esetek;
    }

    #[DataProvider('profilok')]
    public function test_minden_profilra_ervenyes_terv(Cel $cel, Szint $szint, int $heti, bool $otthon, array $panaszok, bool $rehab): void
    {
        [$v, $hibak] = $this->terv($cel, $szint, $heti, $otthon, $panaszok, $rehab);

        $this->assertSame([], $hibak);
        $this->assertCount($rehab ? min(3, $heti) : $heti, $v->napok);
    }

    public function test_azonos_maggal_azonos_terv(): void
    {
        [$a] = $this->terv(Cel::BULK, Szint::HALADO, 4, false, [], false, [1], 7);
        [$b] = $this->terv(Cel::BULK, Szint::HALADO, 4, false, [], false, [1], 7);

        $this->assertSame($this->idk($a), $this->idk($b));
    }

    public function test_rehab_tervben_nincs_kettesnel_nehezebb_gyakorlat(): void
    {
        [$v] = $this->terv(Cel::REHABILITACIO, Szint::PROFI, 3, false, [7], true);

        foreach ($v->osszesTetel() as $t) {
            $this->assertLessThanOrEqual(2, $t->jelolt->nehezseg, $t->jelolt->nev);
        }
    }

    public function test_tiltott_gyakorlat_nem_kerul_be_porckorongserv_eseten(): void
    {
        // 38 = guggolás rúddal, 43 = felhúzás — porckorongsérvnél (2) tiltott
        [$v] = $this->terv(Cel::BULK, Szint::PROFI, 6, false, [2], false);

        $this->assertEmpty(array_intersect([38, 43, 11, 32], $this->idk($v)));
    }

    public function test_mell_fokusz_eseten_a_nyomo_slotokba_mellgyakorlat_kerul(): void
    {
        [$v] = $this->terv(Cel::BULK, Szint::HALADO, 4, false, [], false, [1]);
        $felsoA = $v->napok[0]->tetelek;

        $this->assertArrayHasKey(1, $felsoA[0]->jelolt->izmok);
        $this->assertArrayHasKey(1, $felsoA[1]->jelolt->izmok);
    }

    public function test_haladonak_egyenlo_pontnal_nem_jut_kezdo_valtozat(): void
    {
        [$v] = $this->terv(Cel::BULK, Szint::HALADO, 4, false, [], false, [1]);

        $this->assertNotContains(6, $this->idk($v));   // 6 = térdelő fekvőtámasz
    }

    public function test_zsiregetesnel_minden_nap_pontosan_egy_kardio_blokkal_zarul(): void
    {
        foreach (Sablonok::normal(6, Cel::FOGYAS) as $nap) {
            $kardio = array_filter($nap->slotok, fn ($s) => $s->blokk->value === 'kardio');
            $this->assertCount(1, $kardio, $nap->nev);
        }
    }
}
