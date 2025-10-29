<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;

class AddresController extends Controller
{
    public function region()
    {
        $districts = Region::select('id', 'name')->orderBy('name')->get();
        $regionMap = $districts->pluck('name', 'id')->toArray();
        return $this->successResponse([
            "regions" => $regionMap,
        ], "Return regions Successfully!!!");
    }

    public function district($region_id)
    {
        $districts   = District::select('id', 'name')->orderBy('name')->where('region_id', $region_id)->get();
        $districtMap = $districts->pluck('name', 'id')->toArray();
        return $this->successResponse([
            "districts" => $districtMap,
        ], "Return districts Successfully!!!");

    }
}
