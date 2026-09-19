# 🏡 Panduan Memasang Subdomain di Domain Utama: `docutrack7.web.id`

Sekarang jelas sekali penyebab error **NXDOMAIN** sebelumnya! Domain `tanjunglesung.com` bukan milik akun hosting Anda atau belum dibeli DNS-nya. 

Karena domain utama hosting Anda adalah:
👉 **`docutrack7.web.id`**

Maka Anda **TIDAK PERLU MEMBELI DOMAIN APAPUN!** Cukup buat subdomain di bawah domain utama Anda, contohnya:
👉 **`estate.docutrack7.web.id`** *(atau `billing.docutrack7.web.id`)*

Subdomain ini **100% GRATIS**, langsung aktif, dan otomatis terhubung dengan hosting cPanel Anda.

---

## 🚀 4 Langkah Sangat Mudah di cPanel:

### Langkah 1: Buat Subdomain di cPanel
1. Login ke **cPanel** akun Anda (`docutrac`).
2. Cari dan klik menu **Domains** (atau **Subdomains**).
3. Klik tombol **Create A New Domain** (atau form *Create a Subdomain*):
   * **Domain:** `estate.docutrack7.web.id`
   * Hapus centang pada *"Share document root (/home/docutrac/public_html) with other domain"*.
   * **Document Root:** Isi dengan lokasi folder tempat file php_app berada, misalnya:
     * `/home/docutrac/estate.docutrack7.web.id/public` *(Sangat Disarankan / Paling Aman)*
     * ATAU folder yang sudah ada saat ini: `/home/docutrac/estate.tanjunglesung.com/public` (bisa diarahkan ke folder ini tanpa perlu memindahkan file!).
4. Klik tombol **Submit** / **Create**.

---

### Langkah 2: Lokasi File Website
Jika Anda mengarahkan Document Root subdomain ke folder yang sudah Anda buat sebelumnya di File Manager cPanel:
* Lokasi Folder: `/home/docutrac/estate.tanjunglesung.com/` (atau folder baru `/home/docutrac/estate.docutrack7.web.id/`).
* Pastikan seluruh file `php_app` berada di folder tersebut.

---

### Langkah 3: Konfigurasi Database
Kredensial database MySQL Anda yang sudah dibuat tetap sama dan siap digunakan:
* **Host:** `127.0.0.1` (atau `localhost` / `195.88.211.212`)
* **Database:** `docutrac_estate.tanjunglesung.com`
* **User:** `docutrac_Jo`
* **Password:** `tanjunglesung7`

*(Nilai ini sudah terpasang otomatis di file `config/Database.php`)*.

---

### Langkah 4: Buka Website di Browser
Setelah membuat subdomain di Langkah 1, langsung buka di browser Anda:
👉 **`http://estate.docutrack7.web.id`** *(atau aktifkan SSL di menu SSL/TLS Status untuk HTTPS)*

Error **`DNS_PROBE_FINISHED_NXDOMAIN`** dijamin **hilang**, dan halaman login Kalicaa Villa Estate Management langsung muncul!

* **Username:** `superadmin`
* **Password:** `password123`
