<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\CaseOpening;
use App\Models\UserInventory;
use App\Services\ProvablyFairService;
use App\Services\CaseOpeningService;
use App\Jobs\BroadcastCaseOpening;
use App\Events\CaseOpened;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CaseController extends Controller
{
    protected ProvablyFairService $provablyFairService;
    protected CaseOpeningService $caseOpeningService;

    public function __construct(
        ProvablyFairService $provablyFairService,
        CaseOpeningService $caseOpeningService
    ) {
        $this->provablyFairService = $provablyFairService;
        $this->caseOpeningService = $caseOpeningService;
    }

    /**
     * Получить все активные кейсы
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'string|in:general,premium,special,weapons,knives',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'featured' => 'boolean',
            'sort' => 'string|in:price_asc,price_desc,name,popular,newest',
            'limit' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $query = CaseModel::active()
            ->with(['items' => function ($query) {
                $query->select('items.id', 'name', 'image', 'price', 'rarity')
                      ->orderBy('price', 'desc')
                      ->limit(6);
            }]);

        // Применяем фильтры
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        // Применяем сортировку
        switch ($request->get('sort', 'sort_order')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
                $query->orderBy('total_openings', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }

        $limit = $request->get('limit', 20);
        $cases = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $cases->items(),
            'pagination' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ]
        ]);
    }

    /**
     * Получить детали кейса
     */
    public function show(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Кейс не найден или неактивен'
            ], 404);
        }

        $case->load(['items' => function ($query) {
            $query->orderBy('price', 'desc');
        }]);

        // Добавляем статистику
        $case->statistics = $case->getStatistics();
        $case->recent_openings = $case->getRecentOpenings(10);

        // Проверяем, может ли пользователь открыть этот кейс
        if (Auth::check()) {
            $case->can_open = $case->canBeOpenedBy(Auth::user());
        }

        return response()->json([
            'success' => true,
            'data' => $case
        ]);
    }

    /**
     * Получить предметы кейса
     */
    public function getItems(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Кейс не найден или неактивен'
            ], 404);
        }

        $items = $case->items()
            ->orderBy('price', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Проверить, может ли пользователь открыть кейс
     */
    public function canOpen(CaseModel $case): JsonResponse
    {
        $user = Auth::user();
        
        if (!$case->is_active) {
            return response()->json([
                'can_open' => false,
                'reason' => 'Кейс неактивен'
            ]);
        }

        $canOpen = $case->canBeOpenedBy($user);
        $reason = null;

        if (!$canOpen) {
            if ($user->level < $case->min_level) {
                $reason = "Требуемый минимальный уровень: {$case->min_level}";
            } elseif (!$user->canAfford($case->price)) {
                $reason = "Недостаточно средств";
            } elseif ($case->max_daily_openings > 0) {
                $todayOpenings = $case->openings()
                    ->where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->count();
                
                if ($todayOpenings >= $case->max_daily_openings) {
                    $reason = "Достигнут дневной лимит открытий";
                }
            }
        }

        return response()->json([
            'can_open' => $canOpen,
            'reason' => $reason,
            'user_balance' => $user->balance,
            'case_price' => $case->price,
            'user_level' => $user->level,
            'required_level' => $case->min_level,
        ]);
    }

    /**
     * Открыть один кейс
     */
    public function openCase(Request $request, CaseModel $case): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_seed' => 'required|string|min:1|max:128',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Проверяем, можно ли открыть кейс
        if (!$case->canBeOpenedBy($user)) {
            return response()->json([
                'error' => 'Невозможно открыть этот кейс'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Открываем кейс
            $opening = $this->caseOpeningService->openCase(
                $user,
                $case,
                $request->client_seed
            );

            DB::commit();

            // Транслируем открытие
            BroadcastCaseOpening::dispatch($opening);
            
            // Запускаем событие
            event(new CaseOpened($opening));

            return response()->json([
                'success' => true,
                'data' => $opening->load(['item', 'case'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Не удалось открыть кейс',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Открыть несколько кейсов
     */
    public function multiOpen(Request $request, CaseModel $case): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_seed' => 'required|string|min:1|max:128',
            'count' => 'required|integer|min:2|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $count = $request->count;
        $totalCost = $case->price * $count;

        // Проверяем, может ли пользователь позволить себе множественные открытия
        if (!$user->canAfford($totalCost)) {
            return response()->json([
                'error' => 'Недостаточно средств для множественного открытия'
            ], 403);
        }

        // Проверяем дневные лимиты
        if ($case->max_daily_openings > 0) {
            $todayOpenings = $case->openings()
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();
            
            if ($todayOpenings + $count > $case->max_daily_openings) {
                return response()->json([
                    'error' => 'Превышен дневной лимит открытий'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $openings = [];
            $clientSeed = $request->client_seed;

            for ($i = 0; $i < $count; $i++) {
                // Используем разные nonce для каждого открытия
                $opening = $this->caseOpeningService->openCase(
                    $user,
                    $case,
                    $clientSeed . ':' . $i
                );
                
                $openings[] = $opening;
            }

            DB::commit();

            // Транслируем каждое открытие
            foreach ($openings as $opening) {
                BroadcastCaseOpening::dispatch($opening);
                event(new CaseOpened($opening));
            }

            return response()->json([
                'success' => true,
                'data' => collect($openings)->load(['item', 'case']),
                'summary' => [
                    'total_spent' => $totalCost,
                    'total_won' => collect($openings)->sum('item_value'),
                    'total_profit' => collect($openings)->sum('profit'),
                    'best_item' => collect($openings)->sortByDesc('item_value')->first(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Не удалось открыть кейсы',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Симулировать открытие кейса (для предпросмотра)
     */
    public function simulate(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Кейс не найден или неактивен'
            ], 404);
        }

        // Генерируем случайные seeds для симуляции
        $serverSeed = bin2hex(random_bytes(32));
        $clientSeed = bin2hex(random_bytes(16));
        $nonce = random_int(1, 1000000);

        $item = $case->getRandomItem($serverSeed, $clientSeed, $nonce);

        if (!$item) {
            return response()->json([
                'error' => 'В кейсе нет предметов'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $item,
                'case' => $case,
                'profit' => $item->price - $case->price,
                'is_profitable' => $item->price > $case->price,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce,
            ]
        ]);
    }

    /**
     * Получить открытия кейсов пользователя
     */
    public function getOpenings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'integer|exists:cases,id',
            'date_from' => 'date',
            'date_to' => 'date',
            'limit' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $query = $user->caseOpenings()->with(['case', 'item']);

        // Применяем фильтры
        if ($request->has('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $limit = $request->get('limit', 20);
        $openings = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $openings->items(),
            'pagination' => [
                'current_page' => $openings->currentPage(),
                'last_page' => $openings->lastPage(),
                'per_page' => $openings->perPage(),
                'total' => $openings->total(),
            ]
        ]);
    }

    /**
     * Получить конкретное открытие кейса
     */
    public function getOpening(CaseOpening $opening): JsonResponse
    {
        $user = Auth::user();

        if ($opening->user_id !== $user->id) {
            return response()->json([
                'error' => 'Нет доступа'
            ], 403);
        }

        $opening->load(['case', 'item']);

        return response()->json([
            'success' => true,
            'data' => $opening
        ]);
    }

    /**
     * Проверить открытие кейса (Provably Fair)
     */
    public function verifyOpening(CaseOpening $opening): JsonResponse
    {
        $user = Auth::user();

        if ($opening->user_id !== $user->id) {
            return response()->json([
                'error' => 'Нет доступа'
            ], 403);
        }

        $isValid = $opening->verifyHash();

        // Пересоздаем результат для проверки
        $case = $opening->case;
        $verificationItem = $case->getRandomItem(
            $opening->server_seed,
            $opening->client_seed,
            $opening->nonce
        );

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'server_seed' => $opening->server_seed,
                'client_seed' => $opening->client_seed,
                'nonce' => $opening->nonce,
                'hash' => $opening->hash,
                'expected_hash' => hash('sha256', $opening->server_seed . ':' . $opening->client_seed . ':' . $opening->nonce),
                'original_item' => $opening->item,
                'verification_item' => $verificationItem,
                'matches' => $verificationItem && $verificationItem->id === $opening->item_id,
            ]
        ]);
    }
}