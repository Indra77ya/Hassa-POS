# Panduan Instalasi HassaPOS

HassaPOS adalah aplikasi Point of Sales (Kasir) & Inventaris berbasis web yang modern dan mudah digunakan. Panduan ini dirancang khusus agar mudah dipahami, singkat, padat, dan sangat detail, bahkan bagi Anda yang masih awam.

## Persyaratan Sistem

Sebelum memulai instalasi, pastikan lingkungan (lokal atau server) memenuhi spesifikasi berikut:
*   **PHP**: Versi `8.1` atau `8.2` (Sangat disarankan **PHP 8.2**)
*   **Ekstensi PHP Wajib**: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `cURL`, `GD`, `ZIP`
*   **Database**: MySQL `5.7+` atau MariaDB `10.3+`
*   **Composer**: Versi 2.x (untuk mengelola paket PHP)
*   **Aset Frontend**: Sudah siap pakai (pre-compiled) di dalam folder `public/` (Tidak perlu Node.js/NPM)

## 1. Instalasi di Komputer Lokal (localhost)

Ada dua software web server lokal terpopuler yang sangat disarankan untuk orang awam di Windows, yaitu **Laragon** (Sangat Direkomendasikan) atau **XAMPP**.

Pilih salah satu panduan di bawah ini yang sesuai dengan software yang Anda gunakan:

### Pilihan A: Menggunakan Laragon (Sangat Direkomendasikan)
Laragon sangat praktis karena mengelola database, composer, dan pembuatan domain lokal secara otomatis. Anda tidak perlu menjalankan perintah php artisan serve jika menggunakan Laragon.

1.  **Unduh & Jalankan Laragon**:
    *   Unduh dan install Laragon dari situs resminya (pilih versi yang menyertakan **PHP 8.1** atau **PHP 8.2**).
    *   Buka aplikasi Laragon, lalu klik tombol **Start All**.
2.  **Masuk ke Folder Root**:
    *   Buka folder instalasi Laragon di komputer Anda, biasanya terletak di:
        ```text
        C:\laragon\www
        ```
    *   Klik kanan di area kosong di dalam folder `www` tersebut, lalu pilih **Git Bash Here** atau buka Terminal/Command Prompt di lokasi tersebut.
3.  **Unduh Source Code (Clone)**:
    *   Jalankan perintah clone berikut:
        ```bash
        git clone https://github.com/Indra77ya/HassaPOS.git
        ```
    *   Masuk ke folder proyek yang baru diunduh:
        ```bash
        cd HassaPOS
        ```
4.  **Pasang Dependensi**:
    *   Jalankan perintah Composer untuk memasang seluruh paket pendukung Laravel:
        ```bash
        composer install
        ```
5.  **Setup Database via Database Tool**:
    *   Pada aplikasi Laragon, klik tombol **Database** (membuka HeidiSQL atau phpMyAdmin).
    *   Klik kanan di daftar database sebelah kiri, pilih **Create new** > **Database**.
    *   Beri nama database baru tersebut, misalnya: `hassapos_db`, lalu klik **OK** / simpan.
6.  **Konfigurasi File `.env`**:
    *   Salin file template `.env.example` dan ubah namanya menjadi `.env`.
    *   Buka file `.env` menggunakan notepad atau VS Code, lalu sesuaikan bagian koneksi database:
        ```env
        APP_ENV=local
        APP_DEBUG=true
        APP_URL=http://hassapos.test

        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=hassapos_db
        DB_USERNAME=root
        DB_PASSWORD=
        ```
        *(Di Laragon, password mysql bawaannya adalah kosong/blank)*
7.  **Generate Key, Storage Link, & Migrasi**:
    *   Kembali ke terminal, jalankan perintah pembuatan kunci aplikasi:
        ```bash
        php artisan key:generate
        ```
    *   Hubungkan folder penyimpanan file:
        ```bash
        php artisan storage:link
        ```
    *   Buat struktur tabel database dan isi data awal otomatis:
        ```bash
        php artisan migrate --seed
        ```
8.  **Buka Aplikasi (Tanpa php artisan serve)**:
    *   Laragon secara otomatis membuat domain lokal yang cantik dan siap diakses tanpa perlu menjalankan perintah php artisan serve di terminal.
    *   Cukup buka browser Anda, dan langsung ketik alamat: **`http://hassapos.test`** (Atau klik kanan pada jendela Laragon, klik **Web**, lalu pilih **HassaPOS**).

