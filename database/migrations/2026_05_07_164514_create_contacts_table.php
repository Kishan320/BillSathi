<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identity
            $table->string('name');
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->enum('type', ['customer', 'vendor', 'both'])->default('customer');

            // Payment preferences
            $table->unsignedInteger('due_in_days')->default(0);
            $table->string('currency', 10)->default('INR');

            // Billing Address
            $table->string('billing_address1')->nullable();
            $table->string('billing_address2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_pincode')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->default('India');

            // Shipping Address
            $table->tinyInteger('shipping_same_as_billing')->default(1);
            $table->string('shipping_address1')->nullable();
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_pincode')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->default('India');

            // Opening Balance
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('opening_balance_type', ['payable', 'receivable'])->default('payable');

            // Preferences
            $table->tinyInteger('enable_customer_portal')->default(1);

            // Notes
            $table->string('notes', 250)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('contacts');
    }
};
