<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->integer('shares_count')->default(0);
            $table->decimal('share_value', 15, 2); // Value per share
            $table->decimal('total_value', 15, 2); // shares_count * share_value
            $table->date('purchase_date');
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index('certificate_number');
            $table->index('purchase_date');
            $table->index('shares_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_accounts');
    }
};
