<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenCardRequest;
use App\Http\Requests\OpenCardTwoRequest;
use App\Http\Resources\GameResource;
use App\Jobs\GameAddPromoballJob;
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
        $lang = $user['lang'] ?? 'uz';

        $validated = $request->validate([
            'session_id' => 'required|integer|exists:game_sessions,id',
            'lang' => 'required|in:uz,ru,kr,en',
        ]);

        $session = GameSession::where('id', $validated['session_id'])
            ->where('user_id', $user['id'])
            ->where('status', '!=', 'finished')
            ->first();

        // 🔹 Xabarlar (til bo‘yicha)
        $messages = [
            'no_session' => [
                'uz' => "Faol o'yin sessiyasi topilmadi yoki allaqachon yakunlangan.",
                'ru' => "Активная игровая сессия не найдена или уже завершена.",
                'kr' => "Фаол ўйин сессияси топилмади ёки аллақачон якунланган.",
                'en' => "Active game session not found or already finished.",
            ],
            'not_enough_steps' => [
                'uz' => "Stage 2 ni rad etish uchun 1-bosqichda kamida 5 ta to‘g‘ri qadam bo‘lishi kerak.",
                'ru' => "Для отказа от 2-го этапа нужно пройти как минимум 5 шагов первого этапа.",
                'kr' => "2-босқични рад этиш учун 1-босқичда камида 5 та тўғри қадам бўлиши керак.",
                'en' => "To reject Stage 2, you must complete at least 5 correct steps in Stage 1.",
            ],
            'success' => [
                'uz' => "Stage 2 muvaffaqiyatli rad etildi.",
                'ru' => "Этап 2 успешно отклонён.",
                'kr' => "2-босқич муваффақиятли рад этилди.",
                'en' => "Stage 2 rejected successfully.",
            ],
        ];

        // 🔹 Sessiya topilmasa
        if (!$session) {
            return $this->errorResponse(
                $messages['no_session'][$lang] ?? $messages['no_session']['uz'],
                404
            );
        }

        // 🔹 1-bosqich hali yetarli darajada tugamagan bo‘lsa
        if ($session->stage1_success_steps < 5) {
            return $this->errorResponse(
                $messages['not_enough_steps'][$lang] ?? $messages['not_enough_steps']['uz'],
                400
            );
        }

        // 🔹 Sessiyani yakunlash
        $session->fill([
            'stage2_attempted' => false,
            'stage2_confirmed' => false,
            'status' => 'finished',
        ])->save();
            $promoball = $session->stage1_score;
            GameAddPromoballJob::dispatch($promoball, $session['id'], $user['id'])
                ->onQueue('promo_queue');


            return $this->successResponse([], $messages['success'][$lang] ?? $messages['success']['uz'], 200);
    });
}
    public function startNext(Request $request)
    {
        $request->validate([
            'lang' => ['required', 'in:uz,ru,kr,en'],
        ]);
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $now = now();

            $game = $this->gameStartService->getGameWithRelations();
            $session = $this->gameStartService->getActiveSession($game->id, $user['id']);

            if ($session) {
                return $this->successResponse($this->gameStartService->handleStage1($game, $session), "Stage 1 completed successfully", 200);
            }
            return $this->successResponse($this->gameStartService->startNewSession($game, $user['id'], $now), "Stage 1 completed successfully", 200);
        });
    }
    public function startTwo(Request $request)
    {
        $request->validate([
            'lang' => ['required', 'in:uz,ru,kr,en'],
        ]);
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $now = now();

            $game = $this->gameStartService->getGameWithRelations();
            $session = $this->gameStartService->getActiveSession($game->id, $user['id']);

            if (!$session) {
                return $this->errorResponse(
                    $this->gameStartService->getNoSessionMessage($user['lang'] ?? 'uz'),
                    ['error' => $this->gameStartService->getNoSessionMessage($user['lang'] ?? 'uz')],
                    404
                );
            }
            if ($this->gameStartService->isStage2Eligible($session)) {
                return $this->successResponse($this->gameStartService->handleStage2($game, $session, $now), "Stage 2 started successfully", 200);
            }

            return $this->errorResponse(
                $this->gameStartService->getNoTwoStepMessage($request->lang),
                ['error' => $this->gameStartService->getNoTwoStepMessage($request->lang)],
                422
            );
        });
    }


    public function openCards(OpenCardRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $req = $request->validated();
            $data = [];
            $session = $this->openCardsService->getActiveSession(
                $req['session_id'],
                $user['id'],
                $req['selected_cards_id']
            );
            if (!$session) {
                return $this->errorResponse(
                    $this->gameStartService->getNoSessionMessage($user['lang'] ?? 'uz'),
                    ['error' => $this->gameStartService->getNoSessionMessage($user['lang'] ?? 'uz')],
                    404
                );
            }

            $gameStep = $session->stage1_success_steps + 1;
            $maxStep = GameStage1Step::where('game_id', $session->game_id)->max('step_number');

            $stepConfig = GameStage1Step::where('game_id', $session->game_id)
                ->where('step_number', $gameStep)
                ->select('step_number', 'card_count')
                ->first();

            if ($gameStep <= $maxStep) {
                $data = $this->openCardsService->handleStage1($session, $req, $gameStep, $stepConfig);
                Log::info('Open Cards Result', ['data' => $data]);
                if (!empty($data['message'] ?? null)) {
                    return $this->errorResponse(
                        $data['message'],
                        ['error' => [$data['message']]],
                        422
                    );
                }
                return $this->successResponse($data, "Cards opened successfully", 200);
            }
            return $this->errorResponse(
                $this->openCardsService->getAllStepsCompletedMessage($request->lang),
                ['error' => $this->openCardsService->getAllStepsCompletedMessage($request->lang)],
                422
            );


        });
    }

    public function openCardsFinal(OpenCardTwoRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request['auth_user'];
            $req = $request->validated();
            $lang = $user['lang'] ?? 'uz';

            // 🔹 Aktiv sessiyani tekshirish
            $session = $this->openCardsService->getActiveSession(
                $req['session_id'],
                $user['id'],
                $req['selected_cards_id']
            );

            if (!$session) {
                $message = $this->gameStartService->getNoSessionMessage($lang);
                return $this->errorResponse($message, ['error' => [$message]], 404);
            }

            // 🔹 1-bosqichdagi mavjud bosqichlar sonini aniqlash
            $maxStep = GameStage1Step::query()
                ->where('game_id', $session->game_id)
                ->max('step_number');

            // 🔹 Agar 1-bosqich hali tugamagan bo‘lsa, foydalanuvchini to‘xtatish
            if ($session->stage1_success_steps < $maxStep) {
                $message = $this->openCardsService->getStage2BeforeStage1CompletedMessage($lang);
                return $this->errorResponse($message, ['error' => [$message]], 422);
            }

            // 🔹 Endi 2-bosqichni ishlov berish
            $gameStep = $session->stage1_success_steps + 1;
            $data = $this->openCardsService->handleStage2($session, $req, $gameStep, $user['id']);
Log::info('Open Cards Final Result', ['data' => $data]);
            // 🔹 Xabar bilan qaytish (agar mavjud bo‘lsa)
            if (!empty($data['message'])) {
                return $this->errorResponse($data['message'], ['error' => [$data['message']]], 422);
            }

            return $this->successResponse($data, "Cards opened successfully", 200);
        });
    }
}
