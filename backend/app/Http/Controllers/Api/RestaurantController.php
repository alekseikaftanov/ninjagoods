<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    /**
     * Получить все рестораны текущего пользователя
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isBuyer()) {
            // Закупщик видит свои рестораны
            $restaurants = $user->ownedRestaurants()->with('employees')->get();
        } elseif ($user->isEmployee()) {
            // Сотрудник видит рестораны где работает
            $restaurants = $user->restaurants()->with('buyer')->get();
        } else {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        return response()->json(['data' => $restaurants]);
    }

    /**
     * Создать новый ресторан (только для закупщика)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isBuyer()) {
            return response()->json(['message' => 'Только закупщики могут создавать рестораны'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'inn' => 'required|string|size:12',
            'kpp' => 'nullable|string|size:9',
            'ogrn' => 'required|string|size:15',
            'address_legal' => 'required|string',
            'address_actual' => 'required|string',
            'phone' => 'required|string|max:20',
        ]);

        $validated['created_by'] = $user->id;

        $restaurant = Restaurant::create($validated);

        return response()->json([
            'message' => 'Ресторан успешно создан',
            'data' => $restaurant,
        ], 201);
    }

    /**
     * Получить информацию о конкретном ресторане
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $restaurant = Restaurant::with(['buyer', 'employees'])->findOrFail($id);

        // Проверяем доступ
        if ($user->isBuyer() && $restaurant->created_by !== $user->id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        if ($user->isEmployee() && !$restaurant->hasEmployee($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        return response()->json(['data' => $restaurant]);
    }

    /**
     * Обновить ресторан (только владелец)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $restaurant = Restaurant::findOrFail($id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'legal_name' => 'sometimes|required|string|max:255',
            'inn' => 'sometimes|required|string|size:12',
            'kpp' => 'nullable|string|size:9',
            'ogrn' => 'sometimes|required|string|size:15',
            'address_legal' => 'sometimes|required|string',
            'address_actual' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'message' => 'Ресторан успешно обновлен',
            'data' => $restaurant,
        ]);
    }

    /**
     * Удалить ресторан (только владелец)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $restaurant = Restaurant::findOrFail($id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $restaurant->delete();

        return response()->json([
            'message' => 'Ресторан успешно удален',
        ]);
    }

    /**
     * Добавить сотрудника в ресторан
     */
    public function addEmployee(Request $request, $id)
    {
        $user = $request->user();
        $restaurant = Restaurant::findOrFail($id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $employee = User::findOrFail($validated['user_id']);

        if (!$employee->isEmployee()) {
            return response()->json(['message' => 'Пользователь не является сотрудником'], 400);
        }

        $restaurant->addEmployee($employee);

        return response()->json([
            'message' => 'Сотрудник успешно добавлен',
            'data' => $restaurant->load('employees'),
        ]);
    }

    /**
     * Удалить сотрудника из ресторана
     */
    public function removeEmployee(Request $request, $id, $employeeId)
    {
        $user = $request->user();
        $restaurant = Restaurant::findOrFail($id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $employee = User::findOrFail($employeeId);
        $restaurant->removeEmployee($employee);

        return response()->json([
            'message' => 'Сотрудник успешно удален',
            'data' => $restaurant->load('employees'),
        ]);
    }

    /**
     * Получить сотрудников ресторана
     */
    public function employees(Request $request, $id)
    {
        $user = $request->user();
        $restaurant = Restaurant::findOrFail($id);

        // Проверяем доступ
        if ($user->isBuyer() && !$restaurant->isOwnedBy($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        if ($user->isEmployee() && !$restaurant->hasEmployee($user)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        return response()->json([
            'data' => $restaurant->employees,
        ]);
    }
}
