<?php

namespace Tests\Unit\Terv;

use App\Services\Terv\Blokk;
use App\Services\Terv\PontozasiKontextus;
use App\Services\Terv\Pontozo;
use PHPUnit\Framework\TestCase;
use Tests\Support\SeedJeloltek;

class PontozoTest extends TestCase
{
    private function ktx(array $f = []): PontozasiKontextus
    {
        return new PontozasiKontextus(...($f + [
            'foFokusz' => null, 'fokuszok' => [], 'panaszok' => [], 'rehabFelhasznalo' => false,
            'blokk' => Blokk::ERO, 'hetiHasznalat' => [],
        ]));
    }

    public function test_semleges_jelolt_nulla_pont(): void
    {
        $this->assertSame(0.0, (new Pontozo())->pont(SeedJeloltek::jelolt(), $this->ktx()));
    }

    public function test_fo_fokusz_plusz_harom_es_egyeb_fokusz_plusz_ketto(): void
    {
        $j = SeedJeloltek::jelolt(['izmok' => [1 => true, 6 => false]]);
        $r = (new Pontozo())->reszletek($j, $this->ktx(['foFokusz' => 1, 'fokuszok' => [1, 6]]));

        $this->assertSame(['fo_fokusz' => 3.0, 'egyeb_fokusz' => 2.0], $r);
    }

    public function test_egyeb_fokusz_csak_egyszer_szamit(): void
    {
        $j = SeedJeloltek::jelolt(['izmok' => [4 => true, 6 => true]]);
        $p = (new Pontozo())->pont($j, $this->ktx(['foFokusz' => 1, 'fokuszok' => [1, 4, 6]]));

        $this->assertSame(2.0, $p);
    }

    public function test_rehab_ajanlott_plusz_negy_csak_sajat_panaszra(): void
    {
        $j = SeedJeloltek::jelolt(['ajanlottPanaszok' => [2, 4]]);
        $this->assertSame(4.0, (new Pontozo())->pont($j, $this->ktx(['panaszok' => [4]])));
        $this->assertSame(0.0, (new Pontozo())->pont($j, $this->ktx(['panaszok' => [7]])));
    }

    public function test_heti_ismetles_minus_negy_elofordulasonkent(): void
    {
        $j = SeedJeloltek::jelolt(['id' => 9]);
        $this->assertSame(-8.0, (new Pontozo())->pont($j, $this->ktx(['hetiHasznalat' => [9 => 2]])));
    }

    public function test_terapias_buntetes_csak_nem_rehab_felhasznalonal(): void
    {
        $j = SeedJeloltek::jelolt(['terapias' => true]);
        $this->assertSame(-1.5, (new Pontozo())->pont($j, $this->ktx()));
        $this->assertSame(0.0, (new Pontozo())->pont($j, $this->ktx(['rehabFelhasznalo' => true])));
    }

    public function test_nem_ero_jellegu_gyakorlat_ero_blokkban_minusz_egy(): void
    {
        $j = SeedJeloltek::jelolt(['tipus' => 'ido_alapu']);
        $this->assertSame(-1.0, (new Pontozo())->pont($j, $this->ktx()));
        $this->assertSame(0.0, (new Pontozo())->pont($j, $this->ktx(['blokk' => Blokk::CORE])));
    }
}
