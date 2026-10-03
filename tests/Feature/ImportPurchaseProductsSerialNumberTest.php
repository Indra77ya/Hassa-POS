<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\ProductSerialNumber;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ImportPurchaseProductsSerialNumberTest extends TestCase
{
    protected $user;

    protected $business;

    protected $location;

    protected $unit;

    protected $product;

    protected $variation;

    protected $createdEnv = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), 'APP_ENV=testing');
            $this->createdEnv = true;
        }

        Artisan::call('migrate');
        DB::beginTransaction();

        $currency_id = DB::table('currencies')->insertGetId([
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = User::firstOrCreate(
            ['email' => 'admin_imp_pur_sn@test.com'],
            [
                'surname' => 'Mr',
                'first_name' => 'Admin',
                'username' => 'admin_imp_pur_sn',
                'password' => bcrypt('123456'),
                'business_id' => 1,
                'user_type' => 'user',
                'allow_login' => 1,
            ]
        );

        $this->business = Business::firstOrCreate(
            ['name' => 'Import Purchase SN Business'],
            [
                'currency_id' => $currency_id,
                'start_date' => '2023-01-01',
                'time_zone' => 'Asia/Jakarta',
                'tax_number_1' => '',
                'tax_label_1' => '',
                'stop_selling_before' => 0,
                'owner_id' => $this->user->id,
                'weighing_scale_setting' => json_encode([]),
            ]
        );

        $this->user->business_id = $this->business->id;
        $this->user->save();

        Permission::firstOrCreate(['name' => 'purchase.create', 'guard_name' => 'web']);
        $this->user->givePermissionTo('purchase.create');

        $this->location = BusinessLocation::firstOrCreate(
            ['business_id' => $this->business->id, 'location_id' => 'LOC-IMP-PUR'],
            [
                'name' => 'Toko Utama',
                'country' => 'Indonesia',
                'state' => 'DKI Jakarta',
                'city' => 'Jakarta',
                'zip_code' => '10000',
                'landmark' => '',
                'invoice_scheme_id' => 1,
                'invoice_layout_id' => 1,
            ]
        );

        $this->unit = Unit::firstOrCreate(
            ['business_id' => $this->business->id, 'short_name' => 'Pcs'],
            ['actual_name' => 'Piece', 'allow_decimal' => 0, 'created_by' => $this->user->id]
        );

        $this->actingAs($this->user);
        session([
            'user.business_id' => $this->business->id,
            'user.id' => $this->user->id,
            'currency' => [
                'id' => $currency_id,
                'code' => 'IDR',
                'symbol' => 'Rp',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
            ],
            'business.currency_precision' => 2,
            'business.quantity_precision' => 2,
        ]);

        $sku = 'GALS23-01-' . uniqid();
        $this->product = Product::create([
            'name' => 'Smartphone Galaxy S23',
            'business_id' => $this->business->id,
            'unit_id' => $this->unit->id,
            'brand_id' => null,
            'category_id' => null,
            'tax' => null,
            'tax_type' => 'exclusive',
            'barcode_type' => 'C128',
            'type' => 'single',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'sku' => $sku,
            'created_by' => $this->user->id,
        ]);

        $pv = ProductVariation::create([
            'product_id' => $this->product->id,
            'name' => 'DUMMY',
            'is_dummy' => 1,
        ]);

        $this->variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $this->product->id,
            'product_variation_id' => $pv->id,
            'sub_sku' => $sku,
            'default_purchase_price' => 5000000,
            'dpp_inc_tax' => 5000000,
            'profit_percent' => 20,
            'default_sell_price' => 6000000,
            'sell_price_inc_tax' => 6000000,
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        if ($this->createdEnv && file_exists(base_path('.env'))) {
            unlink(base_path('.env'));
        }

        parent::tearDown();
    }

    public function test_import_purchase_products_with_serial_numbers()
    {
        $sku = $this->product->sku;
        $csvHeader = "SKU,QUANTITY,UNIT COST (BEFORE DISCOUNT),DISCOUNT PERCENT,PRODUCT TAX,LOT NUMBER,MFG DATE,EXP DATE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "{$sku},,5000000,0,,LOT-A1,,,SN-PUR-101,5100000,6100000\n" .
            "{$sku},,5000000,0,,LOT-A1,,,SN-PUR-102,5300000,6300000\n";

        $file = UploadedFile::fake()->createWithContent('import_purchase.csv', $csvContent);

        $response = $this->post('/import-purchase-products', [
            'file' => $file,
            'location_id' => $this->location->id,
            'row_count' => 0,
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('html', $json);

        $html = $json['html'];

        // Assert serial numbers are rendered in the HTML dropdown with selected="selected"
        $this->assertStringContainsString('SN-PUR-101', $html);
        $this->assertStringContainsString('SN-PUR-102', $html);
        $this->assertStringContainsString('selected="selected"', $html);

        // Assert quantity is calculated as 2 (count of serial numbers)
        $this->assertStringContainsString('value="2', $html);

        // Assert average purchase price (5,200,000) is rendered
        $this->assertStringContainsString('5.200.000', $html);
    }

    public function test_import_purchase_products_rejects_duplicate_in_stock_serial_number()
    {
        $sku = $this->product->sku;

        // Existing in_stock serial number in database
        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'serial_number' => 'SN-EXISTS-100',
            'status' => 'in_stock',
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
        ]);

        $csvHeader = "SKU,QUANTITY,UNIT COST (BEFORE DISCOUNT),DISCOUNT PERCENT,PRODUCT TAX,LOT NUMBER,MFG DATE,EXP DATE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "{$sku},,5000000,0,,LOT-A1,,,SN-EXISTS-100,5000000,6000000\n";

        $file = UploadedFile::fake()->createWithContent('import_purchase.csv', $csvContent);

        $response = $this->post('/import-purchase-products', [
            'file' => $file,
            'location_id' => $this->location->id,
            'row_count' => 0,
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertFalse($json['success']);
        $this->assertStringContainsString('SN-EXISTS-100', $json['msg']);
    }
}
