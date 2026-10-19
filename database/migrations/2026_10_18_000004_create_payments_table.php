<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('provider', 20)->default('cod');            // cod | stripe
            $table->string('status', 20)->default('pending')->index();  // pending | paid | failed | cancelled | refunded
            $table->string('payment_intent_id', 100)->nullable()->index();
            $table->string('charge_id', 100)->nullable()->index();
            // amount actually charged, in the customer's currency
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            // card details as reported by Stripe (never the full number)
            $table->string('card_brand', 30)->nullable();
            $table->string('card_last4', 4)->nullable()->index();
            $table->unsignedTinyInteger('card_exp_month')->nullable();
            $table->unsignedSmallInteger('card_exp_year')->nullable();
            $table->string('card_funding', 20)->nullable();            // credit | debit | prepaid
            $table->string('card_country', 2)->nullable();
            $table->string('card_wallet', 30)->nullable();             // apple_pay | google_pay | …
            $table->string('cardholder_name', 150)->nullable();
            $table->string('billing_email', 190)->nullable();
            $table->string('risk_level', 20)->nullable();              // normal | elevated | highest
            $table->unsignedSmallInteger('risk_score')->nullable();
            $table->string('receipt_url', 500)->nullable();
            $table->string('failure_message', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });

        // Backfill one ledger row per existing order so history is complete.
        // Card details for old Stripe orders can be pulled with "Refresh from Stripe".
        $now = now();
        DB::table('orders')->orderBy('id')->chunk(200, function ($orders) use ($now) {
            $rows = [];
            foreach ($orders as $o) {
                $status = $o->payment_status === 'paid' ? 'paid'
                    : ($o->payment_status === 'refunded' ? 'refunded'
                    : ($o->status === 'cancelled' ? 'cancelled' : 'pending'));
                $rows[] = [
                    'order_id'          => $o->id,
                    'provider'          => $o->payment_method === 'card' ? 'stripe' : 'cod',
                    'status'            => $status,
                    'payment_intent_id' => $o->payment_intent_id ?? null,
                    'amount'            => round((float) $o->total * (float) ($o->exchange_rate ?? 1), 2),
                    'currency'          => $o->currency_code ?? 'PKR',
                    'billing_email'     => $o->customer_email,
                    'ip_address'        => $o->ip_address ?? null,
                    'paid_at'           => $o->paid_at ?? ($status === 'paid' ? $o->updated_at : null),
                    'created_at'        => $o->created_at ?? $now,
                    'updated_at'        => $now,
                ];
            }
            DB::table('payments')->insert($rows);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
