# Ninja Goods - Telegram Miniapp

Telegram miniapp для заказа микрозелени с админ-панелью для управления каталогом.

## Структура проекта

```
NinjaGoods/
├── backend/          # Laravel API
├── admin-frontend/   # Vue.js админ-панель
├── miniapp-frontend/ # Vue.js Telegram miniapp
├── docker/          # Docker конфигурация
└── docs/            # Документация
```

## Технологический стек

- **Backend**: Laravel + PostgreSQL
- **Admin Frontend**: Vue.js 3 + Vite
- **Miniapp Frontend**: Vue.js 3 + Vite + Telegram WebApp
- **Deployment**: Docker + Render

## Быстрый старт

1. Клонируйте репозиторий
2. Настройте Docker окружение
3. Запустите `docker-compose up`
4. Откройте админ-панель: http://localhost:3000
5. Откройте miniapp: http://localhost:3001

## Этапы разработки

### Этап 1 (MVP)
- ✅ Регистрация через Telegram ID
- ✅ Категории и товары
- ✅ Корзина и заказы
- ✅ Админ-панель с CRUD операциями

### Этап 2 (Расширение)
- Просмотр заказов в админке
- История заказов пользователей
- Telegram уведомления
- Импорт/экспорт товаров
