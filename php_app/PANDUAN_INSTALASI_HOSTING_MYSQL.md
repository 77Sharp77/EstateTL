# 📖 Panduan Lengkap Instalasi Web Kalicaa Villa di Hosting & MySQL

Dokumen ini berisi panduan langkah demi langkah cara memasang (*deploy*) aplikasi **Kalicaa Villa Estate Management & Billing System (PHP)** ke Web Hosting (cPanel, DirectAdmin, Plesk, Hostinger, Niagahoster, DomaiNesia, dll.) maupun Cloud VPS (Ubuntu/Debian).

---

## 📦 1. Ringkasan File Database Tunggal (`database.sql`)

Untuk memudahkan Anda, seluruh skema tabel (DDL) dan data awal (Master Seeder) kini telah disatukan menjadi **1 file SQL tunggal**:
* 📄 **Lokasi File:** 
  * `php_app/database.sql` *(di root folder aplikasi)*
  * `php_app/database/database.sql` *(di dalam folder database)*

### Isi dari `database.sql`:
1. **13 Tabel Lengkap**: `app_settings`, `users`, `master_coa`, `debiturs`, `skp_properties`, `invoices`, `invoice_items`, `rvbw`, `rvbw_linked_invoices`, `spk_records`, `spk_payments`, `titipan_rvbw`, `titipan_pvbw`.
2. **Data Master Awal**:
   * Pengaturan tarif PLN, PDAM, IPL Kawasan, dan data rekening bank.
   * Master COA Akuntansi.
   * 4 Akun login pengguna default dengan berbagai role (Super Admin, Finance, Billing, Manager).
   * Data contoh debitur, unit villa, kontrak SKP, SPK proyek, dan titipan dana.

---

## ⚙️ 2. Kebutuhan Sistem (System Requirements)

Pastikan paket hosting Anda memenuhi spesifikasi berikut:
* **Versi PHP**: PHP 8.1, PHP 8.2, atau PHP 8.3+ *(Rekomendasi: PHP 8.2)*
* **Ekstensi PHP Aktif**:
  * `pdo_mysql` (Wajib untuk koneksi database MySQL)
  * `mbstring`
  * `json`
  * `session`
* **Database**: MySQL 5.7+, MySQL 8.0+, atau MariaDB 10.3+
* **Web Server**: Apache / LiteSpeed (mendukung `mod_rewrite` via `.htaccess`) atau Nginx.

---

## 🚀 3. Langkah Instalasi di cPanel Hosting

### Langkah 3.1: Membuat Database MySQL di cPanel
1. Login ke **cPanel** akun hosting Anda.
2. Di bagian menu **Databases**, pilih **MySQL® Database Wizard** atau **MySQL® Databases**.
3. **Buat Database Baru**: Masukkan nama database (misalnya: `kalicaa_db`), lalu klik *Next Step*. (Nama lengkap biasanya berformat `usernamecpanel_kalicaa_db`).
4. **Buat User Database**:
   * Username: Masukkan nama user (misalnya: `kalicaa_user`).
   * Password: Buat password yang kuat dan catat password tersebut.
   * Klik *Create User*.
5. **Berikan Hak Akses**:
   * Centang opsi **ALL PRIVILEGES** (Semua Hak Akses).
   * Klik tombol **Make Changes** / *Next Step*.

---

### Langkah 3.2: Import File `database.sql` ke MySQL via phpMyAdmin
1. Kembali ke halaman utama cPanel, buka menu **phpMyAdmin**.
2. Pada panel navigasi di sebelah kiri, klik nama database yang baru saja Anda buat di Langkah 3.1.
3. Klik tab **Import** pada baris menu bagian atas.
4. Pada bagian **File to import** (*Berkas untuk diimpor*):
   * Klik tombol **Choose File** / *Pilih Berkas*.
   * Pilih file `database.sql` dari komputer Anda.
5. Biarkan pengaturan lainnya secara default (*Character set: utf-8*).
6. Gulir ke bawah dan klik tombol **Import** / **Kirim**.
7. Tunggu beberapa detik hingga muncul notifikasi hijau: 
   > *"Import has been successfully finished, queries executed."*

---

### Langkah 3.3: Upload File Aplikasi Web ke Hosting

Terdapat 2 opsi penempatan file di hosting:

#### ✅ Opsi A (Paling Mudah & Cepat):
1. Buka **File Manager** di cPanel.
2. Masuk ke folder `public_html`.
3. Upload seluruh isi folder `php_app` langsung ke dalam `public_html`.
4. File `.htaccess` bawaan yang sudah kami sertakan di root akan secara otomatis meneruskan setiap request pengunjung ke folder `public/index.php`.

