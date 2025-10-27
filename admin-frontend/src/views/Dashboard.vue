<template>
  <AdminLayout>
    <div class="dashboard">
      <!-- Header Section -->
      <div class="dashboard-header">
        <div class="header-content">
          <div class="header-title">
            <div class="title-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <rect x="7" y="7" width="3" height="3"/>
                <rect x="14" y="7" width="3" height="3"/>
                <rect x="7" y="14" width="3" height="3"/>
                <rect x="14" y="14" width="3" height="3"/>
              </svg>
            </div>
            <h1 class="title-text">Дашборд</h1>
          </div>
          <p class="subtitle-text">Обзор системы Ninja Goods</p>
        </div>
        <button @click="runTests" class="btn-run-tests" :disabled="runningTests">
          <svg v-if="runningTests" class="spinner" width="16" height="16" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="60" stroke-dashoffset="60">
              <animateTransform attributeName="transform" type="rotate" values="0 12 12;360 12 12" dur="1s" repeatCount="indefinite"/>
            </circle>
          </svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          {{ runningTests ? 'Выполняются тесты...' : 'Пройти тесты' }}
        </button>
      </div>
      
      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card" :class="`stat-card--${index}`" v-for="(stat, index) in statsData" :key="stat.key">
          <div class="stat-icon" :class="`stat-icon--${stat.color}`">
            <component :is="stat.icon" />
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ stat.value }}</h3>
            <p class="stat-label">{{ stat.label }}</p>
          </div>
        </div>
      </div>
      
      <!-- Activity Log Section -->
      <div class="activity-section">
        <div class="section-header">
          <div class="section-title">
            <div class="section-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4"/>
                <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"/>
                <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"/>
                <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"/>
                <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"/>
              </svg>
            </div>
            <h2 class="section-title-text">Последние действия</h2>
          </div>
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
              <circle cx="12" cy="12" r="1"/>
            </svg>
          </button>
        </div>
        
        <div class="activity-container">
          <div v-if="loadingLogs" class="loading-state">
            <div class="loading-spinner">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="60">
                  <animateTransform attributeName="transform" type="rotate" values="0 12 12;360 12 12" dur="1s" repeatCount="indefinite"/>
                </circle>
              </svg>
            </div>
            <p>Загружаем логи...</p>
          </div>
          
          <div v-else-if="logs.length === 0" class="empty-state">
            <div class="empty-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10,9 9,9 8,9"/>
              </svg>
            </div>
            <p>Пока нет действий</p>
          </div>
          
          <div v-else class="activity-list">
            <div 
              v-for="log in logs" 
              :key="log.id" 
              class="activity-item"
              :class="`activity-item--${log.action}`"
            >
              <div class="activity-icon">
                <svg v-if="log.action === 'created'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 8v8"/>
                  <path d="M8 12h8"/>
                </svg>
                <svg v-else-if="log.action === 'updated'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                  <path d="M15 5l4 4"/>
                  <path d="M12 12l4 4"/>
                </svg>
                <svg v-else-if="log.action === 'deleted'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M15 9l-6 6"/>
                  <path d="M9 9l6 6"/>
                </svg>
              </div>
              
              <div class="activity-content">
                <div class="activity-text">
                  <span class="user-name">{{ log.admin_name }}</span>
                  <span class="action-text">{{ getActionText(log.action) }}</span>
                  <span class="entity-type">{{ getEntityTypeText(log.entity_type) }}</span>
                  <span class="entity-name">"{{ log.entity_name }}"</span>
                </div>
                <div class="activity-time">{{ formatLogTime(log.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Test Results Modal -->
    <div v-if="showTestModal" class="modal-overlay" @click="showTestModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Результаты выполнения тестов</h3>
          <button @click="showTestModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <div v-if="runningTests" class="loading-container">
            <div class="spinner-large"></div>
            <p>Выполняются тесты...</p>
          </div>
          <div v-else-if="testResults" class="test-results">
            <div class="summary">
              <div class="summary-item passed">
                <span class="summary-label">Успешно:</span>
                <span class="summary-value">{{ testResults.tests }}</span>
              </div>
              <div class="summary-item assertions">
                <span class="summary-label">Asserts:</span>
                <span class="summary-value">{{ testResults.assertions }}</span>
              </div>
              <div class="summary-item duration">
                <span class="summary-label">Время:</span>
                <span class="summary-value">{{ testResults.duration }}s</span>
              </div>
            </div>
            
            <div class="test-list">
              <div v-for="(suite, suiteIndex) in testResults.suites" :key="suiteIndex" class="test-suite">
                <h4 class="suite-name">{{ suite.name }}</h4>
                <div class="suite-tests">
                  <div v-for="(test, testIndex) in suite.tests" :key="testIndex" class="test-item" :class="test.status">
                    <span class="test-status-icon">{{ test.status === 'PASS' ? '✓' : '✗' }}</span>
                    <span class="test-name">{{ test.name }}</span>
                    <span class="test-duration">{{ test.duration }}s</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="no-results">
            <p>Результаты тестов не доступны</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '../components/AdminLayout.vue'
import axios from 'axios'

// Icons as components
const CategoryIcon = {
  template: `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5a2 2 0 0 0-2-2z"/>
      <path d="M8 21v-4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/>
      <path d="M12 3v4"/>
      <path d="M8 3v4"/>
      <path d="M16 3v4"/>
    </svg>
  `
}

const ProductIcon = {
  template: `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 0 1-8 0"/>
      <circle cx="12" cy="16" r="2"/>
    </svg>
  `
}

const UserIcon = {
  template: `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
      <circle cx="12" cy="7" r="4"/>
      <path d="M12 11v4"/>
      <path d="M8 13h8"/>
    </svg>
  `
}

const OrderIcon = {
  template: `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
      <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
      <path d="M12 11v6"/>
      <path d="M9 14l3-3 3 3"/>
    </svg>
  `
}

const stats = ref({
  categories: 0,
  products: 0,
  users: 0,
  orders: 0
})

const logs = ref([])
const loadingLogs = ref(false)
const runningTests = ref(false)
const testResults = ref(null)
const showTestModal = ref(false)

const statsData = computed(() => [
  {
    key: 'categories',
    value: stats.value.categories,
    label: 'Категорий',
    icon: CategoryIcon,
    color: 'green'
  },
  {
    key: 'products',
    value: stats.value.products,
    label: 'Товаров',
    icon: ProductIcon,
    color: 'blue'
  },
  {
    key: 'users',
    value: stats.value.users,
    label: 'Пользователей',
    icon: UserIcon,
    color: 'purple'
  },
  {
    key: 'orders',
    value: stats.value.orders,
    label: 'Заказов',
    icon: OrderIcon,
    color: 'orange'
  }
])

const loadStats = async () => {
  try {
    // Загружаем статистику категорий, товаров и заказов
    const [categoriesRes, productsRes, ordersRes] = await Promise.all([
      axios.get('http://localhost:8000/api/admin/categories'),
      axios.get('http://localhost:8000/api/admin/products'),
      axios.get('http://localhost:8000/api/admin/orders')
    ])
    
    stats.value.categories = categoriesRes.data.data.length
    stats.value.products = productsRes.data.data.length
    stats.value.orders = ordersRes.data.data.length
    
    // Пока что статичные данные для пользователей
    stats.value.users = 0
    
    console.log('Stats loaded:', {
      categories: stats.value.categories,
      products: stats.value.products,
      orders: stats.value.orders,
      users: stats.value.users
    })
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
    // Fallback значения для демонстрации
    stats.value.categories = 8
    stats.value.products = 11
    stats.value.orders = 3
    stats.value.users = 0
  }
}

const loadLogs = async () => {
  loadingLogs.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/admin/admin-logs', {
      params: { limit: 10 }
    })
    logs.value = response.data.data
    console.log('Logs loaded:', logs.value.length)
  } catch (error) {
    console.error('Ошибка загрузки логов:', error)
    // Fallback данные для демонстрации
    logs.value = [
      {
        id: 1,
        admin_name: 'Администратор',
        action: 'created',
        entity_type: 'product',
        entity_name: 'Микрозелень шпината',
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        admin_name: 'Администратор',
        action: 'updated',
        entity_type: 'category',
        entity_name: 'Листовая зелень',
        created_at: new Date(Date.now() - 300000).toISOString()
      },
      {
        id: 3,
        admin_name: 'Администратор',
        action: 'deleted',
        entity_type: 'product',
        entity_name: 'Старый товар',
        created_at: new Date(Date.now() - 600000).toISOString()
      }
    ]
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

const runTests = async () => {
  runningTests.value = true
  showTestModal.value = true
  
  try {
    const response = await axios.get('http://localhost:8000/api/admin/tests/run')
    testResults.value = response.data.data
  } catch (error) {
    console.error('Ошибка выполнения тестов:', error)
    testResults.value = { error: 'Ошибка выполнения тестов' }
  } finally {
    runningTests.value = false
  }
}

onMounted(() => {
  loadStats()
  loadLogs()
})
</script>

<style scoped>
.dashboard {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 var(--spacing-lg);
}

/* Header Section */
.dashboard-header {
  margin-bottom: var(--spacing-xl);
  padding-top: var(--spacing-lg);
}

.header-content {
  text-align: left;
}

.header-title {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-xs);
}

.title-icon {
  width: 32px;
  height: 32px;
  background: var(--accent-blue-light);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent-blue);
}

