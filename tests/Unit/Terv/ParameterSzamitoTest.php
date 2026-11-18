<?php

namespace Tests\Unit\Terv;

use App\Enums\Cel;
use App\Enums\Szint;
use App\Services\Terv\ParameterSzamito;
use PHPUnit\Framework\TestCase;
use Tests\Support\SeedJeloltek;

class ParameterSzamitoTest extends TestCase
{
    public function test_minden_seed_gyakorlatra_minden_kombinacioban_van_ismetles_vagy_ido(): void
    {
        $sz = new ParameterSzamito();
        [$pool] = SeedJeloltek::pool(Szint::PROFI, false, []);
        $db = 0;
        foreach (array_merge(...array_values($pool)) as $j) {
            foreach (Cel::cases() as $cel) {
                foreach (Szint::cases() as $szint) {
                    foreach ([true, false] as $rehab) {
                        $p = $sz->szamol($j, $cel, $szint, $rehab);
                        $this->assertTrue($p->ismMin !== null || $p->idoMp !== null, $j->kod);
                        $this->assertGreaterThanOrEqual(1, $p->szett);
                        if ($p->ismMin !== null) {
                            $this->assertGreaterThanOrEqual($p->ismMin, $p->ismMax);
                        }
                        $db++;
                    }
                }
            }
        }
        $this->assertSame(69 * 5 * 3 * 2, $db);
    }

    public function test_erofejlesztes_osszetett_gyakorlatnal_alacsony_ismetles_hosszu_piheno(): void
    {
        $j = SeedJeloltek::jelolt(['nehezseg' => 4, 'alapPihenoMp' => 120]);
        $p = (new ParameterSzamito())->szamol($j, Cel::ERO, Szint::HALADO, false);

        $this->assertSame([4, 6, 150, 4], [$p->ismMin, $p->ismMax, $p->pihenoMp, $p->szett]);
    }

    public function test_kezdo_eggyel_kevesebb_szett_de_legalabb_ketto(): void
    {
        $p = (new ParameterSzamito())->szamol(SeedJeloltek::jelolt(['alapSzett' => 3]), Cel::BULK, Szint::KEZDO, false);
        $this->assertSame(2, $p->szett);
    }

    public function test_rehab_tervben_terapias_gyakorlat_legfeljebb_ket_szett(): void
    {
        $j = SeedJeloltek::jelolt(['terapias' => true, 'alapSzett' => 3]);
        $this->assertSame(2, (new ParameterSzamito())->szamol($j, Cel::REHABILITACIO, Szint::KEZDO, true)->szett);
    }

    public function test_ido_alapu_kezdonel_csokken_es_ot_mp_re_kerekit(): void
    {
        $j = SeedJeloltek::jelolt(['tipus' => 'ido_alapu', 'alapIsmMin' => null, 'alapIsmMax' => null, 'alapIdoMp' => 45]);
        $p = (new ParameterSzamito())->szamol($j, Cel::BULK, Szint::KEZDO, false);

        $this->assertNull($p->ismMin);
        $this->assertSame(35, $p->idoMp);   // 45 × 0,75 = 33,75 → 35
    }

    public function test_kardio_idotartam_felso_korlatja(): void
    {
        $j = SeedJeloltek::jelolt(['tipus' => 'kardio', 'alapIsmMin' => null, 'alapIsmMax' => null, 'alapIdoMp' => 2400]);
        $p = (new ParameterSzamito())->szamol($j, Cel::FOGYAS, Szint::HALADO, false);

        $this->assertSame(ParameterSzamito::KARDIO_MAX_MP, $p->idoMp);
    }
}
