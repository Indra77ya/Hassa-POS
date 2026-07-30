# Panduan Penggunaan & Pengujian: Fitur Map Transactions (Pemetaan Transaksi)

Fitur **Map Transactions** (Pemetaan Transaksi) menjembatani transaksi yang terjadi pada modul POS utama (Sales, Purchases, Expenses, Payments) dengan modul Akuntansi (Accounting). Fitur ini secara otomatis menghasilkan entri jurnal akuntansi (Double-Entry Bookkeeping) ke Chart of Accounts (CoA) berdasarkan konfigurasi default lokasi bisnis atau pemetaan manual per transaksi.

---

## 1. Cara Kerja & Aliran Jurnal Akuntansi

Setiap jenis transaksi memiliki aturan aliran debit/kredit yang telah ditentukan dalam logika sistem (`AccountingUtil.php`). Berikut adalah tabel penjelasan alur debit/kredit untuk masing-masing tipe:

| Tipe Transaksi | Payment Account (Credit 🟢) | Deposit To (Debit 🔵) | Keterangan Jurnal |
| :--- | :--- | :--- | :--- |
| **Sell** (Penjualan) | **Pendapatan Penjualan** (Sales/Revenue) | **Piutang Usaha** (Accounts Receivable) | Mencatat piutang baru atas penjualan barang/jasa sebelum pembayaran lunas. |
| **Sales Payments** (Pembayaran Penjualan) | **Piutang Usaha** (Accounts Receivable) | **Kas / Bank** (Cash/Bank) | Mencatat pelunasan piutang oleh pelanggan sehingga kas bertambah dan piutang berkurang. |
| **Purchases** (Pembelian) | **Hutang Usaha** (Accounts Payable) | **Persediaan Barang** (Inventory) | Mencatat penambahan persediaan barang dagang yang dibeli secara kredit (hutang). |
| **Purchase Payments** (Pembayaran Pembelian) | **Kas / Bank** (Cash/Bank) | **Hutang Usaha** (Accounts Payable) | Mencatat pengeluaran kas/bank untuk melunasi hutang kepada supplier. |
| **Expenses** (Pengeluaran Beban) | **Kas / Bank** (Cash/Bank) | **Beban Biaya** (Expense Account, misal: Beban Listrik & Air) | Mencatat pengeluaran kas langsung untuk membayar beban biaya operasional. |

---

## 2. Cara Menggunakan Fitur Map Transactions di UI (User Interface)

### A. Melalui Konfigurasi Default Lokasi Bisnis (Bulk/Otomatis)
1. Masuk ke menu **Accounting** -> **Settings** -> Tab **Account Setting / Map Transactions**.
2. Di sini Anda akan melihat daftar Lokasi Bisnis (Business Locations).
3. Untuk setiap lokasi bisnis, Anda dapat memilih akun default untuk masing-masing transaksi:
   - **Sell**: Tentukan akun pendapatan dan akun piutang.
   - **Sales Payments**: Tentukan akun piutang dan akun kas/bank penampung.
   - **Purchases**: Tentukan akun hutang dan akun persediaan barang.
   - **Purchase Payments**: Tentukan akun kas/bank pembayar dan akun hutang.
   - **Expenses**: Tentukan akun beban/biaya default dan akun kas/bank pembayar.
4. Anda juga dapat memetakan pengeluaran per **Kategori Beban** (Expense Category) jika ingin membagi beban ke akun CoA yang berbeda (misalnya Kategori Beban "Marketing" masuk ke akun "Beban Pemasaran").
5. Klik **Update** untuk menyimpan.
6. **Tombol Auto Map Default Accounts**: Jika Anda klik tombol ini, sistem akan otomatis mendeteksi akun-akun CoA yang sesuai dengan nama standar PSAK Indonesia dan mengisi semua form pemetaan default di atas secara otomatis.

### B. Melalui Pemetaan Manual Per Transaksi
Jika ada transaksi tertentu yang ingin disesuaikan akun pemetaannya di luar default:
1. Masuk ke menu **Accounting** -> **Transactions**.
2. Pilih tab yang sesuai (**Sales**, **Purchases**, **Expenses**, atau **Payments**).
3. Pada baris transaksi yang ingin dipetakan, klik tombol **Map Transaction** (atau **Edit Mapping** jika sudah pernah dipetakan).
4. Jendela popup modal akan muncul (seperti pada gambar yang Anda kirimkan).
5. Pilih **Payment Account** dan **Deposit To** secara spesifik untuk transaksi tersebut, tambahkan deskripsi bila perlu, lalu klik **Update**.

---

## 3. Cara Melakukan Pengujian (Testing)

Sistem menggunakan **Event Listeners** Laravel untuk mendengarkan perubahan transaksi secara real-time dan mengeksekusi pemetaan otomatis ke modul Akuntansi:
* **MapSellTransaction** -> Mendengarkan event `SellCreatedOrModified`.
* **MapPurchaseTransaction** -> Mendengarkan event `PurchaseCreatedOrModified`.
* **MapExpenseTransactions** -> Mendengarkan event `ExpenseCreatedOrModified`.
* **MapPaymentTransaction** -> Mendengarkan event `TransactionPaymentAdded`, `TransactionPaymentUpdated`, dan `TransactionPaymentDeleted`.

### Cara Menguji Secara Manual (QA)
1. Atur pemetaan default di menu **Accounting Settings**.
2. Lakukan transaksi baru (misalnya membuat Penjualan baru di POS / menu Sell).
3. Buka menu **Accounting** -> **Journal Entry** atau **Ledger** (Buku Besar).
4. Verifikasi bahwa jurnal debit/kredit otomatis terbentuk pada akun yang telah dipetakan dengan nominal yang sesuai dengan nilai transaksi.

### Cara Menguji Secara Otomatis (Automated PHPUnit Test)
Kami telah membuat suite pengujian otomatis lengkap yang dapat dijalankan langsung di terminal untuk memverifikasi seluruh fungsionalitas ini secara instan menggunakan database SQLite in-memory:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: vendor/bin/phpunit tests/Feature/TransactionMappingTest.php
```

Suite test otomatis tersebut mencakup skenario pengujian:
1. Pemetaan otomatis transaksi **Sell** (Penjualan).
2. Pemetaan otomatis transaksi **Purchase** (Pembelian).
3. Pemetaan otomatis transaksi **Expense** (Beban) menggunakan kategori spesifik dan fallback default.
4. Pemetaan otomatis transaksi **Payments** (Pelunasan Pembayaran Penjualan & Pembelian) beserta integrasi saat pembayaran ditambahkan atau dihapus.
