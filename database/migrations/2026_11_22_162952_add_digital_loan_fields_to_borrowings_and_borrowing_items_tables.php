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
        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->change();
            $table->string('loan_type', 20)->default('physical');
            $table->foreignId('borrower_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->index(['borrower_user_id', 'loan_type', 'status', 'due_at']);
        });

        Schema::table('borrowing_items', function (Blueprint $table) {
            $table->foreignId('novel_volume_id')->nullable()->constrained('novel_volumes')->restrictOnDelete();
            $table->unsignedSmallInteger('volume_number_snapshot')->nullable();
            $table->string('volume_title_snapshot')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('borrowings')
            ->whereNull('staff_id')
            ->update(['staff_id' => DB::raw('borrower_user_id')]);

        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable(false)->change();
        });

        Schema::table('borrowing_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('novel_volume_id');
            $table->dropColumn(['volume_number_snapshot', 'volume_title_snapshot']);
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex(['borrower_user_id', 'loan_type', 'status', 'due_at']);
            $table->dropConstrainedForeignId('borrower_user_id');
            $table->dropColumn('loan_type');
        });
    }
};
