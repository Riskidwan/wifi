@php 
    $menu = $menu ?? ''; 
    $submenu = $submenu ?? ''; 
@endphp
<div class="sidebar sidebar-style-2">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-primary">
                <li class="nav-item {{ $menu == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
    <li class="nav-item {{ $menu == 'dashboard-pelanggan' ? 'active' : '' }}">
        <a href="{{ route('dashboard.pelanggan') }}">
            <i class="fas fa-chart-pie"></i>
            <p>Dashboard Pelanggan</p>
        </a>
    </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Components</h4>
                </li>
                <li class="nav-item {{ $menu == 'interface' ? 'active' : '' }}">
                    <a href="{{ route('interface.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <p>Interface</p>
                    </a>
                </li>
                <li class="nav-item {{ $menu == 'pppoe' ? 'active' : '' }}">
                    <a data-toggle="collapse" href="#base">
                        <i class="fas fa-rocket"></i>
                        <p>PPPoE</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="base">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('pppoe.secret') }}">
                                    <span class="sub-item {{ ($submenu ?? '') == 'secret' ? 'active' : '' }}">PPPoE Secret</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('pppoe.active') }}">
                                 <span class="sub-item {{ ($submenu ?? '') == 's-active' ? 'active' : '' }}">PPPoE Active</span>
                                </a>
                            </li>

                        </ul>
                    </div>

                </li>
                           <li class="nav-item {{ $menu == 'paket' ? 'active' : '' }}">
    <a href="{{ route('paket.index') }}">
        <i class="fas fa-box"></i>
        <p>Paket Internet</p>
    </a>
</li>
                <li class="nav-item {{ $menu == 'hotspot' ? 'active' : '' }}">
                    <a data-toggle="collapse" href="#base1">
                        <i class="fas fa-wifi"></i>
                        <p>Hotspot</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="base1">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('hotspot.users') }}">
                                    <span class="sub-item {{ $submenu == 'user' ? 'active' : '' }}">Hotspot
                                        Users</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('hotspot.active') }}">
                                    <span class="sub-item {{ $submenu == 'u-active' ? 'active' : '' }}">Hotspot Users
                                        Active</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item {{ $menu == 'report' ? 'active' : '' }}">
                    <a data-toggle="collapse" href="#base3">
                        <i class="fas fa-paste"></i>
                        <p>Report</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="base3">
                        <ul class="nav nav-collapse">

                            <li>
                                <a href="{{ route('report-up.index') }}">
                                    <span class="sub-item {{ $submenu == 'traffic-up' ? 'active' : '' }}">Report
                                        Traffic UP</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('search.report') }}">
                                    <span class="sub-item {{ $submenu == 'search-traffic' ? 'active' : '' }}">Search
                                        Report</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item {{ $menu == 'useractive' ? 'active' : '' }}">
                    <a href="{{ route('user.index') }}">
                        <i class="fas fa-user-alt"></i>
                        <p>User Mikrotik Active</p>
                    </a>
                </li>

<li class="nav-item {{ $menu == 'pelanggan' ? 'active' : '' }}">
    <a href="{{ route('pelanggan.index') }}">
        <i class="fas fa-users"></i>
        <p>Pelanggan</p>
    </a>
</li>
<li class="nav-item {{ $menu == 'admin' ? 'active' : '' }}">
    <a href="{{ route('admin.index') }}">
        <i class="fa fa-users"></i>
        <p>Kelola Admin</p>
    </a>
</li>
<li class="nav-item {{ $menu == 'invoices' ? 'active' : '' }}">
    <a data-toggle="collapse" href="#invoicesSubmenu">
        <i class="fas fa-file-invoice"></i>
        <p>
            Tagihan
            <b class="caret"></b>
        </p>
    </a>
    <div class="collapse {{ $menu == 'invoices' ? 'show' : '' }}" id="invoicesSubmenu">
        <ul class="nav nav-collapse">
            <li class="{{ $submenu == 'list' ? 'active' : '' }}">
                <a href="{{ route('invoices.index') }}">
                    <span class="sub-item">Daftar Tagihan</span>
                </a>
            </li>
            {{-- <li class="{{ $submenu == 'wa-bulk' ? 'active' : '' }}">
                <a href="{{ route('invoices.wa.bulk') }}">
                    <span class="sub-item">Kirim Massal via WA</span>
                </a>
            </li> --}}
        </ul>
    </div>
