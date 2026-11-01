<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hrm_attendance_breaks')) {
            Schema::create('hrm_attendance_breaks', function (Blueprint $t) {
                $t->id();
                $t->foreignId('attendance_id')->constrained('hrm_attendances')->cascadeOnDelete();
                $t->timestamp('break_start')->nullable();
                $t->timestamp('break_end')->nullable();
                $t->timestamps();
                $t->index(['attendance_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hrm_attendance_breaks');
    }
};
