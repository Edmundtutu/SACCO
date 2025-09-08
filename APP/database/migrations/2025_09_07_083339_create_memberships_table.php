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
        Schema::create('memberships', function (Blueprint $table) {
            $table->string('id')->primary();;
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('profile');
            $table->enum('approval_status', ['pending','approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by_level_1')->nullable()->constrained('users');
            $table->timestamp('approved_at_level_1')->nullable();
            $table->foreignId('approved_by_level_2')->nullable()->constrained('users');
            $table->timestamp('approved_at_level_2')->nullable();
            $table->foreignId('approved_by_level_3')->nullable()->constrained('users');
            $table->timestamp('approved_at_level_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memberships');
    }
};
