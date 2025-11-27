<?php

namespace App\Services;

use App\Models\SavingsGoal;
use App\Notifications\SavingsGoalLaggingNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SavingsGoalService
{
    public function hydrateGoal(SavingsGoal $goal): SavingsGoal
    {
        if ($goal->savings_account_id) {
            $this->refreshFromLinkedAccount($goal);
        }

        $progress = $this->calculateProgress($goal);
        $goal->setAttribute('progress', $progress);

        $nudge = $this->evaluateNudge($goal, $progress);
        $goal->setAttribute('nudge', $nudge);

        return $goal;
    }

    public function calculateProgress(SavingsGoal $goal): array
    {
        $target = (float) $goal->target_amount;
        $current = min((float) $goal->current_amount, $target);
        $percentage = $target > 0 ? round(($current / $target) * 100, 1) : 0.0;
        $remaining = max(0.0, $target - $current);

        $expectedPercentage = null;
        $daysElapsed = null;
        $daysTotal = null;

        if ($goal->target_date) {
            $createdAt = $goal->created_at ?? now();
            $targetDate = Carbon::parse($goal->target_date);

            if ($targetDate->lessThanOrEqualTo($createdAt)) {
                $expectedPercentage = 100.0;
            } else {
                $daysTotal = max(1, $createdAt->diffInDays($targetDate));
                $daysElapsed = $createdAt->diffInDays(now());
                $daysElapsed = min($daysTotal, max(0, $daysElapsed));
                $expectedPercentage = round(min(100, ($daysElapsed / $daysTotal) * 100), 1);
            }
        }

        $isOnTrack = true;
        if ($expectedPercentage !== null) {
            // Provide small buffer (5%) before marking as lagging
            $isOnTrack = $percentage + 5 >= $expectedPercentage;
        }

        return [
            'percentage' => min(100.0, $percentage),
            'amount_remaining' => $remaining,
            'expected_percentage' => $expectedPercentage,
            'days_elapsed' => $daysElapsed,
            'days_total' => $daysTotal,
            'is_on_track' => $isOnTrack,
        ];
    }

    public function updateGoalAmounts(SavingsGoal $goal, float $targetAmount, ?float $currentAmount = null): SavingsGoal
    {
        $goal->target_amount = $targetAmount;

        if ($currentAmount !== null) {
            $goal->current_amount = min($currentAmount, $targetAmount);
        }

        if ($goal->current_amount >= $goal->target_amount && $goal->status !== SavingsGoal::STATUS_COMPLETED) {
            $goal->markCompleted();
        }

        $goal->save();

        return $goal;
    }

    protected function refreshFromLinkedAccount(SavingsGoal $goal): void
    {
        $account = $goal->savingsAccount;

        if (!$account || !$account->accountable) {
            return;
        }

        $balance = (float) ($account->accountable->balance ?? $goal->current_amount);
        $goal->current_amount = $balance;

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->markCompleted();
        } elseif ($goal->status === SavingsGoal::STATUS_COMPLETED && $goal->current_amount < $goal->target_amount) {
            $goal->markActive();
        }

        if ($goal->isDirty(['current_amount', 'status', 'achieved_at'])) {
            $goal->save();
        }
    }

    protected function evaluateNudge(SavingsGoal $goal, array $progress): ?array
    {
        if (!$goal->auto_nudge || $goal->status !== SavingsGoal::STATUS_ACTIVE) {
            return null;
        }

        $isLagging = !($progress['is_on_track'] ?? true);

        if (!$isLagging) {
            return null;
        }

        $message = $this->buildNudgeMessage($goal, $progress);
        $emailSent = false;

        if ($this->withinCadenceWindow($goal)) {
            try {
                $goal->member?->notify(new SavingsGoalLaggingNotification($goal, $progress, $message));
                $goal->last_nudged_at = now();
                $goal->save();
                $emailSent = true;
            } catch (\Throwable $exception) {
                Log::warning('Failed to dispatch savings goal nudge', [
                    'goal_id' => $goal->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [
            'should_display' => true,
            'message' => $message,
            'channels' => ['in_app', 'email'],
            'email_sent' => $emailSent,
            'last_sent_at' => $goal->last_nudged_at?->toIso8601String(),
        ];
    }

    protected function withinCadenceWindow(SavingsGoal $goal): bool
    {
        if (!$goal->last_nudged_at) {
            return true;
        }

        $frequencyDays = match ($goal->nudge_frequency) {
            SavingsGoal::NUDGE_DAILY => 1,
            SavingsGoal::NUDGE_WEEKLY => 7,
            SavingsGoal::NUDGE_MONTHLY => 30,
            default => 7,
        };

        return $goal->last_nudged_at->diffInDays(now()) >= $frequencyDays;
    }

    protected function buildNudgeMessage(SavingsGoal $goal, array $progress): string
    {
        $remaining = number_format($progress['amount_remaining'] ?? 0, 0);
        $percentage = number_format($progress['percentage'] ?? 0, 1);
        $targetDate = $goal->target_date ? Carbon::parse($goal->target_date)->format('M j, Y') : 'your target date';

        return "You're at {$percentage}% of your '{$goal->title}' goal with UGX {$remaining} remaining before {$targetDate}. Keep going, you can make it!";
    }
}
