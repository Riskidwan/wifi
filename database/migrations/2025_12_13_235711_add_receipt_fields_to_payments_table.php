<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number')->nullable(); // Nomor struk
            $table->string('cashier_name')->default('Admin'); // Nama kasir
            $table->string('payment_proof')->nullable(); // Bukti transfer (opsional)
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'cashier_name', 'payment_proof']);
        });
    }
};