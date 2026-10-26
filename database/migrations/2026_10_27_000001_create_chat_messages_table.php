<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chat_messages')) return; // may already exist via ChatMessage::ensureTable()
        Schema::create('chat_messages', function (Blueprint $t) {
            $t->id();
            $t->string('thread_token', 64)->index();
            $t->string('name', 100)->nullable();
            $t->string('email', 150)->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('sender', 10);
            $t->text('message');
            $t->boolean('read_by_agent')->default(false);
            $t->boolean('read_by_user')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
