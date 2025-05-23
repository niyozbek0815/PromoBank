<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\GameCard;
use App\Models\GameSession;
use App\Models\GameSessionCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $games =  new GameResource(Game::select('id', 'name', 'title', 'about')->first());
        return $this->successResponse(
            $games,
            "GameController index method",
            200
        );
    }
    public function start(Request $request)
    {
        // return $request->all();
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];

            // Check if game exists
            $game = Game::first();

            // Prevent duplicate sessions
            // $existing = GameSession::where('game_id', $game['id'])
            //     ->where('user_id', $user['id'])
            //     ->where('status', 'in_progress')
            //     ->first();

            // if ($existing) {
            //     return $existing;
            // }

            // Create new game session
            $session = GameSession::create([
                'game_id' => $game['id'],
                'user_id' => $user['id'],
                'status' => 'in_progress',
                'total_score' => 0,
                'stage1_score' => 0,
                'stage2_score' => 0,
                'stage1_success_steps' => 0,
                'stage2_attempted' => false,
            ]);

            // Get all stage1 cards with frequency
            $cards = GameCard::where('game_id', $game['id'])
                ->where('stage', 'stage1')
                ->get();

            // Expand cards by frequency
            foreach ($cards as $card) {
                for ($i = 0; $i < $card->frequency; $i++) {
                    $expanded[] = $card;
                }
            }

            // Shuffle and pick needed number
            shuffle($expanded);
            $selectedCards = array_slice($expanded, 0, $game->stage1_card_count);
            return $selectedCards;
            // Insert game_session_cards
            foreach ($selectedCards as $card) {
                GameSessionCard::create([
                    'session_id' => $session->id,
                    'card_id' => $card->id,
                    'is_revealed' => false,
                    'is_success' => false,
                    'selected_by_user' => false,
                    'step_number' => null, // step_number will be set during stage1 play
                ]);
            }

            return $session;
        });
    }
}
