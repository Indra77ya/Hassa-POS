<?php

namespace App\Http\Controllers;

use App\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        $account_types = AccountType::where('business_id', $business_id)
                                     ->whereNull('parent_account_type_id')
                                     ->get();

        return view('account_types.create')
                ->with(compact('account_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'parent_account_type_id']);
            $input['business_id'] = $request->session()->get('user.business_id');

            AccountType::create($input);
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        $account_type = AccountType::where('business_id', $business_id)
                                     ->findOrFail($id);

        $account_types = AccountType::where('business_id', $business_id)
                                     ->whereNull('parent_account_type_id')
                                     ->get();

        return view('account_types.edit')
                ->with(compact('account_types', 'account_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'parent_account_type_id']);
            $business_id = $request->session()->get('user.business_id');

            $account_type = AccountType::where('business_id', $business_id)
                                     ->findOrFail($id);

            //Account type is changed to subtype update all its sub type's parent type
            if (empty($account_type->parent_account_type_id) && ! empty($input['parent_account_type_id'])) {
                AccountType::where('business_id', $business_id)
                        ->where('parent_account_type_id', $account_type->id)
                        ->update(['parent_account_type_id' => $input['parent_account_type_id']]);
            }

            $account_type->update($input);

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        AccountType::where('business_id', $business_id)
                                     ->where('id', $id)
                                     ->delete();

        //Upadete parent account if set
        AccountType::where('business_id', $business_id)
                 ->where('parent_account_type_id', $id)
                 ->update(['parent_account_type_id' => null]);

        $output = ['success' => true,
            'msg' => __('lang_v1.deleted_success'),
        ];

        return redirect()->back()->with('status', $output);
    }

    /**
     * Add default account types for the business.
     *
     * @return \Illuminate\Http\Response
     */
    public function seedDefault()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');

            $default_types = [
                // 1. AKTIVA LANCAR
                ['key' => 'aktiva_lancar', 'parent' => null],
                ['key' => 'kas_dan_setara_kas', 'parent' => 'aktiva_lancar'],
                ['key' => 'bank', 'parent' => 'aktiva_lancar'],
                ['key' => 'piutang_usaha', 'parent' => 'aktiva_lancar'],
                ['key' => 'piutang_lain_lain', 'parent' => 'aktiva_lancar'],
                ['key' => 'persediaan_barang_dagang', 'parent' => 'aktiva_lancar'],
                ['key' => 'persediaan_bahan_baku', 'parent' => 'aktiva_lancar'],
                ['key' => 'persediaan_bahan_pembantu', 'parent' => 'aktiva_lancar'],
                ['key' => 'perlengkapan_toko', 'parent' => 'aktiva_lancar'],
                ['key' => 'perlengkapan_kantor', 'parent' => 'aktiva_lancar'],
                ['key' => 'biaya_dibayar_dimuka', 'parent' => 'aktiva_lancar'],
                ['key' => 'sewa_dibayar_dimuka', 'parent' => 'aktiva_lancar'],
                ['key' => 'asuransi_dibayar_dimuka', 'parent' => 'aktiva_lancar'],
                ['key' => 'pajak_dibayar_dimuka', 'parent' => 'aktiva_lancar'],
                ['key' => 'uang_muka_pembelian', 'parent' => 'aktiva_lancar'],

                // 2. AKTIVA TETAP
                ['key' => 'aktiva_tetap', 'parent' => null],
                ['key' => 'tanah', 'parent' => 'aktiva_tetap'],
                ['key' => 'bangunan', 'parent' => 'aktiva_tetap'],
                ['key' => 'akumulasi_penyusutan_bangunan', 'parent' => 'aktiva_tetap'],
                ['key' => 'kendaraan', 'parent' => 'aktiva_tetap'],
                ['key' => 'akumulasi_penyusutan_kendaraan', 'parent' => 'aktiva_tetap'],
                ['key' => 'peralatan_kantor', 'parent' => 'aktiva_tetap'],
                ['key' => 'akumulasi_penyusutan_peralatan_kantor', 'parent' => 'aktiva_tetap'],
                ['key' => 'mesin_dan_peralatan', 'parent' => 'aktiva_tetap'],
                ['key' => 'akumulasi_penyusutan_mesin', 'parent' => 'aktiva_tetap'],
                ['key' => 'inventaris_toko', 'parent' => 'aktiva_tetap'],
                ['key' => 'akumulasi_penyusutan_inventaris_toko', 'parent' => 'aktiva_tetap'],

                // 3. KEWAJIBAN LANCAR
                ['key' => 'kewajiban_lancar', 'parent' => null],
                ['key' => 'hutang_usaha', 'parent' => 'kewajiban_lancar'],
                ['key' => 'hutang_gaji', 'parent' => 'kewajiban_lancar'],
                ['key' => 'hutang_listrik_air_telepon', 'parent' => 'kewajiban_lancar'],
                ['key' => 'hutang_pajak', 'parent' => 'kewajiban_lancar'],
                ['key' => 'hutang_pph_21', 'parent' => 'kewajiban_lancar'],
                ['key' => 'uang_muka_penjualan', 'parent' => 'kewajiban_lancar'],
                ['key' => 'hutang_biaya_lainnya', 'parent' => 'kewajiban_lancar'],

                // 4. KEWAJIBAN JANGKA PANJANG
                ['key' => 'kewajiban_jangka_panjang', 'parent' => null],
                ['key' => 'hutang_bank_long_term', 'parent' => 'kewajiban_jangka_panjang'],
                ['key' => 'hutang_pembiayaan_kendaraan', 'parent' => 'kewajiban_jangka_panjang'],
                ['key' => 'hutang_jangka_panjang_lainnya', 'parent' => 'kewajiban_jangka_panjang'],

                // 5. EKUITAS
                ['key' => 'ekuitas', 'parent' => null],
                ['key' => 'modal_pemilik', 'parent' => 'ekuitas'],
                ['key' => 'prive', 'parent' => 'ekuitas'],
                ['key' => 'laba_ditahan', 'parent' => 'ekuitas'],
                ['key' => 'laba_tahun_berjalan', 'parent' => 'ekuitas'],

                // 6. PENDAPATAN
                ['key' => 'pendapatan', 'parent' => null],
                ['key' => 'pendapatan_penjualan', 'parent' => 'pendapatan'],
                ['key' => 'retur_penjualan', 'parent' => 'pendapatan'],
                ['key' => 'potongan_penjualan', 'parent' => 'pendapatan'],
                ['key' => 'pendapatan_jasa', 'parent' => 'pendapatan'],
                ['key' => 'pendapatan_lain_lain', 'parent' => 'pendapatan'],

                // 7. HARGA POKOK PENJUALAN
                ['key' => 'harga_pokok_penjualan', 'parent' => null],
                ['key' => 'hpp_produk', 'parent' => 'harga_pokok_penjualan'],
                ['key' => 'hpp_jasa', 'parent' => 'harga_pokok_penjualan'],
                ['key' => 'biaya_angkut_pembelian', 'parent' => 'harga_pokok_penjualan'],
                ['key' => 'potongan_pembelian', 'parent' => 'harga_pokok_penjualan'],

                // 8. BIAYA OPERASIONAL
                ['key' => 'biaya_operasional', 'parent' => null],
                ['key' => 'biaya_gaji_dan_tunjangan', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_listrik_air_dan_internet', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_sewa', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_pemasaran_dan_iklan', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_perbaikan_dan_pemeliharaan', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_transportasi_dan_bensin', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_keperluan_kantor', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_keperluan_toko', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_penyusutan_aktiva_tetap', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_adm_bank_dan_pajak_bunga', 'parent' => 'biaya_operasional'],
                ['key' => 'biaya_operasional_lainnya', 'parent' => 'biaya_operasional'],
            ];

            $type_map = [];
            foreach ($default_types as $at) {
                $translated_name = __('account.' . $at['key']);

                // Check if already exists
                $exists = AccountType::where('business_id', $business_id)
                                     ->where('name', $translated_name)
                                     ->first();
                if ($exists) {
                    $type_map[$at['key']] = $exists->id;
                    continue;
                }

                $parent_id = $at['parent'] ? ($type_map[$at['parent']] ?? null) : null;
                $new_type = AccountType::create([
                    'name' => $translated_name,
                    'business_id' => $business_id,
                    'parent_account_type_id' => $parent_id
                ]);
                $type_map[$at['key']] = $new_type->id;
            }

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }
}
