<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
{
    public function index()
    {
        $promo = Promotions::whereHas('platforms', function ($query) {
            $query->where('name', 'mobile');
        })
            ->with([
                'media',
                'company:id,name,title,region,address',
                'company.media',
                'company.socialMedia.type',
                'participationTypes.participationType'
            ])
            ->select('id', 'company_id', 'name', 'title', 'description', 'start_date', 'end_date')
            ->paginate(10);
        return $this->successResponse(['promotions' => PromotionResource::collection($promo)], "success");
    }
}
