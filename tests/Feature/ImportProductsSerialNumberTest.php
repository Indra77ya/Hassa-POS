<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\ProductSerialNumber;
use App\Unit;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportProductsSerialNumberTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $business;

    protected $location;

    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();
        if (! $this->user) {
            $this->business = Business::create([
                'name' => 'Test Business SN Import',
                'currency_id' => 1,
                'start_date' => '2023-01-01',
                'tax_number_1' => '12345',
            ]);
            $this->user = User::create([
                'surname' => 'Mr',
                'first_name' => 'Admin',
                'email' => 'admin_sn_import@test.com',
                'username' => 'admin_sn_import',
                'password' => bcrypt('123456'),
                'business_id' => $this->business->id,
            ]);
        } else {
            $this->business = Business::find($this->user->business_id);
            if (! $this->business) {
                $this->business = Business::create([
                    'name' => 'Test Business SN Import',
                    'currency_id' => 1,
                    'start_date' => '2023-01-01',
                    'tax_number_1' => '12345',
                ]);
                $this->user->business_id = $this->business->id;
                $this->user->save();
            }
        }

        $this->location = BusinessLocation::where('business_id', $this->business->id)->first();
        if (! $this->location) {
            $this->location = BusinessLocation::create([
                'business_id' => $this->business->id,
                'name' => 'Gudang Utama',
            ]);
        }

        $this->unit = Unit::where('business_id', $this->business->id)->first();
        if (! $this->unit) {
            $this->unit = Unit::create([
                'business_id' => $this->business->id,
                'actual_name' => 'Pcs',
                'short_name' => 'Pcs',
                'allow_decimal' => 0,
                'created_by' => $this->user->id,
            ]);
        }

        $this->actingAs($this->user);
        session([
            'user.business_id' => $this->business->id,
            'user.id' => $this->user->id,
            'business.default_profit_percent' => 20,
            'financial_year.start' => date('Y') . '-01-01',
        ]);
    }

    public function test_import_products_with_serial_numbers()
    {
        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "iPhone 15 Pro,,Pcs,,,IP15P-IMP,C128,1,5,,,exclusive,single,,,15000000,15000000,20,18000000,1,Gudang Utama,,1,0.2,,,,Test,,,,,0,Gudang Utama,percentage,SN-IMP-001,15000000,18000000\n" .
            "iPhone 15 Pro,,Pcs,,,IP15P-IMP,C128,1,5,,,exclusive,single,,,15000000,15000000,20,18000000,1,Gudang Utama,,1,0.2,,,,Test,,,,,0,Gudang Utama,percentage,SN-IMP-002,16000000,19000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('status', ['success' => 1, 'msg' => __('product.file_imported_successfully')]);

        // Assert 1 product created with enable_sr_no = 1
        $product = Product::where('business_id', $this->business->id)->where('sku', 'IP15P-IMP')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->enable_sr_no);

        // Assert serial numbers created
        $serials = ProductSerialNumber::where('business_id', $this->business->id)->where('product_id', $product->id)->get();
        $this->assertCount(2, $serials);

        $sn1 = $serials->where('serial_number', 'SN-IMP-001')->first();
        $this->assertNotNull($sn1);
        $this->assertEquals(15000000, $sn1->purchase_price);
        $this->assertEquals(18000000, $sn1->selling_price);

        $sn2 = $serials->where('serial_number', 'SN-IMP-002')->first();
        $this->assertNotNull($sn2);
        $this->assertEquals(16000000, $sn2->purchase_price);
        $this->assertEquals(19000000, $sn2->selling_price);

        // Assert product variation prices were calculated as average (15.5m and 18.5m)
        $variation = $product->variations()->first();
        $this->assertEquals(15500000, (float)$variation->default_purchase_price);
        $this->assertEquals(18500000, (float)$variation->default_sell_price);
    }

    public function test_import_products_rejects_opening_stock_greater_than_one_for_serial_numbers()
    {
        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "iPhone 15 Pro Invalid,,Pcs,,,IP15P-INV,C128,1,5,,,exclusive,single,,,15000000,15000000,20,18000000,5,Gudang Utama,,1,0.2,,,,Test,,,,,0,Gudang Utama,percentage,SN-IMP-INV01,15000000,18000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products_invalid.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('notification');
        $notification = session('notification');
        $this->assertEquals(0, $notification['success']);
        $this->assertStringContainsString('Serial number hanya boleh stoknya 1 aja', $notification['msg']);
    }

    public function test_import_products_rejects_duplicate_serial_number()
    {
        // Register an existing in_stock serial number
        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'serial_number' => 'SN-DUP-EXIST',
            'status' => 'in_stock',
        ]);

        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "Laptop Gaming,,Pcs,,,LAP-001,C128,1,5,,,exclusive,single,,,10000000,10000000,20,12000000,1,Gudang Utama,,1,0.2,,,,Test,,,,,0,Gudang Utama,percentage,SN-DUP-EXIST,10000000,12000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('notification');
        $notification = session('notification');
        $this->assertEquals(0, $notification['success']);
        $this->assertStringContainsString('SN-DUP-EXIST', $notification['msg']);
    }
}
