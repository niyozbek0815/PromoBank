<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenCardRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameStage1Step;
use App\Services\GameStartService;
use App\Services\OpenCardsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function __construct(protected GameStartService $gameStartService, protected OpenCardsService $openCardsService)
    {
        $this->gameStartService = $gameStartService;
        $this->openCardsService = $openCardsService;
    }

    public function getTypes(Request $request)
    {
        $games = Game::select('id', 'name')->get();

        $data = $games->map(function ($game) {
            return [
                'value' => $game->id,
                'label' => $game->getTranslation('name', 'uz'),
            ];
        })->toArray();

        Log::info('Game Types', ['data' => $data]);
        return response()->json($data);
    }

    public function index(Request $request)
    {
        $games = new GameResource(Game::select('id', 'name', 'title', 'about')->first());
        return $this->successResponse(
            $games,
            "GameController index method",
            200
        );
    }
    public function rejectStage2(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $req  = $request->validate([
                'session_id' => 'required|integer|exists:game_sessions,id',
            ]);
            $session = GameSession::where('game_id', 1)
                ->where('user_id', $user['id'])
                ->where('status', '!=', 'finished')
                ->where('stage1_success_steps', '>=', 5)
                ->first();
            $session->fill([
                'stage2_attempted' => false,
                'stage2_confirmed' => false,
                'status'           => 'finished',
            ])->save();
            return $this->successResponse([], "Stage 2 rejected successfully", 200);
        });
    }

    public function startNext(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $now  = now();

            $game    = $this->gameStartService->getGameWithRelations();
            $session = $this->gameStartService->getActiveSession($game->id, $user['id']);
            // return [
            //     'game' => $game,
            //     'session' => $session,
            // ];
            if ($session) {
                if ($this->gameStartService->isStage1InProgress($session)) {
                    return $this->gameStartService->handleStage1($game, $session);
                } elseif ($this->gameStartService->isStage2Eligible($session)) {
                    return $this->gameStartService->handleStage2($game, $session, $now);
                }
            }

            // Agar sessiya yo'q bo'lsa - yangi sessiya yaratish va boshlash
            return $this->gameStartService->startNewSession($game, $user['id'], $now);
        });
    }
    public function openCards(OpenCardRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $req  = $request->validated();

            $session = $this->openCardsService->getActiveSession(
                $req['session_id'],
                $user['id'],
                $req['selected_cards_id']
            );

            $gameStep = $session->stage1_success_steps + 1;
            $maxStep  = GameStage1Step::where('game_id', $session->game_id)->max('step_number');

            $stepConfig = GameStage1Step::where('game_id', $session->game_id)
                ->where('step_number', $gameStep)
                ->select('step_number', 'card_count')
                ->first();

            if ($gameStep <= $maxStep) {
                $data = $this->openCardsService->handleStage1($session, $req, $gameStep, $stepConfig);
            } else {
                $data = $this->openCardsService->handleStage2($session, $req, $gameStep);
            }
            if ($data['message']) {
                return $this->errorResponse(
                    $data['message'],
                    ['error' => [$data['message']]],
                    422
                );
            }
            return $this->successResponse($data, "Cards opened successfully", 200);
        });
    }
}
