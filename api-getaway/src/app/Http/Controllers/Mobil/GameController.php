<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameController extends Controller
{

    protected $gameServices;
    public function __construct()
    {
        $this->gameServices = config('games.services');
    }
    public  function listAllGames(Request $request)
    {

        $results = [];
        foreach ($this->gameServices as $slug => $url) {
            try {
                $response = $this->forwardRequest("POST", $url, '/info', $request);
                if ($response->ok()) {
                    $res = $response->json();
                    $results[] = $res['data'];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        return $this->successResponse($results, "success", 200);
    }
}
