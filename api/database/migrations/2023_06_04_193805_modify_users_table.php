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
        // add store_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_store')->nullable()->references('id')->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop store_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_store']);
            $table->dropColumn('current_store');
        });
    }
};
