<?php

namespace App\Repositories;

use App\Models\Promotions;
use App\Repositories\RepositoryInterface\PromotionRepositoryInterface;

class PromotionRepository implements PromotionRepositoryInterface
{
    protected $model;

    public function __construct(Promotions $model)
    {
        $this->model = $model;
    }

    public function getAllPromotionsForMobile()
    {
        // Mobil platforma uchun barcha reklama aktsiyalarini olish
        return $this->model->whereHas('platforms', function ($query) {
            $query->where('name', 'mobile');
        })->where('status', true)
            ->where('is_public', true)
            ->with([
                'company:id,name,title,region,address',
                'company.media',
                'company.socialMedia.type',
                'participationTypes',
            ])
            ->get();
    }
    public function getAllPromotionsForWebHome()
    {
        // Mobil platforma uchun barcha reklama aktsiyalarini olish
        return $this->model->where('status', true)
            ->where('is_public', true)
            ->get();
    }
    public function getAllPromotionsShowForWebHome($id)
    {
        // Mobil platforma uchun barcha reklama aktsiyalarini olish
        return $this->model->where('status', true)
            ->where('is_public', true)->with([
            'company:id,name,title,region,address',
            'company.media',
            'company.socialMedia.type',
            'participationTypes',
            'platforms'
        ])
            ->findOrFail($id);
    }

    public function getPromotionByIdforVia($id, array $slug)
    {
        // Mobil platforma uchun reklama aktsiyasini ID bo'yicha olish
        return $this->model->whereHas('platforms', function ($query) {
            $query->where('name', 'mobile');
        })
            ->whereHas('participationTypes', function ($query) use ($slug) {
                $query->whereIn('slug', $slug);
            })->select('id', 'winning_strategy') // faqat kerakli ustun
            ->find($id);
    }
}
