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
        Schema::create('pakets', function (Blueprint $table) {
            $table->id();
    $table->string('nama_paket'); // harus unik & cocok dengan nama PPPoE Profile di MikroTik
    $table->string('kecepatan')->nullable(); // contoh: "10M/5M"
    $table->bigInteger('harga')->default(0);
    $table->text('keterangan')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakets');
    }
};
