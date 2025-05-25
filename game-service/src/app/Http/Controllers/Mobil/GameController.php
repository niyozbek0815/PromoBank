<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\GameCard;
use App\Models\GameSession;
use App\Models\GameSessionCard;
use App\Models\GameStage1Result;
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
            $stepConfig = [
                "step_number" => 0,
                "card_count" => 0,
                "all_step_count" => 0,
                "etap" => 1,
                "stage2_card_count" => 0
            ];
            $expanded = [];
            $now = now();
            $promoball = 0;
            $game = Game::with([
                'cards:id,game_id,stage,point,frequency',
                'stage1Steps:game_id,step_number,card_count'
            ])->select('id', 'stage1_card_count')->firstOrFail();
            $session = GameSession::where('game_id', $game->id)
                ->where('user_id', $user['id'])
                ->where('status', '!=', 'finished')
                ->with(['sessionCards.card:id,point']) // eager load nested card
                ->first();
            if ($session) {
                if ($session['status'] === 'in_progress' && $session->stage1_success_steps < 5) {
                    $promoball = $session->stage1_score;
                    $game_step = max(1, $session->stage1_success_steps);


                    $stepConfig = $game->stage1Steps
                        ->firstWhere('step_number', $game_step)
                        ?->only(['step_number', 'card_count']) ?? [];
                    $stepConfig['all_step_count'] = $game->stage1Steps->count();
                    $stepConfig['etap'] = 1;
                    if ($session->stage1_success_steps >= 5) {
                        if ($session->stage2_confirmed === true) {
                            $stepConfig['etap'] = 2;
                        } else {
                            $stepConfig['etap'] = 1;
                            $session->stage2_confirmed = null;
                            $session->save();
                        }
                    }
                    $sessionCardsAll = $session->sessionCards->where('etap', 1)->values();
                    [$selectedCards, $unselectedCards] = [
                        $sessionCardsAll->where('selected_by_user', true)->values(),
                        $sessionCardsAll->where('selected_by_user', false)->values()
                    ];
                    $minimalUnselected = $unselectedCards->pluck('id')->map(fn($id) => ['id' => $id])->values();
                    $minimalSelected = $selectedCards->map(fn($card) => [
                        'id' => $card->id,
                        'point' => $card->card->point,
                    ])->values();
                    $summary = [
                        'all_card_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                        'selected_summary' => $selectedCards->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                        'remaining_summary' => $unselectedCards->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                    ];

                    // return [
                    //     'step_config' => $stepConfig,
                    //     'promoball' => $promoball,
                    //     'summary' => $summary,
                    //     'card_data' => [
                    //         'selected_cards' =>  $minimalSelected,
                    //         'unselected_cards' => $minimalUnselected,
                    //     ],
                    // ];
                }
                if ($session->stage1_success_steps >= 5) {

                    $stepConfig['etap'] = 2;
                    $stepConfig['stage2_card_count'] = $game->stage2_card_count ?? 10;
                    $promoball = 0;
                    $sessionCardsAll = $session->sessionCards->where('etap', 2)->values();

                    if ($sessionCardsAll->isEmpty()) {
                        $cardsShablon = $game->cards->where('stage', 'stage2')->values();

                        $expanded = $cardsShablon->flatMap(
                            fn($card) =>
                            array_fill(0, $card->frequency, $card)
                        )->all();
                        shuffle($expanded);
                        $selectedCards = array_slice($expanded, 0, $game->stage2_card_count);
                        $insertData = collect($selectedCards)->map(fn($card) => [
                            'session_id' => $session->id,
                            'card_id' => $card->id,
                            'is_revealed' => false,
                            'is_success' => false,
                            'selected_by_user' => false,
                            'step_number' => null,
                            'etap' => 2,
                            'created_at' =>  $now,
                            'updated_at' =>  $now,
                        ])->toArray();
                        GameSessionCard::insert($insertData);
                        $sessionCardsAll = GameSessionCard::where('session_id', $session->id)
                            ->where('etap', 2)
                            ->with('card:id,point')
                            ->orderBy('id')
                            ->get();
                    }
                    $minimalUnselected = $sessionCardsAll->map(fn($card) => ['id' => $card->id])->values();
                    $minimalSelected = [];
                    $summary = [
                        'all_card_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                        'selected_summary' => [],
                        'remaining_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                    ];
                }
            } else {
                $promoball = 0;
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
                $game_step = max(1, $session->stage1_success_steps);
                $stepConfig = $game->stage1Steps
                    ->firstWhere('step_number', $game_step)
                    ?->only(['step_number', 'card_count']) ?? [];
                $stepConfig['all_step_count'] = $game->stage1Steps->count();
                $stepConfig['etap'] = 1;
                $cardsShablon = $game->cards->where('stage', 'stage1')->values();
                foreach ($cardsShablon as $card) {
                    for ($i = 0; $i < $card->frequency; $i++) {
                        $expanded[] = $card;
                    }
                }
                if (count($expanded) > $game->stage1_card_count) {
                    shuffle($expanded);
                }
                $selectedCards = array_slice($expanded, 0, $game->stage1_card_count);

                $insertData = [];
                foreach ($selectedCards as $card) {
                    $insertData[] = [
                        'session_id' => $session->id,
                        'card_id' => $card->id,
                        'is_revealed' => false,
                        'is_success' => false,
                        'selected_by_user' => false,
                        'step_number' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                GameSessionCard::insert($insertData);

                // endi faqat id + card_id larni olish
                $sessionCardsAll = GameSessionCard::where('session_id', $session->id)
                    // ->select('id', 'card_id')
                    ->orderBy('id')
                    ->where('etap', 1)
                    ->get();
                [$selectedCards, $unselectedCards] = [
                    [],
                    $sessionCardsAll->where('selected_by_user', false)->values()
                ];
                $minimalUnselected = $unselectedCards->pluck('id')->map(fn($id) => ['id' => $id])->values();
                $minimalSelected = [];
                $summary = [
                    'all_card_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                    'selected_summary' => [],
                    'remaining_summary' => $unselectedCards->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
                ];
            }
            return [
                'step_config' => $stepConfig,
                'promoball' => $promoball,
                'summary' => $summary,
                'card_data' => [
                    'selected_cards' => $minimalSelected,
                    'unselected_cards' => $minimalUnselected,
                ],
            ];
        });
    }
}