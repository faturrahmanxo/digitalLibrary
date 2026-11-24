<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')->where('collection_type', 'fisik')->update(['collection_type' => 'e-book']);
    }

    public function down(): void
    {
        DB::table('books')->where('collection_type', 'e-book')->update(['collection_type' => 'fisik']);
    }
};
