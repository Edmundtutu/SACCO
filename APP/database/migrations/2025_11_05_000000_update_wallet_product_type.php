<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SavingsProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update any existing wallet products with type 'special' to 'wallet'
        // This standardizes the product type while maintaining backward compatibility
        DB::table('savings_products')
            ->where('code', 'WL001')
            ->where('type', 'special')
            ->update(['type' => 'wallet']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert wallet products back to 'special' type if needed
        DB::table('savings_products')
            ->where('code', 'WL001')
            ->where('type', 'wallet')
            ->update(['type' => 'special']);
    }
};

