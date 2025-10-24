<template>
  <div class="home">
    <!-- Simple Header -->
    <div class="simple-header">
      <h1>Выберите товар</h1>
    </div>

    <!-- Loading State -->
    <div v-if="catalogStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Загрузка каталога...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="catalogStore.error" class="error-state">
      <div class="error-icon">⚠️</div>
      <p>{{ catalogStore.error }}</p>
      <button @click="loadData" class="btn btn-primary">
        Попробовать снова
      </button>
    </div>

    <!-- Content -->
    <div v-else class="content">
      <!-- All Products -->
      <div v-if="allProducts.length > 0" class="section">
        <div class="products-grid">
          <div
            v-for="product in allProducts"
            :key="product.id"
            class="product-card"
          >
            <div class="product-image-container">
              <img :src="product.photo_url" :alt="product.name" class="product-image" />
            </div>
            <div class="product-info">
              <div class="product-rating">
                <span class="rating-star">⭐</span>
                <span class="rating-value">4.9</span>
              </div>
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-price">{{ product.price }} ₽</p>
              <button @click="addToCart(product)" class="btn-add-to-cart">
                <span class="cart-icon">🛒</span>
                <span class="cart-text">В корзину</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Cart -->
    <FloatingCart />
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useCatalogStore } from '../stores/catalog'
import { useCartStore } from '../stores/cart'
import FloatingCart from '../components/FloatingCart.vue'
import type { Product } from '../types'

const catalogStore = useCatalogStore()
const cartStore = useCartStore()

const mainCategories = computed(() => catalogStore.getMainCategories())
const allProducts = computed(() => catalogStore.products)

const loadData = async () => {
  await Promise.all([
    catalogStore.loadCategories(),
    catalogStore.loadProducts()
  ])
}

const addToCart = (product: Product) => {
  cartStore.addItem(product)
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.home {
  padding: 0;
  padding-bottom: 80px; /* Space for floating cart */
}

/* Simple Header */
.simple-header {
  padding: var(--spacing-xl) var(--spacing-md) var(--spacing-lg);
  text-align: center;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
}

.simple-header h1 {
  font-size: var(--font-size-large);
  font-weight: 600;
  margin: 0;
  color: var(--text-primary);
}

/* Loading & Error States */
.loading-state, .error-state {
  text-align: center;
  padding: var(--spacing-xl);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--accent-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto var(--spacing-md);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-icon {
  font-size: 48px;
  margin-bottom: var(--spacing-md);
}

/* Content Sections */
.content {
  padding: var(--spacing-lg) var(--spacing-md);
}

.section {
  margin-bottom: var(--spacing-xl);
}

/* Products Grid - 4 per row with increased spacing */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-lg);
  padding: var(--spacing-md) 0;
}

.product-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  border: 1px solid var(--border-color);
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  position: relative;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-image-container {
  aspect-ratio: 1;
  overflow: hidden;
  background: var(--bg-tertiary);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.product-info {
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  background: white;
  padding: 4px 8px;
  border-radius: 12px;
  width: fit-content;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.rating-star {
  font-size: 12px;
}

.rating-value {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-primary);
}

.product-name {
  font-size: 14px;
  font-weight: 700;
  margin: 0;
  line-height: 1.3;
  text-align: left;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-primary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-price {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  text-align: left;
  background: var(--bg-tertiary);
  padding: 6px 12px;
  border-radius: 8px;
  width: fit-content;
}

.btn-add-to-cart {
  width: 100%;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  margin-top: auto;
  min-height: 48px;
}

.btn-add-to-cart:hover {
  background: #2ECC71;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(52, 199, 89, 0.3);
}

.btn-add-to-cart:active {
  transform: translateY(0);
}

.cart-icon {
  font-size: 16px;
}

.cart-text {
  font-size: 14px;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 1200px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
  }
}

@media (max-width: 900px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
  }
}

@media (max-width: 600px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
  }
  
  .simple-header {
    padding: var(--spacing-lg) var(--spacing-md) var(--spacing-md);
  }
  
  .simple-header h1 {
    font-size: var(--font-size-medium);
  }
  
  .content {
    padding: var(--spacing-md);
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-xs);
  }
  
  .product-info {
    padding: var(--spacing-sm);
  }
  
  .product-name {
    font-size: 12px;
  }
  
  .product-price {
    font-size: 14px;
  }
  
  .btn-add-to-cart {
    font-size: 12px;
    min-height: 40px;
  }
}
</style>
