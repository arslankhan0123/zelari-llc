<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * One row per live-chat message. Threads are grouped by thread_token —
 * a UUID the widget keeps in the visitor's browser, so guests (not logged in)
 * get their replies too.
 */
class ChatMessage extends Model
{
    protected $fillable = [
        'thread_token', 'name', 'email', 'user_id',
        'sender', 'message', 'read_by_agent', 'read_by_user',
    ];

    protected $casts = [
        'read_by_agent' => 'boolean',
        'read_by_user'  => 'boolean',
    ];

    protected static bool $ensured = false;

    /** Create the table on first use so no manual migration is required. */
    public static function ensureTable(): void
    {
        if (static::$ensured) return;
        static::$ensured = true;
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function ($t) {
                $t->id();
                $t->string('thread_token', 64)->index();
                $t->string('name', 100)->nullable();
                $t->string('email', 150)->nullable();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('sender', 10); // user | agent
                $t->text('message');
                $t->boolean('read_by_agent')->default(false);
                $t->boolean('read_by_user')->default(false);
                $t->timestamps();
            });
        }
    }
}
