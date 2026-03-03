@extends('layouts.layouts', ['menu' => 'invoices', 'submenu' => ''])

@section('title', 'Manajemen Tagihan')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Manajemen Tagihan</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $invoices->count() }} Tagihan</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <form action="{{ route('invoices.generate') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Generate tagihan bulanan untuk semua pelanggan aktif?')">
                                <i class="fas fa-plus"></i> Generate Tagihan Bulanan
                            </button>
                        </form>
                    </div>
                    <!-- Di bagian header (setelah tombol Generate Tagihan) -->
<a href="javascript:void(0)" 
   class="btn btn-info ml-2" 
   onclick="sendAllWa()">
    <i class="fab fa-whatsapp"></i> Kirim Semua via WA
</a>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Filter Form -->
                            <form method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <select name="status" class="form-control">
                                            <option value="">Semua Status</option>
                                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Bayar</option>
                                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Telat Bayar</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="pelanggan" class="form-control">
                                            <option value="">Semua Pelanggan</option>
                                            @foreach($pelanggans as $p)
                                                <option value="{{ $p->id_pelanggan }}" {{ request('pelanggan') == $p->id_pelanggan ? 'selected' : '' }}>
                                                    {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex gap-2">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Reset</a>
</div>

                                </div>
                            </form>

                            <!-- Tabel Invoice -->
                           <div class="table-responsive">
                                <table id="invoiceTable" class="display table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Invoice</th>
                                            <th>Pelanggan</th>
                                            <th>Paket</th>
                                            <th>Jumlah</th>
                                            <th>Periode</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoices as $invoice)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><code>{{ $invoice->invoice_number }}</code></td>
                                            <td>
                                                {{ $invoice->pelanggan->kode_pelanggan }}<br>
                                                <small>{{ $invoice->pelanggan->nama_pelanggan }}</small>
                                            </td>
                                            <td>{{ $invoice->paket_nama }}</td>
                                           <td>
    Rp {{ number_format($invoice->total_amount ?? $invoice->amount, 0, ',', '.') }}
    @if($invoice->total_amount)
        <br><small class="text-muted">+PPN/diskon</small>
    @endif
</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($invoice->billing_period_start)->translatedFormat('F Y') }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</td>
                                            <!-- Kolom Status -->
<td>
    @if($invoice->status == 'paid')
        <span class="badge badge-success">Sudah Bayar</span>
    @elseif($invoice->status == 'overdue')
        <!-- 🔁 Arahkan ke pembayaran manual dengan prefill -->
        <a href="{{ route('payments.create.manual') }}?pelanggan_id={{ $invoice->pelanggan_id }}"
           class="btn btn-sm btn-warning">
            <i class="fas fa-money-bill"></i> Bayar (Telat)
        </a>
    @else
        <!-- 🔁 Arahkan ke pembayaran manual dengan prefill -->
        <a href="{{ route('payments.create.manual') }}?pelanggan_id={{ $invoice->pelanggan_id }}"
           class="btn btn-sm btn-success">
            <i class="fas fa-money-bill"></i> Bayar
        </a>
    @endif
</td>
                                            <td>
                                                @if($invoice->status == 'unpaid' && $invoice->pelanggan->no_hp)
                                                    <button class="btn btn-sm btn-success btn-send-wa" 
                                                            data-invoice-id="{{ $invoice->id }}" 
                                                            data-nama="{{ $invoice->pelanggan->nama_pelanggan }}"
                                                            title="Kirim WA">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
    $('#invoiceTable').DataTable({
        "processing": true,
        "serverSide": false,
        "pageLength": 10,
        "order": [[0, 'asc']],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total entri)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Berikutnya",
                "previous": "Sebelumnya"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        "columnDefs": [
            { "orderable": false, "targets": [8] } // nonaktifkan sorting di kolom Aksi
        ]
    });
});

// === Kirim WA per invoice ===
$(document).on('click', '.btn-send-wa', function() {
    const btn = $(this);
    const invoiceId = btn.data('invoice-id');
    const nama = btn.data('nama');
    
    if (!confirm('Kirim tagihan via WA ke ' + nama + '?')) return;
    
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: '{{ route("whatsapp.send-invoice") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', invoice_id: invoiceId },
        success: function(res) {
            if (res.success) {
                btn.removeClass('btn-success').addClass('btn-secondary').html('<i class="fas fa-check"></i>');
                alert('✅ ' + res.message);
            } else {
                btn.prop('disabled', false).html('<i class="fab fa-whatsapp"></i>');
                alert('❌ ' + res.message);
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fab fa-whatsapp"></i>');
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal mengirim';
            alert('❌ ' + msg);
        }
    });
});

// === Kirim Semua WA (bulk) ===
function sendAllWa() {
    const invoiceIds = [
        @foreach($invoices as $invoice)
            @if($invoice->status == 'unpaid' && $invoice->pelanggan->no_hp)
                {{ $invoice->id }},
            @endif
        @endforeach
    ];
    
    if (invoiceIds.length === 0) {
        alert('Tidak ada invoice unpaid untuk dikirim.');
        return;
    }
    
    if (!confirm('Kirim tagihan WA ke ' + invoiceIds.length + ' pelanggan?')) return;
    
    // Disable tombol
    const btn = $('[onclick="sendAllWa()"]');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
    
    $.ajax({
        url: '{{ route("whatsapp.send-bulk-invoice") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', invoice_ids: invoiceIds },
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> Kirim Semua via WA');
            alert(res.message);
            if (res.errors && res.errors.length > 0) {
                console.log('Errors:', res.errors);
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> Kirim Semua via WA');
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal mengirim';
            alert('❌ ' + msg);
        }
    });
}
</script>
@endpush