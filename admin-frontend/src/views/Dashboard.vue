<template>
  <AdminLayout>
    <div class="dashboard">
      <div class="dashboard-header">
        <h1>Дашборд</h1>
        <p>Обзор системы Ninja Goods</p>
      </div>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📁</div>
          <div class="stat-content">
            <h3>{{ stats.categories }}</h3>
            <p>Категорий</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">🛍️</div>
          <div class="stat-content">
            <h3>{{ stats.products }}</h3>
            <p>Товаров</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <h3>{{ stats.users }}</h3>
            <p>Пользователей</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📦</div>
          <div class="stat-content">
            <h3>{{ stats.orders }}</h3>
            <p>Заказов</p>
          </div>
        </div>
      </div>
      
      <div class="recent-orders">
        <h2>Последние заказы</h2>
        <div class="card">
          <p>Заказы будут отображаться здесь после реализации функционала заказов.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '../components/AdminLayout.vue'
import axios from 'axios'

const stats = ref({
  categories: 0,
  products: 0,
  users: 0,
  orders: 0
})

const loadStats = async () => {
  try {
    // Загружаем статистику категорий и товаров
    const [categoriesRes, productsRes] = await Promise.all([
      axios.get('http://localhost:8001/api/admin/categories'),
      axios.get('http://localhost:8001/api/admin/products')
    ])
    
    stats.value.categories = categoriesRes.data.data.length
    stats.value.products = productsRes.data.data.length
    
    // Пока что статичные данные для пользователей и заказов
    stats.value.users = 0
    stats.value.orders = 0
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  }
}

onMounted(() => {
  loadStats()
})
</script>

<style scoped>
.dashboard-header {
  margin-bottom: var(--spacing-xl);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
}

.stat-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  box-shadow: 0 2px 16px var(--shadow-light);
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  transition: all 0.3s ease;
}

.stat-card:hover {
  box-shadow: 0 4px 24px var(--shadow-medium);
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 32px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent-blue-light);
  border-radius: var(--radius-md);
}

.stat-content h3 {
  font-size: var(--font-size-large);
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.stat-content p {
  font-size: var(--font-size-small);
  color: var(--text-secondary);
  margin: 0;
}

.recent-orders {
  margin-top: var(--spacing-xl);
}
</style>
