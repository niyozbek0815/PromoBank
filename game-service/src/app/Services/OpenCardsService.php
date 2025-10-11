<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameSessionCard;

class OpenCardsService
{
    public function handleStage1($session, $req, $gameStep, $stepConfig)
    {
        if (!$stepConfig || $stepConfig->card_count !== count($req['selected_cards_id'])) {
            return [
                'message' => "Iltimos {$stepConfig->card_count} ta kartani tanlang.",
            ];
        }

        $session_update_data = ['stage1_success_steps' => $gameStep];
        $cards = $session->sessionCards;
        $won_promoball = 0;
        $data = $this->updateSelectedCards($cards, $req['selected_point'], $gameStep);
        $fallbackCard = $data['fallbackCard'];

        if (!$data['hasWinningCard']) {
            $fallbackCard = $this->revealFallbackCard($session->id, $req['selected_point'], $gameStep);
        } else {
            $session_update_data['stage1_score'] = $session->stage1_score + (($fallbackCard && $fallbackCard->card) ? ($fallbackCard->card->point ?? 0) : 0);
            $won_promoball = $fallbackCard->card->point;
        }

        $session->update($session_update_data);

        return response()->json([
            'new_game' => false,
            'won' => $data['hasWinningCard'],
            'won_promoball' => $won_promoball,
            'fallback_card' => [
                'id' => $fallbackCard->id,
                'point' => $fallbackCard->card->point,
            ],
            'cards' => $data['minimalCardData']
        ]);
    }
    public function handleStage2($session, $req, $gameStep)
    {
        $cards = $session->sessionCards;
        $selectedCount = GameSessionCard::where('session_id', $session->id)
            ->where('selected_by_user', true)
            ->where('is_revealed', true)
            ->where('is_success', true)
            ->count();

        if ($selectedCount !== count($req['selected_cards_id'])) {
            return [
                'message' => "Iltimos {$selectedCount} ta kartani tanlang.",
            ];
        }

        $won_promoball = $cards->sum(fn($card) => $card->card->point ?? 0);
        $minimalCardData = $cards->map(fn($card) => [
            'id' => $card->id,
            'point' => $card->card->point
        ])->toArray();

        $session->update([
            'status' => 'finished',
            'stage2_attempted' => true,
            'total_score' => $won_promoball,
            'stage2_score' => $won_promoball,
            'stage2_confirmed' => true,
        ]);

        foreach ($cards as $card) {
            $card->fill([
                'is_success' => true,
                'selected_by_user' => true,
                'is_revealed' => true,
                'step_number' => $gameStep,
            ])->save();
        }

        return response()->json([
            'new_game' => true,
            'won' => true,
            'won_promoball' => $won_promoball,
            'fallback_card' => [],
            'cards' => $minimalCardData
        ]);
    }

    public function getActiveSession(int $sessionId, int $userId, array $selectedCardIds)
    {
        return GameSession::where('user_id', $userId)
            ->where('id', $sessionId)
            ->where('status', '!=', 'finished')
            ->with([
                'sessionCards' => fn($q) =>
                $q->whereIn('id', $selectedCardIds)
                    ->where('is_revealed', false)
                    ->with('card:id,point')
            ])
            ->whereHas(
                'sessionCards',
                fn($q) =>
                $q->whereIn('id', $selectedCardIds)
                    ->where('is_revealed', false)
            )
            ->first();
    }

    public function getGameWithRelations()
    {
        return Game::with([
            'cards:id,game_id,stage,point,frequency',
            'stage1Steps:game_id,step_number,card_count'
        ])->select('id', 'stage1_card_count', 'stage2_card_count')->firstOrFail();
    }
    public function updateSelectedCards($cards, int $selectedPoint, int $gameStep)
    {
        $hasWinningCard = false;
        $fallbackCard = null;
        // Shu yerda bug bor doim true bo'lib qolganm
        foreach ($cards as $card) {
            $isWin = false;
            if ($card->card->point === $selectedPoint && !$hasWinningCard) {
                $hasWinningCard = true;
                $isWin = true;
                $fallbackCard = $card;
            }
            $card->fill([
                'is_success' => $isWin,
                'selected_by_user' => false,
                'is_revealed' => true,
                'step_number' => $gameStep,
            ])->save();
        }

        $minimalCardData = $cards->map(fn($card) => [
            'id' => $card->id,
            'point' => $card->card->point,
        ])->toArray();

        return [
            'hasWinningCard' => $hasWinningCard,
            'minimalCardData' => $minimalCardData,
            'fallbackCard' => $fallbackCard,
        ];
    }

    public function revealFallbackCard(int $sessionId, int $point, int $step)
    {
        $fallbackCard = GameSessionCard::where('session_id', $sessionId)
            ->whereHas('card', fn($q) => $q->where('point', $point))
            ->where('is_revealed', false)
            ->where('selected_by_user', false)
            ->orderBy('id')
            ->first();

        if ($fallbackCard) {
            $fallbackCard->update([
                'is_success' => false,
                'selected_by_user' => true,
                'is_revealed' => true,
                'step_number' => $step,
            ]);
        }

        return $fallbackCard;
    }
}
