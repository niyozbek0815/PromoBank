<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;

class AddresController extends Controller
{
    public function region()
    {
        return $this->successResponse([
            "regions" => Region::get()
        ], "Return regions Saccessfully!!!");
    }

    public function district($region_id)
    {
        return $this->successResponse([
            "regions" => District::select('id', 'name')->where('region_id', $region_id)->get()
        ], "Return districts Saccessfully!!!");
    }
}
