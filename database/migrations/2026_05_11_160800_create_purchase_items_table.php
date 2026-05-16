<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->decimal('qty', 15, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->json('taxes')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('vendor_name')->nullable()->after('contact_id');
            $table->string('warehouse')->nullable()->after('vendor_name');
            $table->string('payment_terms')->nullable()->after('notes');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_items');
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['vendor_name', 'warehouse', 'payment_terms']);
        });
    }
};
