<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\ProductSerialNumber;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLine;
use App\Unit;
use App\User;
use App\Variation;
use Tests\TestCase;

class StockTransferSerialNumberTest extends TestCase
{
    protected $user;
    protected $business;
    protected $location_from;
    protected $location_to;
    protected $product;
    protected $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::firstOrCreate(
            ['name' => 'Stock Transfer SN Business'],
            [
                'currency_id' => 1,
                'start_date' => '2023-01-01',
                'time_zone' => 'Asia/Jakarta'
            ]
        );

        $this->user = User::firstOrCreate(
            ['email' => 'admin_st_sn@test.com'],
            [
                'surname' => 'Mr',
                'first_name' => 'Admin',
                'username' => 'admin_st_sn',
                'password' => bcrypt('123456'),
                'business_id' => $this->business->id
            ]
        );

        $this->actingAs($this->user);

        $this->location_from = BusinessLocation::firstOrCreate(
            ['business_id' => $this->business->id, 'location_id' => 'LOC-ST-FROM'],
            ['name' => 'Location From']
        );

        $this->location_to = BusinessLocation::firstOrCreate(
            ['business_id' => $this->business->id, 'location_id' => 'LOC-ST-TO'],
            ['name' => 'Location To']
        );

        $unit = Unit::firstOrCreate(
            ['business_id' => $this->business->id, 'short_name' => 'Pcs'],
            ['actual_name' => 'Piece', 'allow_decimal' => 0, 'created_by' => $this->user->id]
        );

        $this->product = Product::create([
            'name' => 'Laptop SN Test Unit',
            'business_id' => $this->business->id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'sku' => 'LAPTOP-ST-SN-' . uniqid(),
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $this->user->id
        ]);

        $this->variation = Variation::create([
            'product_id' => $this->product->id,
            'name' => 'DUMMY',
            'product_variation_id' => 1,
            'sub_sku' => $this->product->sku,
            'default_purchase_price' => 5000000,
            'dpp_inc_tax' => 5000000,
            'profit_percent' => 0,
            'default_sell_price' => 6000000,
            'sell_price_inc_tax' => 6000000
        ]);
    }

    public function test_stock_transfer_creates_and_links_serial_numbers()
    {
        // 1. Create 2 in_stock serial numbers
        $sn1 = ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'serial_number' => 'ST-SN-0001',
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
            'status' => 'in_stock'
        ]);

        $sn2 = ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'serial_number' => 'ST-SN-0002',
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
            'status' => 'in_stock'
        ]);

        // 2. Submit stock transfer with completed status
        $data = [
            'location_id' => $this->location_from->id,
            'transfer_location_id' => $this->location_to->id,
            'transaction_date' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'shipping_charges' => 0,
            'final_total' => 10000000,
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'variation_id' => $this->variation->id,
                    'quantity' => 2,
                    'unit_price' => 5000000,
                    'product_unit_id' => $this->product->unit_id,
                    'enable_stock' => 1,
                    'serial_numbers' => ['ST-SN-0001', 'ST-SN-0002']
                ]
            ]
        ];

        $response = $this->post('/stock-transfers', $data);
        $response->assertRedirect('stock-transfers');

        // 3. Verify sell_transfer and purchase_transfer created
        $sell_transfer = Transaction::where('business_id', $this->business->id)
            ->where('type', 'sell_transfer')
            ->where('location_id', $this->location_from->id)
            ->first();

        $this->assertNotNull($sell_transfer);

        $purchase_transfer = Transaction::where('business_id', $this->business->id)
            ->where('type', 'purchase_transfer')
            ->where('transfer_parent_id', $sell_transfer->id)
            ->first();

        $this->assertNotNull($purchase_transfer);

        $s_line = TransactionSellLine::where('transaction_id', $sell_transfer->id)->first();
        $p_line = PurchaseLine::where('transaction_id', $purchase_transfer->id)->first();

        // 4. Assert serial numbers are linked to sell_line and purchase_line at destination location
        $this->assertDatabaseHas('product_serial_numbers', [
            'id' => $sn1->id,
            'transaction_sell_line_id' => $s_line->id,
            'purchase_line_id' => $p_line->id,
            'status' => 'in_stock'
        ]);

        $this->assertDatabaseHas('product_serial_numbers', [
            'id' => $sn2->id,
            'transaction_sell_line_id' => $s_line->id,
            'purchase_line_id' => $p_line->id,
            'status' => 'in_stock'
        ]);
    }
}