.title-text {
  font-size: var(--font-size-large);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0;
  letter-spacing: -0.02em;
}

.subtitle-text {
  font-size: var(--font-size-regular);
  color: var(--text-secondary);
  margin: 0;
  font-weight: var(--font-weight-regular);
}

/* Stats Grid - Compact Layout */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-xl);
}

.stat-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-md);
  box-shadow: 0 2px 8px var(--shadow-light);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  cursor: pointer;
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--accent-blue), var(--accent-green));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover {
  box-shadow: 0 4px 16px var(--shadow-medium);
  transform: translateY(-1px);
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon--green {
  background: var(--accent-green-light);
  color: var(--accent-green);
}

.stat-icon--blue {
  background: var(--accent-blue-light);
  color: var(--accent-blue);
}

.stat-icon--purple {
  background: var(--accent-purple-light);
  color: var(--accent-purple);
}

.stat-icon--orange {
  background: var(--accent-orange-light);
  color: var(--accent-orange);
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 20px;
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0 0 var(--spacing-xs) 0;
  line-height: 1.2;
}

.stat-label {
  font-size: var(--font-size-small);
  color: var(--text-tertiary);
  margin: 0;
  font-weight: var(--font-weight-regular);
}

/* Activity Section - Compact Layout */
.activity-section {
  margin-top: var(--spacing-xl);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-md);
}

