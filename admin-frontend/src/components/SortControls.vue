<template>
  <div class="sort-controls">
    <div class="sort-section">
      <div class="sort-field">
        <div class="sort-icon">📊</div>
        <select 
          v-model="sortBy" 
          @change="handleSortChange"
          class="sort-select"
        >
          <option value="id">ID</option>
          <option value="created_at">Дата</option>
          <option value="name">Название</option>
          <option v-if="showPriceSort" value="price">Цена</option>
        </select>
      </div>
      
      <div class="sort-order">
        <button 
          @click="toggleOrder"
          class="sort-order-btn"
          :class="{ 'active': sortOrder === 'asc' }"
          title="По возрастанию"
        >
          ↑
        </button>
        <button 
          @click="toggleOrder"
          class="sort-order-btn"
          :class="{ 'active': sortOrder === 'desc' }"
          title="По убыванию"
        >
          ↓
        </button>
      </div>
      
      <button 
        @click="resetSort" 
        class="reset-btn"
        title="Сбросить сортировку"
      >
        <span class="reset-icon">↻</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
  showPriceSort?: boolean
  defaultSortBy?: string
  defaultSortOrder?: string
}

const props = withDefaults(defineProps<Props>(), {
  showPriceSort: false,
  defaultSortBy: 'id',
  defaultSortOrder: 'asc'
})

const emit = defineEmits<{
  sortChange: [sortBy: string, sortOrder: string]
}>()

const sortBy = ref(props.defaultSortBy)
const sortOrder = ref(props.defaultSortOrder)

const handleSortChange = () => {
  emit('sortChange', sortBy.value, sortOrder.value)
}

const toggleOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  emit('sortChange', sortBy.value, sortOrder.value)
}

const resetSort = () => {
  sortBy.value = props.defaultSortBy
  sortOrder.value = props.defaultSortOrder
  emit('sortChange', sortBy.value, sortOrder.value)
}

// Отслеживаем изменения пропсов
watch(() => props.defaultSortBy, (newValue) => {
  sortBy.value = newValue
})

watch(() => props.defaultSortOrder, (newValue) => {
  sortOrder.value = newValue
})
</script>

<style scoped>
.sort-controls {
  margin-bottom: var(--spacing-lg);
}

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

.sort-field {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  flex: 1;
}

.sort-icon {
  font-size: 14px;
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.sort-select {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: var(--font-size-small);
  font-weight: 500;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 80px;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 12px;
  padding-right: 28px;
}

.sort-select:hover {
  background-color: var(--accent-blue-light);
}

.sort-select:focus {
  outline: none;
  background-color: var(--accent-blue-light);
  box-shadow: 0 0 0 2px var(--accent-blue);
}

.sort-order {
  display: flex;
  gap: 2px;
  background: var(--bg-primary);
  border-radius: var(--radius-sm);
  padding: 2px;
  border: 1px solid var(--border-color);
}

.sort-order-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 12px;
  font-weight: 600;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: calc(var(--radius-sm) - 2px);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sort-order-btn:hover {
  background-color: var(--accent-blue-light);
  color: var(--accent-blue);
}

.sort-order-btn.active {
  background-color: var(--accent-blue);
  color: white;
  box-shadow: 0 1px 3px rgba(0, 122, 255, 0.3);
}

.reset-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 14px;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.reset-btn:hover {
  background-color: var(--accent-blue-light);
  color: var(--accent-blue);
  transform: rotate(180deg);
}

.reset-icon {
  font-size: 16px;
  font-weight: 600;
  transition: transform 0.3s ease;
}

.reset-btn:hover .reset-icon {
  transform: rotate(180deg);
}

/* Адаптивность */
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
  
  .sort-order {
    order: 3;
  }
  
  .reset-btn {
    order: 4;
  }
}

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
</style>