</li>

<li class="nav-item {{ $menu == 'payments' ? 'active' : '' }}">
    <a data-toggle="collapse" href="#payments">
        <i class="fas fa-money-bill-wave"></i>
        <p>Pembayaran</p>
        <span class="caret"></span>
    </a>
    <div class="collapse" id="payments">
        <ul class="nav nav-collapse">
            <li>
                <a href="{{ route('payments.index') }}">
                    <span class="sub-item {{ $submenu == 'list' ? 'active' : '' }}">Riwayat Pembayaran</span>
                </a>
            </li>
           <li>
                <a href="{{ route('payments.create.manual') }}">
                    <span class="sub-item {{ $submenu == 'manual' ? 'active' : '' }}">Pembayaran Manual</span>
                </a>
            </li>
        </ul>
    </div>
</li>
<li class="nav-item {{ $menu == 'laporan' ? 'active' : '' }}">
    <a data-toggle="collapse" href="#laporanSubmenu">
        <i class="fas fa-file-invoice-dollar"></i>
        <p>Laporan Keuangan</p>
        <span class="caret"></span>
    </a>
    <div class="collapse {{ $menu == 'laporan' ? 'show' : '' }}" id="laporanSubmenu">
        <ul class="nav nav-collapse">
            <li class="nav-item {{ ($menu == 'laporan' && $submenu == 'dashboard') ? 'active' : '' }}">
                <a href="{{ route('laporan.keuangan') }}">
                    <span class="sub-item">Dashboard Keuangan</span>
                </a>
            </li>
            <li class="nav-item {{ ($menu == 'laporan' && $submenu == 'pemasukan') ? 'active' : '' }}">
                <a href="{{ route('pemasukan.manual.index') }}">
                    <span class="sub-item">Pemasukan</span>
                </a>
            </li>
            <li class="nav-item {{ ($menu == 'laporan' && $submenu == 'pengeluaran') ? 'active' : '' }}">
                <a href="{{ route('pengeluaran.index') }}">
                    <span class="sub-item">Pengeluaran</span>
                </a>
            </li>
        </ul>
    </div>
</li>
<li class="nav-item {{ $menu == 'master' ? 'active' : '' }}">
    <a data-toggle="collapse" href="#masterSubmenu">
        <i class="fas fa-layer-group"></i>
        <p>Master Kategori</p>
        <span class="caret"></span>
    </a>
    <div class="collapse {{ $menu == 'master' ? 'show' : '' }}" id="masterSubmenu">
        <ul class="nav nav-collapse">
            <li class="nav-item {{ ($menu == 'master' && $submenu == 'pemasukan') ? 'active' : '' }}">
                <a href="{{ route('master.kategori.pemasukan') }}">
                    <span class="sub-item">Kategori Pemasukan</span>
                </a>
            </li>
            <li class="nav-item {{ ($menu == 'master' && $submenu == 'pengeluaran') ? 'active' : '' }}">
                <a href="{{ route('master.kategori.pengeluaran') }}">
                    <span class="sub-item">Kategori Pengeluaran</span>
                </a>
            </li>
        </ul>
    </div>
</li>


                <li class="nav-item {{ $menu == 'setting' ? 'active' : '' }}">
                    <a href="{{ route('setting.index') }}"> <!-- ✅ Diperbaiki di sini -->
                        <i class="fas fa-cog"></i>
                        <p>Setting</p>
                    </a>
                </li>


            </ul>
        </div>
    </div>
</div>
