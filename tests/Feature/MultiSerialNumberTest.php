<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\InvoiceLayout;
use App\Product;
use App\ProductSerialNumber;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLine;
use App\Unit;
use App\User;
use App\Utils\ProductSerialNumberUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiSerialNumberTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;
    protected $location;
    protected $contact;
    protected $product;
    protected $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Test Business MultiSerial',
            'currency_id' => 1,
            'start_date' => '2023-01-01',
            'time_zone' => 'Asia/Jakarta'
        ]);

        $this->user = User::create([
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'email' => 'admin_sn@test.com',
            'username' => 'admin_sn',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id
        ]);

        $this->actingAs($this->user);

        $this->location = BusinessLocation::create([
            'business_id' => $this->business->id,
            'name' => 'Main Store',
            'location_id' => 'LOC-SN-1'
        ]);

        $this->contact = Contact::create([
            'business_id' => $this->business->id,
            'type' => 'customer',
            'name' => 'Customer Test',
            'mobile' => '08123456789'
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'actual_name' => 'Piece',
            'short_name' => 'Pcs',
            'allow_decimal' => 0,
            'created_by' => $this->user->id
        ]);

        $this->product = Product::create([
            'name' => 'iPhone 15 Pro Test Unit',
            'business_id' => $this->business->id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'sku' => 'TEST-IPHONE15-PRO-' . time(),
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $this->user->id
        ]);

        $this->variation = Variation::create([
            'product_id' => $this->product->id,
            'name' => 'DUMMY',
            'product_variation_id' => 1,
            'sub_sku' => $this->product->sku,
            'default_purchase_price' => 1000,
            'dpp_inc_tax' => 1000,
            'profit_percent' => 0,
            'default_sell_price' => 1200,
            'sell_price_inc_tax' => 1200
        ]);

        InvoiceLayout::create([
            'name' => 'Default Layout',
            'business_id' => $this->business->id,
            'show_customer' => 1
        ]);
    }

    public function test_purchase_registers_in_stock_serial_numbers()
    {
        $snUtil = new ProductSerialNumberUtil();

        $purchase = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'purchase',
            'status' => 'received',
            'payment_status' => 'paid',
            'contact_id' => $this->contact->id,
            'transaction_date' => now(),
            'total_before_tax' => 2000,
            'final_total' => 2000,
            'created_by' => $this->user->id
        ]);

        $p_line = PurchaseLine::create([
            'transaction_id' => $purchase->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'quantity' => 2,
            'purchase_price' => 1000,
            'purchase_price_inc_tax' => 1000
        ]);

        $snUtil->syncPurchaseSerialNumbers(
            $this->business->id,
            $this->product->id,
            $this->variation->id,
            $p_line->id,
            ['SN-IPH15-001', 'SN-IPH15-002'],
            1000
        );

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'serial_number' => 'SN-IPH15-001',
            'status' => 'in_stock'
        ]);

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'serial_number' => 'SN-IPH15-002',
            'status' => 'in_stock'
        ]);
    }

    public function test_sell_updates_serial_numbers_to_sold_and_receipt_details()
    {
        $snUtil = new ProductSerialNumberUtil();
        $txUtil = new TransactionUtil();

        $sn1 = ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'serial_number' => 'SN-SOLD-TEST-001',
            'status' => 'in_stock'
        ]);

        $sell = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'paid',
            'contact_id' => $this->contact->id,
            'invoice_no' => 'INV-SN-TEST-01',
            'transaction_date' => now(),
            'total_before_tax' => 1200,
            'final_total' => 1200,
            'created_by' => $this->user->id
        ]);

        $s_line = TransactionSellLine::create([
            'transaction_id' => $sell->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'quantity' => 1,
            'unit_price' => 1200,
            'unit_price_inc_tax' => 1200
        ]);

        $snUtil->syncSellSerialNumbers($this->business->id, $s_line->id, ['SN-SOLD-TEST-001']);

        $this->assertDatabaseHas('product_serial_numbers', [
            'id' => $sn1->id,
            'transaction_sell_line_id' => $s_line->id,
            'status' => 'sold'
        ]);

        $il = InvoiceLayout::where('business_id', $this->business->id)->first();
        $details = $txUtil->getReceiptDetails($sell->id, $sell->location_id, $il, $this->business, $this->location, 'browser');

        $this->assertNotNull($details);
        $this->assertStringContainsString('SN-SOLD-TEST-001', $details->lines[0]['serial_numbers']);

        // Test deleting sell line restores serial number to in_stock
        $txUtil->deleteSellLines([$s_line->id], $sell->location_id, false);

        $this->assertDatabaseHas('product_serial_numbers', [
            'id' => $sn1->id,
            'transaction_sell_line_id' => null,
            'status' => 'in_stock'
        ]);
    }

    public function test_serial_numbers_index_and_registered_serials_routes()
    {
        $response = $this->get('/product-serial-numbers');
        $response->assertStatus(200);

        $response_json = $this->getJson('/get-registered-serials?product_id=' . $this->product->id);
        $response_json->assertStatus(200);
        $response_json->assertJson(['success' => true]);
    }
}
