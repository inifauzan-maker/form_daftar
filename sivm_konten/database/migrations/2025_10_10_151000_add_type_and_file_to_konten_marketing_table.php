<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('konten_marketing', function (Blueprint $table) {
            if (!Schema::hasColumn('konten_marketing', 'type')) {
                $table->string('type')->nullable()->after('status');
            }
            if (!Schema::hasColumn('konten_marketing', 'file')) {
                $table->string('file')->nullable()->after('type');
            }
        });
    }
    public function down(): void
    {
        Schema::table('konten_marketing', function (Blueprint $table) {
            $table->dropColumn(['type', 'file']);
        });
    }
};