### Pilihan B: Menggunakan XAMPP
XAMPP adalah alternatif klasik yang sangat populer digunakan oleh pengembang web di Windows.

1.  **Jalankan XAMPP Control Panel**:
    *   Buka aplikasi **XAMPP Control Panel** di komputer Anda.
    *   Klik tombol **Start** pada baris **Apache** dan **MySQL** hingga indikatornya berwarna hijau.
2.  **Masuk ke Folder Root (htdocs)**:
    *   Buka folder instalasi XAMPP Anda, biasanya di:
        ```text
        C:\xampp\htdocs
        ```
    *   Buka Terminal, Git Bash, atau Command Prompt di lokasi folder `htdocs` tersebut.
3.  **Unduh Source Code (Clone)**:
    *   Jalankan perintah clone berikut:
        ```bash
        git clone https://github.com/Indra77ya/HassaPOS.git
        ```
    *   Masuk ke folder proyek yang baru diunduh:
        ```bash
        cd HassaPOS
        ```
4.  **Pasang Dependensi**:
    *   Pastikan Anda sudah mengunduh dan menginstal **Composer** secara global di Windows Anda.
    *   Jalankan perintah Composer di terminal:
        ```bash
        composer install
        ```
        *(Jika komputer Anda sudah menggunakan PHP 8.3, jalankan: `composer install --ignore-platform-reqs`)*
5.  **Buat Database via phpMyAdmin**:
    *   Buka browser Anda, lalu ketik alamat: **`http://localhost/phpmyadmin`**
    *   Klik menu **Baru** (New) di panel sebelah kiri.
    *   Tulis nama database baru: `hassapos_db`, lalu klik tombol **Buat** (Create).
6.  **Konfigurasi File `.env`**:
    *   Salin file template `.env.example` dan ubah namanya menjadi `.env`.
    *   Buka file `.env` menggunakan notepad atau VS Code, lalu masukkan detail konfigurasi database berikut:
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
        *(Di XAMPP, username default database adalah `root` dan password-nya kosong/blank)*
7.  **Generate Key, Storage Link, & Migrasi**:
    *   Jalankan perintah pembuatan kunci aplikasi:
        ```bash
        php artisan key:generate
        ```
    *   Hubungkan folder penyimpanan file:
        ```bash
        php artisan storage:link
        ```
    *   Buat struktur tabel database dan isi data awal otomatis:
        ```bash
        php artisan migrate --seed
        ```
8.  **Jalankan Server Lokal**:
    *   Karena Anda menggunakan XAMPP, Anda wajib menjalankan server pengembangan PHP secara manual agar web bisa diakses. Jalankan perintah berikut di terminal:
        ```bash
        php artisan serve
        ```
    *   Buka browser Anda dan akses alamat: **`http://localhost:8000`**

## 2. Instalasi di VPS Unmanaged (Ubuntu LTS - LAMP/LEMP)

Panduan ini menggunakan sistem operasi **Ubuntu 22.04 LTS** dengan **Nginx** (LEMP) atau **Apache** (LAMP).

### Langkah 1: Persiapan Server (Pasang PHP & MySQL)
Hubungkan ke VPS Anda via SSH (`ssh root@ip_vps_anda`).

Langkah pertama, update sistem Anda:
```bash
sudo apt update && sudo apt upgrade -y
```
Pasang paket Git, Curl, Unzip, Nginx, dan MySQL Server:
```bash
sudo apt install git curl unzip nginx mysql-server -y
```
Tambahkan repositori PHP agar mendapatkan versi PHP terbaru:
```bash
sudo apt install software-properties-common -y
```
```bash
sudo add-apt-repository ppa:ondrej/php -y
```
Update kembali list repositori server Anda:
```bash
sudo apt update
```
Pasang PHP 8.2 beserta ekstensinya yang dibutuhkan:
```bash
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl -y
```

