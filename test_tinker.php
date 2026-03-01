dump(\App\Models\Invoice::latest()->limit(5)->get(['id', 'invoice_number', 'pelanggan_id', 'paket_nama', 'amount', 'total_amount', 'status'])->toArray());
