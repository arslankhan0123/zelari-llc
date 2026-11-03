<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Suppliers ──
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('vendor_id')->constrained('suppliers')->nullOnDelete();
            });
        }

        // ── Stock movements (inventory ledger) ──
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
                $table->integer('change');                 // +restock / -sale|adjust
                $table->integer('balance')->nullable();    // resulting qty snapshot
                $table->string('type')->default('adjust'); // adjust|restock|sale|return
                $table->string('reason')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['product_id', 'created_at']);
            });
        }

        // ── Support ticketing ──
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ref')->unique();
                $table->string('subject');
                $table->string('customer_name')->nullable();
                $table->string('customer_email');
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('open');     // open|pending|closed
                $table->string('priority')->default('normal');  // low|normal|high|urgent
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('due_at')->nullable();
                $table->timestamp('first_responded_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'priority']);
            });
        }
        if (!Schema::hasTable('support_ticket_replies')) {
            Schema::create('support_ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('author_name')->nullable();
                $table->text('body');
                $table->boolean('is_staff')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('canned_replies')) {
            Schema::create('canned_replies', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body');
                $table->timestamps();
            });
        }

        // ── Returns / refunds ──
        if (!Schema::hasTable('return_requests')) {
            Schema::create('return_requests', function (Blueprint $table) {
                $table->id();
                $table->string('ref')->unique();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('customer_email');
                $table->string('type')->default('return');     // return|exchange|refund
                $table->text('reason')->nullable();
                $table->string('status')->default('requested'); // requested|approved|rejected|completed
                $table->decimal('amount', 12, 2)->nullable();
                $table->text('admin_note')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->index(['status']);
            });
        }

        // ── Permissions: admin role gets the new modules ──
        $now = now();
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['suppliers', 'inventory', 'tickets', 'returns', 'reports'] as $mod) {
                foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                    $perm = "$mod.$act";
                    if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('canned_replies');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('stock_movements');
        if (Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', fn (Blueprint $t) => $t->dropConstrainedForeignId('supplier_id'));
        }
        Schema::dropIfExists('suppliers');
    }
};
