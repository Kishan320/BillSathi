<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();

            // Ledger semantics
            $table->string('type', 30); // opening_balance, deposit, withdrawal, transfer, purchase_payment, expense, adjustment
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->date('occurred_on');
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            // Link to a "source" business document (expense/income/payment/transfer/etc.)
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_key', 40)->nullable(); // for multi-legged sources (e.g. transfer: from/to)

            $table->timestamps();

            $table->index(['user_id', 'bank_account_id', 'occurred_on']);
            $table->index(['source_type', 'source_id', 'source_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};

