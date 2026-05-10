<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE items MODIFY item_type ENUM('product', 'service', 'charge') NOT NULL DEFAULT 'product'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE items SET item_type = 'service' WHERE item_type = 'charge'");
            DB::statement("ALTER TABLE items MODIFY item_type ENUM('product', 'service') NOT NULL DEFAULT 'product'");
        }
    }
};
