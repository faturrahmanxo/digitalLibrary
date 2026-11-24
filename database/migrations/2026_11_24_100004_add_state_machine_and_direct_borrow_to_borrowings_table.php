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
            if (! Schema::hasColumn('borrowings', 'book_id')) {
                $table->foreignId('book_id')->nullable()->after('transaction_code')->constrained('books')->nullOnDelete();
            }

            if (! Schema::hasColumn('borrowings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('borrower_user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('borrowings', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('staff_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('borrowings', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('borrowings', 'handed_over_by')) {
                $table->foreignId('handed_over_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('borrowings', 'due_date')) {
                $table->date('due_date')->nullable()->after('due_at');
                $table->index(['status', 'due_date']);
            }

            if (! Schema::hasColumn('borrowings', 'notes')) {
                $table->text('notes')->nullable()->after('payload_hash');
            }

            if (! Schema::hasColumn('borrowings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('borrowings', 'is_direct_borrow')) {
                $table->boolean('is_direct_borrow')->default(false)->after('rejection_reason');
            }

            $table->dateTime('borrowed_at')->nullable()->change();
            $table->date('due_at')->nullable()->change();
        });

        // Backfill due_date, user_id, approval and handover fields from existing records
        DB::table('borrowings')->update([
            'due_date' => DB::raw('due_at'),
            'user_id' => DB::raw('borrower_user_id'),
            'approved_by' => DB::raw('CASE WHEN approved_by IS NULL AND staff_id IS NOT NULL THEN staff_id ELSE approved_by END'),
            'approved_at' => DB::raw('CASE WHEN approved_at IS NULL AND borrowed_at IS NOT NULL THEN borrowed_at ELSE approved_at END'),
            'handed_over_by' => DB::raw('CASE WHEN handed_over_by IS NULL AND staff_id IS NOT NULL THEN staff_id ELSE handed_over_by END'),
        ]);

        // Backfill book_id from borrowing_items if single item exists
        $borrowingItems = DB::table('borrowing_items')
            ->select('borrowing_id', DB::raw('MIN(book_id) as primary_book_id'))
            ->groupBy('borrowing_id')
            ->get();

        foreach ($borrowingItems as $item) {
            DB::table('borrowings')
                ->where('id', $item->borrowing_id)
                ->whereNull('book_id')
                ->update([
                    'book_id' => $item->primary_book_id,
                    'is_direct_borrow' => true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('borrowings', 'due_date')) {
                $table->dropIndex(['status', 'due_date']);
                $table->dropColumn('due_date');
            }

            if (Schema::hasColumn('borrowings', 'is_direct_borrow')) {
                $table->dropColumn('is_direct_borrow');
            }

            if (Schema::hasColumn('borrowings', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('borrowings', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('borrowings', 'handed_over_by')) {
                $table->dropConstrainedForeignId('handed_over_by');
            }

            if (Schema::hasColumn('borrowings', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('borrowings', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('borrowings', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('borrowings', 'book_id')) {
                $table->dropConstrainedForeignId('book_id');
            }
        });
    }
};
