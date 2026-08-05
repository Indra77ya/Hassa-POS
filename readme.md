# Panduan Instalasi HassaPOS

HassaPOS adalah aplikasi Point of Sales (Kasir) & Inventaris berbasis web yang modern dan mudah digunakan. Panduan ini dirancang khusus agar mudah dipahami, singkat, padat, dan sangat detail—bahkan bagi Anda yang masih awam.

---

## 📋 Persyaratan Sistem

Sebelum memulai instalasi, pastikan lingkungan (lokal atau server) memenuhi spesifikasi berikut:
*   **PHP**: Versi `8.1` atau `8.2` (Sangat disarankan **PHP 8.2**)
*   **Ekstensi PHP Wajib**: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `cURL`, `GD`, `ZIP`
*   **Database**: MySQL `5.7+` atau MariaDB `10.3+`
*   **Composer**: Versi 2.x (untuk mengelola paket PHP)
*   **Aset Frontend**: Sudah siap pakai (pre-compiled) di dalam folder `public/` (Tidak perlu Node.js/NPM)

---

## 💻 1. Instalasi di Komputer Lokal (localhost)

Ikuti 6 langkah mudah berikut untuk menjalankan HassaPOS di komputer Anda (menggunakan XAMPP/Laragon):

### Langkah 1: Unduh Kode Sumber (Source Code)
Buka Terminal (macOS/Linux) atau Git Bash / Command Prompt (Windows), lalu jalankan:
```bash
git clone https://github.com/Indra77ya/HassaPOS.git
cd HassaPOS
```

### Langkah 2: Pasang Dependensi PHP (Composer)
Jalankan perintah ini di dalam folder proyek Anda:
```bash
composer install
```
> **Tips PHP 8.3**: Jika komputer Anda menggunakan PHP 8.3, jalankan: `composer install --ignore-platform-reqs`

### Langkah 3: Konfigurasi Database & Environment
1.  Buka browser Anda dan masuk ke `http://localhost/phpmyadmin`.
2.  Buat database baru, misalnya dengan nama: `hassapos_db`.
3.  Kembali ke folder proyek, salin file `.env.example` dan ubah namanya menjadi `.env`.
4.  Buka file `.env` menggunakan teks editor (Notepad/VSCode), lalu sesuaikan konfigurasi berikut:
    ```env
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=hassapos_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    *(Kosongkan `DB_PASSWORD` jika Anda menggunakan XAMPP default bawaan Windows)*

### Langkah 4: Generate Kunci Aplikasi & Hubungkan Penyimpanan
Jalankan perintah berikut satu per satu:
```bash
php artisan key:generate
php artisan storage:link
```

### Langkah 5: Migrasi Database (Migrate & Seed)
Untuk membuat tabel-tabel database beserta data awal bawaan sistem, jalankan:
```bash
php artisan migrate --seed
```

### Langkah 6: Jalankan Aplikasi
Jalankan server lokal dengan perintah:
```bash
php artisan serve
```
Buka browser Anda dan akses: **`http://localhost:8000`**

---

## ☁️ 2. Instalasi di VPS Unmanaged (Ubuntu LTS - LAMP/LEMP)

Panduan ini menggunakan sistem operasi **Ubuntu 22.04 LTS** dengan **Nginx** (LEMP) atau **Apache** (LAMP).

### Langkah 1: Persiapan Server (Pasang PHP & MySQL)
Hubungkan ke VPS Anda via SSH (`ssh root@ip_vps_anda`), lalu update sistem dan pasang paket yang diperlukan:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install git curl unzip nginx mysql-server -y

# Tambahkan repositori PHP & Pasang PHP 8.2 beserta ekstensinya
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl -y
```

### Langkah 2: Buat Database di MySQL
Masuk ke terminal MySQL:
```bash
sudo mysql
```
Jalankan kueri berikut untuk membuat database dan pengguna baru (ganti `PasswordKuatAnda` dengan password yang aman):
```sql
CREATE DATABASE hassapos_db;
CREATE USER 'hassapos_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda';
GRANT ALL PRIVILEGES ON hassapos_db.* TO 'hassapos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Langkah 3: Clone Proyek & Pasang Composer
```bash
# Pasang Composer Secara Global
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Masuk ke direktori web dan clone kode
cd /var/www
sudo git clone https://github.com/Indra77ya/HassaPOS.git hassapos
cd hassapos

# Install dependensi
sudo composer install --no-dev --optimize-autoloader
```

