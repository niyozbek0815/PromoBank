<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DevicesController extends Controller
{
    public function index(Request $request, $id)
    {
        $devices = UserDevice::where('user_id',$id)->get(); // Bu yerda qurilmalarni olish lozim
        return response()->json([
            'data' => $devices
        ]);
    }
}
