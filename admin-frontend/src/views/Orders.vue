<template>
  <AdminLayout>
    <div class="orders-page">
      <div class="page-header">
        <h1>Заказы</h1>
        <div class="header-actions">
          <button @click="refreshOrders" class="btn-refresh" :disabled="loading">
            <span v-if="loading">⟳</span>
            <span v-else>⟳</span>
            Обновить
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card" @click="toggleChart">
          <div class="stat-icon">📦</div>
          <div class="stat-content">
            <div class="stat-number">{{ totalOrders }}</div>
            <div class="stat-label">Всего заказов</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <div class="stat-number">{{ formatPrice(totalRevenue) }} ₽</div>
            <div class="stat-label">Общая сумма</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <div class="stat-number">{{ todayOrders }}</div>
            <div class="stat-label">Сегодня</div>
          </div>
        </div>
      </div>

      <!-- Chart -->
      <div v-if="showChart" class="chart-container">
        <div class="chart-header">
          <h3>📈 Заказы по дням</h3>
          <button @click="closeChart" class="close-chart">×</button>
        </div>
        <div class="chart-content">
          <div v-if="chartData.length === 0" class="no-data">
            Нет данных для отображения
          </div>
          <div v-else class="modern-chart">
            <svg class="chart-svg" viewBox="0 0 400 200">
              <!-- Градиент для заливки -->
              <defs>
                <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" style="stop-color:#007AFF;stop-opacity:0.3" />
                  <stop offset="100%" style="stop-color:#007AFF;stop-opacity:0.05" />
                </linearGradient>
                <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" style="stop-color:#007AFF" />
                  <stop offset="100%" style="stop-color:#34C759" />
                </linearGradient>
                <filter id="glow">
                  <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                  <feMerge> 
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                  </feMerge>
                </filter>
              </defs>
              
              <!-- Сетка -->
              <g class="grid-lines">
                <line v-for="i in 4" :key="`h-${i}`" 
                      :x1="0" :y1="i * 40" :x2="400" :y2="i * 40" 
                      stroke="rgba(0,0,0,0.04)" stroke-width="1"/>
                <line v-for="i in 7" :key="`v-${i}`" 
                      :x1="i * 57.14" :y1="0" :x2="i * 57.14" :y2="200" 
                      stroke="rgba(0,0,0,0.04)" stroke-width="1"/>
              </g>
              
              <!-- Область под кривой -->
              <path 
                :d="areaPath" 
                fill="url(#areaGradient)" 
                class="area-fill"
              />
              
              <!-- Основная линия -->
              <path 
                :d="linePath" 
                fill="none" 
                stroke="url(#lineGradient)" 
                stroke-width="3" 
                class="main-line"
                filter="url(#glow)"
              />
              
              <!-- Точки данных -->
              <g class="data-points">
                <circle 
                  v-for="([date, count], index) in chartData" 
                  :key="`point-${index}`"
                  :cx="getX(index)" 
                  :cy="getY(count)" 
                  r="4" 
                  fill="white" 
                  stroke="#007AFF" 
                  stroke-width="2"
                  class="data-point"
                  @mouseenter="showTooltip($event, date, count)"
                  @mouseleave="hideTooltip"
                />
              </g>
              
              <!-- Подписи осей -->
              <g class="axis-labels">
                <text v-for="([date, count], index) in chartData" 
                      :key="`label-${index}`"
                      :x="getX(index)" 
                      y="195" 
                      text-anchor="middle" 
                      class="axis-label">{{ formatDateShort(date) }}</text>
              </g>
            </svg>
            
            <!-- Tooltip -->
            <div v-if="tooltip.visible" 
                 class="chart-tooltip" 
                 :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }">
              <div class="tooltip-content">
                <div class="tooltip-date">{{ tooltip.date }}</div>
                <div class="tooltip-value">{{ tooltip.value }} заказов</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="card">
        <div class="card-header">
          <h2>Список заказов</h2>
          <div class="table-controls">
            <select v-model="sortBy" @change="loadOrders" class="sort-select">
              <option value="created_at">По дате</option>
              <option value="total">По сумме</option>
              <option value="id">По номеру</option>
            </select>
            <select v-model="sortOrder" @change="loadOrders" class="sort-select">
              <option value="desc">Новые сначала</option>
              <option value="asc">Старые сначала</option>
            </select>
          </div>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>№</th>
                <th>Клиент</th>
                <th>Телефон</th>
                <th>Товары</th>
                <th>Сумма</th>
                <th>Дата</th>
                <th>Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading" class="loading-row">
                <td colspan="7" class="loading-cell">
                  <div class="loading-spinner">⟳</div>
                  Загружаем заказы...
                </td>
              </tr>
              <tr v-else-if="orders.length === 0" class="empty-row">
                <td colspan="7" class="empty-cell">
                  <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <div class="empty-text">Заказов пока нет</div>
                  </div>
                </td>
              </tr>
              <tr 
                v-else
                v-for="order in orders" 
                :key="order.id" 
                class="clickable-row"
                @click="viewOrder(order)"
              >
                <td class="order-id">#{{ order.id }}</td>
                <td class="customer-name">{{ order.user.name }}</td>
                <td class="customer-phone">{{ order.user.phone }}</td>
                <td class="order-items">
                  <div class="items-preview">
                    <span v-for="(item, index) in order.items.slice(0, 2)" :key="index" class="item-tag">
                      {{ item.product_name || `Товар #${item.product_id}` }} ({{ item.quantity }})
                    </span>
                    <span v-if="order.items.length > 2" class="more-items">
                      +{{ order.items.length - 2 }} еще
                    </span>
                  </div>
                </td>
                <td class="order-total">
                  <div class="price-container">
                    <span class="price-amount">{{ formatPrice(order.total) }}</span>
                    <span class="price-currency">₽</span>
                  </div>
                </td>
                <td class="order-date">{{ formatDate(order.created_at) }}</td>
                <td class="order-actions">
                  <button @click.stop="viewOrder(order)" class="btn-action btn-view" title="Просмотр">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Заказ #{{ selectedOrder.id }}</h3>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        
        <div class="modal-body">
          <div class="order-details">
            <div class="detail-section">
              <h4>Информация о клиенте</h4>
              <div class="detail-grid">
                <div class="detail-item">
                  <label>Имя:</label>
                  <span>{{ selectedOrder.user.name }}</span>
                </div>
                <div class="detail-item">
                  <label>Телефон:</label>
                  <span>{{ selectedOrder.user.phone }}</span>
                </div>
                <div class="detail-item">
                  <label>Дата заказа:</label>
                  <span>{{ formatDate(selectedOrder.created_at) }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h4>Товары в заказе</h4>
              <div class="order-items-list">
                <div v-for="item in selectedOrder.items" :key="item.product_id" class="order-item-detail">
                  <div class="item-info">
                    <div class="item-name">{{ item.product_name || `Товар #${item.product_id}` }}</div>
                    <div class="item-details">
                      <span>Цена: {{ item.price }} ₽</span>
                      <span>Количество: {{ item.quantity }}</span>
                    </div>
                  </div>
                  <div class="item-total">{{ (item.price * item.quantity) }} ₽</div>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h4>Итого</h4>
              <div class="total-summary">
                <div class="total-line">
                  <span>Товаров:</span>
                  <span>{{ selectedOrder.items.length }}</span>
                </div>
                <div class="total-line final">
                  <span>Общая сумма:</span>
                  <span>{{ selectedOrder.total }} ₽</span>
                </div>
              </div>
            </div>
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

interface OrderItem {
  product_id: number
  product_name?: string
  quantity: number
  price: number
}

interface Order {
  id: number
  user_id: number
  items: OrderItem[]
  total: number
  created_at: string
  user: {
    id: number
    name: string
    phone: string
  }
}

const orders = ref<Order[]>([])
const selectedOrder = ref<Order | null>(null)
const loading = ref(false)
const sortBy = ref('created_at')
const sortOrder = ref('desc')
const showChart = ref(false)

const totalOrders = computed(() => orders.value.length)
const totalRevenue = computed(() => {
  return orders.value.reduce((sum, order) => {
    const total = typeof order.total === 'string' ? parseFloat(order.total) : order.total
    return sum + (isNaN(total) ? 0 : total)
  }, 0)
})
const todayOrders = computed(() => {
  const today = new Date().toDateString()
  return orders.value.filter(order => new Date(order.created_at).toDateString() === today).length
})

// Данные для графика за последние 7 дней
const chartData = computed(() => {
  const data: { [key: string]: number } = {}
  
  // Создаем массив последних 7 дней
  const last7Days = []
  for (let i = 6; i >= 0; i--) {
    const date = new Date()
    date.setDate(date.getDate() - i)
    const dateStr = date.toLocaleDateString('ru-RU')
    last7Days.push(dateStr)
    data[dateStr] = 0 // Инициализируем нулем
  }
  
  // Заполняем реальными данными
  orders.value.forEach(order => {
    const date = new Date(order.created_at).toLocaleDateString('ru-RU')
    if (data.hasOwnProperty(date)) {
      data[date] = (data[date] || 0) + 1
    }
  })
  
  return last7Days.map(date => [date, data[date]])
})

// Данные для современного графика
const maxValue = computed(() => Math.max(...chartData.value.map(([, count]) => count as number), 1))

const getX = (index: number) => {
  return (index * 57.14) + 28.57 // Центр каждого столбца
}

const getY = (value: number) => {
  return 180 - ((value / maxValue.value) * 160) + 10 // Инвертируем Y и добавляем отступ
}

// SVG пути для плавной кривой
const linePath = computed(() => {
  if (chartData.value.length < 2) return ''
  
  const points = chartData.value.map(([, count], index) => {
    const x = getX(index)
    const y = getY(count as number)
    return `${x},${y}`
  })
  
  // Создаем плавную кривую с помощью cubic bezier
  let path = `M ${points[0]}`
  
  for (let i = 1; i < points.length; i++) {
    const [x, y] = points[i].split(',').map(Number)
    const [prevX, prevY] = points[i - 1].split(',').map(Number)
    
    const cp1x = prevX + (x - prevX) / 3
    const cp1y = prevY
    const cp2x = x - (x - prevX) / 3
    const cp2y = y
    
    path += ` C ${cp1x},${cp1y} ${cp2x},${cp2y} ${x},${y}`
  }
  
  return path
})

const areaPath = computed(() => {
  if (chartData.value.length < 2) return ''
  
  const line = linePath.value
  const firstX = getX(0)
  const lastX = getX(chartData.value.length - 1)
  
  return `${line} L ${lastX},190 L ${firstX},190 Z`
})

// Tooltip состояние
const tooltip = ref({
  visible: false,
  x: 0,
  y: 0,
  date: '',
  value: 0
})

onMounted(() => {
  loadOrders()
})

const loadOrders = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://localhost:8001/api/admin/orders', {
      params: {
        sort_by: sortBy.value,
        sort_order: sortOrder.value
      }
    })
    orders.value = response.data.data
  } catch (error) {
    console.error('Ошибка загрузки заказов:', error)
  } finally {
    loading.value = false
  }
}

