<?php

namespace Tests\Feature;

use App\BusinessLocation;
use App\Contact;
use App\Http\Middleware\IsInstalled;
use App\Product;
use App\ProductSerialNumber;
use App\ProductVariation;
use App\PurchaseLine;
use App\Transaction;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ImportPurchasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

        $this->withoutMiddleware([
            IsInstalled::class,
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
        ]);

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->string('start_date')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('fy_start_month')->default(1);
            $table->string('accounting_method')->default('fifo');
            $table->string('default_sales_discount')->default('0.00');
            $table->string('sell_price_tax')->default('includes');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });
        DB::table('system')->insert(['key' => 'db_version', 'value' => config('author.app_version')]);
    }

    private function createBusinessAndUser()
    {
        DB::table('currencies')->insertOrIgnore([
            'id' => 1,
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $business = \App\Business::create([
            'name' => 'Test Business',
            'currency_id' => 1,
            'start_date' => '2023-01-01',
            'time_zone' => 'Asia/Jakarta',
        ]);

        Permission::firstOrCreate(['name' => 'purchase.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $user = User::create([
            'business_id' => $business->id,
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'username' => 'admin_test_'.uniqid(),
            'email' => 'testuser_import_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'user_type' => 'user',
        ]);

        $user->givePermissionTo(['purchase.create', 'superadmin']);

        $business->owner_id = $user->id;
        $business->save();

        return [$business, $user];
    }

    public function test_import_purchases_index_page_is_accessible()
    {
        [$business, $user] = $this->createBusinessAndUser();

        $response = $this->actingAs($user)
            ->withSession([
                'user' => [
                    'id' => $user->id,
                    'business_id' => $business->id,
                ],
            ])
            ->get('/import-purchases');

        $response->assertStatus(200);
        $response->assertSee(__('lang_v1.import_purchases'));
    }

    public function test_import_purchases_preview_and_import_process()
    {
        [$business, $user] = $this->createBusinessAndUser();
        $business_id = $business->id;

        // Ensure Business Location exists
        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'name' => 'Main Location',
            'landmark' => 'Center City',
            'city' => 'Jakarta',
            'state' => 'DKI',
            'country' => 'Indonesia',
            'zip_code' => '12345',
            'mobile' => '08123456789',
            'invoice_scheme_id' => 1,
            'invoice_layout_id' => 1,
        ]);

        // Ensure Supplier exists
        $supplier = Contact::create([
            'business_id' => $business_id,
            'type' => 'supplier',
            'name' => 'Supplier Test',
            'contact_id' => 'SUP-TEST-001',
            'mobile' => '08123456789',
            'created_by' => $user->id,
        ]);

        // Ensure Product & Variation exist
        $unit = Unit::create([
            'actual_name' => 'Pcs',
            'short_name' => 'Pcs',
            'allow_decimal' => 0,
            'created_by' => $user->id,
            'business_id' => $business_id,
        ]);

        $product = Product::create([
            'name' => 'Import Purchase Product',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'enable_stock' => 1,
            'sku' => 'SKU-IMP-PUR-001',
            'barcode_type' => 'C128',
            'tax_type' => 'exclusive',
            'created_by' => $user->id,
        ]);

        $productVariation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'DUMMY',
            'is_dummy' => 1,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $productVariation->id,
            'sub_sku' => 'SKU-IMP-PUR-001',
            'default_purchase_price' => 100000,
            'dpp_inc_tax' => 100000,
            'profit_percent' => 20,
            'default_sell_price' => 120000,
            'sell_price_inc_tax' => 120000,
        ]);

        // Test File Upload Preview
        $csvContent = "Reference No,Supplier ID,Supplier Name,Supplier Phone,Purchase Date,Purchase Status,Product Name,SKU,Quantity,Unit Cost Before Discount,Discount Percent,Item Tax,Selling Price,Serial Numbers\n";
        $csvContent .= "REF-PUR-TEST-999,".$supplier->contact_id.",,,2026-10-06 10:00:00,received,".$product->name.",".$variation->sub_sku.",2,100000,0,,120000,\"SN-IMP-101,SN-IMP-102\"\n";

        $uploadedFile = UploadedFile::fake()->createWithContent('purchases.csv', $csvContent);

        $previewResponse = $this->actingAs($user)
            ->withSession([
                'user' => [
                    'id' => $user->id,
                    'business_id' => $business_id,
                ],
            ])
            ->post('/import-purchases/preview', [
                'purchases' => $uploadedFile,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertSee(__('lang_v1.preview_imported_purchases'));

        // Test Import Action directly
        $tempFilePath = public_path('uploads/temp/test_import_purchase.csv');
        file_put_contents($tempFilePath, $csvContent);

        $requestData = [
            'file_name' => 'test_import_purchase.csv',
            'import_fields' => [
                0 => 'ref_no',
                1 => 'supplier_id',
                2 => 'supplier_name',
                3 => 'supplier_phone',
                4 => 'date',
                5 => 'status',
                6 => 'product',
                7 => 'sku',
                8 => 'quantity',
                9 => 'unit_cost_before_discount',
                10 => 'discount_percent',
                11 => 'item_tax',
                12 => 'selling_price',
                13 => 'serial_numbers',
            ],
            'group_by' => 0,
            'location_id' => $location->id,
        ];

        $response = $this->actingAs($user)
            ->withSession([
                'user' => [
                    'id' => $user->id,
                    'business_id' => $business_id,
                ],
            ])
            ->post('/import-purchases', $requestData);

        $response->assertRedirect('import-purchases');

        // Assert Purchase Transaction created
        $transaction = Transaction::where('business_id', $business_id)
            ->where('ref_no', 'REF-PUR-TEST-999')
            ->where('type', 'purchase')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('received', $transaction->status);

        // Assert Purchase Line created
        $purchaseLine = PurchaseLine::where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($purchaseLine);
        $this->assertEquals(2, $purchaseLine->quantity);

        // Assert Product Serial Numbers created
        $serials = ProductSerialNumber::where('purchase_line_id', $purchaseLine->id)->pluck('serial_number')->toArray();
        $this->assertContains('SN-IMP-101', $serials);
        $this->assertContains('SN-IMP-102', $serials);
    }
}
