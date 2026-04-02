@extends('layouts.layouts', ['menu' => 'payments', 'submenu' => ''])

@section('title', 'Riwayat Pembayaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Riwayat Pembayaran</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $payments->total() }} Pembayaran</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="paymentTable" class="display table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pelanggan</th>
                                            <th>Invoice</th>
                                            <th>Jumlah</th>
                                            <th>Metode</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                                            <td>{{ $payment->pelanggan->nama_pelanggan }}</td>
<td>
    @if($payment->invoice)
        <code>{{ $payment->invoice->invoice_number }}</code>
    @else
        <span class="text-muted">Manual</span>
    @endif
</td>
                                            <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                            <td>
                                                @if($payment->status == 'completed')
                                                    <span class="badge badge-success">Selesai</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- {{ $payments->links() }} --}}
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
    $('#paymentTable').DataTable({
        "processing": true,
        "serverSide": false,
        "pageLength": 10,
        "order": [[0, 'asc']],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ ",
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
            { "orderable": false, "targets": [7] } // nonaktifkan sorting di kolom Aksi
        ]
    });
});
</script>
@endpush