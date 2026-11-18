<?php

namespace Tests\Unit\Rehab;

use App\Enums\SzuresEredmeny;
use App\Services\Rehab\SzuresKiertekelo;
use PHPUnit\Framework\TestCase;

class SzuresKiertekeloTest extends TestCase
{
    public function test_piros_zaszlo_orvoshoz_kuld_akkor_is_ha_van_terhelesi_jelzo(): void
    {
        $e = (new SzuresKiertekelo())->ertekel('Derék', [
            ['vorosjelzes' => 0, 'panasz_tipus_id' => 13],
            ['vorosjelzes' => 1, 'panasz_tipus_id' => null],
        ]);

        $this->assertSame(SzuresEredmeny::ORVOSHOZ, $e->eredmeny);
        $this->assertSame([13], $e->panaszok);
    }

    public function test_panaszhoz_kotott_valasz_terhelesi_jelzo_duplikacio_nelkul(): void
    {
        $e = (new SzuresKiertekelo())->ertekel('Váll', [
            ['vorosjelzes' => false, 'panasz_tipus_id' => 16],
            ['vorosjelzes' => false, 'panasz_tipus_id' => 16],
            ['vorosjelzes' => false, 'panasz_tipus_id' => null],
        ]);

        $this->assertSame(SzuresEredmeny::TERHELESI_JELZO, $e->eredmeny);
        $this->assertSame([16], $e->panaszok);
        $this->assertStringContainsString('váll területére', $e->osszegzes);
    }

    public function test_semleges_valaszoknal_rendben(): void
    {
        $e = (new SzuresKiertekelo())->ertekel('Térd', [['vorosjelzes' => false, 'panasz_tipus_id' => null]]);

        $this->assertSame(SzuresEredmeny::RENDBEN, $e->eredmeny);
        $this->assertSame([], $e->panaszok);
    }
}
