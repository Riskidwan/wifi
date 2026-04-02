@extends('layouts.layouts')

@section('title', 'Dashboard Pelanggan & Keuangan')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white fw-bold">Dashboard Pelanggan & Keuangan</h2>
            </div>
        </div>

        <div class="page-inner">
            <!-- Ringkasan Statistik -->
            <div class="row">
                <!-- Total Pelanggan -->
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-stats">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Total Pelanggan</p>
                                        <h4 class="card-title">{{ $totalPelanggan }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pelanggan Aktif -->
                <div class="col-md-3">
                    <div class="card card-stats card-success">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-stats">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Aktif</p>
                                        <h4 class="card-title">{{ $pelangganAktif }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pelanggan Tidak Aktif -->
                <div class="col-md-3">
                    <div class="card card-stats card-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-stats">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Tidak Aktif</p>
                                        <h4 class="card-title">{{ $pelangganTidakAktif }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo Bersih -->
                <div class="col-md-3">
                    <div class="card card-stats card-info">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-stats">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Saldo Bersih</p>
                                        <h4 class="card-title">Rp {{ number_format($saldoBersih, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Ringkas -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Ringkasan Keuangan Bulan Ini</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <td><strong>Pendapatan</strong></td>
                                        <td class="text-right"><span class="badge badge-success">Rp {{ number_format($pendapatan, 0, ',', '.') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pengeluaran</strong></td>
                                        <td class="text-right"><span class="badge badge-warning">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Saldo Bersih</strong></td>
                                        <td class="text-right">
                                            <span class="badge badge-{{ $saldoBersih >= 0 ? 'info' : 'danger' }}">
                                                Rp {{ number_format($saldoBersih, 0, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                                <!-- Log Aktivitas -->
<!-- Aktivitas Terbaru - Versi Rapi & Profesional -->
<div class="col-md-6">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
            <h4 class="card-title mb-0 fw-bold text-dark">Aktivitas Terbaru</h4>
            <span class="badge bg-light text-dark fs-6 px-2 py-1">
                {{ $activities->count() }} item
            </span>
        </div>
        <div class="card-body p-0">
            @if($activities->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-clock text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted fw-medium">Belum ada aktivitas</p>
                    <small class="text-secondary">Semua aksi akan muncul di sini</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            @foreach($activities as $activity)
                            <tr class="border-bottom hover:bg-gray-50">
                                <td class="py-3 px-4" style="width: 18%;">
                                    <small class="text-muted d-block">
                                        {{ $activity->created_at->format('H:i') }}
                                    </small>
                                    <small class="text-secondary d-block">
                                        {{ $activity->created_at->format('d M') }}
                                    </small>
                                </td>
                                <td class="py-3 px-4" style="width: 22%;">
                                    <div class="d-flex align-items-center">

                                        <div>
                                            <strong class="text-dark">{{ $activity->causer?->name ?? 'System' }}</strong><br>
                                            <small class="text-muted">{{ ucfirst($activity->log_name) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-dark fw-medium">
                                        {{ $activity->description }}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Statistik Pelanggan</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-center">
                                    <h5>{{ $pelangganAktif }}</h5>
                                    <small>Aktif</small>
                                </div>
                                <div class="text-center">
                                    <h5>{{ $pelangganTidakAktif }}</h5>
                                    <small>Tidak Aktif</small>
                                </div>
                                <div class="text-center">
                                    <h5>{{ $totalPelanggan }}</h5>
                                    <small>Total</small>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $totalPelanggan > 0 ? ($pelangganAktif / $totalPelanggan) * 100 : 0 }}%">
                                    <span class="sr-only">Aktif</span>
                                </div>
                                <div class="progress-bar bg-danger" style="width: {{ $totalPelanggan > 0 ? ($pelangganTidakAktif / $totalPelanggan) * 100 : 0 }}%">
                                    <span class="sr-only">Tidak Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                

            </div>
            
        </div>
    </div>
</div>
@endsection