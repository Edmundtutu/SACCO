<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    public function toArray($request): array
    {
        $progress = data_get($this->resource, 'progress');
        $nudge = data_get($this->resource, 'nudge');

        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'savings_account_id' => $this->savings_account_id,
            'title' => $this->title,
            'description' => $this->description,
            'target_amount' => (float) $this->target_amount,
            'current_amount' => (float) $this->current_amount,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'status' => $this->status,
            'auto_nudge' => (bool) $this->auto_nudge,
            'nudge_frequency' => $this->nudge_frequency,
            'last_nudged_at' => $this->last_nudged_at?->toIso8601String(),
            'achieved_at' => $this->achieved_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'progress' => $progress,
            'nudge' => $nudge,
        ];
    }
}