const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0' : numPrice.toFixed(0)
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const toggleChart = () => {
  showChart.value = !showChart.value
}

const closeChart = () => {
  showChart.value = false
}

const showTooltip = (event: MouseEvent, date: string, value: number) => {
  tooltip.value = {
    visible: true,
    x: event.clientX - 50,
    y: event.clientY - 60,
    date: date,
    value: value
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

const formatDateShort = (dateString: string) => {
  const date = new Date(dateString.split('.').reverse().join('-'))
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit'
  })
}

const refreshOrders = () => {
  loadOrders()
}

const viewOrder = (order: Order) => {
  selectedOrder.value = order
}

const closeModal = () => {
  selectedOrder.value = null
}
</script>

<style scoped>
.orders-page {
  padding: 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.page-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: #1C1C1E;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-refresh {
  background: #007AFF;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-refresh:hover:not(:disabled) {
  background: #0056CC;
  transform: translateY(-1px);
}

.btn-refresh:disabled {
  background: #8E8E93;
  cursor: not-allowed;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  font-size: 32px;
  opacity: 0.8;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #8E8E93;
  font-weight: 500;
}

.card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0;
}

.table-controls {
  display: flex;
  gap: 12px;
}

.sort-select {
  padding: 8px 12px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  background: white;
  font-size: 14px;
  cursor: pointer;
}

