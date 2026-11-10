<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotency keys for posting endpoints.
 *
 * Stock movements and journal entries are the two authoritative ledgers.
 * A posting request carries an idempotency key (client-generated); retrying
 * the same key returns the already-posted record instead of creating a
 * duplicate ledger entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('reference');
                $table->unique(['tenant_id', 'idempotency_key'])->whereNull('deleted_at');
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entries', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('reference');
                $table->unique(['tenant_id', 'idempotency_key']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'idempotency_key')) {
                $table->dropUnique(['tenant_id', 'idempotency_key']);
                $table->dropColumn('idempotency_key');
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'idempotency_key')) {
                $table->dropUnique(['tenant_id', 'idempotency_key']);
                $table->dropColumn('idempotency_key');
            }
        });
    }
};
