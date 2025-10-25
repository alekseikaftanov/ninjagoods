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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->string('admin_name')->default('Admin');
            $table->string('action'); // created, updated, deleted
            $table->string('entity_type'); // category, product
            $table->string('entity_name');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('changes')->nullable(); // Старые и новые значения
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
