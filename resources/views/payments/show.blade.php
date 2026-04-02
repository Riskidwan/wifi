@extends('layouts.layouts', ['menu' => 'payments', 'submenu' => 'list'])

@section('title', 'Detail Pembayaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Detail Pembayaran</h2>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <a href="{{ route('payments.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Informasi Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Pembayaran</strong></td>
                                    <td>:</td>
                                    <td>{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nomor Invoice</strong></td>
                                    <td>:</td>
                                    <td>
                                       @if($payment->invoice)
    <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-primary">
        {{ $payment->invoice->invoice_number }}
    </a>
@else
    <span class="text-muted">Manual</span>
@endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pelanggan</strong></td>
                                    <td>:</td>
                                    <td>
                                        {{ $payment->pelanggan->kode_pelanggan }} - 
                                        {{ $payment->pelanggan->nama_pelanggan }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Bayar</strong></td>
                                    <td>:</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah</strong></td>
                                    <td>:</td>
                                    <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Metode Pembayaran</strong></td>
                                    <td>:</td>
                                    <td>{{ $payment->payment_method }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nomor Referensi</strong></td>
                                    <td>:</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Catatan</strong></td>
                                    <td>:</td>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>:</td>
                                    <td>
                                        @if($payment->status == 'completed')
                                            <span class="badge badge-success">Berhasil</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                   <!-- Tombol Aksi -->
<div class="text-right mt-3">
    <!-- Cetak Struk (selalu ada) -->
    <a href="{{ route('payments.receipt', $payment->id) }}" 
       class="btn btn-info" target="_blank">
        <i class="fas fa-receipt"></i> Cetak Struk
    </a>

    @if($payment->invoice_id)
        <!-- Preview Surat Pembayaran -->
        <a href="{{ route('invoices.pdf.advanced.preview', $payment->invoice_id) }}" 
           class="btn btn-primary" target="_blank">
            <i class="fas fa-eye"></i> Lihat Invoice Pembayaran
        </a>
        
        <!-- Download Surat Pembayaran -->
        <a href="{{ route('invoices.pdf.advanced.download', $payment->invoice_id) }}" 
           class="btn btn-success">
            <i class="fas fa-download"></i> Download Invoice Pembayaran
        </a>
    @endif
</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection