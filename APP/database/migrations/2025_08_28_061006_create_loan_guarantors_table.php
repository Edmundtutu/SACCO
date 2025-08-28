<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('guarantor_id')->constrained('users'); // Reference to users table
            $table->decimal('guaranteed_amount', 15, 2); // Amount this guarantor covers
            $table->decimal('guarantor_savings_at_time', 15, 2); // Guarantor's savings balance when guarantee was made
            $table->enum('status', ['pending', 'accepted', 'rejected', 'released', 'claimed'])->default('pending');
            $table->date('guarantee_date')->nullable(); // Date when guarantee was accepted
            $table->text('guarantee_terms')->nullable(); // Special terms if any
            $table->decimal('claimed_amount', 15, 2)->default(0); // Amount claimed from guarantor if borrower defaults
            $table->date('claim_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('release_reason')->nullable(); // Reason for releasing guarantee
            $table->date('release_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users'); // Staff who processed
            $table->timestamps();

            $table->unique(['loan_id', 'guarantor_id']);
            $table->index(['guarantor_id', 'status']);
            $table->index(['loan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_guarantors');
    }
};