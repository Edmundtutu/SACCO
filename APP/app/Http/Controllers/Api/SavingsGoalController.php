<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SavingsGoalController extends Controller
{
    public function __construct(protected SavingsGoalService $service)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', SavingsGoal::class);

        $user = $request->user();
        $perPage = min($request->integer('per_page', 15), 100);

        if ($user->isStaff()) {
            $goals = SavingsGoal::where('status', '!=', SavingsGoal::STATUS_CANCELLED)
                ->with('savingsAccount')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        } else {
            $goals = $user->savingsGoals()
                ->with('savingsAccount')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }

        $hydrated = $goals->getCollection()->map(fn (SavingsGoal $goal) => $this->service->hydrateGoal($goal));
        $goals->setCollection($hydrated);

        return SavingsGoalResource::collection($goals)
            ->additional(['success' => true]);
    }

    public function store(StoreSavingsGoalRequest $request)
    {
        Gate::authorize('create', SavingsGoal::class);

        $data = $request->validated();
        $data['member_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? SavingsGoal::STATUS_ACTIVE;
        $data['current_amount'] = $data['current_amount'] ?? 0;

        $goal = SavingsGoal::create($data);
        $goal = $this->service->hydrateGoal($goal->fresh(['savingsAccount']));

        return (new SavingsGoalResource($goal))
            ->additional([
                'success' => true,
                'message' => 'Savings goal created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(SavingsGoal $goal)
    {
        Gate::authorize('view', $goal);

        $goal->load('savingsAccount');
        $goal = $this->service->hydrateGoal($goal);

        return (new SavingsGoalResource($goal))
            ->additional(['success' => true]);
    }

    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $goal)
    {
        Gate::authorize('update', $goal);

        $data = $request->validated();

        if (array_key_exists('target_amount', $data) || array_key_exists('current_amount', $data)) {
            $target = array_key_exists('target_amount', $data)
                ? (float) $data['target_amount']
                : (float) $goal->target_amount;

            $current = array_key_exists('current_amount', $data)
                ? (float) $data['current_amount']
                : (float) $goal->current_amount;

            $this->service->updateGoalAmounts($goal, $target, $current);

            unset($data['target_amount'], $data['current_amount']);
        }

        if (array_key_exists('status', $data)) {
            $goal->status = $data['status'];
            if ($goal->status === SavingsGoal::STATUS_COMPLETED && !$goal->achieved_at) {
                $goal->achieved_at = now();
            }

            if ($goal->status === SavingsGoal::STATUS_ACTIVE && $goal->achieved_at && $goal->current_amount < $goal->target_amount) {
                $goal->achieved_at = null;
            }

            unset($data['status']);
        }

        if (!empty($data)) {
            $goal->fill($data);
            $goal->save();
        } else {
            $goal->save();
        }

        $goal->refresh()->load('savingsAccount');
        $goal = $this->service->hydrateGoal($goal);

        return (new SavingsGoalResource($goal))
            ->additional([
                'success' => true,
                'message' => 'Savings goal updated successfully.',
            ]);
    }

    public function destroy(SavingsGoal $goal): JsonResponse
    {
        Gate::authorize('delete', $goal);

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Savings goal removed.',
        ]);
    }
}
