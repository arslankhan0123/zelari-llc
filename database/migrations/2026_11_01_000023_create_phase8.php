<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('automation_rules')) {
            Schema::create('automation_rules', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('event');                 // order.created | order.delivered | product.low_stock | vendor.registered
                $t->json('conditions')->nullable();  // [{field,op,value}]
                $t->json('actions')->nullable();     // [{type,...}]
                $t->boolean('enabled')->default(true);
                $t->unsignedInteger('run_count')->default(0);
                $t->timestamp('last_run_at')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('automation_runs')) {
            Schema::create('automation_runs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('rule_id')->constrained('automation_rules')->cascadeOnDelete();
                $t->string('event');
                $t->text('result')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('segment')->default('all'); // all|new|repeat|vip|lapsed
                $t->string('channel')->default('email'); // email|sms|push
                $t->string('subject')->nullable();
                $t->text('body')->nullable();
                $t->string('status')->default('draft'); // draft|sent
                $t->unsignedInteger('recipients')->default(0);
                $t->timestamp('sent_at')->nullable();
                $t->timestamps();
            });
        }
        Schema::table('customers', function (Blueprint $t) {
            if (!Schema::hasColumn('customers', 'points_balance')) $t->integer('points_balance')->default(0);
            if (!Schema::hasColumn('customers', 'referral_code'))  $t->string('referral_code')->nullable()->unique();
            if (!Schema::hasColumn('customers', 'referred_by'))    $t->unsignedBigInteger('referred_by')->nullable();
        });
        if (!Schema::hasTable('redirects')) {
            Schema::create('redirects', function (Blueprint $t) {
                $t->id();
                $t->string('from_path')->unique();
                $t->string('to_path');
                $t->unsignedSmallInteger('code')->default(301);
                $t->unsignedInteger('hits')->default(0);
                $t->boolean('enabled')->default(true);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->unique();
                $t->string('cover')->nullable();
                $t->text('excerpt')->nullable();
                $t->longText('body')->nullable();
                $t->string('status')->default('draft'); // draft|published
                $t->string('meta_title')->nullable();
                $t->string('meta_description')->nullable();
                $t->timestamp('published_at')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->string('action');        // created|updated|deleted
                $t->string('model');
                $t->unsignedBigInteger('model_id')->nullable();
                $t->string('description')->nullable();
                $t->string('ip')->nullable();
                $t->timestamps();
                $t->index(['model', 'model_id']);
            });
        }
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->decimal('price', 10, 2)->default(0);
                $t->unsignedInteger('product_limit')->nullable(); // null = unlimited
                $t->decimal('commission_rate', 5, 2)->nullable();  // optional override
                $t->json('features')->nullable();
                $t->boolean('enabled')->default(true);
                $t->integer('sort')->default(0);
                $t->timestamps();
            });
        }
        if (!Schema::hasColumn('vendors', 'plan_id')) {
            Schema::table('vendors', function (Blueprint $t) {
                $t->foreignId('plan_id')->nullable()->after('status')->constrained('subscription_plans')->nullOnDelete();
            });
        }
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'two_factor_enabled'))    $t->boolean('two_factor_enabled')->default(false);
            if (!Schema::hasColumn('users', 'two_factor_code'))       $t->string('two_factor_code')->nullable();
            if (!Schema::hasColumn('users', 'two_factor_expires_at')) $t->timestamp('two_factor_expires_at')->nullable();
        });

        // Settings defaults
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $defs = [
                'loyalty_enabled' => '0', 'loyalty_earn_rate' => '1', 'referral_reward_points' => '100',
                'seo_title_template' => '{name} — {store}', 'seo_desc_template' => '{excerpt}',
            ];
            foreach ($defs as $k => $v) {
                if (!DB::table('settings')->where('key', $k)->exists()) {
                    DB::table('settings')->insert(['key' => $k, 'value' => $v, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        // Admin role → all new modules
        $now = now();
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminId) {
            foreach (['automations', 'campaigns', 'loyalty', 'blog', 'redirects', 'audit', 'plans'] as $mod) {
                foreach (['list', 'view', 'create', 'edit', 'delete'] as $act) {
                    $perm = "$mod.$act";
                    if (!DB::table('role_permissions')->where('role_id', $adminId)->where('permission', $perm)->exists()) {
                        DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission' => $perm, 'created_at' => $now, 'updated_at' => $now]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['automation_runs', 'automation_rules', 'campaigns', 'redirects', 'blog_posts', 'audit_logs'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        if (Schema::hasColumn('vendors', 'plan_id')) {
            Schema::table('vendors', fn (Blueprint $t) => $t->dropConstrainedForeignId('plan_id'));
        }
        Schema::dropIfExists('subscription_plans');
        foreach (['points_balance', 'referral_code', 'referred_by'] as $c) {
            if (Schema::hasColumn('customers', $c)) Schema::table('customers', fn (Blueprint $t) => $t->dropColumn($c));
        }
        foreach (['two_factor_enabled', 'two_factor_code', 'two_factor_expires_at'] as $c) {
            if (Schema::hasColumn('users', $c)) Schema::table('users', fn (Blueprint $t) => $t->dropColumn($c));
        }
    }
};
