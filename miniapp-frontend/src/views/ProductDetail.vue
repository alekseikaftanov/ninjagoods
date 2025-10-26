<template>
  <div class="product-detail">
    <div class="header">
      <h1>{{ product?.name || 'Товар' }}</h1>
    </div>

    <div v-if="isLoading" class="loading">
      <p>Загрузка товара...</p>
    </div>

    <div v-else-if="error" class="error">
      <p>{{ error }}</p>
      <button @click="loadProduct" class="btn btn-primary">
        Попробовать снова
      </button>
    </div>

    <div v-else-if="product" class="product-content">
      <img :src="product.photo_url" :alt="product.name" class="product-image" />
      
      <div class="product-info">
        <h2>{{ product.name }}</h2>
        <p class="product-description">{{ product.description }}</p>
        
        <div class="product-details">
          <div class="detail-row">
            <span>Цена:</span>
            <span class="price">{{ product.price }} ₽ за {{ product.unit }}</span>
          </div>
          <div class="detail-row">
            <span>Минимальный заказ:</span>
            <span>{{ product.min_order }} {{ product.unit }}</span>
          </div>
          <div class="detail-row">
            <span>Категория:</span>
            <span>{{ product.category?.name || 'Не указана' }}</span>
          </div>
        </div>

        <div class="product-actions">
          <div class="quantity-controls">
            <button
              @click="decreaseQuantity"
              class="btn btn-secondary"
              :disabled="quantity <= product.min_order"
            >
              -
            </button>
            <span class="quantity">{{ quantity }}</span>
            <button
              @click="increaseQuantity"
              class="btn btn-secondary"
            >
              +
            </button>
          </div>
          
          <button
            v-if="!cartStore.isInCart(product.id)"
            @click="addToCart"
            class="btn btn-primary add-to-cart-btn"
          >
            В корзину
          </button>
          <button
            v-else
            @click="updateCart"
            class="btn btn-primary add-to-cart-btn"
          >
            Обновить в корзине
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCatalogStore } from '../stores/catalog'
import { useCartStore } from '../stores/cart'
import { hapticFeedback } from '../utils/telegram'

const route = useRoute()
const catalogStore = useCatalogStore()
const cartStore = useCartStore()

const isLoading = ref(false)
const error = ref<string | null>(null)
const quantity = ref(1)

const product = computed(() => {
  const productId = parseInt(route.params.id as string)
  return catalogStore.products.find(p => p.id === productId)
})

const loadProduct = async () => {
  const productId = parseInt(route.params.id as string)
  if (!productId) return

  isLoading.value = true
  error.value = null

  try {
    const productData = await catalogStore.getProductById(productId)
    if (productData) {
      quantity.value = Math.max(productData.min_order, cartStore.getQuantity(productId) || productData.min_order)
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load product'
  } finally {
    isLoading.value = false
  }
}

const increaseQuantity = () => {
  quantity.value++
  hapticFeedback('light')
}

const decreaseQuantity = () => {
  if (quantity.value > (product.value?.min_order || 1)) {
    quantity.value--
    hapticFeedback('light')
  }
}

const addToCart = () => {
  if (product.value) {
    cartStore.addItem(product.value, quantity.value)
    hapticFeedback('success')
  }
}

const updateCart = () => {
  if (product.value) {
    cartStore.updateQuantity(product.value.id, quantity.value)
    hapticFeedback('success')
  }
}

onMounted(() => {
  loadProduct()
})
</script>

<style scoped>
.product-detail {
  max-width: 100%;
}

.header {
  margin-bottom: 24px;
}

.product-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.product-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 12px;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.product-info h2 {
  font-size: 24px;
  margin-bottom: 8px;
}

.product-description {
  font-size: 16px;
  line-height: 1.5;
  color: var(--tg-theme-text-color, #000000);
}

.product-details {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  padding: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.price {
  font-size: 18px;
  font-weight: 600;
  color: var(--tg-theme-button-color, #007AFF);
}

.product-actions {
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: center;
}

.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.quantity {
  font-size: 20px;
  font-weight: 600;
  min-width: 40px;
  text-align: center;
}

.add-to-cart-btn {
  width: 200px;
  padding: 16px 24px;
  font-size: 18px;
  font-weight: 600;
  border-radius: 12px;
  transition: all 0.2s ease;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-to-cart-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
}

.loading, .error {
  text-align: center;
  padding: 32px;
}

.error {
  color: #FF3B30;
}

@media (max-width: 480px) {
  .product-image {
    height: 200px;
  }
  
  .product-info h2 {
    font-size: 20px;
  }
  
  .product-description {
    font-size: 14px;
  }
}
</style>
