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
        Schema::create('pelanggans', function (Blueprint $table) {
    $table->id('id_pelanggan');
    $table->string('nama_pelanggan');
    $table->string('username_pppoe')->unique();
    $table->string('password_pppoe');
    $table->string('email')->nullable();
    $table->string('no_hp')->nullable();
    $table->text('alamat')->nullable();
    $table->string('status_akun')->default('active');
    $table->unsignedBigInteger('id_paket')->nullable(); // tanpa foreign key dulu
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
