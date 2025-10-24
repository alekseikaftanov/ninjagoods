<template>
  <div class="categories">
    <div class="header">
      <h1>📁 Категории</h1>
    </div>

    <div v-if="catalogStore.isLoading" class="loading">
      <p>Загрузка категорий...</p>
    </div>

    <div v-else-if="catalogStore.error" class="error">
      <p>{{ catalogStore.error }}</p>
      <button @click="loadCategories" class="btn btn-primary">
        Попробовать снова
      </button>
    </div>

    <div v-else class="categories-list">
      <div
        v-for="category in mainCategories"
        :key="category.id"
        class="category-item"
        @click="toggleCategory(category.id)"
      >
        <div class="category-main">
          <div class="category-icon">🌿</div>
          <div class="category-info">
            <h3>{{ category.name }}</h3>
            <p>{{ category.products?.length || 0 }} товаров</p>
          </div>
          <div class="category-arrow">
            {{ expandedCategories.includes(category.id) ? '▼' : '▶' }}
          </div>
        </div>

        <!-- Подкатегории -->
        <div
          v-if="expandedCategories.includes(category.id)"
          class="subcategories"
        >
          <div
            v-for="subcategory in getSubCategories(category.id)"
            :key="subcategory.id"
            class="subcategory-item"
            @click.stop="goToProducts(subcategory.id)"
          >
            <div class="subcategory-icon">🌱</div>
            <div class="subcategory-info">
              <h4>{{ subcategory.name }}</h4>
              <p>{{ subcategory.products?.length || 0 }} товаров</p>
            </div>
            <div class="subcategory-arrow">▶</div>
          </div>
        </div>
      </div>

      <!-- Если нет подкатегорий, показываем кнопку для перехода к товарам -->
      <div
        v-for="category in mainCategories.filter(c => !getSubCategories(c.id).length)"
        :key="`direct-${category.id}`"
        class="category-item direct"
        @click="goToProducts(category.id)"
      >
        <div class="category-main">
          <div class="category-icon">🌿</div>
          <div class="category-info">
            <h3>{{ category.name }}</h3>
            <p>{{ category.products?.length || 0 }} товаров</p>
          </div>
          <div class="category-arrow">▶</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCatalogStore } from '../stores/catalog'

const router = useRouter()
const catalogStore = useCatalogStore()

const expandedCategories = ref<number[]>([])

const mainCategories = computed(() => catalogStore.getMainCategories())

const getSubCategories = (parentId: number) => {
  return catalogStore.getSubCategories(parentId)
}

const toggleCategory = (categoryId: number) => {
  const index = expandedCategories.value.indexOf(categoryId)
  if (index > -1) {
    expandedCategories.value.splice(index, 1)
  } else {
    expandedCategories.value.push(categoryId)
  }
}

const goToProducts = (categoryId: number) => {
  router.push(`/products/${categoryId}`)
}

const loadCategories = async () => {
  await catalogStore.loadCategories()
}

onMounted(() => {
  loadCategories()
})
</script>

<style scoped>
.categories {
  max-width: 100%;
}

.header {
  margin-bottom: 24px;
}

.categories-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.category-item {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.category-item:hover {
  background: var(--tg-theme-hint-color, #e0e0e0);
}

.category-item.direct {
  background: var(--tg-theme-button-color, #007AFF);
  color: white;
}

.category-item.direct:hover {
  opacity: 0.9;
}

.category-main {
  display: flex;
  align-items: center;
  padding: 16px;
  gap: 12px;
}

.category-icon {
  font-size: 24px;
  width: 40px;
  text-align: center;
}

.category-info {
  flex: 1;
}

.category-info h3 {
  font-size: 16px;
  margin-bottom: 4px;
}

.category-info p {
  font-size: 14px;
  opacity: 0.7;
}

.category-arrow {
  font-size: 16px;
  opacity: 0.6;
}

.subcategories {
  background: var(--tg-theme-bg-color, #ffffff);
  border-top: 1px solid var(--tg-theme-hint-color, #e0e0e0);
}

.subcategory-item {
  display: flex;
  align-items: center;
  padding: 12px 16px 12px 48px;
  gap: 12px;
  border-bottom: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.subcategory-item:hover {
  background: var(--tg-theme-secondary-bg-color, #f0f0f0);
}

.subcategory-item:last-child {
  border-bottom: none;
}

.subcategory-icon {
  font-size: 20px;
  width: 32px;
  text-align: center;
}

.subcategory-info {
  flex: 1;
}

.subcategory-info h4 {
  font-size: 14px;
  margin-bottom: 2px;
}

.subcategory-info p {
  font-size: 12px;
  opacity: 0.7;
}

.subcategory-arrow {
  font-size: 14px;
  opacity: 0.6;
}

.loading, .error {
  text-align: center;
  padding: 32px;
}

.error {
  color: #FF3B30;
}

@media (max-width: 480px) {
  .category-main {
    padding: 12px;
  }
  
  .subcategory-item {
    padding: 10px 12px 10px 40px;
  }
}
</style>
