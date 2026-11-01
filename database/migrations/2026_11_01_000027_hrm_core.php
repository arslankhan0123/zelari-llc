<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hrm_roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('description')->nullable();
            $t->boolean('is_system')->default(false);
            $t->timestamps();
        });

        Schema::create('hrm_role_permissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hrm_role_id')->constrained('hrm_roles')->cascadeOnDelete();
            $t->string('permission');
            $t->timestamps();
            $t->index(['hrm_role_id']);
        });

        if (!Schema::hasColumn('users', 'hrm_role_id')) {
            Schema::table('users', function (Blueprint $t) {
                $t->foreignId('hrm_role_id')->nullable()->after('vendor_id')->constrained('hrm_roles')->nullOnDelete();
            });
        }

        Schema::create('hrm_departments', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('hrm_departments')->nullOnDelete();
            $t->unsignedBigInteger('head_employee_id')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('hrm_employees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // optional login link
            $t->foreignId('department_id')->nullable()->constrained('hrm_departments')->nullOnDelete();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('designation')->nullable();
            $t->string('employment_type')->default('full_time'); // full_time|part_time|contract|intern
            $t->date('join_date')->nullable();
            $t->decimal('base_salary', 12, 2)->default(0);
            $t->string('status')->default('active'); // active|on_leave|terminated
            $t->string('photo')->nullable();
            $t->text('address')->nullable();
            $t->timestamps();
            $t->index(['department_id', 'status']);
        });

        Schema::create('hrm_leave_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedSmallInteger('annual_quota')->default(0);
            $t->boolean('paid')->default(true);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('hrm_leaves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('hrm_employees')->cascadeOnDelete();
            $t->foreignId('leave_type_id')->nullable()->constrained('hrm_leave_types')->nullOnDelete();
            $t->date('from_date');
            $t->date('to_date');
            $t->unsignedSmallInteger('days')->default(1);
            $t->text('reason')->nullable();
            $t->string('status')->default('pending'); // pending|approved|rejected
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
            $t->index(['employee_id', 'status']);
        });

        Schema::create('hrm_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('hrm_employees')->cascadeOnDelete();
            $t->date('date');
            $t->time('check_in')->nullable();
            $t->time('check_out')->nullable();
            $t->string('status')->default('present'); // present|absent|leave|half_day|holiday
            $t->string('note')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'date']);
        });

        Schema::create('hrm_payslips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('hrm_employees')->cascadeOnDelete();
            $t->string('period'); // YYYY-MM
            $t->decimal('basic', 12, 2)->default(0);
            $t->decimal('allowances', 12, 2)->default(0);
            $t->decimal('deductions', 12, 2)->default(0);
            $t->decimal('net', 12, 2)->default(0);
            $t->string('status')->default('draft'); // draft|paid
            $t->timestamp('paid_at')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'period']);
        });

        Schema::create('hrm_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->timestamps();
        });

        // ── Seed: HR Admin role with every HRM permission ──
        $now = now();
        $roleId = DB::table('hrm_roles')->insertGetId([
            'name' => 'HR Admin', 'slug' => 'hr_admin',
            'description' => 'Full access to all HRM modules.', 'is_system' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $perms = [];
        foreach (array_keys(config('hrm_permissions', [])) as $m) {
            foreach (config("hrm_permissions.$m.actions", []) as $a) {
                $perms[] = ['hrm_role_id' => $roleId, 'permission' => "hrm.$m.$a", 'created_at' => $now, 'updated_at' => $now];
            }
        }
        if ($perms) DB::table('hrm_role_permissions')->insert($perms);

        // A lighter "HR Manager" role (no roles/users/settings control)
        $mgrId = DB::table('hrm_roles')->insertGetId([
            'name' => 'HR Manager', 'slug' => 'hr_manager',
            'description' => 'Manage employees, attendance, leave and payroll.', 'is_system' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $mgr = [];
        foreach (['dashboard', 'employees', 'departments', 'attendance', 'leave', 'payroll', 'reports'] as $m) {
            foreach (config("hrm_permissions.$m.actions", []) as $a) {
                $mgr[] = ['hrm_role_id' => $mgrId, 'permission' => "hrm.$m.$a", 'created_at' => $now, 'updated_at' => $now];
            }
        }
        if ($mgr) DB::table('hrm_role_permissions')->insert($mgr);

        // ── Seed: default leave types ──
        DB::table('hrm_leave_types')->insert([
            ['name' => 'Annual Leave', 'annual_quota' => 14, 'paid' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sick Leave',   'annual_quota' => 10, 'paid' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Casual Leave',  'annual_quota' => 8,  'paid' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unpaid Leave',  'annual_quota' => 0,  'paid' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ── Seed: default HR settings ──
        foreach ([
            'hrm_company_name'   => 'Zelari',
            'hrm_work_days'      => 'Mon,Tue,Wed,Thu,Fri',
            'hrm_work_start'     => '09:00',
            'hrm_work_end'       => '18:00',
            'hrm_payroll_cycle'  => 'monthly',
            'hrm_currency'       => 'PKR',
        ] as $k => $v) {
            DB::table('hrm_settings')->insert(['key' => $k, 'value' => $v, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'hrm_role_id')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('hrm_role_id'));
        }
        Schema::dropIfExists('hrm_payslips');
        Schema::dropIfExists('hrm_attendances');
        Schema::dropIfExists('hrm_leaves');
        Schema::dropIfExists('hrm_leave_types');
        Schema::dropIfExists('hrm_employees');
        Schema::dropIfExists('hrm_departments');
        Schema::dropIfExists('hrm_settings');
        Schema::dropIfExists('hrm_role_permissions');
        Schema::dropIfExists('hrm_roles');
    }
};
