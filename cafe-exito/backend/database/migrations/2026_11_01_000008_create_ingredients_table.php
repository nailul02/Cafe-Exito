<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('category', 60)->default('Bahan Makanan');  // tab kategori bahan di Figma
            $table->string('unit', 20);
            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('min_stock', 12, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index('category');
        });
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_stock_check CHECK (stock >= 0)');
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_min_check CHECK (min_stock >= 0)');
        DB::statement('CREATE UNIQUE INDEX ingredients_name_unique ON ingredients (lower(name)) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
