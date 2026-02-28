# 🌐 Aplikasi Billing ISP & Manajemen MikroTik

Sistem manajemen pelanggan ISP / RT-RW Net terintegrasi dengan **MikroTik RouterOS API** dan **WhatsApp Gateway (Fonnte)**. Dibangun menggunakan **Laravel 12** + **PHP 8.2**.

![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#%EF%B8%8F-konfigurasi)
- [Cronjob / Task Scheduling](#-cronjob--task-scheduling)
- [Deploy ke Production](#-deploy-ke-production)
- [Struktur Folder](#-struktur-folder)
- [Screenshots](#-screenshots)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### 🔌 Integrasi MikroTik
- Koneksi langsung ke router MikroTik via API
- Manajemen **PPPoE Secret** (tambah, edit, hapus, enable/disable)
- Monitoring **PPPoE Active** & **Hotspot Users**
- Auto-create **PPPoE Profile** saat membuat paket internet baru
- Auto-create **PPPoE Secret** saat mendaftarkan pelanggan baru
- Auto-disable koneksi pelanggan yang telat bayar
- Auto-enable koneksi pelanggan setelah membayar

### 👥 Manajemen Pelanggan
- CRUD data pelanggan lengkap dengan kode pelanggan otomatis
- Integrasi langsung dengan paket internet & akun MikroTik
- Detail pelanggan dengan riwayat tagihan & pembayaran
- Dukungan Google Maps untuk lokasi pelanggan

### 🧾 Sistem Tagihan Otomatis (Auto-Invoicing)
- Generate tagihan bulanan otomatis untuk semua pelanggan aktif
- Kalkulasi otomatis: **Harga Paket + PPN − Diskon**
- PPN & Diskon bisa diatur per paket internet (aktif/nonaktif + persentase)
- Multiple template cetak PDF (Standard, Advanced, Clean)
- Filter & pencarian tagihan berdasarkan status dan pelanggan

### 💰 Sistem Pembayaran (Kasir)
- Pembayaran manual dengan perhitungan otomatis
- Pembayaran multi-bulan sekaligus (maks 12 bulan)
- Hitung uang dibayar & kembalian otomatis
- Cetak struk pembayaran
- Auto-activate koneksi internet setelah pembayaran

### 📱 WhatsApp Gateway (Fonnte)
- Integrasi API Fonnte untuk pengiriman pesan otomatis
- Scan QR Code untuk menghubungkan WhatsApp langsung di web
- Kirim tagihan per pelanggan (1 klik dari halaman tagihan)
- Kirim tagihan massal ke semua pelanggan sekaligus
- Auto-reminder tagihan belum bayar setiap pagi (Cronjob)
- Kirim pesan test untuk verifikasi koneksi

### 📊 Laporan Keuangan
- Dashboard pemasukan & pengeluaran
- Pencatatan pemasukan manual & otomatis dari pembayaran
- Pencatatan pengeluaran dengan kategori
- Master data kategori pemasukan & pengeluaran
- Export laporan keuangan

### 🔐 Keamanan
- Login dengan throttle / pembatasan percobaan (3x gagal → jeda 1 menit)
- Countdown timer saat login terkunci (tetap berlaku meskipun halaman di-refresh)
- Activity log untuk semua aksi penting

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12 (PHP 8.2) |
| Database | MySQL / MariaDB |
| Frontend | Blade + Bootstrap 4 + jQuery |
| PDF | barryvdh/laravel-dompdf |
| Alert | realrashid/sweet-alert |
| Activity Log | spatie/laravel-activitylog |
| WhatsApp API | Fonnte (fonnte.com) |
| MikroTik API | RouterOS API (Custom Class) |
| HTTP Client | Guzzle |

---

## 📌 Persyaratan Sistem

- **PHP** >= 8.2
- **Composer** >= 2.x
- **MySQL** >= 5.7 atau **MariaDB** >= 10.3
- **Node.js** >= 16 (opsional, untuk compile asset)
- **Web Server**: Apache / Nginx
- **Ekstensi PHP wajib**:
  - `php-mysql` / `php-pdo`
  - `php-mbstring`
  - `php-xml`
  - `php-curl`
  - `php-gd`
  - `php-zip`
  - `php-bcmath`

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/aplikasi-billing.git
cd aplikasi-billing
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billing_isp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Migrasi Database

```bash
php artisan migrate
```

### 5. Seed Data Awal (Opsional)

```bash
php artisan db:seed
```

### 6. Jalankan Server Development

```bash
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## ⚙️ Konfigurasi

### Koneksi MikroTik

1. Login ke aplikasi
2. Masuk ke menu **Setting** → tab **Koneksi MikroTik**
3. Isi:
   - **IP Address**: IP router MikroTik (contoh: `192.168.1.1`)
   - **Username**: username API MikroTik (contoh: `admin`)
   - **Password**: password MikroTik
4. Klik **Simpan**

> ⚠️ Pastikan API Service di MikroTik sudah diaktifkan:
> ```
> /ip service set api disabled=no
> ```

### Konfigurasi Billing

1. Menu **Setting** → tab **Konfigurasi Billing**
2. Isi nama perusahaan, alamat, telepon, email
3. Atur tanggal mulai periode tagihan dan hari jatuh tempo

### WhatsApp Gateway (Fonnte)

1. Daftar akun di [fonnte.com](https://fonnte.com)
2. Salin **API Token** dari dashboard Fonnte
3. Menu **Setting** → tab **WhatsApp**
4. Paste token → klik **Simpan Token**
5. Klik **Hubungkan WhatsApp** → scan QR Code dengan HP
6. Setelah status **Connected**, WhatsApp siap digunakan!

---

## ⏰ Cronjob / Task Scheduling

Aplikasi ini memiliki **3 perintah terjadwal** yang berjalan otomatis:

| Command | Jadwal | Fungsi |
|---------|--------|--------|
| `billing:disable-overdue` | Setiap jam | Menonaktifkan koneksi internet pelanggan yang telat bayar |
| `invoices:check-overdue` | Setiap jam | Mengubah status invoice menjadi *overdue* jika sudah lewat jatuh tempo |
| `invoices:send-unpaid` | Setiap hari jam 09:00 | Mengirim reminder WA ke pelanggan yang belum bayar |

### Setup Cronjob di Server (Linux / VPS)

Buka crontab:

```bash
crontab -e
```

Tambahkan baris berikut:

```bash
* * * * * cd /path/to/aplikasi-billing && php artisan schedule:run >> /dev/null 2>&1
```

> Ganti `/path/to/aplikasi-billing` dengan path absolut ke folder project Anda.

Contoh:

```bash
* * * * * cd /var/www/billing && php artisan schedule:run >> /dev/null 2>&1
```

### Setup di cPanel

1. Login ke **cPanel** → **Cron Jobs**
2. Setting: `Every Minute (* * * * *)`
3. Command:
   ```
   cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```

### Setup di Windows (Task Scheduler)

1. Buka **Task Scheduler** → Create Basic Task
2. Trigger: **Daily**, repeat every **1 minute**
3. Action: **Start a Program**
   - Program: `php`
   - Arguments: `artisan schedule:run`
   - Start in: `D:\Project\Laravel-master_1`

### Test Cronjob Manual

Untuk menjalankan cronjob secara manual (testing):

```bash
# Jalankan semua jadwal yang sudah waktunya
php artisan schedule:run

# Atau jalankan command satu per satu:
php artisan billing:disable-overdue
php artisan invoices:check-overdue
php artisan invoices:send-unpaid
```

---

## 🌍 Deploy ke Production

### 1. Upload File ke Server

Upload semua file project ke server via **Git**, **FTP**, atau **SSH**.

### 2. Ubah `.env` untuk Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_HOST=localhost
DB_DATABASE=nama_database_production
DB_USERNAME=user_production
DB_PASSWORD=password_production
```

### 3. Install Dependencies (Production)

```bash
composer install --optimize-autoloader --no-dev
```

### 4. Migrasi Database

```bash
php artisan migrate --force
```

### 5. Cache Konfigurasi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Permissions (Linux)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Setup Cronjob

```bash
crontab -e
# Tambahkan:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Konfigurasi Web Server

#### Apache (`.htaccess` sudah include di `public/`)

Arahkan Document Root ke folder `public/`:

```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    DocumentRoot /var/www/billing/public

    <Directory /var/www/billing/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/billing/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📁 Struktur Folder

```
├── app/
│   ├── Console/Commands/          # Artisan commands (cronjob)
│   │   ├── BillingDisableOverdue.php
│   │   ├── CheckOverdueInvoices.php
│   │   └── SendUnpaidInvoices.php
│   ├── Helpers/
│   │   └── helpers.php            # Helper functions (WA message template, dll)
│   ├── Http/Controllers/
│   │   ├── AdminAuthController.php
│   │   ├── DashboardController.php
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── PaketInternetController.php
│   │   ├── PelangganController.php
│   │   ├── PPPoEController.php
│   │   ├── SettingController.php
│   │   ├── WhatsAppController.php
│   │   └── ...
│   ├── Models/
│   │   ├── BillingConfig.php
│   │   ├── Invoice.php
│   │   ├── Paket.php
│   │   ├── Payment.php
│   │   ├── Pelanggan.php
│   │   ├── RouterosAPI.php        # MikroTik API library
│   │   └── ...
│   └── Services/
│       └── BillingService.php     # Generate tagihan bulanan
├── resources/views/
│   ├── auth/                      # Login page
│   ├── dashboard/                 # Dashboard views
│   ├── invoices/                  # Invoice views + PDF templates
│   ├── payments/                  # Payment views + struk
│   ├── pelanggan/                 # Customer management views
│   ├── setting/                   # Setting page (MikroTik, Billing, WA)
│   └── layouts/                   # Master layout + sidebar
├── routes/
│   └── web.php                    # All application routes
├── database/migrations/           # 39 migration files
└── .env                           # Environment configuration
```

---

## 📸 Screenshots

> Tambahkan screenshot aplikasi Anda di sini.

---

## 📄 Lisensi

Project ini menggunakan lisensi [MIT](LICENSE).

---

## 👨‍💻 Developer

Dibuat oleh **Markisanet Team**

📧 Customer Service: 081572024200
