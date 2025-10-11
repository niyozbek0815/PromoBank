<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // You can add logic here to fetch data for the dashboard
        // For example, fetching user statistics, sales data, etc.

        return view('admin.index');
    }
}
