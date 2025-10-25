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
      
      <!-- Логи администратора -->
      <div class="logs-section">
        <div class="section-header">
          <h2>📋 Последние действия</h2>
          <button @click="refreshLogs" class="refresh-btn" :disabled="loadingLogs">
            <svg v-if="loadingLogs" class="spinner" width="16" height="16" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="60" stroke-dashoffset="60">
                <animateTransform attributeName="transform" type="rotate" values="0 12 12;360 12 12" dur="1s" repeatCount="indefinite"/>
              </circle>
            </svg>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
              <path d="M21 3v5h-5"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
              <path d="M3 21v-5h5"/>
            </svg>
          </button>
        </div>
        
        <div class="logs-container">
          <div v-if="loadingLogs" class="loading-logs">
            <div class="loading-spinner">⟳</div>
            <p>Загружаем логи...</p>
          </div>
          
          <div v-else-if="logs.length === 0" class="no-logs">
            <div class="no-logs-icon">📝</div>
            <p>Пока нет действий</p>
          </div>
          
          <div v-else class="logs-list">
            <div 
              v-for="log in logs" 
              :key="log.id" 
              class="log-item"
              :class="`log-${log.action}`"
            >
              <div class="log-icon">
                <svg v-if="log.action === 'created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14"/>
                  <path d="M5 12h14"/>
                </svg>
                <svg v-else-if="log.action === 'updated'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                  <path d="M15 5l4 4"/>
                </svg>
                <svg v-else-if="log.action === 'deleted'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18"/>
                  <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                  <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
              </div>
              
              <div class="log-content">
                <div class="log-text">
                  <span class="admin-name">{{ log.admin_name }}</span>
                  <span class="log-action">{{ getActionText(log.action) }}</span>
                  <span class="entity-type">{{ getEntityTypeText(log.entity_type) }}</span>
                  <span class="entity-name">"{{ log.entity_name }}"</span>
                </div>
                <div class="log-time">{{ formatLogTime(log.created_at) }}</div>
              </div>
            </div>
          </div>
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

const logs = ref([])
const loadingLogs = ref(false)

const loadStats = async () => {
  try {
    // Загружаем статистику категорий, товаров и заказов
    const [categoriesRes, productsRes, ordersRes] = await Promise.all([
      axios.get('http://localhost:8002/api/admin/categories'),
      axios.get('http://localhost:8002/api/admin/products'),
      axios.get('http://localhost:8002/api/admin/orders')
    ])
    
    stats.value.categories = categoriesRes.data.data.length
    stats.value.products = productsRes.data.data.length
    stats.value.orders = ordersRes.data.data.length
    
    // Пока что статичные данные для пользователей
    stats.value.users = 0
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  }
}

const loadLogs = async () => {
  loadingLogs.value = true
  try {
    const response = await axios.get('http://localhost:8002/api/admin/admin-logs', {
      params: { limit: 10 }
    })
    logs.value = response.data.data
  } catch (error) {
    console.error('Ошибка загрузки логов:', error)
  } finally {
    loadingLogs.value = false
  }
}

const refreshLogs = () => {
  loadLogs()
}

const getActionText = (action: string) => {
  const actions = {
    'created': 'создал',
    'updated': 'изменил',
    'deleted': 'удалил'
  }
  return actions[action] || action
}

const getEntityTypeText = (entityType: string) => {
  const types = {
    'category': 'категорию',
    'product': 'товар'
  }
  return types[entityType] || entityType
}

const formatLogTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'только что'
  if (diffMins < 60) return `${diffMins} мин назад`
  if (diffHours < 24) return `${diffHours} ч назад`
  if (diffDays < 7) return `${diffDays} дн назад`
  
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadStats()
  loadLogs()
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

/* Logs Section Styles */
.logs-section {
  margin-top: var(--spacing-xl);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-lg);
}

.section-header h2 {
  font-size: var(--font-size-medium);
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.refresh-btn {
  background: none;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 14px;
}

.refresh-btn:hover:not(:disabled) {
  background: var(--accent-blue-light);
  border-color: var(--accent-blue);
  color: var(--accent-blue);
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.logs-container {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.loading-logs {
  padding: var(--spacing-xl);
  text-align: center;
  color: var(--text-secondary);
}

.loading-spinner {
  font-size: 24px;
  margin-bottom: var(--spacing-sm);
  animation: spin 1s linear infinite;
}

.no-logs {
  padding: var(--spacing-xl);
  text-align: center;
  color: var(--text-secondary);
}

.no-logs-icon {
  font-size: 32px;
  margin-bottom: var(--spacing-sm);
  opacity: 0.5;
}

.logs-list {
  padding: 0;
}

.log-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md) var(--spacing-lg);
  border-bottom: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.log-item:last-child {
  border-bottom: none;
}

.log-item:hover {
  background: var(--bg-primary);
}

.log-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.log-created .log-icon {
  background: rgba(52, 199, 89, 0.1);
  color: #34C759;
}

.log-updated .log-icon {
  background: rgba(0, 122, 255, 0.1);
  color: #007AFF;
}

.log-deleted .log-icon {
  background: rgba(255, 59, 48, 0.1);
  color: #FF3B30;
}

.log-content {
  flex: 1;
  min-width: 0;
}

.log-text {
  font-size: 15px;
  color: var(--text-primary);
  line-height: 1.4;
  margin-bottom: 2px;
}

.admin-name {
  font-weight: 600;
  color: var(--accent-blue);
}

.log-action {
  font-weight: 500;
  margin: 0 4px;
}

.entity-type {
  color: var(--text-secondary);
  margin: 0 4px;
}

.entity-name {
  font-weight: 500;
  color: var(--text-primary);
}

.log-time {
  font-size: 13px;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-sm);
  }
  
  .log-item {
    padding: var(--spacing-sm) var(--spacing-md);
  }
  
  .log-text {
    font-size: 14px;
  }
  
  .log-time {
    font-size: 12px;
  }
}
</style>
