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
        // Убираем organization_id из users - теперь связь через restaurant_employees
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        // Убираем organization_id из orders перед удалением таблицы
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });

        // Обновляем invites - привязка к ресторану вместо организации
        Schema::table('invites', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
            $table->foreignId('restaurant_id')->after('id')->constrained()->onDelete('cascade');
        });

        // Удаляем старую таблицу organizations (после удаления всех связей)
        Schema::dropIfExists('organizations');

        // Добавляем restaurant_id в orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Восстанавливаем organizations
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name');
            $table->string('inn', 12);
            $table->string('kpp', 9)->nullable();
            $table->string('ogrn', 15);
            $table->text('address_legal');
            $table->text('address_actual');
            $table->string('phone');
            $table->string('email');
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Восстанавливаем organization_id в users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('role')->constrained()->onDelete('set null');
        });

        // Удаляем restaurant_id из orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });

        // Восстанавливаем organization_id в invites
        Schema::table('invites', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
            $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
        });
    }
};
