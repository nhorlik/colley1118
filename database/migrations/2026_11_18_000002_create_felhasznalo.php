<?php

/*
 * Felhasználó (fiók + regisztrációs kérdőív egy táblában) és egészségügyi panaszai.
 * A felhasznalo_panasz GDPR 9. cikk szerinti különleges adat: csak egeszseg_hozzajarulas mellett rögzíthető.
 * Minden felhasználói tábla CASCADE-del kötődik → egy DELETE lekezeli a törlési kérelmet.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('felhasznalo')) {
            Schema::create('felhasznalo', function (Blueprint $table) {
                $table->increments('felhasznalo_id');
                $table->string('email', 180)->unique();
                $table->string('jelszo_hash', 255);
                $table->string('nev', 100);
                $table->string('cel', 20);
                $table->string('szint', 12)->default('KEZDO');
                $table->string('testalkat', 12)->nullable();
                $table->string('nem', 12)->nullable();
                $table->smallInteger('szuletesi_ev')->nullable();
                $table->smallInteger('magassag_cm')->nullable();
                $table->decimal('testsuly_kg', 5, 2)->nullable();
                $table->tinyInteger('heti_edzesszam')->default(3);
                $table->boolean('otthon_edz')->default(false);
                $table->boolean('rehab_mod')->default(false);
                $table->dateTime('orvosi_engedely')->nullable();          // orvosi tisztázás visszaigazolva
                $table->dateTime('egeszseg_hozzajarulas')->nullable();    // GDPR 9. cikk – explicit hozzájárulás
                $table->dateTime('gdpr_hozzajarulas')->nullable();
                $table->string('remember_token', 100)->nullable();
                $table->dateTime('regisztralt')->useCurrent();
            });
        }

        if (! Schema::hasTable('felhasznalo_panasz')) {
            Schema::create('felhasznalo_panasz', function (Blueprint $table) {
                $table->unsignedInteger('felhasznalo_id');
                $table->unsignedInteger('panasz_tipus_id');
                $table->string('megjegyzes', 255)->nullable();     // pl. 'Szűrésből: Derék' – ebből tudjuk, mi törölhető újraszűréskor
                $table->dateTime('rogzitve')->useCurrent();

                $table->primary(['felhasznalo_id', 'panasz_tipus_id']);
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
                $table->foreign('panasz_tipus_id')->references('panasz_tipus_id')->on('panasz_tipus');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('felhasznalo_panasz');
        Schema::dropIfExists('felhasznalo');
    }
};
