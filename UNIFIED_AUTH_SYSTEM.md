# Единая система авторизации для всех клиентов

## Обзор изменений

Упразднена система B2C авторизации. **Все клиенты** (и обычные покупатели, и B2B) теперь используют единую систему авторизации с JWT токенами и ролями.

---

## 🔐 Архитектура авторизации

### Единая точка входа
**Endpoint:** `POST /api/auth/telegram`

Все клиенты (customer, buyer, employee) авторизуются через один endpoint.

### Роли пользователей

| Роль | Описание | Возможности |
|------|----------|-------------|
| **customer** | Обычный покупатель (B2C) | Просмотр товаров, создание простых заказов |
| **buyer** | Закупщик организации (B2B) | Создание организации, коллективные заказы, управление сотрудниками |
| **employee** | Сотрудник организации (B2B) | Добавление позиций в коллективные заказы |

---

## 🚀 Процесс авторизации

### Шаг 1: Telegram Authentication

```typescript
POST /api/auth/telegram
```

**Request:**
```json
{
  "id": 123456,
  "first_name": "Иван",
  "last_name": "Петров",
  "username": "ivan",
  "auth_date": 1698765432,
  "hash": "telegram_hash_signature"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "telegram_id": 123456,
      "first_name": "Иван",
      "last_name": "Петров",
      "name": "Иван Петров",
      "role": null,
      "organization_id": null
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "needs_role_selection": true,
    "needs_organization": false
  }
}
```

### Шаг 2: Выбор роли (если needs_role_selection = true)

```typescript
POST /api/auth/role
Authorization: Bearer {jwt_token}
```

**Request:**
```json
{
  "role": "customer" // или "buyer" или "employee"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "role": "customer",
      ...
    },
    "needs_organization": false
  }
}
```

### Шаг 3: JWT Token

**Формат токена:**
```json
{
  "iss": "https://ninjagoods.com",
  "iat": 1698765432,
  "exp": 1699370232,  // 7 дней
  "sub": 1,  // user_id
  "telegram_id": 123456,
  "role": "customer"
}
```

**Срок действия:** 7 дней

---

## 📡 API Endpoints

### Публичные (без авторизации)

```
GET  /api/categories          # Список категорий
GET  /api/products             # Список товаров
GET  /api/products/{id}        # Детали товара
POST /api/auth/telegram        # Авторизация
GET  /api/invites/validate     # Проверка инвайта
```

### Защищенные (требуют JWT)

```
# User Management
POST /api/auth/role            # Установка роли
GET  /api/auth/me              # Информация о пользователе

# Orders (для всех)
GET  /api/orders               # Список заказов
POST /api/orders               # Создание заказа

# B2B Orders (для buyer и employee)
GET  /api/b2b/orders           # Коллективные заказы
POST /api/b2b/orders           # Создание коллективного заказа
GET  /api/b2b/orders/{id}      # Детали заказа
POST /api/b2b/orders/{id}/items    # Добавить позицию
DELETE /api/b2b/orders/{id}/items/{item_id}  # Удалить позицию

# B2B Orders - только для buyer
POST /api/b2b/orders/{id}/submit   # Отправить заказ

# Organization (для buyer и employee)
GET  /api/organization         # Информация об организации
POST /api/organization         # Создать организацию
PUT  /api/organization         # Обновить организацию
POST /api/organization/invite  # Создать инвайт

# Invites
POST /api/invites/join         # Присоединиться по инвайту
```

---

## 🔧 Backend Implementation

### Models

**User Model** (`app/Models/User.php`)

```php
protected $fillable = [
    'name',
    'email',
    'telegram_id',
    'username',
    'first_name',
    'last_name',
    'role',  // customer, buyer, employee
    'organization_id',
];

public function isCustomer(): bool
{
    return $this->role === 'customer';
}

public function isBuyer(): bool
{
    return $this->role === 'buyer';
}

public function isEmployee(): bool
{
    return $this->role === 'employee';
}

public function needsOrganization(): bool
{
    return $this->isBuyer() && is_null($this->organization_id);
}
```

### Middleware

**JWT Authentication** (`app/Http/Middleware/JWTAuth.php`)
- Проверяет наличие и валидность JWT токена
- Декодирует токен и загружает пользователя
- Добавляет пользователя в request

**Role Middleware** (`app/Http/Middleware/Role.php`)
- Проверяет роль пользователя
- Ограничивает доступ к эндпоинтам по ролям

### Controllers

**TelegramAuthController**
- `authenticate()` - авторизация через Telegram
- `setRole()` - установка роли пользователя
- `me()` - получение информации о текущем пользователе
- `generateJWT()` - генерация JWT токена

