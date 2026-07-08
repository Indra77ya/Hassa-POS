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
        abort(403, 'Unauthorized action.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(403, 'Unauthorized action.');
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
        abort(403, 'Unauthorized action.');
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
        abort(403, 'Unauthorized action.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(403, 'Unauthorized action.');
    }

    /**
     * Add default account types for the business.
     *
     * @return \Illuminate\Http\Response
     */
    public function seedDefault(Request $request)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');

            $default_types = [
                ['key' => 'kas_dan_bank', 'parent' => null],
                ['key' => 'piutang_usaha', 'parent' => null],
                ['key' => 'persediaan', 'parent' => null],
                ['key' => 'aktiva_lancar_lainnya', 'parent' => null],
                ['key' => 'aktiva_tetap', 'parent' => null],
                ['key' => 'akumulasi_penyusutan', 'parent' => null],
                ['key' => 'aktiva_lainnya', 'parent' => null],
                ['key' => 'hutang_usaha', 'parent' => null],
                ['key' => 'hutang_lancar_lainnya', 'parent' => null],
                ['key' => 'hutang_jangka_panjang', 'parent' => null],
                ['key' => 'ekuitas', 'parent' => null],
                ['key' => 'pendapatan_usaha', 'parent' => null],
                ['key' => 'pendapatan_lainnya', 'parent' => null],
                ['key' => 'harga_pokok_penjualan', 'parent' => null],
                ['key' => 'beban_operasional', 'parent' => null],
                ['key' => 'beban_lain_lain', 'parent' => null],
                ['key' => 'beban_pajak', 'parent' => null],
            ];

            $created_types = [];
            foreach ($default_types as $at) {
                $translated_name = __('account.' . $at['key']);

                // Check if already exists
                $type = AccountType::where('business_id', $business_id)
                                     ->where('fixed_key', $at['key'])
                                     ->first();
                if (! $type) {
                    $type = AccountType::create([
                        'name' => $translated_name,
                        'business_id' => $business_id,
                        'parent_account_type_id' => null,
                        'fixed_key' => $at['key']
                    ]);
                } else {
                    $type->update(['name' => $translated_name]);
                }
                $created_types[$at['key']] = $type->id;
            }

            // Seed basic accounts (COA)
            $default_accounts = [
                ['name' => 'Kas', 'type' => 'kas_dan_bank', 'number' => '1101', 'balance' => 'debit'],
                ['name' => 'Bank', 'type' => 'kas_dan_bank', 'number' => '1102', 'balance' => 'debit'],
                ['name' => 'Piutang Usaha', 'type' => 'piutang_usaha', 'number' => '1201', 'balance' => 'debit'],
                ['name' => 'Persediaan Barang', 'type' => 'persediaan', 'number' => '1301', 'balance' => 'debit'],
                ['name' => 'Hutang Usaha', 'type' => 'hutang_usaha', 'number' => '2101', 'balance' => 'credit'],
                ['name' => 'Modal Pemilik', 'type' => 'ekuitas', 'number' => '3101', 'balance' => 'credit'],
                ['name' => 'Laba Ditahan', 'type' => 'ekuitas', 'number' => '3201', 'balance' => 'credit'],
                ['name' => 'Pendapatan Penjualan', 'type' => 'pendapatan_usaha', 'number' => '4101', 'balance' => 'credit'],
                ['name' => 'Harga Pokok Penjualan', 'type' => 'harga_pokok_penjualan', 'number' => '5101', 'balance' => 'debit'],
                ['name' => 'Beban Gaji', 'type' => 'beban_operasional', 'number' => '6101', 'balance' => 'debit'],
                ['name' => 'Beban Sewa', 'type' => 'beban_operasional', 'number' => '6102', 'balance' => 'debit'],
                ['name' => 'Beban Listrik & Air', 'type' => 'beban_operasional', 'number' => '6103', 'balance' => 'debit'],
            ];

            $user_id = $request->session()->get('user.id');
            foreach ($default_accounts as $da) {
                $exists = \App\Account::where('business_id', $business_id)
                                      ->where('name', $da['name'])
                                      ->first();
                if (!$exists) {
                    \App\Account::create([
                        'name' => $da['name'],
                        'business_id' => $business_id,
                        'account_number' => $da['number'],
                        'account_type_id' => $created_types[$da['type']],
                        'normal_balance' => $da['balance'],
                        'created_by' => $user_id
                    ]);
                }
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

        if ($request->ajax()) {
            return $output;
        }

        return redirect()->back()->with('status', $output);
    }
}
