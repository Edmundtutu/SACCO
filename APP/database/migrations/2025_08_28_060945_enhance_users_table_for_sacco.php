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
        Schema::table('users', function (Blueprint $table) {
            $table->string('member_number')->unique()->nullable()->after('id');
            $table->enum('role', ['member', 'admin', 'staff', 'loan_officer', 'accountant'])->default('member')->after('email');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_approval'])->default('pending_approval')->after('role');
            $table->string('phone')->nullable()->after('email');
            $table->string('national_id')->unique()->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('national_id');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('address');
            $table->decimal('monthly_income', 15, 2)->nullable()->after('occupation');
            $table->date('membership_date')->nullable()->after('monthly_income');
            $table->timestamp('approved_at')->nullable()->after('membership_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'member_number', 'role', 'status', 'phone', 'national_id',
                'date_of_birth', 'gender', 'address', 'occupation',
                'monthly_income', 'membership_date', 'approved_at', 'approved_by'
            ]);
        });
    }
};