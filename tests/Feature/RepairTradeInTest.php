<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Repair\Entities\JobSheet;
use Modules\Repair\Entities\RepairStatus;
use Modules\Repair\Entities\RepairTradeIn;
use Modules\Repair\Utils\RepairUtil;
use Tests\TestCase;

class RepairTradeInTest extends TestCase
{
    use DatabaseMigrations;

    protected $business;
    protected $user;
    protected $location;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::firstOrCreate(
            ['name' => 'Test Repair Business'],
            ['currency_id' => 1, 'start_date' => '2025-01-01', 'time_zone' => 'Asia/Jakarta']
        );

        $this->user = User::firstOrCreate(
            ['email' => 'tech_test@example.com'],
            [
                'surname' => 'Mr',
                'first_name' => 'Tech',
                'last_name' => 'Tester',
                'username' => 'tech_tester_' . time(),
                'password' => bcrypt('secret'),
                'business_id' => $this->business->id,
            ]
        );

        $this->location = BusinessLocation::firstOrCreate(
            ['business_id' => $this->business->id, 'name' => 'Repair Center'],
            ['landmark' => 'Main Street']
        );

        $this->customer = Contact::firstOrCreate(
            ['business_id' => $this->business->id, 'type' => 'customer', 'name' => 'John Doe'],
            ['mobile' => '08123456789', 'created_by' => $this->user->id]
        );

        $this->actingAs($this->user);
        session(['user' => ['business_id' => $this->business->id, 'id' => $this->user->id]]);
    }

    public function test_save_or_update_trade_in_creates_product_and_purchase()
    {
        $status = RepairStatus::create([
            'business_id' => $this->business->id,
            'name' => 'Diterima',
            'color' => '#00ff00',
        ]);

        $job_sheet = JobSheet::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'contact_id' => $this->customer->id,
            'job_sheet_no' => 'JS-TEST-' . time(),
            'status_id' => $status->id,
            'serial_no' => 'SN-12345',
            'created_by' => $this->user->id,
        ]);

        $trade_in_data = [
            'device_name' => 'iPhone 11 Second',
            'brand' => 'Apple',
            'model' => 'iPhone 11',
            'serial_no' => 'SN-IPHONE11-99',
            'condition' => 'Mulus, Battery Health 85%',
            'trade_in_value' => '1500000',
            'unit_price' => '2000000',
            'notes' => 'Unit + Charger',
        ];

        $repairUtil = new RepairUtil();
        $trade_in = $repairUtil->saveOrUpdateTradeIn($this->business->id, $this->user->id, $trade_in_data, null, $job_sheet->id);

        $this->assertNotNull($trade_in);
        $this->assertEquals($this->business->id, $trade_in->business_id);
        $this->assertEquals(1500000, $trade_in->trade_in_value);
        $this->assertEquals(2000000, $trade_in->unit_price);

        // Verify Product was created with stock enabled
        $product = Product::find($trade_in->product_id);
        $this->assertNotNull($product);
        $this->assertStringContainsString('iPhone 11 Second', $product->name);
        $this->assertEquals(1, $product->enable_stock);

        // Verify Purchase Transaction was created
        $purchase = Transaction::find($trade_in->purchase_transaction_id);
        $this->assertNotNull($purchase);
        $this->assertEquals('purchase', $purchase->type);
        $this->assertEquals('received', $purchase->status);
        $this->assertEquals(1500000, $purchase->final_total);
    }

    public function test_trade_in_reduces_sale_transaction_payable()
    {
        $sale = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $this->customer->id,
            'transaction_date' => now(),
            'total_before_tax' => 3000000,
            'final_total' => 3000000,
            'created_by' => $this->user->id,
        ]);

        $trade_in_data = [
            'device_name' => 'Samsung A51 Bekas',
            'brand' => 'Samsung',
            'trade_in_value' => '1000000',
            'unit_price' => '1200000',
        ];

        $repairUtil = new RepairUtil();
        $trade_in = $repairUtil->saveOrUpdateTradeIn($this->business->id, $this->user->id, $trade_in_data, $sale->id);

        $sale->refresh();
        $this->assertNotNull($trade_in);
        $this->assertEquals('partial', $sale->payment_status);

        // Payment line created for trade in deduction
        $payment_sum = $sale->payment_lines()->sum('amount');
        $this->assertEquals(1000000, $payment_sum);
    }
}
