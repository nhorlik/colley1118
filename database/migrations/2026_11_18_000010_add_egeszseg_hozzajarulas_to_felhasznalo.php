<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('felhasznalo') && ! Schema::hasColumn('felhasznalo', 'egeszseg_hozzajarulas')) {
            Schema::table('felhasznalo', function (Blueprint $table) {
                $table->dateTime('egeszseg_hozzajarulas')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('felhasznalo') && Schema::hasColumn('felhasznalo', 'egeszseg_hozzajarulas')) {
            Schema::table('felhasznalo', function (Blueprint $table) {
                $table->dropColumn('egeszseg_hozzajarulas');
            });
        }
    }
};
