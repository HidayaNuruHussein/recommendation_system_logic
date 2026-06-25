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
        schema::create('recommendations', function(Blueprint
        $table){
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('recommended_product_id');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