### Langkah 2: Buat Database di MySQL
Masuk ke terminal MySQL:
```bash
sudo mysql
```
Jalankan kueri berikut satu per satu untuk membuat database dan pengguna baru (ganti `PasswordKuatAnda` dengan password yang aman):
```sql
CREATE DATABASE hassapos_db;
```
```sql
CREATE USER 'hassapos_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda';
```
```sql
GRANT ALL PRIVILEGES ON hassapos_db.* TO 'hassapos_user'@'localhost';
```
```sql
FLUSH PRIVILEGES;
```
```sql
EXIT;
```

### Langkah 3: Clone Proyek & Pasang Composer
Unduh Composer installer:
```bash
curl -sS https://getcomposer.org/installer | php
```
Pindahkan Composer agar dapat digunakan secara global di server:
```bash
sudo mv composer.phar /usr/local/bin/composer
```
Masuk ke direktori web server:
```bash
cd /var/www
```
Unduh kode sumber HassaPOS menggunakan Git:
```bash
sudo git clone https://github.com/Indra77ya/HassaPOS.git hassapos
```
Masuk ke dalam direktori proyek tersebut:
```bash
cd hassapos
```
Pasang dependensi PHP proyek dengan Composer:
```bash
sudo composer install --no-dev --optimize-autoloader
```

### Langkah 4: Konfigurasi File `.env`
Salin template konfigurasi:
```bash
sudo cp .env.example .env
```
Buka file `.env` menggunakan nano editor untuk melakukan penyesuaian:
```bash
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
Jalankan perintah generate key:
```bash
sudo php artisan key:generate
```
Hubungkan folder penyimpanan publik:
```bash
sudo php artisan storage:link
```
Jalankan migrasi database serta pengisian data awal (seeding):
```bash
sudo php artisan migrate --seed
```

### Langkah 6: Atur Hak Akses Folder (Permissions)
Web server membutuhkan hak akses khusus pada folder `storage` dan `bootstrap/cache`:
```bash
sudo chown -R www-data:www-data /var/www/hassapos
```
```bash
sudo chmod -R 775 /var/www/hassapos/storage
```
```bash
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
Hubungkan konfigurasi tersebut agar aktif di Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/hassapos /etc/nginx/sites-enabled/
```
Uji apakah konfigurasi Nginx sudah benar dan tidak ada error:
```bash
sudo nginx -t
```
Restart layanan Nginx untuk menerapkan perubahan:
```bash
sudo systemctl restart nginx
```

## 3. Instalasi di VPS Managed / Control Panel (aaPanel, cPanel, CyberPanel, RunCloud)

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
    *   Masuk ke menu **Terminal** di aaPanel (atau SSH ke VPS), lalu arahkan ke folder web Anda:
        ```bash
        cd /www/wwwroot/domain_anda
        ```
    *   Pasang dependensi dengan Composer:
        ```bash
        composer install --no-dev --optimize-autoloader
        ```
    *   Generate kunci aplikasi:
        ```bash
        php artisan key:generate
        ```
    *   Hubungkan folder storage:
        ```bash
        php artisan storage:link
        ```
    *   Jalankan migrasi database:
        ```bash
        php artisan migrate --seed
        ```
    *   Perbaiki hak akses folder website dengan menekan tombol **Fix Permission** di menu Files aaPanel, atau jalankan perintah:
        ```bash
        chown -R www:www /www/wwwroot/domain_anda
        ```

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
    *   Jika cPanel Anda memiliki fitur **Terminal**, buka terminal, arahkan ke folder proyek, lalu jalankan satu per satu:
        ```bash
        composer install --no-dev --optimize-autoloader
        ```
        ```bash
        php artisan key:generate
        ```
        ```bash
        php artisan storage:link
        ```
        ```bash
        php artisan migrate --seed
        ```
    *   *Jika tidak memiliki akses Terminal*: Anda dapat menggunakan fitur **Cron Jobs** di cPanel untuk menjalankan migrasi sekali saja, contoh command cron:
        ```bash
        /usr/local/bin/php /home/username_cpanel/public_html/artisan migrate --seed
        ```
        *(Setelah berhasil dijalankan, hapus kembali cron tersebut)*.

### C. Panduan CyberPanel
1.  **Buat Website**:
    *   Masuk di dashboard CyberPanel > **Websites** > **Create Website**.
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
    *   Buka SSH VPS Anda, masuk ke folder aplikasi:
        ```bash
        cd /home/domainanda.com/public_html
        ```
    *   Pasang dependensi dengan Composer:
        ```bash
        composer install --no-dev --optimize-autoloader
        ```
    *   Generate kunci aplikasi:
        ```bash
        php artisan key:generate
        ```
    *   Hubungkan folder storage:
        ```bash
        php artisan storage:link
        ```
    *   Jalankan migrasi database:
        ```bash
        php artisan migrate --seed
        ```
    *   Perbaiki kepemilikan file agar dapat dibaca server OpenLiteSpeed:
        ```bash
        chown -R lsadm:lsadm /home/domainanda.com/public_html
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
    *   Masuk ke terminal SSH server, lalu arahkan ke folder web application Anda:
        ```bash
        cd /home/runcloud/webapps/nama_app
        ```
    *   Pasang dependensi:
        ```bash
        composer install --no-dev --optimize-autoloader
        ```
    *   Salin konfigurasi `.env`:
        ```bash
        cp .env.example .env
        ```
    *   Edit file `.env` (gunakan perintah `nano .env` or edit via dashboard RunCloud), lalu isi konfigurasi database dan `APP_URL`.
    *   Generate kunci enkripsi:
        ```bash
        php artisan key:generate
        ```
    *   Hubungkan link storage:
        ```bash
        php artisan storage:link
        ```
    *   Migrasi database dan seeding:
        ```bash
        php artisan migrate --seed
        ```

