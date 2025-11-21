<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;
use App\Models\RegionLang;

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
    public function regionlang()
    {
        $districts = RegionLang::select('id', 'name')->orderBy('id')->get();

        // id => name array shaklida map qilish
        $regionMap = $districts->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        })->toArray();

        return $this->successResponse([
            "regions" => $regionMap,
        ], "Return regions Successfully!!!");
    }

    public function district($region_id)
    {
        $districts = District::select('id', 'name')->orderBy('name')->where('region_id', $region_id)->get();
        $districtMap = $districts->pluck('name', 'id')->toArray();
        return $this->successResponse([
            "districts" => $districtMap,
        ], "Return districts Successfully!!!");

    }
}
