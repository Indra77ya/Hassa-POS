<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\Transaction;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Repair\Entities\RepairTradeIn;
use Modules\Repair\Utils\RepairUtil;
use Tests\TestCase;

class RepairTradeInTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;
    protected $location;
    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Test Business TradeIn',
            'currency_id' => 1,
            'start_date' => '2023-01-01',
            'time_zone' => 'Asia/Jakarta',
        ]);

        $this->user = User::create([
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin_tradein',
            'email' => 'admin_tradein@test.com',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id,
        ]);

        $this->location = BusinessLocation::create([
            'business_id' => $this->business->id,
            'name' => 'Main Location',
            'landmark' => 'Center City',
            'city' => 'Jakarta',
            'state' => 'DKI',
            'country' => 'Indonesia',
            'zip_code' => '12345',
            'mobile' => '08123456789',
        ]);

        $this->contact = Contact::create([
            'business_id' => $this->business->id,
            'type' => 'customer',
            'name' => 'John Customer',
            'mobile' => '08987654321',
        ]);

        $this->actingAs($this->user);
        session(['user.business_id' => $this->business->id, 'user.id' => $this->user->id]);
    }

    public function test_save_or_update_trade_in_creates_product_and_purchase()
    {
        $repairUtil = new RepairUtil();

        // Create dummy sale transaction
        $sale_trans = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'paid',
            'contact_id' => $this->contact->id,
            'transaction_date' => now()->toDateTimeString(),
            'total_before_tax' => 5000000,
            'final_total' => 3000000, // 5.000.000 - 2.000.000 trade in
            'created_by' => $this->user->id,
            'invoice_no' => 'INV-TRD-001',
        ]);

        $trade_in_data = [
            'model_name' => 'iPhone 11 128GB',
            'serial_no' => '358912093810123',
            'condition' => 'Mulus 95%, Battery Health 85%',
            'trade_in_value' => 2000000,
            'resale_price' => 2500000,
        ];

        $trade_in = $repairUtil->saveOrUpdateTradeIn($this->business->id, $this->user->id, $trade_in_data, $sale_trans->id);

        $this->assertNotNull($trade_in);
        $this->assertEquals('iPhone 11 128GB', $trade_in->model_name);
        $this->assertEquals(2000000, $trade_in->trade_in_value);
        $this->assertEquals(2500000, $trade_in->resale_price);

        // Check second hand product created
        $product = Product::find($trade_in->product_id);
        $this->assertNotNull($product);
        $this->assertStringContainsString('iPhone 11 128GB', $product->name);

        // Check purchase transaction created for stock-in
        $purchase = Transaction::find($trade_in->purchase_transaction_id);
        $this->assertNotNull($purchase);
        $this->assertEquals('purchase', $purchase->type);
        $this->assertEquals('received', $purchase->status);
        $this->assertEquals(2000000, $purchase->final_total);

        // Check trade in payment deduction on sale transaction
        $this->assertDatabaseHas('transaction_payments', [
            'transaction_id' => $sale_trans->id,
            'amount' => 2000000,
            'note' => 'Tukar Tambah (Trade-In)',
        ]);
    }

    public function test_print_trade_in_receipt()
    {
        $repairUtil = new RepairUtil();

        $sale_trans = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'paid',
            'contact_id' => $this->contact->id,
            'transaction_date' => now()->toDateTimeString(),
            'total_before_tax' => 10000000,
            'final_total' => 7000000,
            'created_by' => $this->user->id,
            'invoice_no' => 'INV-TRD-002',
        ]);

        $trade_in_data = [
            'model_name' => 'Samsung Galaxy S20',
            'serial_no' => '9900112233',
            'condition' => 'Layar Baret Halus',
            'trade_in_value' => 3000000,
            'resale_price' => 3500000,
        ];

        $trade_in = $repairUtil->saveOrUpdateTradeIn($this->business->id, $this->user->id, $trade_in_data, $sale_trans->id);

        $response = $this->get('/repair/job-sheet/print-trade-in/' . $sale_trans->id);

        $response->assertStatus(200);
        $response->assertSee('KUITANSI TUKAR TAMBAH');
        $response->assertSee('Samsung Galaxy S20');
        $response->assertSee('9900112233');
        $response->assertSee('Pernyataan Kepemilikan');
    }
}
