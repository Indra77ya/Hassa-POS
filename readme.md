# Panduan Instalasi HassaPOS

Selamat datang di panduan instalasi HassaPOS! Panduan ini akan memandu Anda melalui proses instalasi dari awal hingga akhir, baik untuk lingkungan lokal (komputer pribadi) maupun server (hosting online). Kami akan menjelaskan setiap langkah dengan detail dan sederhana, sehingga orang awam pun bisa mengikutinya.

## Persyaratan Sistem

Sebelum memulai, pastikan komputer atau server Anda memenuhi persyaratan berikut:

### Untuk Lokal dan Server:
- **PHP**: Versi 8.1 atau lebih tinggi (disarankan 8.2)
- **Composer**: Alat untuk mengelola dependensi PHP
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Web Server**: Apache atau Nginx (untuk server)
- **Git**: Untuk mengunduh kode dari repository

### Untuk Server Tambahan:
- Akses SSH ke server
- Domain atau subdomain yang sudah diarahkan ke server
- SSL certificate (opsional, tapi disarankan untuk keamanan)

## Instalasi untuk Lingkungan Lokal

### Langkah 1: Unduh Kode Sumber
1. Buka terminal atau command prompt di komputer Anda.
2. Navigasi ke folder tempat Anda ingin menyimpan proyek (misalnya: `cd Desktop`).
3. Jalankan perintah berikut untuk mengunduh kode:
   ```
   git clone https://github.com/Indra77ya/HassaPOS.git
   ```
   Ganti `your-repo` dengan nama repository yang benar jika berbeda.
4. Masuk ke folder proyek:
   ```
   cd HassaPOS
   ```

