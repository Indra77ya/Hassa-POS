<?php

namespace Tests\Feature;

use App\Business;
use App\Product;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\Manufacturing\Entities\MfgRecipe;
use Modules\Manufacturing\Entities\MfgRecipeIngredient;
use Tests\TestCase;

class ManufacturingImportRecipeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Modules\Manufacturing\Providers\ManufacturingServiceProvider::class)) {
            $this->app->register(\Modules\Manufacturing\Providers\ManufacturingServiceProvider::class);
        }

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('manufacturing_settings')->nullable();
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        \DB::table('business')->insert([
            'id' => 1,
            'name' => 'Manufacturing Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        // Create system table for manufacturing version and APP_VERSION
        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });
        \DB::table('system')->insert(['key' => 'db_version', 'value' => config('author.app_version')]);
        \DB::table('system')->insert(['key' => 'manufacturing_version', 'value' => '1.0']);

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        // Create units table
        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->integer('created_by')->nullable();
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
            $table->boolean('enable_stock')->default(1);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create mfg_recipes table
        Schema::dropIfExists('mfg_recipes');
        Schema::create('mfg_recipes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->text('instructions')->nullable();
            $table->decimal('waste_percent', 10, 2)->default(0);
            $table->decimal('ingredients_cost', 22, 4)->default(0);
            $table->decimal('extra_cost', 22, 4)->default(0);
            $table->string('production_cost_type')->default('percentage');
            $table->decimal('total_quantity', 22, 4)->default(0);
            $table->decimal('final_price', 22, 4)->default(0);
            $table->integer('sub_unit_id')->nullable();
            $table->timestamps();
        });

        // Create mfg_recipe_ingredients table
        Schema::dropIfExists('mfg_recipe_ingredients');
        Schema::create('mfg_recipe_ingredients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mfg_recipe_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('waste_percent', 22, 4)->default(0);
            $table->integer('sub_unit_id')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->shouldReceive('hasAnyPermission')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $this->actingAs($user);

        session([
            'user.id' => 1,
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
        ]);
    }

    /** @test */
    public function it_can_import_recipes_from_csv_and_overwrite_existing()
    {
        Business::create([
            'id' => 1,
            'name' => 'Manufacturing Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'allow_decimal' => 0,
            'created_by' => 1,
        ]);

        $mainProduct = Product::create([
            'name' => 'Kue Coklat',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'PROD-KUE',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $mainVariation = Variation::create([
            'name' => 'Dummmy',
            'product_id' => $mainProduct->id,
            'sub_sku' => 'PROD-KUE',
            'default_purchase_price' => 10000,
            'dpp_inc_tax' => 10000,
            'profit_percent' => 10,
            'default_sell_price' => 11000,
            'sell_price_inc_tax' => 11000,
        ]);

        $ingProduct1 = Product::create([
            'name' => 'Tepung Terigu',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'ING-TEPUNG',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $ingVariation1 = Variation::create([
            'name' => 'Dummmy',
            'product_id' => $ingProduct1->id,
            'sub_sku' => 'ING-TEPUNG',
            'default_purchase_price' => 5000,
            'dpp_inc_tax' => 5000,
            'profit_percent' => 0,
            'default_sell_price' => 5000,
            'sell_price_inc_tax' => 5000,
        ]);

        $ingProduct2 = Product::create([
            'name' => 'Coklat Bubuk',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'ING-COKLAT',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $ingVariation2 = Variation::create([
            'name' => 'Dummmy',
            'product_id' => $ingProduct2->id,
            'sub_sku' => 'ING-COKLAT',
            'default_purchase_price' => 15000,
            'dpp_inc_tax' => 15000,
            'profit_percent' => 0,
            'default_sell_price' => 15000,
            'sell_price_inc_tax' => 15000,
        ]);

        // CSV content
        $csvContent = "Product SKU,Output Quantity,Output Sub Unit,Extra Cost,Production Cost Type,Instructions,Ingredient SKU,Ingredient Quantity,Ingredient Sub Unit,Ingredient Waste Percent\n";
        $csvContent .= "PROD-KUE,1,,1000,fixed,Campur bahan dan panggang.,ING-TEPUNG,2,,0\n";
        $csvContent .= "PROD-KUE,1,,1000,fixed,Campur bahan dan panggang.,ING-COKLAT,1,,5\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'recipes.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->post('/manufacturing/import-recipe', [
            'recipes_csv' => $uploadedFile,
        ]);

        $response->assertRedirect('/manufacturing/recipe');
        $response->assertSessionHas('status', ['success' => 1, 'msg' => __('manufacturing::lang.recipe_imported_successfully')]);

        // Assert recipe exists in database
        $recipe = MfgRecipe::where('variation_id', $mainVariation->id)->first();
        $this->assertNotNull($recipe);
        $this->assertEquals(1, $recipe->total_quantity);
        $this->assertEquals(1000, $recipe->extra_cost);
        $this->assertEquals('fixed', $recipe->production_cost_type);

        // Ingredients cost = (2 * 5000) + (1 * 15000) = 25000
        $this->assertEquals(25000, $recipe->ingredients_cost);
        // Final price = 25000 + 1000 (fixed) = 26000
        $this->assertEquals(26000, $recipe->final_price);

        $ingredients = MfgRecipeIngredient::where('mfg_recipe_id', $recipe->id)->get();
        $this->assertCount(2, $ingredients);
    }
}
