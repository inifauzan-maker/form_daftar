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
        Schema::create('konten_marketing', function (Blueprint $table) {
            $table->id('id_konten');
            $table->string('judul_konten');
            $table->enum('tipe_konten', ['Reels', 'Carousel', 'Video', 'Artikel']);
            $table->text('deskripsi');
            $table->enum('platform', ['Instagram', 'TikTok', 'Website']);
            $table->timestamp('tanggal_posting')->nullable();
            $table->decimal('engagement_rate', 8, 2)->default(0);
            $table->bigInteger('views')->default(0);
            $table->bigInteger('likes')->default(0);
            $table->bigInteger('comments')->default(0);
            $table->bigInteger('share_count')->default(0);
            $table->enum('status', ['Draft', 'Scheduled', 'Posted']);
            $table->foreignId('creator')->constrained('users')->onDelete('cascade');
            $table->string('hashtags')->nullable();
            $table->json('media_files')->nullable(); // Store file paths
            $table->text('ai_generated_caption')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamps();
            
            $table->index(['platform', 'status']);
            $table->index(['tanggal_posting']);
            $table->index(['creator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konten_marketing');
    }
};
