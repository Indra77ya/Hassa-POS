<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\ProductSerialNumber;
use App\Unit;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddProductSerialNumberTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;
    protected $unit;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Add Product Serial Business',
            'currency_id' => 1,
            'start_date' => '2023-01-01',
            'time_zone' => 'Asia/Jakarta'
        ]);

        $this->user = User::create([
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'email' => 'admin_add_sn@test.com',
            'username' => 'admin_add_sn',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id
        ]);

        $this->actingAs($this->user);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'actual_name' => 'Piece',
            'short_name' => 'Pcs',
            'allow_decimal' => 0,
            'created_by' => $this->user->id
        ]);

        $this->category = Category::create([
            'name' => 'Handphone',
            'business_id' => $this->business->id,
            'category_type' => 'product',
            'created_by' => $this->user->id
        ]);
    }

    public function test_add_product_succeeds_with_enable_sr_no_checked()
    {
        $data = [
            'name' => 'Samsung Galaxy S24',
            'unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'barcode_type' => 'C128',
            'tax_type' => 'exclusive',
            'type' => 'single',
            'enable_sr_no' => 1,
            'single_dpp' => 10000000,
            'single_dpp_inc_tax' => 10000000,
            'profit_percent' => 20,
            'single_dsp' => 12000000,
            'single_dsp_inc_tax' => 12000000,
        ];

        $response = $this->post('/products', $data);

        $product = Product::where('name', 'Samsung Galaxy S24')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->enable_sr_no);

        // Verify no in_stock serial numbers are created automatically upon product creation
        $this->assertDatabaseMissing('product_serial_numbers', [
            'product_id' => $product->id,
        ]);
    }

    public function test_quick_add_product_with_enable_sr_no()
    {
        $data = [
            'name' => 'Xiaomi 14 Ultra',
            'unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'barcode_type' => 'C128',
            'tax_type' => 'exclusive',
            'type' => 'single',
            'enable_sr_no' => 1,
            'single_dpp' => 12000000,
            'single_dpp_inc_tax' => 12000000,
            'profit_percent' => 20,
            'single_dsp' => 14400000,
            'single_dsp_inc_tax' => 14400000,
        ];

        $response = $this->post('/products/save-quick-product', $data);
        $response->assertJson(['success' => 1]);

        $product = Product::where('name', 'Xiaomi 14 Ultra')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->enable_sr_no);

        // Verify no in_stock serial numbers are created
        $this->assertDatabaseMissing('product_serial_numbers', [
            'product_id' => $product->id,
        ]);
    }
}
