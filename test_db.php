<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=wifi', 'root', '');
$stmt = $pdo->query('SELECT id, invoice_number, pelanggan_id, paket_nama, amount, total_amount, status FROM invoices ORDER BY id DESC LIMIT 5');
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($invoices, JSON_PRETTY_PRINT);
