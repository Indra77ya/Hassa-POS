# Fitur Margin Nominal (Fixed Margin)

Fitur ini memungkinkan pengguna untuk menentukan keuntungan produk menggunakan nominal Rupiah tetap (Fixed) selain menggunakan persentase (%).

## Cara Penggunaan di Halaman Produk

1.  Buka menu **Produk > Tambah Produk** atau **Edit Produk**.
2.  Pada bagian **Harga Jual**, Anda akan melihat dropdown baru di samping label "Margin".
3.  Pilih jenis margin:
    *   **%**: Menggunakan perhitungan persentase (Harga Jual = Harga Beli + % Margin).
    *   **Fixed (Rp)**: Menggunakan nominal Rupiah tetap (Harga Jual = Harga Beli + Nominal Margin).
4.  Jika Anda menggunakan **Fixed (Rp)**, saat harga beli produk berubah, sistem akan secara otomatis menyesuaikan harga jual agar nominal keuntungan Anda tetap sama.

## Fitur Bulk Edit

Fitur ini juga tersedia pada menu **Bulk Edit Produk**. Anda bisa mengubah tipe margin untuk banyak produk sekaligus dan melihat perubahan harga jual secara real-time sebelum disimpan.

## Fitur Import Produk via Excel

Untuk menentukan tipe margin saat melakukan import produk massal:

1.  Gunakan template Excel terbaru yang dapat diunduh di halaman **Import Produk**.
2.  Tambahkan kolom ke-38 (kolom setelah 'Product Locations') dengan judul **Margin Type**.
3.  Isi kolom tersebut dengan salah satu nilai berikut:
    *   `percentage` (untuk persentase)
    *   `fixed` (untuk nominal Rupiah tetap)
4.  Jika dikosongkan, sistem akan menganggap tipe margin sebagai `percentage`.

## Informasi Teknis
*   Data tipe margin disimpan di tabel `variations` pada kolom `profit_margin_type`.
*   Tipe margin ini bersifat per-variasi, sehingga produk variable bisa memiliki tipe margin yang berbeda-beda untuk setiap variasinya.
