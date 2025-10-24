# Telegram MiniApp для Ninja Goods

## Обзор
Создано полнофункциональное приложение покупателя (Telegram MiniApp) для заказа микрозелени с современным интерфейсом и интеграцией с Telegram WebApp API.

## Технологии

### Frontend:
- **Vue.js 3** - современный фреймворк
- **TypeScript** - типизированный JavaScript
- **Vite** - быстрый сборщик
- **Pinia** - управление состоянием
- **Vue Router** - маршрутизация
- **Axios** - HTTP клиент

### Telegram Integration:
- **@twa-dev/sdk** - официальный SDK для Telegram WebApp
- **Telegram WebApp API** - интеграция с Telegram

## Структура приложения

```
miniapp-frontend/
├── src/
│   ├── components/          # Переиспользуемые компоненты
│   ├── views/              # Страницы приложения
│   │   ├── Home.vue        # Главная страница
│   │   ├── Categories.vue  # Категории товаров
│   │   ├── Products.vue    # Список товаров
│   │   ├── ProductDetail.vue # Детали товара
│   │   ├── Cart.vue        # Корзина
│   │   ├── Checkout.vue    # Оформление заказа
│   │   └── OrderSuccess.vue # Успешный заказ
│   ├── stores/             # Pinia stores
│   │   ├── auth.ts         # Аутентификация
│   │   ├── cart.ts         # Корзина
│   │   └── catalog.ts      # Каталог товаров
│   ├── router/             # Маршрутизация
│   ├── types/              # TypeScript типы
│   ├── utils/              # Утилиты
│   │   ├── telegram.ts     # Telegram WebApp API
│   │   └── api.ts          # Backend API
│   ├── App.vue             # Главный компонент
│   └── main.ts             # Точка входа
├── index.html              # HTML шаблон
├── package.json            # Зависимости
├── vite.config.ts          # Конфигурация Vite
└── tsconfig.json           # Конфигурация TypeScript
```

## Функциональность

### 🔐 Аутентификация
- **Автоматическая регистрация** через Telegram ID
- **Интеграция с Telegram WebApp** - получение данных пользователя
- **Безопасная аутентификация** - использование Telegram initData

### 📁 Каталог товаров
- **Иерархические категории** - главные и подкатегории
- **Список товаров** - с фильтрацией по категориям
- **Детальная информация** - фото, описание, цена, минимальный заказ
- **Поиск и навигация** - удобная навигация по каталогу

### 🛒 Корзина и заказы
- **Добавление товаров** - с контролем минимального заказа
- **Управление количеством** - увеличение/уменьшение
- **Расчет стоимости** - автоматический подсчет итогов
- **Оформление заказа** - с контактной информацией
- **Подтверждение заказа** - страница успеха

### 🎨 Пользовательский интерфейс
- **Telegram-стиль** - адаптация под дизайн Telegram
- **Адаптивный дизайн** - работает на всех устройствах
- **Темная/светлая тема** - автоматическое определение темы
- **Тактильная обратная связь** - вибрация при взаимодействии

## API Integration

### Backend Endpoints:
```typescript
// Аутентификация
POST /api/auth/telegram

// Категории
GET /api/categories

// Товары
GET /api/products
GET /api/products?category_id={id}
GET /api/products/{id}

// Заказы
POST /api/orders
```

### Telegram WebApp API:
```typescript
// Получение данных пользователя
const user = getTelegramUser()

// Управление кнопками
showMainButton('Оформить заказ', handleOrder)
showBackButton(handleBack)

// Тактильная обратная связь
hapticFeedback('success')
hapticFeedback('error')
```

## Маршруты

| Маршрут | Компонент | Описание |
|---------|-----------|----------|
| `/` | Home | Главная страница с популярными товарами |
| `/categories` | Categories | Список категорий товаров |
| `/products/:categoryId?` | Products | Товары (все или по категории) |
| `/product/:id` | ProductDetail | Детали товара |
| `/cart` | Cart | Корзина покупок |
| `/checkout` | Checkout | Оформление заказа |
| `/order-success` | OrderSuccess | Подтверждение заказа |

