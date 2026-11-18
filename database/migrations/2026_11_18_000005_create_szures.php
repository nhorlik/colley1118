<?php

/*
 * Régiónkénti egészségügyi szűrőkérdőív (kérdések, válaszlehetőségek) és a felhasználói kitöltések.
 * Egy felhasználónak testterületenként egy aktuális szűrése van (uq_szures_terulet) — újrakitöltéskor felülíródik.
 * vorosjelzes = piros zászlós válasz → orvosi tisztázás; panasz_tipus_id = a válasz által beállított terhelési jelző.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('szures_kerdes')) {
            Schema::create('szures_kerdes', function (Blueprint $table) {
                $table->increments('szures_kerdes_id');
                $table->unsignedInteger('testterulet_id');
                $table->smallInteger('sorrend')->default(1);
                $table->string('kerdes', 255);
                $table->string('magyarazat', 255)->nullable();
                $table->boolean('aktiv')->default(true);

                $table->index(['testterulet_id', 'sorrend'], 'ix_kerdes_terulet');
                $table->foreign('testterulet_id')->references('testterulet_id')->on('testterulet')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('szures_valasz')) {
            Schema::create('szures_valasz', function (Blueprint $table) {
                $table->increments('szures_valasz_id');
                $table->unsignedInteger('szures_kerdes_id');
                $table->string('cimke', 160);
                $table->smallInteger('sorrend')->default(1);
                $table->boolean('vorosjelzes')->default(false);
                $table->unsignedInteger('panasz_tipus_id')->nullable();

                $table->foreign('szures_kerdes_id')->references('szures_kerdes_id')->on('szures_kerdes')->cascadeOnDelete();
                $table->foreign('panasz_tipus_id')->references('panasz_tipus_id')->on('panasz_tipus')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('felhasznalo_szures')) {
            Schema::create('felhasznalo_szures', function (Blueprint $table) {
                $table->increments('felhasznalo_szures_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->unsignedInteger('testterulet_id');
                $table->dateTime('kitoltve')->useCurrent();
                $table->string('eredmeny', 24);                   // rendben | terhelesi_jelzo | orvoshoz
                $table->string('osszegzes', 400)->nullable();

                $table->unique(['felhasznalo_id', 'testterulet_id'], 'uq_szures_terulet');
                $table->index(['felhasznalo_id', 'kitoltve'], 'ix_szures_felh');
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
                $table->foreign('testterulet_id')->references('testterulet_id')->on('testterulet');
            });
        }

        if (! Schema::hasTable('felhasznalo_szures_valasz')) {
            Schema::create('felhasznalo_szures_valasz', function (Blueprint $table) {
                $table->unsignedInteger('felhasznalo_szures_id');
                $table->unsignedInteger('szures_kerdes_id');
                $table->unsignedInteger('szures_valasz_id');

                $table->primary(['felhasznalo_szures_id', 'szures_kerdes_id']);
                $table->foreign('felhasznalo_szures_id')->references('felhasznalo_szures_id')->on('felhasznalo_szures')->cascadeOnDelete();
                $table->foreign('szures_kerdes_id')->references('szures_kerdes_id')->on('szures_kerdes');
                $table->foreign('szures_valasz_id')->references('szures_valasz_id')->on('szures_valasz');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('felhasznalo_szures_valasz');
        Schema::dropIfExists('felhasznalo_szures');
        Schema::dropIfExists('szures_valasz');
        Schema::dropIfExists('szures_kerdes');
    }
};
