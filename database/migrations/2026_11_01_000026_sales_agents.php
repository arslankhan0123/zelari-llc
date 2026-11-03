<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_agents')) {
            Schema::create('sales_agents', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // optional login account
                $t->string('name');
                $t->string('email')->nullable();
                $t->string('phone')->nullable();
                $t->string('code')->unique();                 // referral code entered at checkout
                $t->decimal('commission_rate', 5, 2)->default(0); // % of order subtotal
                $t->boolean('active')->default(true);
                $t->string('payout_method')->nullable();
                $t->string('bank_name')->nullable();
                $t->string('account_name')->nullable();
                $t->string('account_number')->nullable();
                $t->text('note')->nullable();
                $t->timestamps();
                $t->index(['active']);
            });
        }

        if (!Schema::hasTable('agent_payouts')) {
            Schema::create('agent_payouts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('sales_agent_id')->constrained()->cascadeOnDelete();
                $t->decimal('amount', 12, 2)->default(0);     // base currency
                $t->string('method')->nullable();
                $t->string('reference')->nullable();
                $t->text('note')->nullable();
                $t->timestamp('paid_at')->nullable();
                $t->timestamps();
                $t->index(['sales_agent_id']);
            });
        }

        Schema::table('orders', function (Blueprint $t) {
            if (!Schema::hasColumn('orders', 'sales_agent_id')) {
                $t->foreignId('sales_agent_id')->nullable()->after('customer_id')->constrained('sales_agents')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'agent_commission')) {
                $t->decimal('agent_commission', 12, 2)->default(0)->after('discount');
            }
        });

        // Admin role -> agents.*
        $now = now();
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                $perm = "agents.$act";
                if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                    DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            if (Schema::hasColumn('orders', 'sales_agent_id')) $t->dropConstrainedForeignId('sales_agent_id');
            if (Schema::hasColumn('orders', 'agent_commission')) $t->dropColumn('agent_commission');
        });
        Schema::dropIfExists('agent_payouts');
        Schema::dropIfExists('sales_agents');
    }
};
