<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('subscribers')) return;
        Schema::create('subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('token', 64)->index();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subscribers'); }
};
