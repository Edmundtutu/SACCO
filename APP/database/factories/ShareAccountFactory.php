<?php

namespace Database\Factories;

use App\Models\ShareAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareAccountFactory extends Factory
{
    protected $model = ShareAccount::class;

    public function definition(): array
    {
        $sharesCount = $this->faker->numberBetween(1, 1000);
        $shareValue = 10000; // Fixed at UGX 10,000 per share
        
        return [
            'certificate_number' => 'SHR' . now()->format('Y') . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'shares_count' => $sharesCount,
            'share_value' => $shareValue,
            'total_value' => $sharesCount * $shareValue,
            'purchase_date' => $this->faker->dateTimeBetween('-2 years'),
            'notes' => $this->faker->optional()->sentence(),
            'issued_by' => User::factory(),
        ];
    }
}
