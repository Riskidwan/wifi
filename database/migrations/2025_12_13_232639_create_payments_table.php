<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('pelanggan_id')->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
            $table->string('payment_method')->default('manual'); // manual, transfer, etc.
            $table->string('reference_number')->nullable(); // Nomor referensi pembayaran
            $table->decimal('amount_paid', 12, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // completed, pending, failed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};