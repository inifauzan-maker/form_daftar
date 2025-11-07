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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('jabatan', [
                'Kadiv Marketing', 
                'Social Media Specialist', 
                'Ads Specialist', 
                'Content Creator', 
                'Sales Team', 
                'Data Analyst'
            ])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'last_login_at', 'avatar', 'jabatan']);
        });
    }
};
