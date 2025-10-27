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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название ресторана
            $table->string('legal_name'); // Юридическое название
            $table->string('inn', 12); // ИНН
            $table->string('kpp', 9)->nullable(); // КПП
            $table->string('ogrn', 15); // ОГРН
            $table->text('address_legal'); // Юридический адрес
            $table->text('address_actual'); // Фактический адрес
            $table->string('phone'); // Телефон
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Закупщик
            $table->timestamps();
        });

        // Таблица связи многие-ко-многим: ресторан - сотрудники
        Schema::create('restaurant_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // employee
            $table->timestamps();
            
            // Уникальная связь ресторан-сотрудник
            $table->unique(['restaurant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_employees');
        Schema::dropIfExists('restaurants');
    }
};
