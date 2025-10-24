<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Benefit;
use App\Models\Contact;
use App\Models\Download;
use App\Models\ForSponsor;
use App\Models\Portfolio;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'
        $page = $request->get('page', 1);

        $cacheKey = "frontend:home:{$lang}:page:{$page}";
        $ttl      = now()->addMinutes(5);

        $data = Cache::store('redis')->remember($cacheKey, $ttl, function () use ($lang) {
            // About
            $aboutModel = About::first();
            $about = $aboutModel ? [
                'subtitle'    => $aboutModel->subtitle[$lang] ?? '',
                'title'       => $aboutModel->title[$lang] ?? '',
                'description' => $aboutModel->description[$lang] ?? '',
                'image'       => $aboutModel->image ?? '',
                'list'        => $aboutModel->list[$lang] ?? [],
            ] : null;

            // Download
            $downloadModel = Download::with('links')->first();
            $download = $downloadModel ? [
                'subtitle'    => $downloadModel->subtitle[$lang] ?? '',
                'title'       => $downloadModel->title[$lang] ?? '',
                'description' => $downloadModel->description[$lang] ?? '',
                'image'       => $downloadModel->image ?? '',
                'links'       => $downloadModel->links->map(fn($link) => [
                    'type' => $link->type,
                    'url'  => $link->url,
                ])->values()->toArray(),
            ] : null;

            // Social links
            $socialLinks = SocialLink::where('status', 1)
                ->orderBy('position')
                ->get(['type', 'url'])
                ->map(fn($link) => [
                    'type' => $link->type,
                    'url'  => $link->url,
                ])
                ->toArray();

            // Contacts
            $contacts = Contact::where('status', 1)
                ->orderBy('position')
                ->get(['type', 'url', 'label'])
                ->map(fn($contact) => [
                    'type'  => $contact->type,
                    'url'   => $contact->url,
                    'label' => $contact->getTranslation('label', $lang),
                ])
                ->toArray();

            // Benefits
            $benefits = Benefit::orderBy('position', 'asc')
                ->take(6)
                ->get()
                ->mapWithKeys(function ($benefit, $index) use ($lang) {
                    return [
                        $index + 1 => [
                            'title' => $benefit->getTranslation('title', $lang),
                            'desc'  => $benefit->getTranslation('description', $lang),
                            'img'   => $benefit->image,
                        ]
                    ];
                })
                ->toArray();

            // Sponsors
            $sponsors = Sponsor::where('status', 1)
                ->orderBy('weight', 'asc')
                ->get()
                ->map(function ($sponsor, $index) use ($lang) {
                    return [
                        'url' => $sponsor->url,
                        'img' => $sponsor->image,
                        'alt' => $sponsor->getTranslation('name', $lang) ?: 'Sponsor ' . ($index + 1),
                    ];
                })
                ->values()
                ->toArray();

            // For Sponsors
            $forSponsors = ForSponsor::where('status', 1)
                ->orderBy('position', 'asc')
                ->take(8)
                ->get()
                ->map(fn($item) => [
                    'title' => $item->getTranslation('title', $lang),
                    'desc'  => $item->getTranslation('description', $lang),
                    'img'   => $item->image,
                ])
                ->values();

            // Portfolios
            $portfolios = Portfolio::where('status', 1)
                ->orderBy('position', 'asc')
                ->get()
                ->map(fn($item) => [
                    'title'    => $item->getTranslation('title', $lang),
                    'subtitle' => $item->getTranslation('subtitle', $lang),
                    'img'      => $item->image,
                ])
                ->values();

            // Settings
            $settings = Setting::all()->mapWithKeys(function ($setting) use ($lang) {
                $val = $setting->val;
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    if ($decoded !== null) {
                        $val = $decoded;
                    }
                }
                if (is_array($val) && $setting->key_name !== 'languages') {
                    $val = $val[$lang] ?? reset($val);
                }
                return [$setting->key_name => $val];
            });


            return [
                'socialLinks' => $socialLinks,
                'contacts'    => $contacts,
                'download'    => $download,
                'benefits'    => $benefits,
                'portfolios'  => $portfolios,
                'forSponsors' => $forSponsors,
                'sponsors'    => $sponsors,
                'about'       => $about,
                'settings'    => $settings,
            ];
        });

        return response()->json($data);
    }
    public function pages(Request $request)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'

        $cacheKey = "frontend:download:{$lang}";
        $ttl      = now()->addMinutes(5);
        // Cache::store('redis')->forget("promotions_list_{$lang}");
        $data = Cache::store('redis')->remember($cacheKey, $ttl, function () use ($lang) {

            $socialLinks = SocialLink::where('status', 1)
                ->orderBy('position')
                ->get(['type', 'url'])
                ->map(fn($link) => [
                    'type' => $link->type,
                    'url'  => $link->url,
                ])
                ->toArray();

            // Contacts
            $contacts = Contact::where('status', 1)
                ->orderBy('position')
                ->get(['type', 'url', 'label'])
                ->map(fn($contact) => [
                    'type'  => $contact->type,
                    'url'   => $contact->url,
                    'label' => $contact->getTranslation('label', $lang),
                ])
                ->toArray();
            // Download
            $downloadModel = Download::with('links')->first();
            $download = $downloadModel ? [
                'subtitle'    => $downloadModel->subtitle[$lang] ?? '',
                'title'       => $downloadModel->title[$lang] ?? '',
                'description' => $downloadModel->description[$lang] ?? '',
                'image'       => $downloadModel->image ?? '',
                'links'       => $downloadModel->links->map(fn($link) => [
                    'type' => $link->type,
                    'url'  => $link->url,
                ])->values()->toArray(),
            ] : null;
            $settings = Setting::all()->mapWithKeys(function ($setting) use ($lang) {
                $val = $setting->val;
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    if ($decoded !== null) {
                        $val = $decoded;
                    }
                }
                if (is_array($val) && $setting->key_name !== 'languages') {
                    $val = $val[$lang] ?? reset($val);
                }
                return [$setting->key_name => $val];
            });


            return [
                'socialLinks' => $socialLinks,
                'contacts'    => $contacts,
                'download'    => $download,
                'settings'    => $settings,
            ];
        });

        return response()->json($data);
    }
}
