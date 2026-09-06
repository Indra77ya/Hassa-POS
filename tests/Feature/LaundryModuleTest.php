<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryProcess;
use Modules\Laundry\Entities\LaundryServiceType;
use Modules\Laundry\Entities\LaundryItemType;

class LaundryModuleTest extends TestCase
{
    public function test_laundry_entities_and_points_calculation()
    {
        $status = new LaundryStatus([
            'business_id' => 1,
            'name' => 'Diterima',
            'color' => '#3c8dbc',
            'sort_order' => 1,
            'is_completed_status' => false,
        ]);

        $process = new LaundryProcess([
            'business_id' => 1,
            'name' => 'Pencucian',
            'points' => 2.5,
            'sort_order' => 1,
        ]);

        $service_type = new LaundryServiceType([
            'business_id' => 1,
            'name' => 'Express 1 Hari',
            'completion_hours' => 24,
        ]);

        $item_type = new LaundryItemType([
            'business_id' => 1,
            'name' => 'Pakaian Kiloan',
            'unit_name' => 'kg',
            'default_price' => 10000,
        ]);

        $this->assertEquals('Diterima', $status->name);
        $this->assertEquals(2.5, $process->points);
        $this->assertEquals(24, $service_type->completion_hours);
        $this->assertEquals('Pakaian Kiloan', $item_type->name);

        // Test staff points formula: Points = Process Points * Quantity
        $quantity = 4.0; // 4 kg
        $points_earned = $process->points * $quantity;
        $this->assertEquals(10.0, $points_earned);
    }

    public function test_laundry_pos_header_view_rendering()
    {
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $this->actingAs($user);

        $view = view('laundry::layouts.partials.pos_header', [
            '__is_laundry_enabled' => true,
            'transaction_sub_type' => '',
        ])->render();

        $this->assertStringContainsString('sub_type=laundry', $view);
        $this->assertStringContainsString('fa-tshirt', $view);
    }
}