---

## 💻 Frontend Implementation

### MiniApp Frontend

**API Configuration** (`miniapp-frontend/src/utils/api.ts`)

```typescript
const API_BASE_URL = 'http://localhost:8000/api'

// JWT токен добавляется автоматически к каждому запросу
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('jwt_token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})
```

**Auth Store** (`miniapp-frontend/src/stores/auth.ts`)

```typescript
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('jwt_token'))
  const needsRoleSelection = ref(false)

  const authenticateWithTelegram = async () => {
    // 1. Получить данные из Telegram WebApp
    const telegramUser = getTelegramUser()
    
    // 2. Отправить на backend
    const response = await authAPI.telegram({
      id: telegramUser.id,
      first_name: telegramUser.first_name,
      // ...
    })
    
    // 3. Сохранить токен и пользователя
    user.value = response.data.user
    token.value = response.data.token
    localStorage.setItem('jwt_token', response.data.token)
    needsRoleSelection.value = response.data.needs_role_selection
  }

  const setRole = async (role: 'customer' | 'buyer' | 'employee') => {
    const response = await authAPI.setRole(role)
    user.value = response.data.user
    needsRoleSelection.value = false
  }
})
```

---

## 🔄 Миграция со старой системы

### Что было удалено

1. ❌ **AuthController** (`app/Http/Controllers/Api/AuthController.php`)
   - Старый B2C контроллер с простой авторизацией
   
2. ❌ **TelegramUser** модель
   - Больше не используется, все пользователи в таблице `users`

3. ❌ Старые маршруты:
   ```php
   Route::post('/auth/telegram', [AuthController::class, 'telegram']);
   ```

### Что было изменено

1. ✅ **User** модель
   - Добавлен метод `isCustomer()`
   - Добавлен метод `needsOrganization()`

2. ✅ **TelegramAuthController**
   - Обновлен `setRole()` - добавлена роль `customer`
   - Обновлен `authenticate()` - использует `needsOrganization()`

3. ✅ **API Routes**
   - Единая точка входа `/api/auth/telegram`
   - Все защищенные маршруты под `jwt.auth` middleware
   - Разделение на простые заказы (`/orders`) и B2B заказы (`/b2b/orders`)

4. ✅ **MiniApp Frontend**
   - Обновлен API_BASE_URL (8000 вместо 8001)
   - Добавлена поддержка JWT токенов
   - Обновлен auth store для работы с новой системой

---

## 🧪 Тестирование

### Проверка авторизации

```bash
# 1. Авторизация
curl -X POST http://localhost:8000/api/auth/telegram \
  -H "Content-Type: application/json" \
  -d '{
    "id": 123456,
    "first_name": "Test",
    "last_name": "User",
    "username": "testuser",
    "auth_date": 1698765432,
    "hash": "test_hash"
  }'

# Ответ:
# {
#   "success": true,
#   "data": {
#     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
#     "user": {...},
#     "needs_role_selection": true
#   }
# }

# 2. Установка роли
curl -X POST http://localhost:8000/api/auth/role \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"role": "customer"}'

# 3. Проверка профиля
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}"
```

---

## 📋 Чеклист для разработчиков

### Backend
- [x] Обновлена User модель (добавлены методы для ролей)
- [x] Удален старый AuthController
- [x] Обновлен TelegramAuthController (поддержка роли customer)
- [x] Обновлены маршруты API
- [x] JWT middleware работает корректно
- [x] Role middleware работает корректно

### Frontend (MiniApp)
- [x] Обновлен API_BASE_URL
- [x] Добавлена поддержка JWT токенов
- [x] Обновлен auth store
- [x] Добавлен метод setRole
- [x] Токен сохраняется в localStorage

### База данных
- [x] Таблица `users` содержит поле `role`
- [x] Роль `customer` добавлена в валидацию

---

## 🚨 Важные замечания

1. **Telegram hash verification** временно отключена для тестирования
   - TODO: Включить в production
   
2. **auth_date проверка** активна (24 часа)
   - Токены старше 24 часов отклоняются

3. **JWT срок действия** - 7 дней
   - После истечения нужна повторная авторизация

4. **Development mode** поддерживается
   - Mock данные для локального тестирования

---

## 📞 Контакты

Для вопросов по новой системе авторизации обращайтесь к документации:
- `TELEGRAM_MINIAPP.md` - описание MiniApp
- `TZ_Telegram_Authorization_Extension.md` - техническое задание B2B

---

**Дата обновления:** 27 октября 2025
**Версия:** 2.0.0

