<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('
            CREATE TABLE `invoices` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `invoice_number` VARCHAR(255) NOT NULL UNIQUE,
                `pelanggan_id` BIGINT UNSIGNED NOT NULL,
                `paket_nama` VARCHAR(255) NOT NULL,
                `amount` DECIMAL(12,2) NOT NULL,
                `billing_period_start` DATE NOT NULL,
                `billing_period_end` DATE NOT NULL,
                `due_date` DATE NOT NULL,
                `status` ENUM("unpaid", "paid", "overdue") NOT NULL DEFAULT "unpaid",
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggans`(`id_pelanggan`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function down()
    {
        DB::statement('DROP TABLE IF EXISTS `invoices`');
    }
};