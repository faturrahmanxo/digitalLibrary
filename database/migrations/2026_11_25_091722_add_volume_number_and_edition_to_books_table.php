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
            if (! Schema::hasColumn('books', 'volume_number')) {
                $table->unsignedInteger('volume_number')->nullable()->after('volume_label');
            }

            if (! Schema::hasColumn('books', 'edition')) {
                $table->string('edition', 100)->nullable()->after('volume_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'volume_number')) {
                $table->dropColumn('volume_number');
            }

            if (Schema::hasColumn('books', 'edition')) {
                $table->dropColumn('edition');
            }
        });
    }
};