.table-container {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.table th,
.table td {
  padding: 16px 20px;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.table th {
  background: rgba(0, 0, 0, 0.02);
  font-weight: 600;
  font-size: 14px;
  color: #6B7280;
  position: sticky;
  top: 0;
  z-index: 10;
}

.clickable-row {
  cursor: pointer;
  transition: all 0.2s ease;
}

.clickable-row:hover {
  background: rgba(0, 0, 0, 0.02);
}

.order-id {
  font-weight: 600;
  color: #007AFF;
}

.customer-name {
  font-weight: 500;
  color: #1C1C1E;
}

.customer-phone {
  color: #6B7280;
  font-family: monospace;
}

.items-preview {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.item-tag {
  background: rgba(0, 122, 255, 0.1);
  color: #007AFF;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
}

.more-items {
  color: #8E8E93;
  font-size: 12px;
  font-style: italic;
}

.price-container {
  display: flex;
  align-items: baseline;
  gap: 2px;
}

.price-amount {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
  font-feature-settings: "tnum";
}

.price-currency {
  font-size: 14px;
  color: #6B7280;
  opacity: 0.6;
}

.order-date {
  color: #6B7280;
  font-size: 14px;
}

.btn-action {
  background: none;
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-action:hover {
  background: rgba(0, 0, 0, 0.05);
  transform: scale(1.1);
}

.btn-action svg {
  width: 16px;
  height: 16px;
  stroke: #8E8E93;
  transition: stroke 0.2s ease;
}

.btn-action:hover svg {
  stroke: #007AFF;
}

.loading-row,
.empty-row {
  text-align: center;
}

.loading-cell,
.empty-cell {
  padding: 40px 20px;
}

.loading-spinner {
  font-size: 24px;
  animation: spin 1s linear infinite;
  margin-bottom: 8px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  color: #8E8E93;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-text {
  font-size: 16px;
  font-weight: 500;
}

/* Modal */
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
  z-index: 2000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 20px;
  max-width: 600px;
  width: 100%;
  max-height: 80vh;
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

.detail-section {
  margin-bottom: 24px;
}

.detail-section h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 16px 0;
}

.detail-grid {
  display: grid;
  gap: 12px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.detail-item label {
  font-weight: 500;
  color: #6B7280;
}

.detail-item span {
  color: #1C1C1E;
  font-weight: 500;
}

.order-items-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-item-detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
}

.item-info {
  flex: 1;
}

.item-name {
  font-weight: 500;
  color: #1C1C1E;
  margin-bottom: 4px;
}

.item-details {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: #6B7280;
}

.item-total {
  font-weight: 600;
  color: #1C1C1E;
}

.total-summary {
  background: rgba(0, 0, 0, 0.02);
  padding: 16px;
  border-radius: 8px;
}

.total-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 14px;
  color: #6B7280;
}

.total-line.final {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
  margin-top: 12px;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .table-controls {
    flex-direction: column;
  }
  
  .modal-content {
    margin: 10px;
    max-height: 90vh;
  }
}

/* Modern Chart Styles */
.chart-container {
  background: #F9FAFB;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: var(--spacing-lg);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.8);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 20px 24px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
}

.chart-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0;
  letter-spacing: -0.01em;
}

.close-chart {
  background: none;
  border: none;
  font-size: 20px;
  color: #8E8E93;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-chart:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1C1C1E;
  transform: scale(1.1);
}

.chart-content {
  padding: 0 24px 24px 24px;
}

.no-data {
  text-align: center;
  color: #8E8E93;
  font-size: 16px;
  padding: 60px 20px;
  font-weight: 500;
}

.modern-chart {
  position: relative;
  height: 240px;
  background: #F9FAFB;
  border-radius: 16px;
  overflow: hidden;
}

.chart-svg {
  width: 100%;
  height: 100%;
  display: block;
}

.grid-lines {
  opacity: 0.6;
}

.area-fill {
  opacity: 0;
  animation: fadeInArea 0.8s ease-out 0.3s forwards;
}

.main-line {
  opacity: 0;
  stroke-dasharray: 1000;
  stroke-dashoffset: 1000;
  animation: drawLine 1.2s ease-out 0.5s forwards;
}

.data-points {
  opacity: 0;
  animation: fadeInPoints 0.6s ease-out 1s forwards;
}

.data-point {
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.data-point:hover {
  r: 6;
  filter: drop-shadow(0 2px 8px rgba(0, 122, 255, 0.3));
}

.axis-labels {
  opacity: 0;
  animation: fadeInLabels 0.4s ease-out 1.2s forwards;
}

.axis-label {
  font-size: 13px;
  fill: #8E8E93;
  font-weight: 500;
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
}

.chart-tooltip {
  position: fixed;
  z-index: 1000;
  pointer-events: none;
  transform: translate(-50%, -100%);
}

.tooltip-content {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 12px;
  padding: 8px 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(20px);
  animation: tooltipAppear 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.tooltip-date {
  font-size: 12px;
  color: #8E8E93;
  font-weight: 500;
  margin-bottom: 2px;
}

.tooltip-value {
  font-size: 14px;
  color: #1C1C1E;
  font-weight: 600;
}

/* Анимации */
@keyframes fadeInArea {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes drawLine {
  from {
    stroke-dashoffset: 1000;
    opacity: 0;
  }
  to {
    stroke-dashoffset: 0;
    opacity: 1;
  }
}

@keyframes fadeInPoints {
  from {
    opacity: 0;
    transform: scale(0);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes fadeInLabels {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes tooltipAppear {
  from {
    opacity: 0;
    transform: translate(-50%, -100%) scale(0.8);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -100%) scale(1);
  }
}

@media (max-width: 768px) {
  .modern-chart {
    height: 200px;
  }
  
  .chart-header {
    padding: 20px 20px 16px 20px;
  }
  
  .chart-content {
    padding: 0 20px 20px 20px;
  }
  
  .chart-header h3 {
    font-size: 16px;
  }
  
  .axis-label {
    font-size: 11px;
  }
}
</style>
