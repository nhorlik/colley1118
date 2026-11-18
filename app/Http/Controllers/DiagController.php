<?php

namespace App\Http\Controllers;

use Database\Seeders\TorzsadatSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Verziószámmal jelzett diagnosztika — egy pillantásra:
 *  - melyik kódverzió fut,
 *  - lefutott-e minden várt migráció,
 *  - a törzsadatok sorszáma egyezik-e a seed-fájlokkal (lefutott-e a seeder),
 *  - a nézetek léteznek-e és jó sorszámot adnak-e,
 *  - a kulcsfájlok a helyükön vannak-e (módosítási idő + rövid hash).
 */
final class DiagController
{
    public function __invoke(): JsonResponse
    {
        abort_unless(config('colley.diag'), 404);

        $migraciok = $this->migraciok();
        $torzs = $this->torzsadat();
        $nezetek = $this->nezetek();
        $fajlok = $this->fajlok();

        $ok = $migraciok['ok']
            && collect($torzs)->every(fn ($t) => $t['ok'])
            && collect($nezetek)->every(fn ($n) => $n['ok'])
            && collect($fajlok)->every(fn ($f) => $f['letezik']);

        return response()->json([
            'ok' => $ok,
            'verzio' => config('colley.verzio'),
            'kornyezet' => app()->environment(),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'adatbazis' => DB::getDriverName(),
            'idopont' => now()->toIso8601String(),
            'migraciok' => $migraciok,
            'torzsadat' => $torzs,
            'nezetek' => $nezetek,
            'fajlok' => $fajlok,
        ], $ok ? 200 : 500, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function migraciok(): array
    {
        $vart = collect(glob(database_path('migrations/*.php')))
            ->map(fn ($f) => basename($f, '.php'))->sort()->values();
        $lefutott = Schema::hasTable('migrations')
            ? DB::table('migrations')->pluck('migration')
            : collect();
        $hianyzo = $vart->diff($lefutott)->values();

        return ['ok' => $hianyzo->isEmpty(), 'vart' => $vart->count(), 'lefutott' => $lefutott->count(), 'hianyzo' => $hianyzo];
    }

    private function torzsadat(): array
    {
        $eredmeny = [];
        foreach (array_keys(TorzsadatSeeder::TABLAK) as $tabla) {
            $vart = count(TorzsadatSeeder::adat($tabla));
            $tenyleges = Schema::hasTable($tabla) ? DB::table($tabla)->count() : null;
            $eredmeny[$tabla] = ['ok' => $tenyleges === $vart, 'vart' => $vart, 'tenyleges' => $tenyleges];
        }

        return $eredmeny;
    }

    private function nezetek(): array
    {
        $eredmeny = [];
        foreach (['v_gyakorlat' => 'gyakorlat_id', 'v_valaszthato_gyakorlat' => 'felhasznalo_id'] as $nezet => $oszlop) {
            try {
                $sor = DB::table($nezet)->count();
                $kulonbozo = DB::table($nezet)->distinct()->count($nezet === 'v_gyakorlat' ? 'gyakorlat_id' : $oszlop);
                // v_gyakorlat: gyakorlatonként pontosan egy sor kell
                $ok = $nezet !== 'v_gyakorlat' || $sor === $kulonbozo;
                $eredmeny[$nezet] = ['ok' => $ok, 'sorok' => $sor];
            } catch (Throwable $e) {
                $eredmeny[$nezet] = ['ok' => false, 'hiba' => $e->getMessage()];
            }
        }

        return $eredmeny;
    }

    private function fajlok(): array
    {
        $eredmeny = [];
        foreach (config('colley.diag_fajlok') as $rel) {
            $ut = base_path($rel);
            $eredmeny[$rel] = is_file($ut)
                ? ['letezik' => true, 'modositva' => date('Y-m-d H:i:s', filemtime($ut)), 'sha1' => substr(sha1_file($ut), 0, 10)]
                : ['letezik' => false];
        }

        return $eredmeny;
    }
}