### Langkah 2: Install Dependensi PHP
1. Pastikan Composer sudah terinstall. Jika belum, unduh dari [getcomposer.org](https://getcomposer.org/).
2. Jalankan perintah untuk install dependensi PHP:
   ```
   composer install
   ```
   Proses ini mungkin memakan waktu beberapa menit. Tunggu hingga selesai.

### Langkah 3: Konfigurasi Database
1. Buat database baru di MySQL/MariaDB Anda. Misalnya, nama database: `hassapos`.
2. Buka file `.env.example` dan salin isinya ke file baru bernama `.env`.
3. Edit file `.env` dengan informasi database Anda:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hassapos
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```
   Ganti `your_username` and `your_password` dengan kredensial database Anda.

### Langkah 4: Generate Application Key & Storage Link
Jalankan perintah berikut untuk membuat kunci aplikasi dan membuat tautan simbolis ke folder storage agar file yang diunggah dapat diakses secara publik:
```bash
php artisan key:generate
php artisan storage:link
```

### Langkah 5: Migrasi Database
Jalankan perintah untuk membuat tabel-tabel database:
```
php artisan migrate
```

### Langkah 6: Seed Database (Opsional)
Jika Anda ingin mengisi database dengan data awal, jalankan:
```
php artisan db:seed
```

### Langkah 7: Jalankan Aplikasi
Jalankan server lokal:
```
php artisan serve
```
Aplikasi akan berjalan di `http://localhost:8000`. Buka browser dan akses alamat tersebut.

## Instalasi untuk Server

### Langkah 1: Upload Kode ke Server
1. Upload seluruh folder proyek ke server Anda menggunakan FTP, SFTP, atau Git.
2. Jika menggunakan Git, SSH ke server dan jalankan:
   ```
   git clone https://github.com/Indra77ya/HassaPOS.git /path/to/your/website
   ```

### Langkah 2: Install Dependensi di Server
1. SSH ke server Anda.
2. Navigasi ke folder proyek:
   ```
   cd /path/to/your/website
   ```
3. Install dependensi PHP:
   ```
   composer install --no-dev --optimize-autoloader
   ```

### Langkah 3: Konfigurasi Environment
1. Salin file `.env.example` ke `.env`:
   ```
   cp .env.example .env
   ```
2. Edit file `.env` dengan konfigurasi server:
   - Database: Sesuaikan dengan database server
   - APP_URL: Set ke URL domain Anda (misalnya: `https://yourdomain.com`)
   - APP_ENV: Set ke `production`
   - APP_DEBUG: Set ke `false`

### Langkah 4: Generate Application Key & Storage Link
Jalankan perintah berikut untuk membuat kunci aplikasi dan membuat tautan simbolis ke folder storage:
```bash
php artisan key:generate
php artisan storage:link
```

### Langkah 5: Migrasi Database
```
php artisan migrate
```

Setelah migrasi, jalankan seeder (opsional) untuk mengisi data awal:
```
php artisan db:seed
```

### Langkah 6: Konfigurasi Web Server

#### Untuk Apache:
1. Pastikan mod_rewrite aktif.
2. Buat file `.htaccess` di root folder (biasanya sudah ada).
3. Konfigurasi virtual host untuk mengarah ke folder `public/` proyek.

#### Untuk Nginx:
Tambahkan konfigurasi berikut ke file nginx.conf atau site config:
```
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/website/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Sesuaikan versi PHP
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Langkah 7: Set Permissions
Jalankan perintah untuk set permission yang benar:
```
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data /path/to/your/website
```
Ganti `www-data` dengan user web server Anda (misalnya `apache` atau `nginx`).

### Langkah 8: Konfigurasi SSL (Opsional tapi Disarankan)
1. Dapatkan SSL certificate gratis dari Let's Encrypt menggunakan Certbot.
2. Install Certbot dan jalankan:
   ```
   certbot --nginx -d yourdomain.com
   ```
   Atau untuk Apache:
   ```
   certbot --apache -d yourdomain.com
   ```

### Langkah 9: Jalankan Aplikasi
Aplikasi sekarang seharusnya dapat diakses melalui domain Anda. Jika ada masalah, periksa log error di `storage/logs/laravel.log`.

### Tips: Mengatasi Masalah Versi PHP di Hosting

Seringkali di lingkungan hosting, perintah `php` atau `composer` secara default mengarah ke versi PHP yang lebih tua (misalnya PHP 8.0), meskipun Anda sudah mengatur versi PHP di cPanel ke 8.2. Jika Anda menemui error saat `composer install` atau `php artisan`, gunakan langkah berikut:

1. **Gunakan Path Lengkap PHP**:
   Cari tahu lokasi binary PHP 8.2 Anda (biasanya `/usr/local/bin/php`). Gunakan path tersebut untuk menjalankan semua perintah terminal.

2. **Install dengan Flag Tambahan**:
   Jika `composer install` gagal karena deteksi versi PHP yang salah, jalankan:
   ```bash
   /usr/local/bin/php $(which composer) install --ignore-platform-reqs --no-scripts
   ```

3. **Jalankan Artisan secara Manual**:
   Karena menggunakan flag `--no-scripts`, Anda harus membuat folder yang diperlukan dan menjalankan perintah Artisan secara manual menggunakan path PHP yang benar:
   ```bash
   mkdir -p storage/framework/{sessions,views,cache}
   /usr/local/bin/php artisan key:generate
   /usr/local/bin/php artisan storage:link
   /usr/local/bin/php artisan package:discover
   ```

## Fitur Margin Nominal (Fixed Margin)

Fitur ini memungkinkan pengguna untuk menentukan keuntungan produk menggunakan nominal Rupiah tetap (Fixed) selain menggunakan persentase (%).

### Cara Pengaturan Default (Tingkat Bisnis)

Jika Anda ingin semua produk baru secara otomatis menggunakan tipe margin tertentu (misal: selalu Rp 5.000), Anda bisa mengaturnya di:
1.  Buka menu **Pengaturan > Pengaturan Bisnis**.
2.  Pilih tab **Bisnis**.
3.  Pada kolom **Margin keuntungan default**, Anda sekarang bisa memilih antara **%** atau **Rp** melalui dropdown di samping kotak input.
4.  Klik **Perbarui Pengaturan**.

### Cara Penggunaan di Halaman Produk

1.  Buka menu **Produk > Tambah Produk** atau **Edit Produk**.
2.  Pada bagian **Harga Jual**, Anda akan melihat dropdown baru di samping label "Margin".
3.  Pilih jenis margin:
    *   **%**: Menggunakan perhitungan persentase (Harga Jual = Harga Beli + % Margin).
    *   **Fixed (Rp)**: Menggunakan nominal Rupiah tetap (Harga Jual = Harga Beli + Nominal Margin).
4.  Jika Anda menggunakan **Fixed (Rp)**, saat harga beli produk berubah, sistem akan secara otomatis menyesuaikan harga jual agar nominal keuntungan Anda tetap sama.

### Fitur Bulk Edit

Fitur ini juga tersedia pada menu **Bulk Edit Produk** (Daftar Produk > centang produk > Edit yang Dipilih). Anda bisa mengubah tipe margin untuk banyak produk sekaligus dan melihat perubahan harga jual secara real-time sebelum disimpan.

### Fitur Import Produk via Excel

Untuk menentukan tipe margin saat melakukan import produk massal:

1.  Gunakan template Excel terbaru yang dapat diunduh di halaman **Import Produk**.
2.  Tambahkan kolom ke-38 (kolom setelah 'Product Locations') dengan judul **Margin Type**.
3.  Isi kolom tersebut dengan salah satu nilai berikut:
    *   `percentage` (untuk persentase)
    *   `fixed` (untuk nominal Rupiah tetap)
4.  Jika dikosongkan, sistem akan menganggap tipe margin sebagai `percentage`.

### Informasi Teknis
*   Data tipe margin disimpan di tabel `variations` pada kolom `profit_margin_type`.
*   Tipe margin ini bersifat per-variasi, sehingga produk variable bisa memiliki tipe margin yang berbeda-beda untuk setiap variasinya.

## Login dan Setup Awal

Setelah instalasi selesai (baik lokal maupun server), Anda dapat login ke aplikasi:

1. Buka aplikasi di browser (lokal: `http://localhost:8000` atau domain Anda untuk server)
2. Login dengan kredensial administrator Anda.
3. **PENTING**: Segera ubah password default setelah login pertama kali demi keamanan.

### Membuat User Tambahan

Setelah login sebagai admin, Anda dapat membuat user tambahan dengan langkah berikut:
1. Pergi ke menu **User Management** atau **Manajemen User**
2. Klik tombol **Create User** atau **Tambah User**
3. Isi data user baru (nama, username, email, password)
4. Assign roles dan permissions sesuai kebutuhan
5. Simpan user baru

## Troubleshooting

### Masalah Umum:
1. **Error 500**: Periksa permission file dan folder.
2. **Database connection error**: Pastikan kredensial database benar di `.env`.
3. **Composer error**: Pastikan PHP versi yang benar dan ekstensi yang diperlukan aktif.
4. **Gagal login**:
   - Pastikan database sudah di-migrate dan di-seed
   - Jika perlu reset password, Anda bisa menggunakan artisan tinker.
5. **Migration error**: Coba jalankan `php artisan migrate:reset` lalu `php artisan migrate` dari awal (hati-hati: ini akan menghapus semua data)

### Ekstensi PHP yang Diperlukan:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD
- ZIP

### Jika Ada Masalah:
1. Periksa log error di `storage/logs/laravel.log`.
2. Jalankan `php artisan config:clear` dan `php artisan cache:clear`.
3. Pastikan semua persyaratan sistem terpenuhi.

## Dukungan
Jika Anda mengalami kesulitan, silakan:
1. Baca dokumentasi resmi Laravel.
2. Cari di forum komunitas HassaPOS.
3. Hubungi tim dukungan jika tersedia.

Selamat menggunakan HassaPOS!
# HassaPOS
