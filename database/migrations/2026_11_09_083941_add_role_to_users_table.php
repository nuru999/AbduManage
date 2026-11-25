<?php

use Illuminate\Database\Migrations\Migration;

// The `role` column now lives in the create_users_table migration.
// Kept as a no-op so migration history stays consistent.
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