## Stores (Pinia)

### Auth Store
```typescript
interface AuthStore {
  user: User | null
  isAuthenticated: boolean
  authenticateWithTelegram(): Promise<boolean>
  logout(): void
}
```

### Cart Store
```typescript
interface CartStore {
  items: CartItem[]
  totalItems: number
  totalPrice: number
  addToCart(product: Product, quantity: number): void
  updateQuantity(productId: number, quantity: number): void
  removeFromCart(productId: number): void
  clearCart(): void
}
```

### Catalog Store
```typescript
interface CatalogStore {
  categories: Category[]
  products: Product[]
  loadCategories(): Promise<void>
  loadProducts(): Promise<void>
  loadProductsByCategory(categoryId: number): Promise<void>
  getProductById(id: number): Promise<Product | null>
}
```

## Telegram WebApp Features

### Инициализация:
```typescript
// Автоматическая инициализация при загрузке
initTelegramWebApp()
```

### Управление интерфейсом:
```typescript
// Главная кнопка
showMainButton('Оформить заказ', () => {
  router.push('/checkout')
})

// Кнопка "Назад"
showBackButton(() => {
  router.back()
})
```

### Обратная связь:
```typescript
// Успешное действие
hapticFeedback('success')

// Ошибка
hapticFeedback('error')

// Легкое касание
hapticFeedback('light')
```

## Стилизация

### CSS Variables:
```css
:root {
  --tg-theme-bg-color: #ffffff;
  --tg-theme-text-color: #000000;
  --tg-theme-button-color: #007AFF;
  --tg-theme-button-text-color: #ffffff;
  --tg-theme-secondary-bg-color: #f8f8f8;
  --tg-theme-hint-color: #999999;
}
```

### Адаптивность:
- **Мобильные устройства** - оптимизирован для смартфонов
- **Планшеты** - адаптивная сетка товаров
- **Десктоп** - работает в браузере для тестирования

## Запуск приложения

### Разработка:
```bash
cd miniapp-frontend
npm run dev
```

### Сборка:
```bash
npm run build
```

### Предварительный просмотр:
```bash
npm run preview
```

## Интеграция с Telegram Bot

### Настройка WebApp:
1. Создать Telegram Bot через @BotFather
2. Настроить WebApp URL: `https://yourdomain.com/miniapp`
3. Добавить команду `/start` с кнопкой "Открыть приложение"

### Пример Bot Command:
```javascript
// В Telegram Bot
bot.command('start', (ctx) => {
  ctx.reply('Добро пожаловать в Ninja Goods!', {
    reply_markup: {
      inline_keyboard: [[
        {
          text: '🛍️ Открыть каталог',
          web_app: { url: 'https://yourdomain.com/miniapp' }
        }
      ]]
    }
  })
})
```

## Тестирование

### В браузере:
- Откройте `http://localhost:3000`
- Приложение работает без Telegram API
- Используйте mock данные для тестирования

### В Telegram:
- Разверните приложение на HTTPS сервере
- Настройте WebApp URL в боте
- Протестируйте в Telegram клиенте

## Безопасность

### Telegram Authentication:
- Использование `initData` для аутентификации
- Проверка подписи данных на backend
- Защита от подделки данных пользователя

### API Security:
- Передача Telegram initData в заголовках
- Валидация данных на backend
- CORS настройки для Telegram доменов

## Производительность

### Оптимизации:
- **Lazy loading** - загрузка компонентов по требованию
- **Code splitting** - разделение кода на чанки
- **Image optimization** - оптимизация изображений товаров
- **Caching** - кэширование API запросов

### Bundle Size:
- Минимальный размер bundle
- Tree shaking неиспользуемого кода
- Оптимизация зависимостей

## Статус
✅ **Завершено**: Telegram MiniApp полностью создано
✅ **Протестировано**: Все функции работают корректно
✅ **Интегрировано**: С backend API и Telegram WebApp
✅ **Документировано**: Полная документация создана
✅ **Запущено**: Приложение доступно на порту 3000
