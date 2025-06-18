<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AddresController extends Controller
{
    public function region()
    {
        $districts = Region::select('id', 'name')->get();

        // ID -> Name tarzida associative array qilib olish
        $regionMap = $districts->pluck('name', 'id')->toArray();

        // Redis cachega saqlash
        Cache::store('redis')->put('regions', json_encode($regionMap));

        return $this->successResponse([
            "regions" => $regionMap,
        ], "Return regions Successfully!!!");
    }

    public function district($region_id)
    {
        $districts = District::select('id', 'name')->where('region_id', $region_id)->get();

        $districtMap = $districts->pluck('name', 'id')->toArray();

        Cache::store('redis')->put('districts:' . $region_id, json_encode($districts), now()->addHours(12));
        Log::info("Districtlar topildi:", ['count' => $districts->count(), 'region_id' => $region_id]);

        return $this->successResponse([
            "districts" => $districtMap,
        ], "Return districts Saccessfully!!!");

    }
}
