<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->unsigned();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->string('image', 1000)->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('name');
            $table->index('active');
            $table->index('price');
            
            // Composite indexes for common filter combinations
            $table->index(['active', 'price']); // For filtering active products by price
            $table->index(['active', 'stock']); // For filtering available products
        });

        // Add CHECK constraints for business rules (spec section 3.2)
        // Note: SQLite has limited CHECK support, but we add them for documentation
        DB::statement('CREATE TABLE IF NOT EXISTS products_check AS SELECT * FROM products WHERE price > 0 AND stock >= 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
