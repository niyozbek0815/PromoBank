<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameSessionCard;
use App\Models\GameStage1Step;

class GameStartService
{
    public function getGameWithRelations()
    {
        return Game::with([
            'cards:id,game_id,stage,point,frequency',
            'stage1Steps:game_id,step_number,card_count'
        ])->select('id', 'stage1_card_count', 'stage2_card_count')->firstOrFail();
    }

    public function getActiveSession(int $gameId, int $userId)
    {
        return GameSession::where('game_id', $gameId)
            ->where('user_id', $userId)
            ->where('status', '!=', 'finished')
            ->with(['sessionCards.card:id,point'])
            ->first();
    }

    public function isStage1InProgress(GameSession $session): bool
    {
        return $session->status === 'in_progress' && $session->stage1_success_steps < 5;
    }

    public function isStage2Eligible(GameSession $session): bool
    {
        return $session->stage1_success_steps >= 5;
    }

    public function handleStage1(Game $game, GameSession $session)
    {
        $promoball = $session->stage1_score;
        $game_step = $session->stage1_success_steps + 1;


        $stepConfig = $game->stage1Steps
            ->firstWhere('step_number', $game_step)
            ?->only(['step_number', 'card_count']) ?? [];

        $stepConfig['all_step_count'] = $game->stage1Steps->count();
        $stepConfig['etap'] = 1;
        $sessionCardsAll = $session->sessionCards->where('etap', 1)->values();
        [$selectedCards, $unselectedCards] = [
            $sessionCardsAll->where('selected_by_user', true)->values(),
            $sessionCardsAll->where('selected_by_user', false)->values()
        ];
        return $this->buildResponse($session, $stepConfig, $promoball, $sessionCardsAll, $selectedCards, $unselectedCards);
    }

    public function handleStage2(Game $game, GameSession $session, $now)
    {
        $selectedCount = GameSessionCard::where('session_id', $session->id)
            ->where('selected_by_user', true)
            ->where('is_revealed', true)
            ->where('is_success', true)
            ->get();
        $stepConfig = ['step_number' => 1, 'all_step_count' => 1, 'card_count' => $selectedCount, 'etap' => 2];
        $promoball = 0;
        $session->update([
            'status' => 'in_progress',
            'stage2_attempted' => true,
            'stage2_score' => 0,
            'stage2_confirmed' => false,
        ]);
        $sessionCardsAll = $session->sessionCards->where('etap', 2)->values();
        if ($sessionCardsAll->isEmpty()) {
            $sessionCardsAll = $this->createStage2Cards($game, $session, $now);
        }
        $minimalUnselected = $sessionCardsAll->map(fn($card) => ['id' => $card->id])->values();
        $summary = [
            'all_card_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
            'selected_summary' => [], // Stage 2 uchun tanlangan kartalar yo'q
            'remaining_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
        ];
        return [
            'promoball' => $promoball,
            'session_id' => $session->id,
            'stage2_request_shown' => true,
            'step_config' => $stepConfig,
            'summary' => $summary,
            'card_data' => [
                'selected_cards' => [],
                'unselected_cards' => $minimalUnselected,
            ],
        ];
    }

    protected function createStage2Cards(Game $game, GameSession $session, $now)
    {
        $cardsShablon = $game->cards->where('stage', 'stage2')->values();

        $expanded = $cardsShablon->flatMap(fn($card) => array_fill(0, $card->frequency, $card))->all();
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
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        GameSessionCard::insert($insertData);

        return GameSessionCard::where('session_id', $session->id)
            ->where('etap', 2)
            ->with('card:id,point')
            ->orderBy('id')
            ->get();
    }

    public function startNewSession(Game $game, int $userId, $now)
    {
        $session = GameSession::create([
            'game_id' => $game['id'],
            'user_id' => $userId,
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
        $expanded = [];

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

        $sessionCardsAll = GameSessionCard::where('session_id', $session->id)
            ->orderBy('id')
            ->where('etap', 1)
            ->get();
        // dd($sessionCardsAll);
        $unselectedCards = $sessionCardsAll->where('selected_by_user', false)->values();

        $minimalUnselected = $unselectedCards->pluck('id')->map(fn($id) => ['id' => $id])->values();

        $summary = [
            'all_card_summary' => $sessionCardsAll->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
            'selected_summary' => [],
            'remaining_summary' => $unselectedCards->groupBy(fn($c) => $c->card->point)->map(fn($g) => $g->count()),
        ];

        return [
            'session_id' => $session->id,
            'promoball' => 0,
            'stage2_request_shown' => false,
            'step_config' => $stepConfig,
            'summary' => $summary,
            'card_data' => [
                'selected_cards' => [],
                'unselected_cards' => $minimalUnselected,
            ],
        ];
    }

    protected function buildResponse($session, $stepConfig, $promoball, $sessionCardsAll, $selectedCards, $unselectedCards)
    {
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

        return [
            'session_id' => $session->id,
            'promoball' => $promoball,
            'stage2_request_shown' => false,
            'step_config' => $stepConfig,
            'summary' => $summary,
            'card_data' => [
                'selected_cards' => $minimalSelected,
                'unselected_cards' => $minimalUnselected,
            ],
        ];
    }
}