## 4. Mengonfigurasi Akun Administrator Utama

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

## 5. Konfigurasi Cron Job (Wajib di VPS)

Agar fitur-fitur terjadwal seperti otomatisasi backup, notifikasi jatuh tempo, dan kalkulasi periodik berjalan otomatis, tambahkan satu baris cron job di VPS Anda.

1.  Di VPS, jalankan perintah editor cron:
    ```bash
    crontab -e
    ```
2.  Tambahkan satu baris berikut di bagian paling bawah file:
    ```cron
    * * * * * cd /path/to/your/hassapos && php artisan schedule:run >> /dev/null 2>&1
    ```
    *(Sesuaikan `/path/to/your/hassapos` dengan lokasi asli folder proyek Anda, misalnya `/var/www/hassapos`)*

## Troubleshooting & Solusi Masalah Umum

### 1. Error 500 (Halaman Putih atau Internal Server Error)
*   **Penyebab 1**: Izin akses (*permissions*) folder storage tidak tepat.
    *   *Solusi*: Jalankan perintah berikut di terminal server:
        ```bash
        chmod -R 775 storage bootstrap/cache
        ```
*   **Penyebab 2**: File `.env` belum dibuat atau konfigurasinya salah.
    *   *Solusi*: Pastikan `.env` sudah ada dan konfigurasi database Anda valid.

### 2. Error Database Connection (Gagal Konek Database)
*   **Penyebab**: Detail host, username, atau password di `.env` salah.
    *   *Solusi*: Periksa kembali nama database dan kredensial MySQL Anda. Pastikan database server aktif dengan perintah:
        ```bash
        sudo systemctl status mysql
        ```

### 3. Error Versi PHP pada Composer di Shared Hosting / cPanel
*   **Penyebab**: Terminal hosting masih menggunakan versi PHP bawaan yang usang (misal PHP 7.4) sedangkan PHP website sudah diset ke 8.2.
    *   *Solusi*: Jalankan composer dengan memanggil langsung binary PHP 8.2 di hosting Anda. Contoh perintah:
        ```bash
        /usr/local/bin/php82 $(which composer) install --ignore-platform-reqs
        ```
        *(Sesuaikan `/usr/local/bin/php82` dengan path php8.2 asli pada hosting Anda)*

### 4. Menghapus Cache Jika Melakukan Perubahan Konfigurasi
Setiap kali Anda mengedit file `.env` or file konfigurasi lainnya di lingkungan produksi (*production*), jalankan perintah-perintah ini satu per satu agar perubahan segera diterapkan oleh server:
```bash
php artisan config:clear
```
```bash
php artisan cache:clear
```
```bash
php artisan view:clear
```

Selamat menggunakan **HassaPOS**! Semoga bisnis Anda berjalan lancar dan semakin sukses dengan bantuan sistem kasir pintar ini. Jika ada pertanyaan, silakan hubungi tim pengembang atau buat issue di repository ini.
