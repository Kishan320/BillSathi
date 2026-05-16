<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['contact_id']);
            
            // Add new foreign key to vendors table
            $table->foreign('contact_id')
                ->references('id')
                ->on('vendors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['contact_id']);
            
            // Restore the old foreign key to contacts
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->nullOnDelete();
        });
    }
};
