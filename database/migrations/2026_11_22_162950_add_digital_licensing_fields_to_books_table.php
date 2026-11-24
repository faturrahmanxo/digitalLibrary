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
            $table->text('description')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('digital_file_path')->nullable();
            $table->unsignedInteger('digital_license_count')->default(0);
            $table->boolean('digital_lending_allowed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'synopsis',
                'digital_file_path',
                'digital_license_count',
                'digital_lending_allowed',
            ]);
        });
    }
};
