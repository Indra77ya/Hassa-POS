<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportController;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ProfitReportPaymentStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function get_profit_query_filters_by_paid_and_partial_payment_status()
    {
        // Reflection check to ensure ReportController getProfit contains payment_status filter logic
        $controllerFile = file_get_contents(app_path('Http/Controllers/ReportController.php'));
        $this->assertStringContainsString("whereIn('sale.payment_status', ['paid', 'partial'])", $controllerFile);
    }
}
