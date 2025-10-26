<template>
  <div class="products">
    <div class="header">
      <h1>🛍️ Товары</h1>
      <div v-if="currentCategory" class="category-info">
        <p>{{ currentCategory.name }}</p>
      </div>
    </div>

    <div v-if="catalogStore.isLoading" class="loading">
      <p>Загрузка товаров...</p>
    </div>

    <div v-else-if="catalogStore.error" class="error">
      <p>{{ catalogStore.error }}</p>
      <button @click="loadProducts" class="btn btn-primary">
        Попробовать снова
      </button>
    </div>

    <div v-else-if="products.length === 0" class="empty">
      <p>Товары не найдены</p>
      <router-link to="/categories" class="btn btn-primary">
        Вернуться к категориям
      </router-link>
    </div>

    <div v-else class="products-grid">
      <div
        v-for="product in products"
        :key="product.id"
        class="product-card"
      >
        <img :src="product.photo_url" :alt="product.name" class="product-image" />
        <div class="product-info">
          <h3 @click="goToProduct(product.id)" class="product-title">{{ product.name }}</h3>
          <p class="product-description">{{ product.description }}</p>
          <div class="product-details">
            <span class="product-price">{{ product.price }} ₽</span>
            <span class="product-unit">за {{ product.unit }}</span>
          </div>
          <div class="product-min-order">
            Мин. заказ: {{ product.min_order }} {{ product.unit }}
          </div>
        </div>
        <div class="product-actions">
          <button
            v-if="!cartStore.isInCart(product.id)"
            @click.stop="addToCart(product)"
            class="btn btn-primary btn-small add-to-cart-btn"
          >
            В корзину
          </button>
          <div v-else class="quantity-controls">
            <button
              @click.stop="decreaseQuantity(product.id)"
              class="btn btn-secondary btn-small"
            >
              -
            </button>
            <span class="quantity">{{ cartStore.getQuantity(product.id) }}</span>
            <button
              @click.stop="increaseQuantity(product.id)"
              class="btn btn-secondary btn-small"
            >
              +
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCatalogStore } from '../stores/catalog'
import { useCartStore } from '../stores/cart'
import { hapticFeedback } from '../utils/telegram'

const route = useRoute()
const router = useRouter()
const catalogStore = useCatalogStore()
const cartStore = useCartStore()

const currentCategory = ref<any>(null)

const products = computed(() => catalogStore.products)

const loadProducts = async () => {
  const categoryId = route.params.categoryId as string
  
  if (categoryId) {
    // Загружаем товары конкретной категории
    await catalogStore.loadProductsByCategory(parseInt(categoryId))
    
    // Находим категорию для отображения
    currentCategory.value = catalogStore.categories.find(c => c.id === parseInt(categoryId))
  } else {
    // Загружаем все товары
    await catalogStore.loadProducts()
    currentCategory.value = null
  }
}

const goToProduct = (productId: number) => {
  router.push(`/product/${productId}`)
}

const addToCart = (product: any) => {
  cartStore.addItem(product, product.min_order)
  hapticFeedback('light')
}

const increaseQuantity = (productId: number) => {
  const currentQuantity = cartStore.getQuantity(productId)
  cartStore.updateQuantity(productId, currentQuantity + 1)
  hapticFeedback('light')
}

const decreaseQuantity = (productId: number) => {
  const currentQuantity = cartStore.getQuantity(productId)
  const product = catalogStore.products.find(p => p.id === productId)
  
  if (product && currentQuantity > product.min_order) {
    cartStore.updateQuantity(productId, currentQuantity - 1)
    hapticFeedback('light')
  } else if (product && currentQuantity === product.min_order) {
    // Удаляем товар из корзины, если количество равно минимальному заказу
    cartStore.removeItem(productId)
    hapticFeedback('medium')
  }
}

// Отслеживаем изменения параметров маршрута
watch(() => route.params.categoryId, () => {
  loadProducts()
})

onMounted(() => {
  loadProducts()
})
</script>

<style scoped>
.products {
  max-width: 100%;
}

.header {
  margin-bottom: 24px;
}

.category-info {
  margin-top: 8px;
  padding: 12px;
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 8px;
}

.category-info p {
  font-size: 14px;
  color: var(--tg-theme-hint-color, #666666);
  margin: 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.product-card {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.product-card:hover {
  transform: translateY(-2px);
}

.product-image {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.product-info {
  padding: 12px;
}

.product-info h3 {
  font-size: 14px;
  margin-bottom: 6px;
  line-height: 1.3;
}

.product-title {
  cursor: pointer;
  transition: color 0.2s ease;
}

.product-title:hover {
  color: var(--tg-theme-button-color, #007AFF);
}

.product-description {
  font-size: 12px;
  color: var(--tg-theme-hint-color, #666666);
  margin-bottom: 8px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-clamp: 2;
  overflow: hidden;
}

.product-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.product-price {
  font-size: 16px;
  font-weight: 600;
  color: var(--tg-theme-button-color, #007AFF);
}

.product-unit {
  font-size: 12px;
  color: var(--tg-theme-hint-color, #666666);
}

.product-min-order {
  font-size: 11px;
  color: var(--tg-theme-hint-color, #666666);
}

.product-actions {
  padding: 0 12px 12px;
  display: flex;
  justify-content: center;
}

.btn-small {
  padding: 8px 12px;
  font-size: 14px;
  min-height: 36px;
}

.add-to-cart-btn {
  width: 100%;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.add-to-cart-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);
}

.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.quantity {
  font-size: 16px;
  font-weight: 600;
  min-width: 24px;
  text-align: center;
}

.loading, .error, .empty {
  text-align: center;
  padding: 32px;
}

.error {
  color: #FF3B30;
}

.empty {
  color: var(--tg-theme-hint-color, #666666);
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .product-image {
    height: 150px;
  }
  
  .product-info {
    padding: 16px;
  }
  
  .product-info h3 {
    font-size: 16px;
  }
  
  .product-description {
    font-size: 14px;
  }
}
</style>
