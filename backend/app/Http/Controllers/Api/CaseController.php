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
     * Get all active cases
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
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $query = CaseModel::active()
            ->with(['items' => function ($query) {
                $query->select('items.id', 'name', 'image', 'price', 'rarity')
                      ->orderBy('price', 'desc')
                      ->limit(6);
            }]);

        // Apply filters
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

        // Apply sorting
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
     * Get case details
     */
    public function show(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Case not found or inactive'
            ], 404);
        }

        $case->load(['items' => function ($query) {
            $query->orderBy('price', 'desc');
        }]);

        // Add statistics
        $case->statistics = $case->getStatistics();
        $case->recent_openings = $case->getRecentOpenings(10);

        // Check if user can open this case
        if (Auth::check()) {
            $case->can_open = $case->canBeOpenedBy(Auth::user());
        }

        return response()->json([
            'success' => true,
            'data' => $case
        ]);
    }

    /**
     * Get case items
     */
    public function getItems(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Case not found or inactive'
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
     * Check if user can open case
     */
    public function canOpen(CaseModel $case): JsonResponse
    {
        $user = Auth::user();
        
        if (!$case->is_active) {
            return response()->json([
                'can_open' => false,
                'reason' => 'Case is not active'
            ]);
        }

        $canOpen = $case->canBeOpenedBy($user);
        $reason = null;

        if (!$canOpen) {
            if ($user->level < $case->min_level) {
                $reason = "Minimum level required: {$case->min_level}";
            } elseif (!$user->canAfford($case->price)) {
                $reason = "Insufficient balance";
            } elseif ($case->max_daily_openings > 0) {
                $todayOpenings = $case->openings()
                    ->where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->count();
                
                if ($todayOpenings >= $case->max_daily_openings) {
                    $reason = "Daily opening limit reached";
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
     * Open a single case
     */
    public function openCase(Request $request, CaseModel $case): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_seed' => 'required|string|min:1|max:128',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if case can be opened
        if (!$case->canBeOpenedBy($user)) {
            return response()->json([
                'error' => 'Cannot open this case'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Open the case
            $opening = $this->caseOpeningService->openCase(
                $user,
                $case,
                $request->client_seed
            );

            DB::commit();

            // Broadcast the opening
            BroadcastCaseOpening::dispatch($opening);
            
            // Fire event
            event(new CaseOpened($opening));

            return response()->json([
                'success' => true,
                'data' => $opening->load(['item', 'case'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to open case',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open multiple cases
     */
    public function multiOpen(Request $request, CaseModel $case): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_seed' => 'required|string|min:1|max:128',
            'count' => 'required|integer|min:2|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $count = $request->count;
        $totalCost = $case->price * $count;

        // Check if user can afford multiple openings
        if (!$user->canAfford($totalCost)) {
            return response()->json([
                'error' => 'Insufficient balance for multiple openings'
            ], 403);
        }

        // Check daily limits
        if ($case->max_daily_openings > 0) {
            $todayOpenings = $case->openings()
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();
            
            if ($todayOpenings + $count > $case->max_daily_openings) {
                return response()->json([
                    'error' => 'Would exceed daily opening limit'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $openings = [];
            $clientSeed = $request->client_seed;

            for ($i = 0; $i < $count; $i++) {
                // Use different nonce for each opening
                $opening = $this->caseOpeningService->openCase(
                    $user,
                    $case,
                    $clientSeed . ':' . $i
                );
                
                $openings[] = $opening;
            }

            DB::commit();

            // Broadcast each opening
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
                'error' => 'Failed to open cases',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate case opening (for preview)
     */
    public function simulate(CaseModel $case): JsonResponse
    {
        if (!$case->is_active) {
            return response()->json([
                'error' => 'Case not found or inactive'
            ], 404);
        }

        // Generate random seeds for simulation
        $serverSeed = bin2hex(random_bytes(32));
        $clientSeed = bin2hex(random_bytes(16));
        $nonce = random_int(1, 1000000);

        $item = $case->getRandomItem($serverSeed, $clientSeed, $nonce);

        if (!$item) {
            return response()->json([
                'error' => 'No items in case'
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
     * Get user's case openings
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
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $query = $user->caseOpenings()->with(['case', 'item']);

        // Apply filters
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
     * Get specific case opening
     */
    public function getOpening(CaseOpening $opening): JsonResponse
    {
        $user = Auth::user();

        if ($opening->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $opening->load(['case', 'item']);

        return response()->json([
            'success' => true,
            'data' => $opening
        ]);
    }

    /**
     * Verify case opening (Provably Fair)
     */
    public function verifyOpening(CaseOpening $opening): JsonResponse
    {
        $user = Auth::user();

        if ($opening->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $isValid = $opening->verifyHash();

        // Regenerate the outcome for verification
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