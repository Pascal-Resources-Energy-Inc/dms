<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereRaw('LOWER(TRIM(role)) = ?', ['sedp'])
            ->update(['role' => 'MFI']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereRaw('LOWER(TRIM(role)) = ?', ['mfi'])
            ->update(['role' => 'SEDP']);
    }
};
