<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('status'); // pending|approved|rejected
            }
            if (!Schema::hasColumn('products', 'approval_note')) {
                $table->string('approval_note')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('products', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['approval_status', 'approval_note', 'submitted_at'] as $c) {
                if (Schema::hasColumn('products', $c)) $table->dropColumn($c);
            }
        });
    }
};
