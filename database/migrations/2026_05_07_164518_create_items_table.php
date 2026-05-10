<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Type & flags
            $table->enum('item_type', ['product', 'service', 'charge'])->default('product');
            $table->tinyInteger('manage_inventory')->default(1);
            $table->tinyInteger('serialized_product')->default(0);

            // Identity
            $table->string('name');
            $table->string('hsn')->nullable();
            $table->string('sku')->nullable();           // item_code
            $table->string('category')->nullable();
            $table->string('unit')->default('Pcs');      // unit of measurement
            $table->string('tax_category')->nullable();
            $table->string('stock_category')->nullable();

            // Descriptions
            $table->string('short_description')->nullable();
            $table->string('invoice_description', 4000)->nullable();

            // Sales Price
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->enum('sale_price_type', ['with_tax', 'without_tax'])->default('with_tax');
            $table->decimal('sale_discount', 10, 2)->default(0);
            $table->enum('sale_discount_type', ['percent', 'amount'])->default('percent');

            // Purchase Price
            $table->decimal('purchase_price', 15, 2)->default(0);

            // Opening Stock
            $table->decimal('opening_stock_qty', 15, 3)->default(0);
            $table->decimal('opening_stock_cost', 15, 2)->default(0);
            $table->text('serial_numbers')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('items'); }
};
