@extends('layouts.layouts', ['menu' => 'invoices', 'submenu' => ''])

@section('title', 'Detail Tagihan')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Detail Tagihan</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><td><strong>Nomor Invoice</strong></td><td>{{ $invoice->invoice_number }}</td></tr>
                                        <tr><td><strong>Status</strong></td>
                                            <td>
                                                @if($invoice->status == 'paid')
                                                    <span class="badge badge-success">Sudah Bayar</span>
                                                @elseif($invoice->status == 'overdue')
                                                    <span class="badge badge-danger">Telat Bayar</span>
                                                @else
                                                    <span class="badge badge-warning">Belum Bayar</span>
                                                @endif
                                            </td>
                                           <div class="card-footer">
    @if($invoice->status == 'unpaid' && $invoice->pelanggan->no_hp)
        @php
            $waNumber = formatWaNumber($invoice->pelanggan->no_hp);
            $waMessage = getInvoiceWaMessage($invoice);
        @endphp
        
        <!-- ✅ LINK WA OTOMATIS -->
       <a href="https://web.whatsapp.com/send?phone={{ $waNumber }}&text={{ urlencode($waMessage) }}" 
   class="btn btn-success btn-block" target="_blank">
    <i class="fab fa-whatsapp"></i> Kirim Tagihan via WhatsApp
</a>
    @endif
    
    <a href="{{ route('invoices.pdf.advanced.preview', $invoice) }}" 
       class="btn btn-primary btn-block" target="_blank">
        <i class="fas fa-file-pdf"></i> Lihat Invoice PDF
    </a>
</div>
                                        </tr>
                                        <tr><td><strong>Tanggal Dibuat</strong></td><td>{{ $invoice->created_at->format('d M Y H:i') }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><td><strong>Periode Tagihan</strong></td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($invoice->billing_period_start)->format('d M Y') }}<br>
                                                s/d {{ \Carbon\Carbon::parse($invoice->billing_period_end)->format('d M Y') }}
                                            </td>
                                        </tr>
                                        <tr><td><strong>Jatuh Tempo</strong></td><td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td></tr>
                                        <tr><td><strong>Jumlah</strong></td><td><strong>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</strong></td></tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <h5>Data Pelanggan</h5>
                            <table class="table table-borderless">
                                <tr><td><strong>Kode</strong></td><td>{{ $invoice->pelanggan->kode_pelanggan }}</td></tr>
                                <tr><td><strong>Nama</strong></td><td>{{ $invoice->pelanggan->nama_pelanggan }}</td></tr>
                                <tr><td><strong>Paket</strong></td><td>{{ $invoice->paket_nama }}</td></tr>
                                <tr><td><strong>Username PPPoE</strong></td><td><code>{{ $invoice->pelanggan->username_pppoe }}</code></td></tr>
                            </table>

                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Kembali</a>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">Ubah Status</a>
                          <div class="mt-3">
    <a href="{{ route('invoices.pdf.advanced.preview', $invoice) }}" target="_blank" class="btn btn-success">
        <i class="fas fa-eye"></i> Preview Advanced PDF
    </a>
    <a href="{{ route('invoices.pdf.advanced.download', $invoice) }}" class="btn btn-primary">
        <i class="fas fa-download"></i> Download Advanced PDF
    </a>
       
</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection