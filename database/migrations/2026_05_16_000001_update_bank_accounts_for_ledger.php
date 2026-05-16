<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('account_type', 20)->nullable()->after('name');
            $table->string('currency', 3)->default('INR')->after('account_type');
            $table->string('status', 20)->default('active')->after('current_balance');
            $table->json('metadata')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('metadata');
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->unique('uuid');
        });

        // Backfill account_type + uuid for existing rows
        DB::table('bank_accounts')
            ->whereNull('account_type')
            ->update([
                'account_type' => DB::raw("CASE type
                    WHEN 'bank' THEN 'bank'
                    WHEN 'cash' THEN 'cash'
                    WHEN 'credit_card' THEN 'credit_card'
                    ELSE 'wallet'
                END"),
            ]);

        DB::table('bank_accounts')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('bank_accounts')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropUnique(['uuid']);
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'account_type', 'currency', 'status', 'metadata', 'closed_at']);
        });
    }
};

