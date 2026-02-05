@extends('layouts.layouts', ['menu' => 'payments', 'submenu' => ''])

@section('title', 'Buat Pembayaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pembayaran Invoice</h4>
                        </div>
                        @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
                                <strong>Pelanggan:</strong> {{ $invoice->pelanggan->nama_pelanggan }}<br>
                                <strong>Total Tagihan:</strong> Rp {{ number_format($invoice->amount * 1.1, 0, ',', '.') }}
                            </div>
                            

                            <form action="{{ route('payments.store') }}" method="POST">
                                @csrf
                                                                <div class="form-group">
                                    <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" 
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                                <div class="form-group">
                                    <label>Jumlah Dibayar <span class="text-danger">*</span></label>
                                    <input type="number" name="amount_paid" class="form-control" 
                                           value="{{ $invoice->amount * 1.1 }}" step="0.01" required>
                                </div>



                                <div class="form-group">
                                    <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="manual">Manual (Cash/Transfer)</option>
                                        <option value="bank_transfer">Transfer Bank</option>
                                        <option value="e_wallet">E-Wallet</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Nomor Referensi (Opsional)</label>
                                    <input type="text" name="reference_number" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-group">
        <label>Nomor Struk <span class="text-danger">*</span></label>
        <input type="text" name="receipt_number" class="form-control" 
               value="STRUK-{{ date('Ymd') }}-{{ $invoice->id }}" required>
    </div>

    <div class="form-group">
        <label>Nama Kasir <span class="text-danger">*</span></label>
        <input type="text" name="cashier_name" class="form-control" 
               value="{{ auth()->user()->name ?? 'Admin' }}" required>
    </div>

    <div class="form-group">
        <label>Bukti Transfer (Opsional)</label>
        <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf">
        <small class="form-text text-muted">Upload screenshot/scan bukti transfer</small>
    </div>

    <button type="submit" class="btn btn-primary">Simpan & Cetak Struk</button>
                                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection