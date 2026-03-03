<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\InterfaceController;
use App\Http\Controllers\PPPoEController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UseractiveController;
use App\Http\Controllers\PaketInternetController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\KategoriKeuanganController;
use App\Http\Controllers\DashboardPelangganController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman error kustom

// Login (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// 🔒 Rute untuk pengguna yang sudah login
Route::middleware(['auth'])->group(function () {

    // 🔁 Redirect root ke dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });


    // 👤 Manajemen Admin (dalam AdminAuthController)
    Route::get('/admin', [AdminAuthController::class, 'index'])->name('admin.index');
    Route::get('/admin/create', [AdminAuthController::class, 'create'])->name('admin.create');
    Route::post('/admin', [AdminAuthController::class, 'store'])->name('admin.store');
    Route::get('/admin/{id}/edit', [AdminAuthController::class, 'edit'])->name('admin.edit');
    Route::put('/admin/{id}', [AdminAuthController::class, 'update'])->name('admin.update');
    Route::delete('/admin/{id}', [AdminAuthController::class, 'destroy'])->name('admin.destroy');

    // 📊 Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/cpu', [DashboardController::class, 'cpu'])->name('dashboard.cpu');
    Route::get('dashboard/load', [DashboardController::class, 'load'])->name('dashboard.load');
    Route::get('dashboard/uptime', [DashboardController::class, 'uptime'])->name('dashboard.uptime');
    Route::get('dashboard/{traffic}', [DashboardController::class, 'traffic'])->name('dashboard.traffic');

    // 🌐 Interface
    Route::get('/interface', [InterfaceController::class, 'index'])->name('interface.index');
    Route::get('/interface/traffic/{interface}', [InterfaceController::class, 'traffic'])->name('interface.traffic');

    // 🔐 PPPoE
    Route::get('pppoe/secret', [PPPoEController::class, 'secret'])->name('pppoe.secret');
    Route::get('pppoe/secret/active', [PPPoEController::class, 'active'])->name('pppoe.active');
    Route::post('pppoe/secret/add', [PPPoEController::class, 'add'])->name('pppoe.add');
    Route::get('pppoe/secret/edit/{id}', [PPPoEController::class, 'edit'])->name('pppoe.edit');
    Route::post('pppoe/secret/update', [PPPoEController::class, 'update'])->name('pppoe.update');
    Route::get('pppoe/secret/delete/{id}', [PPPoEController::class, 'delete'])->name('pppoe.delete');

    // 📶 Hotspot
    Route::get('hotspot/users', [HotspotController::class, 'users'])->name('hotspot.users');
    Route::get('hotspot/users/active', [HotspotController::class, 'active'])->name('hotspot.active');
    Route::post('hotspot/users/add', [HotspotController::class, 'add'])->name('hotspot.add');
    Route::get('hotspot/users/edit/{id}', [HotspotController::class, 'edit'])->name('hotspot.edit');
    Route::post('hotspot/users/update', [HotspotController::class, 'update'])->name('hotspot.update');
    Route::get('hotspot/users/delete/{id}', [HotspotController::class, 'delete'])->name('hotspot.delete');

    // 📈 Report
    Route::get('report-up', [ReportController::class, 'index'])->name('report-up.index');
    Route::get('report-up/load', [ReportController::class, 'load'])->name('report-up.load');
    Route::get('report-up/search', [ReportController::class, 'search'])->name('search.report');
    Route::get('/up', [ReportController::class, 'up']);
    Route::get('/down', [ReportController::class, 'down']);

    // 👥 User Active MikroTik
    Route::get('useractive', [UseractiveController::class, 'index'])->name('user.index');
    Route::get('realtime/useractive', [UseractiveController::class, 'useractive'])->name('realtime.useractive');

    // 📦 Paket Internet
    Route::resource('paket', PaketInternetController::class)->except(['show']);

    // ⚙️ Setting MikroTik

    Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
    Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');

    // � WhatsApp Gateway (Fonnte)
    Route::post('/whatsapp/save-token', [WhatsAppController::class, 'saveToken'])->name('whatsapp.save-token');
    Route::get('/whatsapp/qr', [WhatsAppController::class, 'getQR'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');
    Route::post('/whatsapp/send-invoice', [WhatsAppController::class, 'sendInvoice'])->name('whatsapp.send-invoice');
    Route::post('/whatsapp/send-bulk-invoice', [WhatsAppController::class, 'sendBulkInvoice'])->name('whatsapp.send-bulk-invoice');
    Route::post('/whatsapp/test', [WhatsAppController::class, 'test'])->name('whatsapp.test');
    // 👥 Pelanggan
    Route::resource('pelanggan', PelangganController::class)->except(['show']);
    Route::get('/pelanggan/{id}/detail', [PelangganController::class, 'detail'])->name('pelanggan.detail');
    Route::get('/pelanggan/preview.kode', [App\Http\Controllers\PelangganController::class, 'previewKode'])->name('pelanggan.preview.kode');

    // 🧾 Tagihan (Invoices)
    Route::resource('invoices', InvoiceController::class)->except(['create', 'store']);
    Route::post('/invoices/generate', [InvoiceController::class, 'store'])->name('invoices.generate');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf.download');
    Route::get('/invoices/{invoice}/pdf/advanced', [InvoiceController::class, 'downloadPdfAdvanced'])->name('invoices.pdf.advanced.download');
    Route::get('/invoices/{invoice}/preview/advanced', [InvoiceController::class, 'previewPdfAdvanced'])->name('invoices.pdf.advanced.preview');

    // 💰 Pembayaran
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::get('/payments/manual', [PaymentController::class, 'createManual'])->name('payments.create.manual');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    // API untuk ambil harga paket pelanggan
    Route::get('/api/pelanggan/{id}/paket', function ($id) {
        $pelanggan = \App\Models\Pelanggan::with(['paket', 'invoices' => fn($q) => $q->where('status', 'unpaid')])->findOrFail($id);

        if (!$pelanggan->paket) {
            return response()->json(['harga' => 0, 'has_unpaid_invoice' => false], 404);
        }

        return response()->json([
            'harga' => (float) $pelanggan->paket->harga,
            'nama_paket' => $pelanggan->paket->nama_paket,
            'has_unpaid_invoice' => $pelanggan->invoices->isNotEmpty(),
        ]);
    });

    Route::get('/api/pelanggan/{id}/unpaid-invoices', function ($id) {
        $pelanggan = \App\Models\Pelanggan::with(['invoices'])->findOrFail($id);

        $invoices = $pelanggan->invoices->where('status', 'unpaid')->map(function ($inv) {
            $paket = \App\Models\Paket::where('nama_paket', $inv->paket_nama)->first();

            if (!$paket) {
                $paket = new \App\Models\Paket();
                $paket->ppn_aktif = false;
                $paket->ppn_persen = 10;
                $paket->diskon_aktif = false;
                $paket->diskon_persen = 0;
            }

            $hargaDasar = $inv->amount;
            $ppnPersen = $paket->ppn_aktif ? ($paket->ppn_persen ?? 10) : 0;
            $ppn = $hargaDasar * ($ppnPersen / 100);
            $diskonPersen = $paket->diskon_aktif ? ($paket->diskon_persen ?? 0) : 0;
            $diskon = $hargaDasar * ($diskonPersen / 100);
            $total = $hargaDasar + $ppn - $diskon;

            return [
                'periode' => \Carbon\Carbon::parse($inv->billing_period_start)->format('F Y'),
                'amount_dasar' => $hargaDasar,
                'ppn_persen' => $ppnPersen,
                'ppn_value' => $ppn,
                'diskon_persen' => $diskonPersen,
                'diskon_value' => $diskon,
                'total' => $total,
            ];
        });

        return response()->json(['invoices' => $invoices]);
    });

    // Struk pembayaran
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // WhatsApp Export
    Route::get('/invoices/export-wa', [InvoiceController::class, 'exportWa'])->name('invoices.export.wa');
    Route::get('/invoices/wa-bulk', [InvoiceController::class, 'waBulk'])->name('invoices.wa.bulk');

    // Laporan Keuangan
    Route::get('/laporan-keuangan', [FinancialReportController::class, 'index'])->name('laporan.keuangan');
    Route::post('/pengeluaran', [FinancialReportController::class, 'store'])->name('pengeluaran.store');
    Route::post('/pemasukan-manual', [FinancialReportController::class, 'storePemasukanManual'])->name('pemasukan.manual.store');

    // Pemasukan Manual
    Route::get('/pemasukan-manual', [FinancialReportController::class, 'indexPemasukan'])->name('pemasukan.manual.index');

    // Pengeluaran
    Route::get('/pengeluaran', [FinancialReportController::class, 'indexPengeluaran'])->name('pengeluaran.index');

    // Pemasukan Manual CRUD
    Route::get('/pemasukan/manual/create', [FinancialReportController::class, 'createPemasukan'])->name('pemasukan.manual.create');
    Route::get('/pemasukan/manual/{id}/edit', [FinancialReportController::class, 'editPemasukan'])->name('pemasukan.manual.edit');
    Route::put('/pemasukan/manual/{id}', [FinancialReportController::class, 'updatePemasukan'])->name('pemasukan.manual.update');
    Route::delete('/pemasukan/manual/{id}', [FinancialReportController::class, 'destroyPemasukan'])->name('pemasukan.manual.destroy');

    // Pengeluaran CRUD
    Route::get('/pengeluaran/{id}/edit', [FinancialReportController::class, 'editPengeluaran'])->name('pengeluaran.edit');
    Route::put('/pengeluaran/{id}', [FinancialReportController::class, 'updatePengeluaran'])->name('pengeluaran.update');
    Route::delete('/pengeluaran/{id}', [FinancialReportController::class, 'destroyPengeluaran'])->name('pengeluaran.destroy');

    // Master Kategori
    Route::prefix('master')->group(function () {
        Route::get('/kategori/pemasukan', [KategoriKeuanganController::class, 'indexPemasukan'])->name('master.kategori.pemasukan');
        Route::post('/kategori/pemasukan', [KategoriKeuanganController::class, 'storePemasukan'])->name('master.kategori.pemasukan.store');
        Route::put('/kategori/pemasukan/{id}', [KategoriKeuanganController::class, 'updatePemasukan'])->name('master.kategori.pemasukan.update');
        Route::delete('/kategori/pemasukan/{id}', [KategoriKeuanganController::class, 'destroyPemasukan'])->name('master.kategori.pemasukan.destroy');

        Route::get('/kategori/pengeluaran', [KategoriKeuanganController::class, 'indexPengeluaran'])->name('master.kategori.pengeluaran');
        Route::post('/kategori/pengeluaran', [KategoriKeuanganController::class, 'storePengeluaran'])->name('master.kategori.pengeluaran.store');
        Route::put('/kategori/pengeluaran/{id}', [KategoriKeuanganController::class, 'updatePengeluaran'])->name('master.kategori.pengeluaran.update');
        Route::delete('/kategori/pengeluaran/{id}', [KategoriKeuanganController::class, 'destroyPengeluaran'])->name('master.kategori.pengeluaran.destroy');
    });

    // Laporan Keuangan Export
    Route::get('/laporan-keuangan/export', [FinancialReportController::class, 'export'])->name('laporan.keuangan.export');

    // Dashboard Pelanggan
    Route::get('/dashboard-pelanggan', [DashboardPelangganController::class, 'index'])->name('dashboard.pelanggan');

});

Route::get('/failed', function () {
    return view('failed');
})->name('failed');

// 🔚 Logout (bisa diakses setelah login)
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