#### 🛡️ Opsi B (Standar Keamanan Tertinggi / Best Practice):
1. Di File Manager cPanel, buat folder baru di luar `public_html`, misalnya: `/home/username/kalicaa_core/`.
2. Upload folder `config`, `database`, `src`, dan `views` ke dalam folder `/home/username/kalicaa_core/`.
3. Upload isi dari folder `php_app/public/` (yaitu `index.php` dan `.htaccess`) langsung ke dalam `public_html/`.
4. Buka file `public_html/index.php` lalu sesuaikan path autoloader agar mengarah ke folder `/home/username/kalicaa_core/`.

---

### Langkah 3.4: Konfigurasi Koneksi Database

Ada dua cara mudah untuk mengatur koneksi database:

#### Cara 1: Menggunakan File `.env` (Disarankan)
1. Di folder aplikasi hosting, temukan file bernama `.env.example`.
2. Ubah nama (*Rename*) file tersebut menjadi `.env`.
3. Buka dan edit file `.env`, lalu masukkan data database Anda:
   ```env
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=usernamecpanel_kalicaa_db
   DB_USER=usernamecpanel_kalicaa_user
   DB_PASS=PasswordDatabaseAnda123
   ```
4. Simpan perubahan file (*Save Changes*).

#### Cara 2: Edit Langsung di `config/Database.php`
Jika hosting Anda tidak mengizinkan file `.env`, Anda bisa langsung mengedit file `php_app/config/Database.php`:
```php
$driver   = 'mysql';
$host     = 'localhost';                     // atau 127.0.0.1
$port     = '3306';
$dbname   = 'usernamecpanel_kalicaa_db';     // Nama database Anda
$user     = 'usernamecpanel_kalicaa_user';   // Username database Anda
$password = 'PasswordDatabaseAnda123';        // Password database Anda
```

---

## 🔑 4. Akun Login Pengguna Bawaan (Default Login)

Setelah instalasi selesai, buka domain Anda di browser:
👉 **`https://domainanda.com`**

Gunakan salah satu akun berikut untuk masuk:

| Role / Jabatan | Username | Password Default | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `password123` | Akses Penuh: Konfigurasi Tarif, Master Data, Hapus Data, Cetak |
| **Finance Admin** | `finance` | `password123` | Buat Invoice, Validasi RVBW, Kartu Piutang, Laporan Keuangan |
| **Billing Officer** | `billing` | `password123` | Input Meteran Listrik/Air, Cetak Tagihan, Kirim WhatsApp |
| **Resort Manager** | `manager` | `password123` | Approval RVBW, Monitoring SPK Proyek, Dashboard Eksekutif |

> ⚠️ **PENTING**: Segera ubah password akun default ini setelah pertama kali login melalui menu Pengaturan Pengguna (*User Settings*).

---

## 🖥️ 5. Panduan Instalasi di VPS Linux (Ubuntu/Debian + Nginx/Apache)

Jika Anda menggunakan VPS mandiri:

### 1. Import Database via Command Line:
```bash
mysql -u root -p -e "CREATE DATABASE kalicaa_estate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p kalicaa_estate < /var/www/kalicaa/database.sql
```

### 2. Contoh Konfigurasi VirtualHost Nginx (`/etc/nginx/sites-available/kalicaa.conf`):
```nginx
server {
    listen 80;
    server_name billing.domainanda.com;
    root /var/www/kalicaa/public;
    index index.php index.html;

    charset utf-8;

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

### 3. Setting Hak Akses Folder:
```bash
chown -R www-data:www-data /var/www/kalicaa
chmod -R 755 /var/www/kalicaa
```

---

## 🛠️ 6. Troubleshooting & Pertanyaan Umum (FAQ)

### Q1: Muncul pesan *"Koneksi Database Gagal: Access denied for user..."*
* **Solusi**: Periksa kembali `DB_USER` dan `DB_PASS`. Pastikan user database sudah diberikan izin **ALL PRIVILEGES** ke database terkait di menu cPanel MySQL.

### Q2: Muncul pesan *"Unknown database..."*
* **Solusi**: Di cPanel, nama database biasanya memiliki prefix username cPanel (contoh: `usrcpanel_kalicaa`). Pastikan Anda menuliskan nama lengkapnya.

### Q3: Tampilan halaman 404 saat klik menu/navigasi
* **Solusi**: Pastikan modul `mod_rewrite` aktif pada server Apache/LiteSpeed, dan file `.htaccess` di folder `public/` ter-upload dengan benar (terkadang file dengan awalan titik tersembunyi/hidden di FTP/File Manager).

### Q4: Apakah database SQLite bawaan masih bisa digunakan?
* **Solusi**: Ya. Sistem dirancang cerdas (*smart auto-fallback*). Jika driver diatur ke `sqlite` atau MySQL belum terpasang, aplikasi tetap dapat berjalan menggunakan database SQLite lokal yang tersimpan di `php_app/database/database.sqlite`.

---

🎉 **Selamat! Web Kalicaa Villa Estate Management & Billing System telah berhasil terpasang dan siap digunakan di hosting Anda.**
