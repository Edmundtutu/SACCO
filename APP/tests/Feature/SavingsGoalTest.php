<?php

use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Notifications\SavingsGoalLaggingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function createSavingsGoalTestAccount(User $member, float $balance = 0): Account
{
    $ledger = SavingsAccount::factory()->create([
        'balance' => $balance,
        'available_balance' => $balance,
    ]);

    $account = Account::factory()
        ->withSavingsAccount($ledger)
        ->active()
        ->create([
            'member_id' => $member->id,
        ]);

    $account->setRelation('accountable', $ledger);
    $ledger->setRelation('account', $account);

    return $account;
}

function createSavingsAccountForMember(User $member, float $balance = 0): Account
{
    return createSavingsGoalTestAccount($member, $balance);
}

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->artisan('db:seed', ['--class' => 'SaccoDataSeeder']);

    $this->member = User::where('email', 'jane@example.com')->first();
    $this->memberToken = auth('api')->login($this->member);
    $this->memberSavingsAccount = createSavingsAccountForMember($this->member, 0);
});

test('member can create a savings goal', function () {
    $payload = [
        'title' => 'Emergency Fund',
        'description' => 'Set aside funds for emergencies.',
        'target_amount' => 500000,
        'target_date' => now()->addMonths(6)->format('Y-m-d'),
        'savings_account_id' => $this->memberSavingsAccount->id,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/goals', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Emergency Fund')
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('savings_goals', [
        'member_id' => $this->member->id,
        'title' => 'Emergency Fund',
    ]);
});

test('member cannot create a savings goal without a linked savings account', function () {
    $payload = [
        'title' => 'Emergency Fund',
        'description' => 'Set aside funds for emergencies.',
        'target_amount' => 500000,
        'target_date' => now()->addMonths(6)->format('Y-m-d'),
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/goals', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['savings_account_id']);
});

test('member can list their savings goals with progress information', function () {
    SavingsGoal::factory()->for($this->member, 'member')->create([
        'title' => 'Holiday Savings',
        'target_amount' => 1000000,
        'current_amount' => 250000,
        'target_date' => now()->addMonths(4),
        'status' => SavingsGoal::STATUS_ACTIVE,
        'savings_account_id' => $this->memberSavingsAccount->id,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/goals');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'progress' => [
                        'percentage',
                        'amount_remaining',
                        'is_on_track',
                    ],
                ],
            ],
        ]);
});

test('member can update savings goal progress and mark as completed automatically', function () {
    $ledger = $this->memberSavingsAccount->accountable;
    $goal = SavingsGoal::factory()->for($this->member, 'member')->create([
        'title' => 'Laptop Purchase',
        'target_amount' => 2000000,
        'current_amount' => 500000,
        'status' => SavingsGoal::STATUS_ACTIVE,
        'target_date' => now()->addMonths(3),
        'savings_account_id' => $this->memberSavingsAccount->id,
    ]);

    $payload = [
        'current_amount' => 2000000,
    ];

    $ledger->update([
        'balance' => 2000000,
        'available_balance' => 2000000,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson("/api/savings/goals/{$goal->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('data.status', SavingsGoal::STATUS_COMPLETED)
        ->assertJsonPath('data.progress.percentage', 100);

    $goal->refresh();
    expect($goal->status)->toBe(SavingsGoal::STATUS_COMPLETED);
    $this->assertEquals(2000000.0, (float) $goal->current_amount);
});

test('lagging goal triggers nudge notification and response payload', function () {
    Notification::fake();

    $laggingAccount = createSavingsAccountForMember($this->member, 50000);

    $goal = SavingsGoal::factory()->for($this->member, 'member')->create([
        'title' => 'Home Renovation',
        'target_amount' => 10000000,
        'current_amount' => 50000,
        'status' => SavingsGoal::STATUS_ACTIVE,
        'target_date' => now()->addWeeks(2),
        'nudge_frequency' => SavingsGoal::NUDGE_WEEKLY,
        'last_nudged_at' => now()->subWeeks(2),
        'created_at' => now()->subMonths(3),
        'savings_account_id' => $laggingAccount->id,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/goals');

    $response->assertOk()
        ->assertJsonPath('data.0.nudge.should_display', true)
        ->assertJsonPath('data.0.nudge.channels', ['in_app', 'email']);

    Notification::assertSentTo($this->member, SavingsGoalLaggingNotification::class);

    $goal->refresh();
    expect($goal->last_nudged_at)->not()->toBeNull();
});

test('member cannot access another members goal', function () {
    $otherMember = User::where('email', 'robert@example.com')->first();
    $otherAccount = createSavingsAccountForMember($otherMember, 0);

    $goal = SavingsGoal::factory()->for($otherMember, 'member')->create([
        'title' => 'Not yours',
        'target_amount' => 100000,
        'savings_account_id' => $otherAccount->id,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/goals/{$goal->id}");

    $response->assertStatus(403);
});
