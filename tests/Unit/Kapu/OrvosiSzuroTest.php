<?php

namespace Tests\Unit\Kapu;

use App\Enums\KapuAllapot;
use App\Enums\SzuresEredmeny;
use App\Services\Kapu\OrvosiSzuro;
use App\Services\Kapu\SzuresAllapot;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class OrvosiSzuroTest extends TestCase
{
    private function szures(SzuresEredmeny $e, string $mikor = '2026-10-05 09:00'): SzuresAllapot
    {
        return new SzuresAllapot('Derék', $e, new DateTimeImmutable($mikor));
    }

    public function test_panasz_nelkul_szabad(): void
    {
        $d = (new OrvosiSzuro())->dont([], [], null, false);

        $this->assertSame(KapuAllapot::SZABAD, $d->allapot);
        $this->assertSame([], $d->indokok);
    }

    public function test_rehab_modban_rehab(): void
    {
        $d = (new OrvosiSzuro())->dont([], [$this->szures(SzuresEredmeny::TERHELESI_JELZO)], null, true);

        $this->assertSame(KapuAllapot::REHAB, $d->allapot);
        $this->assertNotEmpty($d->indokok);
    }

    public function test_orvosi_allapot_engedely_nelkul_tiltva_indoklassal_es_teendovel(): void
    {
        $d = (new OrvosiSzuro())->dont(['Szívritmuszavar'], [], null, false);

        $this->assertTrue($d->tiltva());
        $this->assertStringContainsString('Szívritmuszavar', $d->indokok[0]);
        $this->assertNotEmpty($d->teendok);
    }

    public function test_orvosi_allapot_engedellyel_tovabbengedi(): void
    {
        $d = (new OrvosiSzuro())->dont(['Cukorbetegség'], [], new DateTimeImmutable('2026-10-01'), false);

        $this->assertSame(KapuAllapot::SZABAD, $d->allapot);
    }

    public function test_piros_zaszlos_szures_tiltva(): void
    {
        $d = (new OrvosiSzuro())->dont([], [$this->szures(SzuresEredmeny::ORVOSHOZ)], null, true);

        $this->assertTrue($d->tiltva());
        $this->assertStringContainsString('derék', $d->indokok[0]);
    }

    public function test_szures_utani_engedely_feloldja_a_piros_zaszlot(): void
    {
        $d = (new OrvosiSzuro())->dont([], [$this->szures(SzuresEredmeny::ORVOSHOZ, '2026-10-05 09:00')],
            new DateTimeImmutable('2026-10-05 11:26'), true);

        $this->assertSame(KapuAllapot::REHAB, $d->allapot);
    }

    public function test_szures_elotti_regi_engedely_nem_oldja_fel_az_uj_piros_zaszlot(): void
    {
        $d = (new OrvosiSzuro())->dont([], [$this->szures(SzuresEredmeny::ORVOSHOZ, '2026-10-05 09:00')],
            new DateTimeImmutable('2026-09-01'), false);

        $this->assertTrue($d->tiltva());
    }
}
