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
        Schema::create('novel_volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->restrictOnDelete();
            $table->unsignedSmallInteger('volume_number');
            $table->string('title')->nullable();
            $table->string('digital_file_path')->nullable();
            $table->unsignedInteger('digital_license_count')->default(0);
            $table->boolean('digital_lending_allowed')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['book_id', 'volume_number']);
            $table->index(['book_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novel_volumes');
    }
};
