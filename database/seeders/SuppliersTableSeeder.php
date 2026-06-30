<?php

namespace Database\Seeders;

use App\Business;
use App\Contact;
use App\User;
use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $business = Business::first();
        if (!$business) {
            return;
        }

        $user = User::where('business_id', $business->id)->first();
        $created_by = $user ? $user->id : 1;

        $suppliers = [
            ['PT Maju Jaya', 'Budi Santoso', 'budi@majujaya.com', '081234567890', 'Jakarta', 'DKI Jakarta', 15, 'days'],
            ['CV Sumber Rejeki', 'Siti Aminah', 'siti@sumberrejeki.com', '081234567891', 'Surabaya', 'Jawa Timur', 30, 'days'],
            ['PT Alam Sejahtera', 'Agus Setiawan', 'agus@alamsejahtera.com', '081234567892', 'Bandung', 'Jawa Barat', 1, 'months'],
            ['UD Berkah Abadi', 'Dewi Lestari', 'dewi@berkahabadi.com', '081234567893', 'Semarang', 'Jawa Tengah', 7, 'days'],
            ['PT Global Logistik', 'Eko Prasetyo', 'eko@globallogistik.com', '081234567894', 'Medan', 'Sumatera Utara', 45, 'days'],
            ['CV Terang Benderang', 'Rian Hidayat', 'rian@terangbenderang.com', '081234567895', 'Makassar', 'Sulawesi Selatan', 15, 'days'],
            ['PT Indo Makmur', 'Maya Putri', 'maya@indomakmur.com', '081234567896', 'Yogyakarta', 'DI Yogyakarta', 30, 'days'],
            ['UD Jaya Bersama', 'Dedi Kurniawan', 'dedi@jayabersama.com', '081234567897', 'Palembang', 'Sumatera Selatan', 10, 'days'],
            ['PT Sentosa Jaya', 'Indah Permata', 'indah@sentosajaya.com', '081234567898', 'Denpasar', 'Bali', 2, 'months'],
            ['CV Mandiri Sejahtera', 'Fajar Nugraha', 'fajar@mandirisejahtera.com', '081234567899', 'Banjarmasin', 'Kalimantan Selatan', 15, 'days'],
            ['PT Cipta Karya', 'Rina Sari', 'rina@ciptakarya.com', '081234567900', 'Balikpapan', 'Kalimantan Timur', 30, 'days'],
            ['UD Mutiara Hitam', 'Andi Wijaya', 'andi@mutiarahitam.com', '081234567901', 'Jayapura', 'Papua', 15, 'days'],
            ['PT Karya Mandiri', 'Hendra Kusuma', 'hendra@karyamandiri.com', '081234567902', 'Malang', 'Jawa Timur', 30, 'days'],
            ['CV Putra Bangsa', 'Lina Marlina', 'lina@putrabangsa.com', '081234567903', 'Bogor', 'Jawa Barat', 15, 'days'],
            ['PT Mega Buana', 'Taufik Hidayat', 'taufik@megabuana.com', '081234567904', 'Tangerang', 'Banten', 30, 'days'],
            ['UD Sinar Pagi', 'Yanti Susanti', 'yanti@sinarpagi.com', '081234567905', 'Bekasi', 'Jawa Barat', 7, 'days'],
            ['PT Nusantara Perkasa', 'Bambang Sudarsono', 'bambang@nusantara.com', '081234567906', 'Solo', 'Jawa Tengah', 45, 'days'],
            ['CV Bintang Timur', 'Sari Puspita', 'sari@bintangtimur.com', '081234567907', 'Ambon', 'Maluku', 30, 'days'],
            ['PT Inti Cahaya', 'Wawan Setiawan', 'wawan@inticahaya.com', '081234567908', 'Pontianak', 'Kalimantan Barat', 15, 'days'],
            ['UD Harapan Baru', 'Ani Wijayati', 'ani@harapanbaru.com', '081234567909', 'Manado', 'Sulawesi Utara', 30, 'days'],
            ['PT Utama Mandiri', 'Rudi Tabuti', 'rudi@utamamandiri.com', '081234567910', 'Pekanbaru', 'Riau', 15, 'days'],
            ['CV Langit Biru', 'Dian Sastro', 'dian@langitbiru.com', '081234567911', 'Padang', 'Sumatera Barat', 30, 'days'],
            ['PT Samudra Luas', 'Gading Marten', 'gading@samudraluas.com', '081234567912', 'Batam', 'Kepulauan Riau', 1, 'months'],
            ['UD Tani Makmur', 'Joko Widodo', 'joko@tanimakmur.com', '081234567913', 'Lampung', 'Lampung', 15, 'days'],
            ['PT Pangan Sehat', 'Ma ruf Amin', 'maruf@pangansehat.com', '081234567914', 'Serang', 'Banten', 30, 'days'],
            ['CV Alam Lestari', 'Prabowo Subianto', 'prabowo@alamlestari.com', '081234567915', 'Mataram', 'Nusa Tenggara Barat', 15, 'days'],
            ['PT Energi Bangsa', 'Ganjar Pranowo', 'ganjar@energibangsa.com', '081234567916', 'Kupang', 'Nusa Tenggara Timur', 30, 'days'],
            ['UD Kerajinan Tangan', 'Anies Baswedan', 'anies@kerajinan.com', '081234567917', 'Samarinda', 'Kalimantan Timur', 45, 'days'],
            ['PT Tekno Solusi', 'Puan Maharani', 'puan@teknosolusi.com', '081234567918', 'Palu', 'Sulawesi Tengah', 30, 'days'],
            ['CV Media Tama', 'Muhaimin Iskandar', 'cak@mediatama.com', '081234567919', 'Kendari', 'Sulawesi Tenggara', 15, 'days'],
            ['PT Logistik Indonesia', 'Sandiaga Uno', 'sandi@logistik.com', '081234567920', 'Gorontalo', 'Gorontalo', 30, 'days'],
            ['UD Mebel Jati', 'Ridwan Kamil', 'ridwan@mebeljati.com', '081234567921', 'Cirebon', 'Jawa Barat', 1, 'months'],
            ['PT Konstruksi Jaya', 'Khofifah Indar', 'khofifah@konstruksi.com', '081234567922', 'Pasuruan', 'Jawa Timur', 15, 'days'],
            ['CV Percetakan Maju', 'Erick Thohir', 'eric@percetakan.com', '081234567923', 'Bengkulu', 'Bengkulu', 30, 'days'],
            ['PT Transportasi Aman', 'Budi Karya', 'budi@transport.com', '081234567924', 'Jambi', 'Jambi', 15, 'days'],
            ['UD Elektronik Murah', 'Johnny Plate', 'johnny@elektronik.com', '081234567925', 'Mamuju', 'Sulawesi Barat', 30, 'days'],
            ['PT Tambang Mulia', 'Luhut Binsar', 'luhut@tambang.com', '081234567926', 'Ternate', 'Maluku Utara', 45, 'days'],
            ['CV Perikanan Laut', 'Susi Pudjiastuti', 'susi@perikanan.com', '081234567927', 'Pangkal Pinang', 'Bangka Belitung', 15, 'days'],
            ['PT Otomotif Prima', 'Sri Mulyani', 'sri@otomotif.com', '081234567928', 'Tanjung Pinang', 'Kepulauan Riau', 30, 'days'],
            ['UD Pakaian Jadi', 'Retno Marsudi', 'retno@pakaian.com', '081234567929', 'Manokwari', 'Papua Barat', 15, 'days'],
        ];

        foreach ($suppliers as $index => $s) {
            Contact::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'supplier_business_name' => $s[0],
                    'type' => 'supplier'
                ],
                [
                    'name' => $s[1],
                    'email' => $s[2],
                    'mobile' => $s[3],
                    'city' => $s[4],
                    'state' => $s[5],
                    'country' => 'Indonesia',
                    'pay_term_number' => $s[6],
                    'pay_term_type' => $s[7],
                    'created_by' => $created_by,
                    'contact_id' => 'SUP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contact_status' => 'active'
                ]
            );
        }
    }
}
