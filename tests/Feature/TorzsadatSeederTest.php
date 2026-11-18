<?php

namespace Tests\Feature;

use Database\Seeders\Support\SeederValidator;
use Database\Seeders\TorzsadatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class TorzsadatSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ujrafuttatva_sem_duplikal(): void
    {
        $this->seed(TorzsadatSeeder::class);
        $this->seed(TorzsadatSeeder::class);

        foreach (array_keys(TorzsadatSeeder::TABLAK) as $t) {
            $this->assertSame(count(TorzsadatSeeder::adat($t)), DB::table($t)->count(), $t);
        }
    }

    public function test_panasz_tipus_javitas_kod_es_nev_osszhangban(): void
    {
        $this->seed(TorzsadatSeeder::class);

        $this->assertSame('terhelesi_jelzo', DB::table('panasz_tipus')->where('kod', 'HAJLITAS_ERZEKENY')->value('eredet'));
        $this->assertSame('Szívritmuszavar', DB::table('panasz_tipus')->where('kod', 'SZIVRITMUSZAVAR')->value('nev'));
    }

    public function test_validator_minden_hibat_egyszerre_jelez(): void
    {
        $v = (new SeederValidator())->ellenoriz('testterulet', [
            ['testterulet_id' => 99, 'kod' => 'X', 'nincs_ilyen' => 1],   // nev hiányzik + ismeretlen oszlop
        ]);

        try {
            $v->dobjHaHibas();
            $this->fail('RuntimeException várt');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString("ismeretlen oszlop 'nincs_ilyen'", $e->getMessage());
            $this->assertStringContainsString("'nev' kötelező", $e->getMessage());
        }
    }
}
