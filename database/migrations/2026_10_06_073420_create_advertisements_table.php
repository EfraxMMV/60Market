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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions');
            $table->string('title');
            $table->text('destination_url');
            $table->string('content_type'); // image, video, html
            $table->string('status')->default('draft'); // draft, pending_payment, paid, scheduled, cancelled
            $table->string('moderation_status')->default('pending'); // pending, approved, rejected, review
            $table->text('moderation_reason')->nullable();
            $table->text('generated_html')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index(['region_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
