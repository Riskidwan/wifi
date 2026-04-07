<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('pelanggans');
foreach ($columns as $index => $column) {
    echo "[$index] '$column' (HEX: " . bin2hex($column) . ")\n";
}

$dbName = DB::connection()->getDatabaseName();
echo "Database: " . $dbName . "\n";
