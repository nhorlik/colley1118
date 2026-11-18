<?php

namespace App\Console\Commands;

use App\Exceptions\EdzesTiltvaException;
use App\Exceptions\TervGeneralasiHiba;
use App\Models\Felhasznalo;
use App\Services\Terv\TervGenerator;
use Illuminate\Console\Command;

/**
 * A generátor UI nélküli hívása — bemutatáshoz és hibakereséshez.
 *   php artisan colley:terv teszt.bulk@colley.hu --fokusz=1 --fokusz=5 --mag=42
 */
class TervGeneralCommand extends Command
{
    protected $signature = 'colley:terv {email} {--fokusz=* : izomcsoport_id-k, az első a fő fókusz} {--mag= : véletlen-mag}';

    protected $description = 'Edzésterv generálása egy felhasználónak (UI nélkül) és kiírása';

    public function handle(TervGenerator $generator): int
    {
        $f = Felhasznalo::where('email', $this->argument('email'))->first();
        if (! $f) {
            $this->error('Nincs ilyen felhasználó.');

            return self::FAILURE;
        }

        try {
            $terv = $generator->general(
                $f,
                array_map('intval', $this->option('fokusz')),
                mag: $this->option('mag') !== null ? (int) $this->option('mag') : null,
            );
        } catch (EdzesTiltvaException $e) {
            $this->error($e->getMessage());
            foreach ($e->dontes->teendok as $t) {
                $this->line("  → {$t}");
            }

            return self::FAILURE;
        } catch (TervGeneralasiHiba $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("{$terv->nev}  (#{$terv->edzesterv_id}" . ($terv->rehab ? ', rehab' : '') . ')');
        foreach ($terv->napok as $nap) {
            $this->line("\n<comment>{$nap->nap_sorszam}. {$nap->nev}</comment>");
            $this->table(['#', 'Gyakorlat', 'Adag', 'Pihenő'], $nap->tetelek->map(fn ($t) => [
                $t->sorrend,
                $t->gyakorlat->nev,
                $t->idoAlapu() ? "{$t->szett_szam} × {$t->ido_mp} mp" : "{$t->szett_szam} × {$t->ism_min}–{$t->ism_max}",
                "{$t->piheno_mp} mp",
            ])->all());
        }

        return self::SUCCESS;
    }
}
