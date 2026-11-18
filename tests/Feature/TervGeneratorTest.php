<?php

namespace Tests\Feature;

use App\Exceptions\EdzesTiltvaException;
use App\Models\Edzesterv;
use App\Models\Felhasznalo;
use App\Models\FelhasznaloSzures;
use App\Services\Terv\TervGenerator;
use Database\Seeders\TesztFelhasznaloSeeder;
use Database\Seeders\TorzsadatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Teljes út: migrációk + nézetek + seed → EdzesKapu → generátor → mentés. (SQLite :memory:) */
class TervGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TorzsadatSeeder::class, TesztFelhasznaloSeeder::class]);
    }

    private function fh(string $email): Felhasznalo
    {
        return Felhasznalo::where('email', $email)->firstOrFail();
    }

    public function test_v_gyakorlat_gyakorlatonkent_pontosan_egy_sor(): void
    {
        $this->assertSame(69, DB::table('v_gyakorlat')->count());
        $this->assertSame(69, DB::table('v_gyakorlat')->distinct()->count('gyakorlat_id'));
    }

    public function test_normal_terv_mentese_fokusszal(): void
    {
        $terv = app(TervGenerator::class)->general($this->fh('teszt.bulk@colley.hu'), [1, 5], mag: 3);

        $this->assertTrue($terv->aktiv);
        $this->assertFalse($terv->rehab);
        $this->assertCount(4, $terv->napok);
        $this->assertSame(1, $terv->fokuszok->firstWhere('pivot.fo_fokusz', 1)?->izomcsoport_id);
        $this->assertSame(0, DB::table('edzesnap_gyakorlat')->whereNull('ism_min')->whereNull('ido_mp')->count());
    }

    public function test_uj_terv_inaktivalja_a_regit(): void
    {
        $f = $this->fh('teszt.bulk@colley.hu');
        $regi = app(TervGenerator::class)->general($f, mag: 1);
        $uj = app(TervGenerator::class)->general($f, mag: 2);

        $this->assertFalse($regi->fresh()->aktiv);
        $this->assertTrue($uj->fresh()->aktiv);
        $this->assertSame(1, Edzesterv::where('felhasznalo_id', $f->felhasznalo_id)->where('aktiv', true)->count());
    }

    public function test_rehab_felhasznalo_rehab_tervet_kap_tiltott_es_edzotermi_gyakorlat_nelkul(): void
    {
        $f = $this->fh('teszt.rehab@colley.hu');
        $terv = app(TervGenerator::class)->general($f, mag: 5);

        $this->assertTrue($terv->rehab);
        $tiltott = DB::table('gyakorlat_panasz')->whereIn('panasz_tipus_id', [2, 4])
            ->where('viszony', 'tiltott')->pluck('gyakorlat_id')->all();
        foreach ($terv->napok as $nap) {
            foreach ($nap->tetelek as $t) {
                $this->assertNotContains($t->gyakorlat_id, $tiltott);
                $this->assertTrue($t->gyakorlat->otthon_vegezheto);
                $this->assertLessThanOrEqual(2, $t->gyakorlat->nehezseg);
            }
        }
    }

    public function test_piros_zaszlos_szuresnel_tiltva_es_semmi_nem_mentodik(): void
    {
        $f = $this->fh('teszt.bulk@colley.hu');
        FelhasznaloSzures::create([
            'felhasznalo_id' => $f->felhasznalo_id, 'testterulet_id' => 1,
            'eredmeny' => 'orvoshoz', 'kitoltve' => now(),
        ]);

        try {
            app(TervGenerator::class)->general($f);
            $this->fail('EdzesTiltvaException várt');
        } catch (EdzesTiltvaException $e) {
            $this->assertNotEmpty($e->dontes->indokok);
            $this->assertNotEmpty($e->dontes->teendok);
        }
        $this->assertSame(0, Edzesterv::count());
    }

    public function test_orvosi_engedelyhez_kotott_allapot_engedely_nelkul_tiltva(): void
    {
        $f = $this->fh('teszt.otthon@colley.hu');
        $f->panaszok()->attach(21);   // Szívritmuszavar
        $f->rehabModFrissit();

        $this->assertFalse($f->fresh()->rehab_mod, 'orvosi engedélyes állapot önmagában nem kapcsol rehab módot');
        $this->expectException(EdzesTiltvaException::class);
        app(TervGenerator::class)->general($f);
    }

    public function test_felhasznalo_torlese_cascade_minden_sajat_adatot_torol(): void
    {
        $f = $this->fh('teszt.rehab@colley.hu');
        app(TervGenerator::class)->general($f, mag: 1);
        $f->delete();

        foreach (['edzesterv', 'edzesnap', 'edzesnap_gyakorlat', 'felhasznalo_panasz'] as $t) {
            $this->assertSame(0, DB::table($t)->count(), $t);
        }
    }
}
