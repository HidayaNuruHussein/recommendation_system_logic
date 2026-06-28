<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            // ✅ Add parent_id for hierarchy
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('categories')
                    ->onDelete('cascade');
            }
            
            // ✅ Add category group
            if (!Schema::hasColumn('categories', 'category_group')) {
                $table->string('category_group')
                    ->nullable()
                    ->after('parent_id');
            }
            
            // ✅ Add tags for similarity matching
            if (!Schema::hasColumn('categories', 'tags')) {
                $table->json('tags')
                    ->nullable()
                    ->after('category_group');
            }
            
            // ✅ Add indexes for performance
            if (!Schema::hasIndex('categories', 'categories_parent_id_index')) {
                $table->index('parent_id');
            }
            if (!Schema::hasIndex('categories', 'categories_category_group_index')) {
                $table->index('category_group');
            }
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            $table->dropColumn(['parent_id', 'category_group', 'tags']);
        });
    }
};