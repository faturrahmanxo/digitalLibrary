<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'series_id')) {
                $table->foreignId('series_id')->nullable()->after('publisher_id')->constrained('books')->nullOnDelete();
            }

            if (! Schema::hasColumn('books', 'series_title')) {
                $table->string('series_title')->nullable()->after('series_id');
                $table->index('series_title');
            }

            if (! Schema::hasColumn('books', 'volume_label')) {
                $table->string('volume_label', 100)->nullable()->after('series_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'series_id')) {
                $table->dropForeign(['series_id']);
                $table->dropColumn('series_id');
            }

            if (Schema::hasColumn('books', 'series_title')) {
                $table->dropIndex(['series_title']);
                $table->dropColumn('series_title');
            }

            if (Schema::hasColumn('books', 'volume_label')) {
                $table->dropColumn('volume_label');
            }
        });
    }
};
