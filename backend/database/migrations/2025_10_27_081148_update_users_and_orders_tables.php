<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add Telegram fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_id')->nullable()->unique()->after('id');
            $table->string('username')->nullable()->after('telegram_id');
            $table->string('first_name')->nullable()->after('username');
            $table->string('last_name')->nullable()->after('first_name');
            $table->enum('role', ['buyer', 'employee'])->nullable()->after('last_name');
            $table->unsignedBigInteger('organization_id')->nullable()->after('role');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->string('password')->nullable()->change();
        });

        // Update orders table to support new structure
        Schema::table('orders', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['user_id']);
            
            // Add new fields
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->unsignedBigInteger('buyer_id')->nullable()->after('organization_id');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('set null');
            $table->enum('status', ['draft', 'pending', 'submitted'])->default('draft')->after('total');
            $table->timestamp('submitted_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropColumn(['organization_id', 'buyer_id', 'status', 'submitted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['telegram_id', 'username', 'first_name', 'last_name', 'role', 'organization_id']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
