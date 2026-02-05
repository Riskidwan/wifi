<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('pakets', function (Blueprint $table) {
        $table->boolean('diskon_aktif')->default(false)->after('diskon_persen');
        $table->boolean('ppn_aktif')->default(false)->change(); // pastikan tipe boolean
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            //
        });
    }
};