.section-title {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.section-icon {
  width: 24px;
  height: 24px;
  background: var(--accent-blue-light);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent-blue);
}

.section-title-text {
  font-size: 16px;
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0;
}

.refresh-btn {
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  padding: var(--spacing-xs) var(--spacing-sm);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  color: var(--text-secondary);
  font-size: var(--font-size-small);
}

.refresh-btn:hover:not(:disabled) {
  background: var(--accent-blue-light);
  border-color: var(--accent-blue);
  color: var(--accent-blue);
  transform: scale(1.02);
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

/* Activity Container - Compact */
.activity-container {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  box-shadow: 0 2px 8px var(--shadow-light);
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.loading-state,
.empty-state {
  padding: var(--spacing-xl);
  text-align: center;
  color: var(--text-secondary);
}

.loading-spinner {
  font-size: 20px;
  margin-bottom: var(--spacing-sm);
  animation: spin 1s linear infinite;
}

.empty-icon {
  font-size: 24px;
  margin-bottom: var(--spacing-sm);
  opacity: 0.5;
}

/* Activity List - Compact */
.activity-list {
  padding: 0;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  border-bottom: 1px solid var(--border-color);
  transition: all 0.2s ease;
  position: relative;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-item:hover {
  background: rgba(0, 0, 0, 0.02);
}

.activity-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--accent-blue);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.activity-item:hover::before {
  opacity: 1;
}

.activity-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-item--created .activity-icon {
  background: var(--accent-green-light);
  color: var(--accent-green);
}

.activity-item--updated .activity-icon {
  background: var(--accent-blue-light);
  color: var(--accent-blue);
}

.activity-item--deleted .activity-icon {
  background: rgba(255, 59, 48, 0.1);
  color: #FF3B30;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-text {
  font-size: var(--font-size-small);
  color: var(--text-primary);
  line-height: 1.4;
  margin-bottom: 2px;
}

.user-name {
  font-weight: var(--font-weight-medium);
  color: var(--accent-blue);
}

.action-text {
  font-weight: var(--font-weight-regular);
  margin: 0 var(--spacing-xs);
}

.entity-type {
  color: var(--text-secondary);
  margin: 0 var(--spacing-xs);
}

.entity-name {
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  font-style: italic;
}

.activity-time {
  font-size: var(--font-size-tiny);
  color: var(--text-tertiary);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.btn-run-tests {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #34C759;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-run-tests:hover:not(:disabled) {
  background: #30D158;
  transform: translateY(-1px);
}

.btn-run-tests:disabled {
  background: #8E8E93;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 20px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #8E8E93;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1C1C1E;
}

.modal-body {
  padding: 24px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 40px 20px;
}

.spinner-large {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top-color: #007AFF;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.test-results {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.summary {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.summary-item {
  flex: 1;
  padding: 16px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.summary-item.passed {
  background: rgba(52, 199, 89, 0.1);
  border: 1px solid rgba(52, 199, 89, 0.2);
}

.summary-item.assertions {
  background: rgba(0, 122, 255, 0.1);
  border: 1px solid rgba(0, 122, 255, 0.2);
}

.summary-item.duration {
  background: rgba(175, 82, 222, 0.1);
  border: 1px solid rgba(175, 82, 222, 0.2);
}

.summary-label {
  font-size: 14px;
  color: #6B7280;
  font-weight: 500;
}

.summary-value {
  font-size: 24px;
  font-weight: 600;
  color: #1C1C1E;
}

.test-suite {
  margin-bottom: 24px;
}

.suite-name {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 12px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

.suite-tests {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.test-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.02);
  transition: all 0.2s ease;
}

.test-item.PASS {
  border-left: 3px solid #34C759;
}

.test-item.FAIL {
  border-left: 3px solid #FF3B30;
}

.test-status-icon {
  font-size: 16px;
  font-weight: 600;
}

.test-item.PASS .test-status-icon {
  color: #34C759;
}

.test-item.FAIL .test-status-icon {
  color: #FF3B30;
}

.test-name {
  flex: 1;
  font-size: 14px;
  color: #1C1C1E;
}

.test-duration {
  font-size: 12px;
  color: #6B7280;
}

@media (max-width: 768px) {
  .dashboard {
    padding: 0 var(--spacing-md);
  }
  
  .dashboard-header {
    margin-bottom: var(--spacing-lg);
    padding-top: var(--spacing-md);
    flex-direction: column;
    gap: 16px;
  }
  
  .btn-run-tests {
    width: 100%;
    justify-content: center;
  }
  
  .title-text {
    font-size: var(--font-size-medium);
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-xs);
  }
  
  .activity-item {
    padding: var(--spacing-xs) var(--spacing-sm);
  }
  
  .activity-text {
    font-size: var(--font-size-tiny);
  }
  
  .activity-time {
    font-size: 11px;
  }
}

/* Animation Classes */
.fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-up {
  animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>