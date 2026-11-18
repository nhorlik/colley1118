<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('edzes_idopont')) {
            Schema::create('edzes_idopont', function (Blueprint $table) {
                $table->increments('edzes_idopont_id');
                $table->unsignedInteger('felhasznalo_id');
                $table->unsignedInteger('edzesnap_id');
                $table->date('datum');
                $table->time('idopont');
                $table->unsignedSmallInteger('emlekeztet_perc')->default(15);
                $table->boolean('ertesitve')->default(false);
                $table->dateTime('letrehozva')->useCurrent();

                $table->unique(['felhasznalo_id', 'datum'], 'uq_idopont_nap');   // naponta egy beidőzített edzés
                $table->foreign('felhasznalo_id')->references('felhasznalo_id')->on('felhasznalo')->cascadeOnDelete();
                $table->foreign('edzesnap_id')->references('edzesnap_id')->on('edzesnap')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edzes_idopont');
    }
};
