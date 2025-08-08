<?php
namespace App\Services;

use App\Models\PrizeCategory;
use App\Models\Promotions;

class PrizeCategoryService
{
    public function __construct()
    {

    }
    public function getManualData($promotion, $type)
    {
        return [
            'promotion'=>$this->getPromotion($promotion),
            'category' => $this->getCategory($type),
        ];

    }
    public function getSmartRandomData($promotion, $type)
    {
        return [
            'promotion' => $this->getPromotion($promotion),
            'category' => $this->getCategory($type),
        ];

    }
    public function autoBindData($promotion, $type)
    {
        return [
            'promotion' => $this->getPromotion($promotion),
            'category' => $this->getCategory($type),
        ];

    }
    public function getWeightedRandom($promotion, $type)
    {
        return [
            'promotion' =>$this->getPromotion($promotion),
            'category' => $this->getCategory($type),
        ];

    }
    protected function getCategory($type)
    {
        return PrizeCategory::where('name', $type)->firstOrFail();
    }
   protected function getPromotion($id): array
{
    $promotion = Promotions::findOrFail($id);

    return [
        'id'   => $promotion->id,
        'name' => $promotion->getTranslation('name', 'uz'),
    ];
}
}
