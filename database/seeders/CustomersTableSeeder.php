<?php

namespace Database\Seeders;

use App\Business;
use App\Contact;
use App\User;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
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

        $customers = [
            ['Bambang Gunawan', 'bambang.g@example.com', '081111111111', 'Jakarta', 'DKI Jakarta'],
            ['Siti Rahmawati', 'siti.rahma@example.com', '081111111112', 'Surabaya', 'Jawa Timur'],
            ['Ahmad Fauzi', 'ahmad.f@example.com', '081111111113', 'Bandung', 'Jawa Barat'],
            ['Dewi Sartika', 'dewi.sartika@example.com', '081111111114', 'Semarang', 'Jawa Tengah'],
            ['Eko Kurniawan', 'eko.k@example.com', '081111111115', 'Medan', 'Sumatera Utara'],
            ['Maya Sopha', 'maya.s@example.com', '081111111116', 'Makassar', 'Sulawesi Selatan'],
            ['Rian Pratama', 'rian.p@example.com', '081111111117', 'Yogyakarta', 'DI Yogyakarta'],
            ['Dedi Sulaiman', 'dedi.s@example.com', '081111111118', 'Palembang', 'Sumatera Selatan'],
            ['Indah Pertiwi', 'indah.p@example.com', '081111111119', 'Denpasar', 'Bali'],
            ['Fajar Ramadhan', 'fajar.r@example.com', '081111111120', 'Banjarmasin', 'Kalimantan Selatan'],
            ['Rina Meilani', 'rina.m@example.com', '081111111121', 'Balikpapan', 'Kalimantan Timur'],
            ['Andi Saputra', 'andi.s@example.com', '081111111122', 'Jayapura', 'Papua'],
            ['Hendra Wijaya', 'hendra.w@example.com', '081111111123', 'Malang', 'Jawa Timur'],
            ['Lina Marlina', 'lina.m@example.com', '081111111124', 'Bogor', 'Jawa Barat'],
            ['Taufik Hidayat', 'taufik.h@example.com', '081111111125', 'Tangerang', 'Banten'],
            ['Yanti Kumala', 'yanti.k@example.com', '081111111126', 'Bekasi', 'Jawa Barat'],
            ['Budi Santoso', 'budi.s@example.com', '081111111127', 'Solo', 'Jawa Tengah'],
            ['Sari Indah', 'sari.i@example.com', '081111111128', 'Ambon', 'Maluku'],
            ['Wawan Hermawan', 'wawan.h@example.com', '081111111129', 'Pontianak', 'Kalimantan Barat'],
            ['Ani Maryani', 'ani.m@example.com', '081111111130', 'Manado', 'Sulawesi Utara'],
            ['Rudi Tabuti', 'rudi.t@example.com', '081111111131', 'Pekanbaru', 'Riau'],
            ['Dian Sastrowardoyo', 'dian.s@example.com', '081111111132', 'Padang', 'Sumatera Barat'],
            ['Gading Marten', 'gading.m@example.com', '081111111133', 'Batam', 'Kepulauan Riau'],
            ['Joko Widodo', 'joko.w@example.com', '081111111134', 'Lampung', 'Lampung'],
            ['Ma ruf Amin', 'maruf.a@example.com', '081111111135', 'Serang', 'Banten'],
            ['Prabowo Subianto', 'prabowo.s@example.com', '081111111136', 'Mataram', 'Nusa Tenggara Barat'],
            ['Ganjar Pranowo', 'ganjar.p@example.com', '081111111137', 'Kupang', 'Nusa Tenggara Timur'],
            ['Anies Baswedan', 'anies.b@example.com', '081111111138', 'Samarinda', 'Kalimantan Timur'],
            ['Puan Maharani', 'puan.m@example.com', '081111111139', 'Palu', 'Sulawesi Tengah'],
            ['Muhaimin Iskandar', 'cak.imin@example.com', '081111111140', 'Kendari', 'Sulawesi Tenggara'],
            ['Sandiaga Uno', 'sandi.u@example.com', '081111111141', 'Gorontalo', 'Gorontalo'],
            ['Ridwan Kamil', 'kang.emil@example.com', '081111111142', 'Cirebon', 'Jawa Barat'],
            ['Khofifah Indar Parawansa', 'khofifah.i@example.com', '081111111143', 'Pasuruan', 'Jawa Timur'],
            ['Erick Thohir', 'erick.t@example.com', '081111111144', 'Bengkulu', 'Bengkulu'],
            ['Budi Karya Sumadi', 'budi.k@example.com', '081111111145', 'Jambi', 'Jambi'],
            ['Johnny G Plate', 'johnny.p@example.com', '081111111146', 'Mamuju', 'Sulawesi Barat'],
            ['Luhut Binsar Pandjaitan', 'luhut.b@example.com', '081111111147', 'Ternate', 'Maluku Utara'],
            ['Susi Pudjiastuti', 'susi.p@example.com', '081111111148', 'Pangkal Pinang', 'Bangka Belitung'],
            ['Sri Mulyani Indrawati', 'sri.m@example.com', '081111111149', 'Tanjung Pinang', 'Kepulauan Riau'],
            ['Retno Marsudi', 'retno.m@example.com', '081111111150', 'Manokwari', 'Papua Barat'],
            ['Basuki Hadimuljono', 'basuki.h@example.com', '081111111151', 'Madiun', 'Jawa Timur'],
            ['Tri Rismaharini', 'bu.risma@example.com', '081111111152', 'Kediri', 'Jawa Timur'],
            ['Tito Karnavian', 'tito.k@example.com', '081111111153', 'Palembang', 'Sumatera Selatan'],
            ['Pratikno', 'pratikno@example.com', '081111111154', 'Bojonegoro', 'Jawa Timur'],
            ['Moeldoko', 'moeldoko@example.com', '081111111155', 'Kediri', 'Jawa Timur'],
            ['Zulkifli Hasan', 'zul.hasan@example.com', '081111111156', 'Lampung', 'Lampung'],
            ['Hadi Tjahjanto', 'hadi.t@example.com', '081111111157', 'Malang', 'Jawa Timur'],
            ['Suahasil Nazara', 'suahasil@example.com', '081111111158', 'Jakarta', 'DKI Jakarta'],
            ['Yaqut Cholil Qoumas', 'gus.yaqut@example.com', '081111111159', 'Rembang', 'Jawa Tengah'],
            ['Bahlil Lahadalia', 'bahlil.l@example.com', '081111111160', 'Fakfak', 'Papua Barat'],
        ];

        foreach ($customers as $index => $c) {
            Contact::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'email' => $c[1],
                    'type' => 'customer'
                ],
                [
                    'name' => $c[0],
                    'mobile' => $c[2],
                    'city' => $c[3],
                    'state' => $c[4],
                    'country' => 'Indonesia',
                    'created_by' => $created_by,
                    'contact_id' => 'CUST' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contact_status' => 'active'
                ]
            );
        }
    }
}
