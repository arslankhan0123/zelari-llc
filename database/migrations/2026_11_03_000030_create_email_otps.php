<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_otps')) {
            Schema::create('email_otps', function (Blueprint $t) {
                $t->id();
                $t->string('email');
                $t->string('purpose', 40);       // register | 2fa_enable | password_reset
                $t->string('code');              // hashed 6-digit code
                $t->text('payload')->nullable(); // JSON (e.g. pending registration)
                $t->timestamp('expires_at')->nullable();
                $t->timestamps();
                $t->index(['email', 'purpose']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_otps');
    }
};
