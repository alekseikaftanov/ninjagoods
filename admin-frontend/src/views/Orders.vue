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
        <div class="stat-card">
          <div class="stat-icon">📦</div>
          <div class="stat-content">
            <div class="stat-number">{{ totalOrders }}</div>
            <div class="stat-label">Всего заказов</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <div class="stat-number">{{ totalRevenue }} ₽</div>
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
                    <span class="price-amount">{{ order.total }}</span>
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

const totalOrders = computed(() => orders.value.length)
const totalRevenue = computed(() => orders.value.reduce((sum, order) => sum + order.total, 0))
const todayOrders = computed(() => {
  const today = new Date().toDateString()
  return orders.value.filter(order => new Date(order.created_at).toDateString() === today).length
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

const refreshOrders = () => {
  loadOrders()
}

const viewOrder = (order: Order) => {
  selectedOrder.value = order
}

const closeModal = () => {
  selectedOrder.value = null
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
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
</style>
