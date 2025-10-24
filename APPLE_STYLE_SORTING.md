# Apple-стиль компонент сортировки

## Обзор
Переработанный компонент сортировки в максимально Apple-стиле с элегантным дизайном, минималистичным интерфейсом и плавными анимациями.

## Дизайн-принципы Apple

### 🎨 Визуальные особенности:
- **Минимализм** - убраны лишние элементы и подписи
- **Элегантность** - тонкие границы и мягкие тени
- **Прозрачность** - backdrop-filter для эффекта стекла
- **Типографика** - четкие, читаемые шрифты
- **Цветовая схема** - приглушенные тона с акцентами

### ✨ Интерактивность:
- **Плавные переходы** - все анимации 0.2s ease
- **Hover эффекты** - тонкая подсветка при наведении
- **Focus состояния** - четкие индикаторы фокуса
- **Активные состояния** - визуальная обратная связь

## Компоненты интерфейса

### 📊 Поле сортировки:
```vue
<div class="sort-field">
  <div class="sort-icon">📊</div>
  <select class="sort-select">
    <option value="id">ID</option>
    <option value="created_at">Дата</option>
    <option value="name">Название</option>
    <option value="price">Цена</option>
  </select>
</div>
```

**Особенности:**
- ✅ **Прозрачный фон** - сливается с контейнером
- ✅ **Кастомная стрелка** - SVG иконка вместо стандартной
- ✅ **Компактный размер** - минимум места
- ✅ **Hover эффект** - тонкая подсветка

### 🔄 Кнопки порядка:
```vue
<div class="sort-order">
  <button class="sort-order-btn" :class="{ 'active': sortOrder === 'asc' }">
    ↑
  </button>
  <button class="sort-order-btn" :class="{ 'active': sortOrder === 'desc' }">
    ↓
  </button>
</div>
```

**Особенности:**
- ✅ **Сегментированный контрол** - как в iOS
- ✅ **Активное состояние** - синий фон с тенью
- ✅ **Компактные стрелки** - Unicode символы
- ✅ **Toggle функционал** - переключение одним кликом

### 🔄 Кнопка сброса:
```vue
<button class="reset-btn">
  <span class="reset-icon">↻</span>
</button>
```

**Особенности:**
- ✅ **Круглая форма** - минималистичный дизайн
- ✅ **Анимация поворота** - при hover
- ✅ **Иконка Unicode** - без внешних зависимостей
- ✅ **Компактный размер** - 32x32px

## CSS стили

### Основной контейнер:
```css
.sort-section {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
```

**Apple-элементы:**
- 🍎 **Backdrop blur** - эффект матового стекла
- 🍎 **Тонкая тень** - мягкое освещение
- 🍎 **Скругленные углы** - radius-md
- 🍎 **Тонкая граница** - 1px solid

### Select элемент:
```css
.sort-select {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: var(--font-size-small);
  font-weight: 500;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg...");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 12px;
  padding-right: 28px;
}
```

**Apple-элементы:**
- 🍎 **Прозрачный фон** - сливается с контейнером
- 🍎 **Кастомная стрелка** - SVG вместо стандартной
- 🍎 **Убраны границы** - чистый вид
- 🍎 **Правильный отступ** - для кастомной стрелки

### Кнопки порядка:
```css
.sort-order {
  display: flex;
  gap: 2px;
  background: var(--bg-primary);
  border-radius: var(--radius-sm);
  padding: 2px;
  border: 1px solid var(--border-color);
}

.sort-order-btn.active {
  background-color: var(--accent-blue);
  color: white;
  box-shadow: 0 1px 3px rgba(0, 122, 255, 0.3);
}
```

**Apple-элементы:**
- 🍎 **Сегментированный контрол** - как в iOS
- 🍎 **Активное состояние** - синий с тенью
- 🍎 **Минимальный gap** - 2px между кнопками
- 🍎 **Внутренний padding** - 2px для контейнера

### Кнопка сброса:
```css
.reset-btn:hover {
  background-color: var(--accent-blue-light);
  color: var(--accent-blue);
  transform: rotate(180deg);
}

.reset-btn:hover .reset-icon {
  transform: rotate(180deg);
}
```

**Apple-элементы:**
- 🍎 **Двойная анимация** - кнопка и иконка
- 🍎 **Плавный поворот** - 180 градусов
- 🍎 **Hover состояние** - тонкая подсветка
- 🍎 **Круглая форма** - минималистично

## Адаптивность

### Планшеты (768px):
```css
@media (max-width: 768px) {
  .sort-section {
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
  }
  
  .sort-field {
    flex: 1;
    min-width: 120px;
  }
}
```

### Мобильные (480px):
```css
@media (max-width: 480px) {
  .sort-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .sort-field {
    justify-content: center;
  }
  
  .sort-order {
    justify-content: center;
  }
}
```

## Функциональность

### JavaScript логика:
```typescript
const toggleOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  emit('sortChange', sortBy.value, sortOrder.value)
}

const resetSort = () => {
  sortBy.value = props.defaultSortBy
  sortOrder.value = props.defaultSortOrder
  emit('sortChange', sortBy.value, sortOrder.value)
}
```

**Особенности:**
- ✅ **Toggle функционал** - переключение порядка одним кликом
- ✅ **Автоматический emit** - уведомление родительского компонента
- ✅ **Сброс к умолчанию** - возврат к начальным значениям
- ✅ **Reactive обновления** - мгновенная реакция на изменения

## Преимущества нового дизайна

### Для пользователя:
- 🎯 **Интуитивно** - понятно без объяснений
- ⚡ **Быстро** - минимум кликов для сортировки
- 🎨 **Красиво** - современный Apple-стиль
- 📱 **Удобно** - работает на всех устройствах

### Для разработчика:
- 🔧 **Переиспользуемо** - легко интегрировать
- 🎨 **Настраиваемо** - гибкие пропсы
- 🐛 **Отлаживаемо** - четкая логика
- 📊 **Расширяемо** - легко добавить новые поля

## Сравнение с предыдущей версией

### Было:
- ❌ **Громоздко** - много текста и лейблов
- ❌ **Неэлегантно** - стандартные элементы
- ❌ **Медленно** - много кликов для изменения
- ❌ **Не Apple** - обычный веб-стиль

### Стало:
- ✅ **Компактно** - минимум элементов
- ✅ **Элегантно** - кастомные стили
- ✅ **Быстро** - один клик для переключения
- ✅ **Apple-стиль** - современный дизайн

## Статус
✅ **Завершено**: Apple-стиль компонент полностью реализован
✅ **Протестировано**: Все функции работают корректно
✅ **Адаптивно**: Работает на всех устройствах
✅ **Интегрировано**: В админ-панель с новым дизайном