### Langkah 4: Konfigurasi File `.env`
```bash
sudo cp .env.example .env
sudo nano .env
```
Ubah nilai-nilai berikut di dalam nano editor (tekan `Ctrl+O` lalu `Enter` untuk menyimpan, dan `Ctrl+X` untuk keluar):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hassapos_db
DB_USERNAME=hassapos_user
DB_PASSWORD=PasswordKuatAnda
```

### Langkah 5: Set Kunci Aplikasi, Link Storage & Jalankan Migrasi
```bash
sudo php artisan key:generate
sudo php artisan storage:link
sudo php artisan migrate --seed
```

### Langkah 6: Atur Hak Akses Folder (Permissions)
Web server membutuhkan hak akses khusus pada folder `storage` dan `bootstrap/cache`:
```bash
sudo chown -R www-data:www-data /var/www/hassapos
sudo chmod -R 775 /var/www/hassapos/storage
sudo chmod -R 775 /var/www/hassapos/bootstrap/cache
```

### Langkah 7: Konfigurasi Nginx Server Block
Buat konfigurasi virtual host baru untuk Nginx:
```bash
sudo nano /etc/nginx/sites-available/hassapos
```
Tempelkan konfigurasi berikut (ganti `domainanda.com` dengan domain asli Anda):
```nginx
server {
    listen 80;
    server_name domainanda.com;
    root /var/www/hassapos/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/hassapos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🛠️ 3. Instalasi di VPS Managed / Control Panel (aaPanel, cPanel, CyberPanel, RunCloud)

Jika Anda menggunakan control panel, proses instalasi menjadi jauh lebih mudah melalui antarmuka grafis (GUI).

### A. Panduan aaPanel (Sangat Direkomendasikan)
1.  **Siapkan Website & PHP**:
    *   Masuk ke aaPanel > menu **App Store** > pastikan **PHP 8.2** dan **MySQL** sudah terpasang.
    *   Klik menu **Website** > **Add site**. Isi domain Anda, pilih versi PHP 8.2, dan centang opsi pembuatan database sekalian.
2.  **Upload File**:
    *   Masuk ke menu **Files** > buka folder root website yang baru dibuat (biasanya di `/www/wwwroot/domain_anda`).
    *   Klik **Upload**, pilih file ZIP proyek HassaPOS yang sudah Anda download, lalu **Extract** langsung di folder tersebut.
3.  **Arahkan Document Root ke `/public`**:
    *   Kembali ke menu **Website**, klik pada nama domain Anda untuk membuka pengaturan (*Settings*).
    *   Pilih tab **Site directory**, ubah bagian **Running directory** dari `/` menjadi **`/public`**, lalu klik **Save**.
4.  **Konfigurasi `.env` & Terminal**:
    *   Buka menu **Files** > temukan file `.env.example`, rename menjadi `.env`, buka dan edit detail koneksi database serta `APP_URL`.
    *   Masuk ke menu **Terminal** di aaPanel (atau SSH ke VPS), lalu arahkan ke folder web (`cd /www/wwwroot/domain_anda`).
    *   Jalankan perintah-perintah ini berurutan:
        ```bash
        composer install --no-dev --optimize-autoloader
        php artisan key:generate
        php artisan storage:link
        php artisan migrate --seed
        ```
    *   Pastikan hak akses folder aman dengan menekan tombol **Fix Permission** di menu Files atau jalankan: `chown -R www:www /www/wwwroot/domain_anda`.

### B. Panduan cPanel (Hosting Tradisional)
1.  **Upload & Ekstrak**:
    *   Masuk ke cPanel > **File Manager** > buka direktori `public_html` (atau folder subdomain Anda).
    *   Unggah file ZIP HassaPOS, lalu ekstrak. Pastikan semua file proyek berada di dalam folder tersebut.
2.  **Konfigurasi Versi PHP**:
    *   Masuk ke cPanel > cari menu **Select PHP Version** atau **MultiPHP Manager**.
    *   Pilih domain Anda dan ubah versinya menjadi **PHP 8.2**. Pastikan ekstensi seperti `bcmath`, `fileinfo`, `gd`, `zip`, dan `pdo_mysql` dicentang.
3.  **Buat Database**:
    *   Masuk ke cPanel > **MySQL Database Wizard**.
    *   Buat database baru, buat user database baru, catat passwordnya, lalu berikan hak akses penuh (*All Privileges*) kepada user tersebut ke database.
4.  **Edit File `.env`**:
    *   Di **File Manager**, klik kanan pada file `.env.example`, ganti nama menjadi `.env`.
    *   Edit file `.env` dan masukkan detail database yang baru dibuat beserta URL website Anda.
5.  **Arahkan Domain ke folder `/public`**:
    *   Masuk ke cPanel > **Domains** atau **Subdomains**.
    *   Ubah bagian *Document Root* agar mengarah ke `public_html/public` (bukan hanya `public_html`).
6.  **Jalankan Perintah Artisan via Terminal / Cron**:
    *   Jika cPanel Anda memiliki fitur **Terminal**, buka terminal dan jalankan:
        ```bash
        composer install --no-dev --optimize-autoloader
        php artisan key:generate
        php artisan storage:link
        php artisan migrate --seed
        ```
    *   *Jika tidak memiliki akses Terminal*: Anda dapat menggunakan fitur **Cron Jobs** di cPanel untuk menjalankan migrasi sekali saja, contoh command cron: `/usr/local/bin/php /home/username_cpanel/public_html/artisan migrate --seed`. Setelah berhasil dijalankan, hapus kembali cron tersebut.

### C. Panduan CyberPanel
1.  **Buat Website**:
    *   Masuk ke dashboard CyberPanel > **Websites** > **Create Website**.
    *   Pilih Paket, Pemilik, isi Domain, pilih **PHP 8.2**, lalu klik **Create Website**.
2.  **Upload File & Extract**:
    *   Buka **Websites** > **List Websites** > klik **Manage** pada domain Anda > buka **File Manager**.
    *   Masuk ke folder `public_html`, hapus file `index.html` bawaan.
    *   Upload file ZIP HassaPOS, lalu ekstrak.
3.  **Arahkan Document Root (Rewrite Rules)**:
    *   Secara default, OpenLiteSpeed membaca folder `public_html`. Agar otomatis mengarah ke subfolder `public_html/public`, klik menu **Rewrite Rules** di halaman managemen website, lalu tambahkan aturan rewrite berikut:
        ```apache
        RewriteEngine On
        RewriteRule ^(.*)$ public/$1 [L]
        ```
4.  **Buat Database & Setup `.env`**:
    *   Masuk ke CyberPanel > **Databases** > **Create Database**. Catat nama DB, User, dan Password.
    *   Di File Manager, ubah nama `.env.example` menjadi `.env`, lalu masukkan detail database dan URL domain Anda.
5.  **Jalankan Migrasi**:
    *   Buka SSH VPS Anda, masuk ke folder `/home/domainanda.com/public_html`.
    *   Jalankan:
        ```bash
        composer install --no-dev --optimize-autoloader
        php artisan key:generate
        php artisan storage:link
        php artisan migrate --seed
        chown -r lsadm:lsadm /home/domainanda.com/public_html
        ```

### D. Panduan RunCloud
1.  **Buat Web Application**:
    *   Masuk ke RunCloud > pilih server Anda > **Web Application** > **Create Web Application**.
    *   Pilih **Empty Web Application**, isi nama, pilih domain, gunakan **PHP 8.2**, dan pastikan user server (misalnya `runcloud`) dipilih dengan benar.
2.  **Ganti Public Directory**:
    *   Pada pengaturan Web Application Anda di RunCloud, masuk ke menu **Web Application Settings**.
    *   Ubah kolom **Public Directory** dari `/` menjadi **`/public`**, lalu klik **Update**.
3.  **Upload & Install**:
    *   Gunakan Git Deployment bawaan RunCloud atau upload file Anda via SFTP ke folder `/home/runcloud/webapps/nama_app`.
    *   Hubungi terminal SSH server, jalankan perintah:
        ```bash
        cd /home/runcloud/webapps/nama_app
        composer install --no-dev --optimize-autoloader
        cp .env.example .env
        nano .env  # Masukkan konfigurasi database & APP_URL Anda
        php artisan key:generate
        php artisan storage:link
        php artisan migrate --seed
        ```

---

## 🔑 4. Mengonfigurasi Akun Administrator Utama

HassaPOS memiliki fitur keamanan di mana pengguna dengan status Administrator Utama (Superadmin) didefinisikan secara eksplisit melalui file konfigurasi `.env`.

Berikut adalah langkah-langkah untuk mendaftarkan dan menjadikan akun Anda sebagai Administrator Utama:

1.  Buka aplikasi HassaPOS yang sudah berjalan di browser Anda.
2.  Daftarkan akun baru melalui halaman pendaftaran (*Register*) yang tersedia di aplikasi. Catat **Username** yang Anda buat saat pendaftaran.
3.  Buka file `.env` di server/komputer Anda menggunakan teks editor.
4.  Temukan baris konfigurasi `ADMINISTRATOR_USERNAMES`.
5.  Masukkan username yang telah Anda daftarkan sebelumnya. Jika ada lebih dari satu username administrator, pisahkan dengan tanda koma (tanpa spasi):
    ```env
    ADMINISTRATOR_USERNAMES=username_anda,username_admin_kedua
    ```
6.  Simpan file `.env`. Akun tersebut kini telah resmi menjadi Administrator Utama dengan akses kontrol penuh ke seluruh fitur sistem.

---

## 🕒 5. Konfigurasi Cron Job (Wajib di VPS)

Agar fitur-fitur terjadwal seperti otomatisasi backup, notifikasi jatuh tempo, dan kalkulasi periodik berjalan otomatis, tambahkan satu baris cron job di VPS Anda.

1.  Di VPS, jalankan perintah:
    ```bash
    crontab -e
    ```
2.  Tambahkan baris berikut di bagian paling bawah file:
    ```cron
    * * * * * cd /path/to/your/hassapos && php artisan schedule:run >> /dev/null 2>&1
    ```
    *(Sesuaikan `/path/to/your/hassapos` dengan lokasi asli folder proyek Anda, misalnya `/var/www/hassapos`)*

---

## ❓ Troubleshooting & Solusi Masalah Umum

### 1. Error 500 (Halaman Putih atau Internal Server Error)
*   **Penyebab 1**: Izin akses (*permissions*) folder storage tidak tepat.
    *   *Solusi*: Jalankan perintah `chmod -R 775 storage bootstrap/cache` di terminal server.
*   **Penyebab 2**: File `.env` belum dibuat atau konfigurasinya salah.
    *   *Solusi*: Pastikan `.env` sudah ada dan konfigurasi database Anda valid.

### 2. Error Database Connection (Gagal Konek Database)
*   **Penyebab**: Detail host, username, atau password di `.env` salah.
    *   *Solusi*: Periksa kembali nama database dan kredensial MySQL Anda. Pastikan database server aktif (`sudo systemctl status mysql`).

### 3. Error Versi PHP pada Composer di Shared Hosting / cPanel
*   **Penyebab**: Terminal hosting masih menggunakan versi PHP bawaan yang usang (misal PHP 7.4) sedangkan PHP website sudah diset ke 8.2.
    *   *Solusi*: Jalankan composer dengan memanggil langsung binary PHP 8.2 di hosting Anda. Contoh:
        ```bash
        /usr/local/bin/php82 $(which composer) install --ignore-platform-reqs
        ```
        *(Sesuaikan `/usr/local/bin/php82` dengan path php8.2 asli pada hosting Anda)*

### 4. Menghapus Cache Jika Melakukan Perubahan Konfigurasi
Setiap kali Anda mengedit file `.env` atau file konfigurasi lainnya di lingkungan produksi (*production*), jalankan perintah ini agar perubahan segera diterapkan:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

Selamat menggunakan **HassaPOS**! Semoga bisnis Anda berjalan lancar dan semakin sukses dengan bantuan sistem kasir pintar ini. Jika ada pertanyaan, silakan hubungi tim pengembang atau buat issue di repository ini.
