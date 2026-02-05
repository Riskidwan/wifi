@extends('layouts.layouts', ['menu' => 'setting', 'submenu' => ''])

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="main-panel">
    <div class "content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Konfigurasi Sistem</h2>
                        <h5 class="text-white op-7 mb-2">Kelola koneksi MikroTik dan pengaturan billing</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                            <form action="{{ route('setting.update') }}" method="POST">
                                @csrf

                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs" id="settingTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="mikrotik-tab" data-toggle="tab" href="#mikrotik" role="tab">
                                            <i class="fas fa-server"></i> Koneksi MikroTik
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="billing-tab" data-toggle="tab" href="#billing" role="tab">
                                            <i class="fas fa-file-invoice"></i> Konfigurasi Billing
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content mt-4" id="settingTabContent">
                                    <!-- Tab MikroTik (TIDAK DIUBAH) -->
                                    <div class="tab-pane fade show active" id="mikrotik" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>IP Address MikroTik <span class="text-danger">*</span></label>
                                                    <input type="text" name="ip" class="form-control" 
                                                           value="{{ old('ip', $mikrotik['ip']) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Username <span class="text-danger">*</span></label>
                                                    <input type="text" name="user" class="form-control" 
                                                           value="{{ old('user', $mikrotik['user']) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Password</label>
                                                    <input type="password" name="password" class="form-control" 
                                                           value="{{ old('password', $mikrotik['password']) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab Billing (BARU) -->
                                    <div class="tab-pane fade" id="billing" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Nama Perusahaan <span class="text-danger">*</span></label>
                                                    <input type="text" name="company_name" class="form-control" 
                                                           value="{{ old('company_name', $billing->company_name) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Alamat Perusahaan</label>
                                                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $billing->company_address) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Telepon Perusahaan</label>
                                                    <input type="text" name="company_phone" class="form-control" 
                                                           value="{{ old('company_phone', $billing->company_phone) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Email Perusahaan</label>
                                                    <input type="email" name="company_email" class="form-control" 
                                                           value="{{ old('company_email', $billing->company_email) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Tanggal Mulai Periode Tagihan <span class="text-danger">*</span></label>
                                                    <input type="number" name="billing_start_day" class="form-control" 
                                                           value="{{ old('billing_start_day', $billing->billing_start_day) }}" 
                                                           min="1" max="31" required>
                                                    <small class="form-text text-muted">Contoh: 1 (untuk tanggal 1 setiap bulan)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Hari Jatuh Tempo Setelah Periode <span class="text-danger">*</span></label>
                                                    <input type="number" name="due_days_after_period" class="form-control" 
                                                           value="{{ old('due_days_after_period', $billing->due_days_after_period) }}" 
                                                           min="1" max="30" required>
                                                    <small class="form-text text-muted">Contoh: 5 (jatuh tempo 5 hari setelah periode berakhir)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Semua Konfigurasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection