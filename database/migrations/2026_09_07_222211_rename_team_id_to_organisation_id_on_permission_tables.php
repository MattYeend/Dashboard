<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        if (Schema::hasColumn('roles', 'team_id') && ! Schema::hasColumn('roles', 'organisation_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('team_id', 'organisation_id');
            });
        }

        if (Schema::hasColumn('model_has_roles', 'team_id') && ! Schema::hasColumn('model_has_roles', 'organisation_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropPrimary(['team_id', 'role_id', 'model_id', 'model_type']);
            });

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->renameColumn('team_id', 'organisation_id');
            });

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->primary(
                    ['organisation_id', 'role_id', 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            });
        }

        if (Schema::hasColumn('model_has_permissions', 'team_id') && ! Schema::hasColumn('model_has_permissions', 'organisation_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropPrimary(['team_id', 'permission_id', 'model_id', 'model_type']);
            });

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->renameColumn('team_id', 'organisation_id');
            });

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->primary(
                    ['organisation_id', 'permission_id', 'model_id', 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            });
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        if (Schema::hasColumn('model_has_permissions', 'organisation_id') && ! Schema::hasColumn('model_has_permissions', 'team_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            });

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->renameColumn('organisation_id', 'team_id');
            });

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->primary(['team_id', 'permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }

        if (Schema::hasColumn('model_has_roles', 'organisation_id') && ! Schema::hasColumn('model_has_roles', 'team_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropPrimary('model_has_roles_role_model_type_primary');
            });

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->renameColumn('organisation_id', 'team_id');
            });

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->primary(['team_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }

        if (Schema::hasColumn('roles', 'organisation_id') && ! Schema::hasColumn('roles', 'team_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('organisation_id', 'team_id');
            });
        }
    }
};
