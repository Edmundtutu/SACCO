<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $rolesWithSuperAdmin = [
        'member',
        'admin',
        'staff_level_1',
        'staff_level_2',
        'staff_level_3',
        'super_admin',
    ];

    private array $rolesWithoutSuperAdmin = [
        'member',
        'admin',
        'staff_level_1',
        'staff_level_2',
        'staff_level_3',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $roles = "'" . implode("','", $this->rolesWithSuperAdmin) . "'";
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM({$roles}) NOT NULL DEFAULT 'member'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $roles = "'" . implode("','", $this->rolesWithoutSuperAdmin) . "'";
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM({$roles}) NOT NULL DEFAULT 'member'");
        }
    }
};
