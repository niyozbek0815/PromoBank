<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class ReceiptScraperService
{
    public function scrapeReceipt($req): array
    {
        try {
            $url = $req['promocode'];
            $response = Http::timeout(15)->get($url);
            if (!$response->ok()) {
                return ['success' => false, 'message' => "HTTP error: {$response->status()}"];
            }

            $html = $response->body();
            $crawler = new Crawler($html);
            return [
                'success' => true,
                'url' => $url,
                'name' => $crawler->filter('h3')->eq(1)->text(''),
                'chek_id' => $crawler->filter('b')->first()->text(''),
                'nkm_number' => $crawler->filterXPath('//span[contains(.,"Onlayn NKM nomi")]/b')->text(''),
                'sn' => $crawler->filterXPath('//span[contains(.,"SN")]/b')->text(''),
                'check_date' => $this->parseDate($crawler->filter('tr td i')->eq(1)->text('')),
                'qqs_summa' => $this->normalizeFloat($crawler->filterXPath('//td[contains(@class,"nds-sum")]')->first()->text('')),
                'summa' => $this->normalizeSumma($crawler->filterXPath('//td[contains(.,"Jami to`lov:")]/following-sibling::td')->text('')),
                'address' => $this->extractAddress($crawler),
                'lat' => $this->extractLatLong($html, 1),
                'long' => $this->extractLatLong($html, 2),
                'products' => $this->extractProducts($crawler),
                'lang'=>$req['lang'] ?? 'uz'
            ];

        } catch (\Throwable $e) {
            Log::error("Receipt scrape error", ['url' => $url, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function parseDate(string $raw): ?string
    {
        try {
            return Carbon::createFromFormat('d.m.Y, H:i', trim($raw))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeFloat(string $raw): float
    {
        return (float) str_replace([' ', ','], ['', '.'], $raw);
    }

    private function normalizeSumma(string $raw): int
    {
        $clean = str_replace(' ', '', $raw);
        $clean = preg_replace('/[,.]\d{2}$/', '', $clean);
        return (int) preg_replace('/\D/', '', $clean);
    }

    private function extractAddress(Crawler $crawler): string
    {
        $raw = $crawler->filter('td[colspan="3"]')->eq(1)->html('');
        $cleaned = preg_replace('/<h3.*?<\/h3>/s', '', $raw);
        return preg_replace('/\s+/', ' ', trim(strip_tags($cleaned)));
    }

    private function extractLatLong(string $html, int $index): ?string
    {
        preg_match('/Placemark\($begin:math:display$([0-9\\.\\-]+),\\s*([0-9\\.\\-]+)$end:math:display$/', $html, $m);
        return $m[$index] ?? null;
    }

    private function extractProducts(Crawler $crawler): array
    {
        $products = [];

        $crawler->filter('table.products-tables tr.products-row')->each(function (Crawler $row) use (&$products) {
            $cols = $row->filter('td');

            $products[] = [
                'name' => trim($cols->eq(0)->text('')),
                'count' => (float) str_replace(',', '.', $cols->eq(1)->text('')),
                'summa' => $this->normalizeSumma($cols->eq(2)->text('')),
            ];
        });

        return $products;
    }
}
