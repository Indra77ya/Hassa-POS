<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;

class LaundryController extends Controller
{
    public function index()
    {
        return redirect()->action([\Modules\Laundry\Http\Controllers\DashboardController::class, 'index']);
    }
}
