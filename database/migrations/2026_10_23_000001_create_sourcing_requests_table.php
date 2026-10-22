<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sourcing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();          // SR-2026-0001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Who is asking
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();

            // What they need overall
            $table->string('source_country')->nullable();        // where they want it from, e.g. China
            $table->string('deliver_to')->nullable();            // city / country for delivery
            $table->string('budget')->nullable();                // free text: people rarely have an exact figure
            $table->date('needed_by')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['new', 'reviewing', 'quoted', 'closed'])->default('new');
            $table->text('admin_note')->nullable();
            $table->string('ip', 45)->nullable();
            $table->boolean('emailed')->default(false);
            $table->string('email_error', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('sourcing_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sourcing_request_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->nullable();                  // pieces, cartons, kg …
            $table->string('target_price')->nullable();          // free text, any currency
            $table->string('reference_url')->nullable();         // a link to something similar
            $table->text('specs')->nullable();                   // colour, size, material …
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sourcing_request_items');
        Schema::dropIfExists('sourcing_requests');
    }
};
