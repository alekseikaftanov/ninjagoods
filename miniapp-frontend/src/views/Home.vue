<template>
  <div class="home">
    <!-- Hero Section -->
    <div class="hero">
      <div class="hero-content">
        <div class="hero-icon">🌱</div>
        <h1>Ninja Goods</h1>
        <p class="hero-subtitle">Свежая микрозелень<br>прямо к вашему столу</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <router-link to="/categories" class="action-card primary">
        <div class="action-icon">📁</div>
        <div class="action-content">
          <h3>Каталог товаров</h3>
          <p>Выберите свежую микрозелень</p>
        </div>
        <div class="action-arrow">›</div>
      </router-link>
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
        <div class="section-header">
          <h2>Все товары</h2>
          <span class="products-count">{{ allProducts.length }} товаров</span>
        </div>
        <div class="products-grid-small">
          <div
            v-for="product in allProducts"
            :key="product.id"
            class="product-card-small"
          >
            <div class="product-image-container-small">
              <img :src="product.photo_url" :alt="product.name" class="product-image-small" />
            </div>
            <div class="product-info-small">
              <div class="product-rating">
                <span class="rating-star">⭐</span>
                <span class="rating-value">4.9</span>
              </div>
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-price-small">{{ product.price }} ₽</p>
              <button @click="addToCart(product)" class="btn-add-to-cart">
                <span class="cart-icon">🛒</span>
                <span class="cart-text">В корзину</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Popular Categories -->
      <div v-if="mainCategories.length > 0" class="section">
        <div class="section-header">
          <h2>Категории</h2>
        </div>
        <div class="categories-grid">
          <router-link
            v-for="category in mainCategories.slice(0, 6)"
            :key="category.id"
            :to="`/products/${category.id}`"
            class="category-card"
          >
            <div class="category-image">
              <div class="category-icon">🌿</div>
            </div>
            <div class="category-info">
              <h3>{{ category.name }}</h3>
              <p>{{ category.products?.length || 0 }} товаров</p>
            </div>
          </router-link>
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

/* Hero Section */
.hero {
  background: linear-gradient(135deg, var(--accent-green) 0%, #2ECC71 100%);
  color: white;
  padding: var(--spacing-xl) var(--spacing-md);
  text-align: center;
  border-radius: 0 0 var(--radius-xl) var(--radius-xl);
  margin: 0 calc(-1 * var(--spacing-md)) var(--spacing-xl);
}

.hero-content {
  max-width: 400px;
  margin: 0 auto;
}

.hero-icon {
  font-size: 48px;
  margin-bottom: var(--spacing-md);
}

.hero h1 {
  font-size: var(--font-size-large);
  font-weight: 700;
  margin-bottom: var(--spacing-sm);
  color: white;
}

.hero-subtitle {
  font-size: var(--font-size-regular);
  opacity: 0.9;
  line-height: 1.4;
  margin: 0;
}

/* Quick Actions */
.quick-actions {
  margin-bottom: var(--spacing-xl);
}

.action-card {
  display: flex;
  align-items: center;
  padding: var(--spacing-lg);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-md);
  text-decoration: none;
  color: inherit;
  border: 1px solid var(--border-color);
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.action-card.primary {
  background: linear-gradient(135deg, var(--accent-blue) 0%, #0056CC 100%);
  color: white;
}

.action-card.secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.action-icon {
  font-size: 24px;
  margin-right: var(--spacing-md);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  border-radius: var(--radius-md);
}

.action-card.secondary .action-icon {
  background: var(--accent-blue-light);
}

.action-content {
  flex: 1;
}

.action-content h3 {
  font-size: var(--font-size-regular);
  font-weight: 600;
  margin: 0 0 var(--spacing-xs) 0;
}

.action-content p {
  font-size: var(--font-size-small);
  opacity: 0.8;
  margin: 0;
}

.action-arrow {
  font-size: 20px;
  font-weight: 300;
  opacity: 0.6;
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
.section {
  margin-bottom: var(--spacing-xl);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-md);
}

.section-header h2 {
  font-size: var(--font-size-medium);
  font-weight: 600;
  margin: 0;
}

.section-link {
  color: var(--accent-blue);
  text-decoration: none;
  font-size: var(--font-size-small);
  font-weight: 500;
}

.section-link:hover {
  opacity: 0.8;
}

/* Categories Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-md);
}

.category-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  text-decoration: none;
  color: inherit;
  border: 1px solid var(--border-color);
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.category-image {
  text-align: center;
  margin-bottom: var(--spacing-md);
}

.category-icon {
  font-size: 32px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent-green);
  border-radius: var(--radius-md);
  margin: 0 auto;
}

.category-info h3 {
  font-size: var(--font-size-small);
  font-weight: 600;
  margin: 0 0 var(--spacing-xs) 0;
  text-align: center;
}

.category-info p {
  font-size: var(--font-size-caption);
  color: var(--text-secondary);
  margin: 0;
  text-align: center;
}

/* Products Grid Small - 5 per row */
.products-grid-small {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: var(--spacing-sm);
}

.product-card-small {
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  border: 1px solid var(--border-color);
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  position: relative;
}

.product-card-small:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-image-container-small {
  aspect-ratio: 1;
  overflow: hidden;
  background: var(--bg-tertiary);
}

.product-image-small {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card-small:hover .product-image-small {
  transform: scale(1.05);
}

.product-info-small {
  padding: var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 2px;
  background: white;
  padding: 2px 6px;
  border-radius: 8px;
  width: fit-content;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.rating-star {
  font-size: 10px;
}

.rating-value {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-primary);
}

.product-name {
  font-size: 12px;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
  text-align: left;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-primary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-price-small {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  text-align: left;
  background: var(--bg-tertiary);
  padding: 4px 8px;
  border-radius: 6px;
  width: fit-content;
}

.btn-add-to-cart {
  width: 100%;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--spacing-md) var(--spacing-sm);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  margin-top: auto;
  min-height: 40px;
}

.btn-add-to-cart:hover {
  background: #2ECC71;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(52, 199, 89, 0.3);
}

.btn-add-to-cart:active {
  transform: translateY(0);
}

.cart-icon {
  font-size: 14px;
}

.cart-text {
  font-size: 12px;
  font-weight: 600;
}

.products-count {
  font-size: var(--font-size-small);
  color: var(--text-secondary);
  font-weight: 500;
}

/* Responsive */
@media (max-width: 1200px) {
  .products-grid-small {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 900px) {
  .products-grid-small {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 600px) {
  .products-grid-small {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .categories-grid {
    grid-template-columns: 1fr;
  }
  
  .hero {
    padding: var(--spacing-lg) var(--spacing-md);
  }
  
  .hero-icon {
    font-size: 40px;
  }
  
  .products-grid-small {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-xs);
  }
  
  .product-info-small h3 {
    font-size: 10px;
  }
  
  .product-price-small {
    font-size: 11px;
  }
}
</style>
