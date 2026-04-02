@extends('layouts.layouts', ['menu' => 'invoices', 'submenu' => ''])

@section('title', 'Ubah Status Tagihan')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Ubah Status Tagihan</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label>Nomor Invoice</label>
                                    <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Pelanggan</label>
                                    <input type="text" class="form-control" value="{{ $invoice->pelanggan->kode_pelanggan }} - {{ $invoice->pelanggan->nama_pelanggan }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Jumlah</label>
                                    <input type="text" class="form-control" value="Rp {{ number_format($invoice->amount, 0, ',', '.') }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="unpaid" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                        <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Sudah Bayar</option>
                                        <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Telat Bayar</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection