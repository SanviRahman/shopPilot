<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'group_name')) {
                $table->string('group_name', 100)->nullable()->index()->after('guard_name');
            }

            if (! Schema::hasColumn('permissions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            if (Schema::hasColumn('permissions', 'group_name')) {
                $table->dropIndex(['group_name']);
                $table->dropColumn('group_name');
            }
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
