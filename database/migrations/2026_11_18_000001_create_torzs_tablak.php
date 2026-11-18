<?php

/*
 * Törzsadat-táblák: testterület, izomcsoport, panasztípus, gyakorlat és kapcsolótábláik.
 * Minden PK increments() / minden FK unsignedInteger() — INT UNSIGNED mindkét oldalon (errno 150 ellen).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('testterulet')) {
            Schema::create('testterulet', function (Blueprint $table) {
                $table->increments('testterulet_id');
                $table->string('kod', 48)->unique();
                $table->string('nev', 60);
                $table->string('leiras', 255)->nullable();
                $table->smallInteger('sorrend')->default(0);
                $table->boolean('aktiv')->default(true);
            });
        }

        if (! Schema::hasTable('izomcsoport')) {
            Schema::create('izomcsoport', function (Blueprint $table) {
                $table->increments('izomcsoport_id');
                $table->string('kod', 24)->unique();
                $table->string('nev', 60);
                $table->string('testoldal', 12)->default('mindketto');
                $table->string('mesh_nev', 48)->unique();          // = 3D modell felület-neve (data-mesh)
                $table->unsignedInteger('testterulet_id')->nullable();
                $table->smallInteger('sorrend')->default(0);

                $table->foreign('testterulet_id')->references('testterulet_id')->on('testterulet')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('panasz_tipus')) {
            Schema::create('panasz_tipus', function (Blueprint $table) {
                $table->increments('panasz_tipus_id');
                $table->string('kod', 48)->unique();
                $table->string('nev', 80);
                $table->string('terulet', 12);
                $table->string('eredet', 20)->default('diagnozis');
            });
        }

        if (! Schema::hasTable('gyakorlat')) {
            Schema::create('gyakorlat', function (Blueprint $table) {
                $table->increments('gyakorlat_id');
                $table->string('kod', 40)->unique();
                $table->string('nev', 120);
                $table->string('tipus', 12);
                $table->string('mozgasminta', 20);
                $table->string('eszkoz', 20);
                $table->boolean('otthon_vegezheto')->default(false);
                $table->tinyInteger('nehezseg')->default(2);
                $table->boolean('terapias')->default(false);
                $table->tinyInteger('alap_szett')->default(3);
                $table->smallInteger('alap_ism_min')->nullable();
                $table->smallInteger('alap_ism_max')->nullable();
                $table->smallInteger('alap_ido_mp')->nullable();
                $table->smallInteger('alap_piheno_mp')->default(60);
                $table->string('leiras', 400)->nullable();
                $table->boolean('aktiv')->default(true);

                $table->index(['mozgasminta', 'aktiv'], 'ix_gyakorlat_minta');
            });
        }

        if (! Schema::hasTable('gyakorlat_izomcsoport')) {
            Schema::create('gyakorlat_izomcsoport', function (Blueprint $table) {
                $table->unsignedInteger('gyakorlat_id');
                $table->unsignedInteger('izomcsoport_id');
                $table->boolean('elsodleges')->default(true);

                $table->primary(['gyakorlat_id', 'izomcsoport_id']);
                $table->index(['izomcsoport_id', 'elsodleges'], 'ix_gyizom_izom');
                $table->foreign('gyakorlat_id')->references('gyakorlat_id')->on('gyakorlat')->cascadeOnDelete();
                $table->foreign('izomcsoport_id')->references('izomcsoport_id')->on('izomcsoport')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('gyakorlat_panasz')) {
            Schema::create('gyakorlat_panasz', function (Blueprint $table) {
                $table->unsignedInteger('gyakorlat_id');
                $table->unsignedInteger('panasz_tipus_id');
                $table->string('viszony', 10);                    // tiltott | ajanlott
                $table->string('indoklas', 255)->nullable();
                $table->string('helyette', 255)->nullable();

                $table->primary(['gyakorlat_id', 'panasz_tipus_id']);
                $table->index(['panasz_tipus_id', 'viszony'], 'ix_gyp_panasz');
                $table->foreign('gyakorlat_id')->references('gyakorlat_id')->on('gyakorlat')->cascadeOnDelete();
                $table->foreign('panasz_tipus_id')->references('panasz_tipus_id')->on('panasz_tipus')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gyakorlat_panasz');
        Schema::dropIfExists('gyakorlat_izomcsoport');
        Schema::dropIfExists('gyakorlat');
        Schema::dropIfExists('panasz_tipus');
        Schema::dropIfExists('izomcsoport');
        Schema::dropIfExists('testterulet');
    }
};
