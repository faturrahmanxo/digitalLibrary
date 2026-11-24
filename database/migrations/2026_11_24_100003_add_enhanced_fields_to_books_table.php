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
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'shelf_id')) {
                $table->foreignId('shelf_id')->nullable()->after('category_id')->constrained('shelves')->nullOnDelete();
            }

            if (! Schema::hasColumn('books', 'publisher_id')) {
                $table->foreignId('publisher_id')->nullable()->after('shelf_id')->constrained('publishers')->nullOnDelete();
            }

            if (! Schema::hasColumn('books', 'total_copies')) {
                $table->unsignedInteger('total_copies')->default(0)->after('publication_year');
            }

            if (! Schema::hasColumn('books', 'available_copies')) {
                $table->unsignedInteger('available_copies')->default(0)->after('total_copies');
            }

            if (! Schema::hasColumn('books', 'reserved_copies')) {
                $table->unsignedInteger('reserved_copies')->default(0)->after('available_copies');
            }

            if (! Schema::hasColumn('books', 'borrowed_copies')) {
                $table->unsignedInteger('borrowed_copies')->default(0)->after('reserved_copies');
            }

            if (! Schema::hasColumn('books', 'read_url')) {
                $table->string('read_url', 2048)->nullable()->after('download_url');
            }

            if (! Schema::hasColumn('books', 'status')) {
                $table->string('status', 20)->default('active')->after('read_url');
                $table->index('status');
            }

            $table->string('isbn', 50)->nullable()->change();
            $table->mediumText('synopsis')->nullable()->change();
        });

        // Normalize publisher strings to publishers table
        $existingPublishers = DB::table('books')
            ->whereNotNull('publisher')
            ->where('publisher', '!=', '')
            ->distinct()
            ->pluck('publisher');

        foreach ($existingPublishers as $pubName) {
            $pubNameTrimmed = trim((string) $pubName);
            if ($pubNameTrimmed === '') {
                continue;
            }

            $existing = DB::table('publishers')->where('name', $pubNameTrimmed)->first();
            $publisherId = $existing?->id ?? DB::table('publishers')->insertGetId([
                'name' => $pubNameTrimmed,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('books')
                ->where('publisher', $pubName)
                ->whereNull('publisher_id')
                ->update(['publisher_id' => $publisherId]);
        }

        // Backfill copy counts and status from existing inventory
        $driver = DB::getDriverName();
        $borrowedExpr = $driver === 'sqlite'
            ? 'MAX(0, COALESCE(stock_total, 0) - COALESCE(stock_available, 0))'
            : 'GREATEST(0, CAST(COALESCE(stock_total, 0) AS SIGNED) - CAST(COALESCE(stock_available, 0) AS SIGNED))';

        DB::table('books')->update([
            'total_copies' => DB::raw('COALESCE(stock_total, 0)'),
            'available_copies' => DB::raw('COALESCE(stock_available, 0)'),
            'borrowed_copies' => DB::raw($borrowedExpr),
            'reserved_copies' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('books', 'read_url')) {
                $table->dropColumn('read_url');
            }

            $columnsToDrop = array_filter([
                'borrowed_copies',
                'reserved_copies',
                'available_copies',
                'total_copies',
            ], fn (string $col) => Schema::hasColumn('books', $col));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (Schema::hasColumn('books', 'publisher_id')) {
                $table->dropConstrainedForeignId('publisher_id');
            }

            if (Schema::hasColumn('books', 'shelf_id')) {
                $table->dropConstrainedForeignId('shelf_id');
            }
        });
    }
};
