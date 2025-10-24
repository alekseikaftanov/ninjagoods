<template>
  <div class="home">
    <!-- Navigation Header -->
    <header class="nav-header">
      <div class="nav-container">
        <div class="logo">
          <span class="logo-icon">🌱</span>
          <span class="logo-text">Ninja Goods</span>
        </div>
        <nav class="nav-menu">
          <a href="#" class="nav-link">Продукты</a>
          <a href="#" class="nav-link">О нас</a>
          <a href="#" class="nav-link">Контакты</a>
        </nav>
        <div class="cart-button" @click="toggleCart">
          <span class="cart-icon">🛒</span>
          <span v-if="cartStore.totalItems > 0" class="cart-count">{{ cartStore.totalItems }}</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-content">
          <h1 class="hero-title">Выберите товар</h1>
          <p class="hero-subtitle">Свежая микрозелень, собранная утром специально для вас</p>
        </div>
      </section>

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

      <!-- Products Grid -->
      <section v-else-if="allProducts.length > 0" class="products-section">
        <div class="products-grid">
          <div
            v-for="product in allProducts"
            :key="product.id"
            class="product-card"
            @click="addToCart(product)"
          >
            <div class="product-image-container">
              <img :src="product.photo_url" :alt="product.name" class="product-image" />
              <div class="product-overlay"></div>
            </div>
            <div class="product-info">
              <div class="product-rating">
                <span class="rating-star">⭐</span>
                <span class="rating-value">4.9</span>
              </div>
              <h3 class="product-name">{{ product.name }}</h3>
              <div class="product-price-container">
                <span class="product-price">{{ product.price }} ₽</span>
              </div>
              <button class="btn-add-to-cart">
                Добавить
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Empty State -->
      <section v-else class="empty-state">
        <div class="empty-icon">🌱</div>
        <h2>Пока нет товаров</h2>
        <p>Мы работаем над пополнением ассортимента</p>
      </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <p>&copy; 2024 Ninja Goods. Все права защищены.</p>
        <p>Свежая микрозелень для здорового питания</p>
      </div>
    </footer>

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
  // Добавляем bounce эффект
  const button = event?.target as HTMLElement
  if (button) {
    button.style.transform = 'scale(0.95)'
    setTimeout(() => {
      button.style.transform = 'scale(1)'
    }, 150)
  }
}

const toggleCart = () => {
  // Логика для открытия корзины
  console.log('Toggle cart')
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.home {
  min-height: 100vh;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
  padding-bottom: 80px; /* Space for floating cart */
}

/* Navigation Header */
.nav-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  z-index: 1000;
  padding: 16px 0;
}

.nav-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 18px;
  color: #1C1C1E;
}

.logo-icon {
  font-size: 24px;
}

.nav-menu {
  display: flex;
  gap: 32px;
}

.nav-link {
  color: #6B7280;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.nav-link:hover {
  color: #1C1C1E;
}

.cart-button {
  position: relative;
  background: rgba(0, 0, 0, 0.03);
  border-radius: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.cart-button:hover {
  background: rgba(0, 0, 0, 0.06);
  transform: scale(1.05);
}

.cart-count {
  background: #007AFF;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
}

/* Main Content */
.main-content {
  max-width: 1440px;
  margin: 0 auto;
  padding: 100px 60px 0;
}

/* Hero Section */
.hero-section {
  text-align: center;
  margin-bottom: 60px;
}

.hero-content {
  max-width: 600px;
  margin: 0 auto;
}

.hero-title {
  font-size: 32px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 16px 0;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 16px;
  color: #8E8E93;
  margin: 0;
  line-height: 1.5;
}

/* Products Section */
.products-section {
  margin-bottom: 80px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
}

.product-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  position: relative;
}

.product-card:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.product-card:active {
  transform: translateY(-2px) scale(1.01);
}

.product-image-container {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
  filter: saturate(1.1);
}

.product-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
  opacity: 1;
}

.product-info {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #8E8E93;
  font-size: 14px;
}

.product-name {
  font-size: 16px;
  font-weight: 500;
  color: #1C1C1E;
  margin: 0;
  line-height: 1.4;
}

.product-price-container {
  background: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  padding: 8px 12px;
  width: fit-content;
}

.product-price {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0;
}

.btn-add-to-cart {
  background: #007AFF;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: auto;
}

.btn-add-to-cart:hover {
  background: #0056CC;
  box-shadow: 0 0 12px rgba(0, 122, 255, 0.3);
  transform: translateY(-1px);
}

.btn-add-to-cart:active {
  transform: scale(0.98);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: #8E8E93;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 24px;
  opacity: 0.5;
}

.empty-state h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 12px 0;
}

.empty-state p {
  font-size: 16px;
  margin: 0;
}

/* Footer */
.footer {
  background: rgba(0, 0, 0, 0.03);
  padding: 40px 0;
  margin-top: 80px;
}

.footer-content {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 60px;
  text-align: center;
  color: #8E8E93;
}

.footer-content p {
  margin: 4px 0;
  font-size: 14px;
}

/* Loading & Error States */
.loading-state, .error-state {
  text-align: center;
  padding: 80px 20px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-top: 3px solid #007AFF;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 24px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-icon {
  font-size: 48px;
  margin-bottom: 24px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .nav-container {
    padding: 0 40px;
  }
  
  .main-content {
    padding: 100px 40px 0;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }
}

@media (max-width: 768px) {
  .nav-container {
    padding: 0 20px;
  }
  
  .nav-menu {
    display: none;
  }
  
  .main-content {
    padding: 100px 20px 0;
  }
  
  .hero-title {
    font-size: 28px;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .product-image-container {
    height: 200px;
  }
  
  .product-info {
    padding: 20px;
  }
  
  .footer-content {
    padding: 0 20px;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 24px;
  }
  
  .hero-subtitle {
    font-size: 14px;
  }
  
  .product-image-container {
    height: 180px;
  }
  
  .product-info {
    padding: 16px;
  }
  
  .product-name {
    font-size: 14px;
  }
  
  .product-price {
    font-size: 16px;
  }
  
  .btn-add-to-cart {
    font-size: 14px;
    padding: 10px 20px;
  }
}
</style>

