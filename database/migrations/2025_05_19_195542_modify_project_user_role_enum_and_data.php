<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_user', function (Blueprint $table) {
            DB::table('project_user')
                ->where('role', 'viewer')
                ->update(['role' => '_temp_viewer_']);

            DB::table('project_user')
                ->where('role', 'member')
                ->update(['role' => 'editor']);

            DB::table('project_user')
                ->where('role', '_temp_viewer_')
                ->update(['role' => 'member']);

            $newEnumDefinition = "ENUM('owner', 'admin', 'editor', 'member')";
            $defaultRole = 'member';
            DB::statement("ALTER TABLE project_user MODIFY COLUMN role {$newEnumDefinition} NOT NULL DEFAULT '{$defaultRole}'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_user', function (Blueprint $table) {
            DB::table('project_user')
                ->where('role', 'member')
                ->update(['role' => '_temp_member_to_viewer_']);

            DB::table('project_user')
                ->where('role', 'editor')
                ->update(['role' => 'member']);

            DB::table('project_user')
                ->where('role', '_temp_member_to_viewer_')
                ->update(['role' => 'viewer']);

            $originalEnumDefinition = "ENUM('owner', 'admin', 'member', 'viewer')";
            $originalDefaultRole = 'member';
            DB::statement("ALTER TABLE project_user MODIFY COLUMN role {$originalEnumDefinition} NOT NULL DEFAULT '{$originalDefaultRole}'");
        });
    }
};
