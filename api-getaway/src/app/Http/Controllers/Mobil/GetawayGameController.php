<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetawayGameController extends Controller
{
    protected  $gameServices;
    public function __construct()
    {
        $this->gameServices = config('games.services');
    }

    public  function handle(Request $request, $game, $action)
    {

        if (!isset($this->gameServices[$game])) {
            return response()->json(['error' => 'Game service not found'], 404);
        }
        $response = $this->forwardRequest("POST", $this->gameServices[$game], $action, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
}
