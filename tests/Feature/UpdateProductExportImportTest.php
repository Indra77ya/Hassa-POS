<?php

namespace Tests\Feature;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\User;
use App\Variation;
use App\VariationGroupPrice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpdateProductExportImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        // Create system table
        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });
        \DB::table('system')->insert(['key' => 'db_version', 'value' => config('author.app_version')]);

        // Create business_locations table
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('landmark')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });

        // Create categories table
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('category_type')->default('product');
            $table->integer('parent_id')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create brands table
        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create tax_rates table
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->decimal('amount', 22, 4);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create products table
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->string('sku')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(1);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create product_variations table
        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->integer('product_variation_id')->default(1);
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create selling_price_groups table
        Schema::dropIfExists('selling_price_groups');
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('business_id');
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create variation_group_prices table
        Schema::dropIfExists('variation_group_prices');
        Schema::create('variation_group_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variation_id');
            $table->integer('price_group_id');
            $table->decimal('price_inc_tax', 22, 4);
            $table->timestamps();
        });

        // Create product_locations table
        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->shouldReceive('hasAnyPermission')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
            \App\Http\Middleware\IsInstalled::class
        ]);

        session([
            'user.id' => 1,
            'user.business_id' => 1,
        ]);
    }

    public function test_import_products_updates_product_details_by_sku()
    {
        $business_id = 1;

        Business::create([
            'id' => $business_id,
            'name' => 'Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        // Create test business location
        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'name' => 'Location Test Update',
            'landmark' => 'Test',
            'city' => 'Test',
            'state' => 'Test',
            'zip_code' => '12345',
            'country' => 'ID',
        ]);

        // Create initial product
        $product = Product::create([
            'name' => 'Initial Product Update Test',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => 'SKU-UPDATE-TEST-001',
            'created_by' => 1,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => 'SKU-UPDATE-TEST-001',
            'product_variation_id' => 1,
            'default_purchase_price' => 10000,
            'dpp_inc_tax' => 10000,
            'profit_percent' => 20,
            'default_sell_price' => 12000,
            'sell_price_inc_tax' => 12000,
        ]);

        // Create selling price group
        $spg = SellingPriceGroup::create([
            'business_id' => $business_id,
            'name' => 'Wholesale Group Test',
            'description' => 'Test SPG',
        ]);

        // Create Tax Rate
        $tax = TaxRate::create([
            'business_id' => $business_id,
            'name' => 'PPN 11 Test',
            'amount' => 11,
            'created_by' => 1,
        ]);

        // Construct import CSV content
        $headers = [
            'Product',
            'SKU',
            'Category',
            'Sub Category',
            'Brand',
            'Tax',
            'Business Locations',
            'Default Purchase Price Exc. Tax',
            'Default Purchase Price Inc. Tax',
            'Margin (%)',
            'Default Selling Price Exc. Tax',
            'Default Selling Price Inc. Tax',
            'Wholesale Group Test'
        ];

        $row = [
            'Initial Product Update Test',
            'SKU-UPDATE-TEST-001',
            'Elektronik Test',
            'Gadget Test',
            'Brand Test',
            'PPN 11 Test',
            'Location Test Update',
            '20000',
            '22200',
            '25',
            '25000',
            '27750',
            '26000'
        ];

        $csvContent = implode(',', $headers) . "\n" . implode(',', $row);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv_') . '.csv';
        file_put_contents($tempFilePath, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'update_products.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->withSession([
            'user' => ['id' => 1, 'business_id' => $business_id],
            'business' => ['id' => $business_id, 'time_zone' => 'Asia/Jakarta']
        ])
            ->post('/import-product-price', [
                'product_group_prices' => $uploadedFile,
            ]);

        $response->assertRedirect('update-product-price');

        // Refresh product & variation
        $product->refresh();
        $variation->refresh();

        // Assert Category updated
        $this->assertNotNull($product->category_id);
        $category = Category::find($product->category_id);
        $this->assertEquals('Elektronik Test', $category->name);

        // Assert Sub Category updated
        $this->assertNotNull($product->sub_category_id);
        $subCategory = Category::find($product->sub_category_id);
        $this->assertEquals('Gadget Test', $subCategory->name);

        // Assert Brand updated
        $this->assertNotNull($product->brand_id);
        $brand = Brands::find($product->brand_id);
        $this->assertEquals('Brand Test', $brand->name);

        // Assert Tax updated
        $this->assertEquals($tax->id, $product->tax);

        // Assert Business Locations updated
        $this->assertTrue($product->product_locations->contains('id', $location->id));

        // Assert Prices updated
        $this->assertEquals(20000, (float)$variation->default_purchase_price);
        $this->assertEquals(25, (float)$variation->profit_percent);
        $this->assertEquals(27750, (float)$variation->sell_price_inc_tax);

        // Assert Group Price updated
        $groupPrice = VariationGroupPrice::where('variation_id', $variation->id)
            ->where('price_group_id', $spg->id)
            ->first();
        $this->assertNotNull($groupPrice);
        $this->assertEquals(26000, (float)$groupPrice->price_inc_tax);

        @unlink($tempFilePath);
    }
}
