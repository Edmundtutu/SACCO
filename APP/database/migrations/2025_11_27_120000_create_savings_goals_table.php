<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('savings_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'completed', 'paused', 'cancelled'])->default('active');
            $table->boolean('auto_nudge')->default(true);
            $table->enum('nudge_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->timestamp('last_nudged_at')->nullable();
            $table->timestamp('achieved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['status', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
