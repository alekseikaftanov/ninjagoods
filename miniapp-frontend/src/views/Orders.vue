<template>
  <div class="buyer-orders">
    <div class="orders-container">
      <div class="orders-header">
        <h1>Мои заказы</h1>
        <button class="new-order-button" @click="createNewOrder" :disabled="isLoading">
          + Создать заказ
        </button>
      </div>

      <div v-if="orders.length === 0" class="empty-state">
        <p>У вас пока нет заказов</p>
        <button @click="createNewOrder" class="create-button">Создать первый заказ</button>
      </div>

      <div v-else class="orders-list">
        <div 
          v-for="order in orders" 
          :key="order.id" 
          class="order-card"
          @click="openOrder(order.id)"
        >
          <div class="order-header">
            <span class="order-id">Заказ #{{ order.id }}</span>
            <span class="order-status" :class="order.status">
              {{ order.status === 'draft' ? 'Черновик' : 'Отправлен' }}
            </span>
          </div>
          
          <div class="order-info">
            <div class="info-item">
              <span class="label">Создан:</span>
              <span class="value">{{ formatDate(order.created_at) }}</span>
            </div>
            <div v-if="order.submitted_at" class="info-item">
              <span class="label">Отправлен:</span>
              <span class="value">{{ formatDate(order.submitted_at) }}</span>
            </div>
          </div>

          <div class="order-total">
            <span class="total-label">Итого:</span>
            <span class="total-value">{{ order.total.toFixed(2) }} ₽</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API } from '../utils/restaurantApi'
import type { Order } from '../utils/restaurantApi'

const router = useRouter()

const orders = ref<Order[]>([])
const isLoading = ref(false)

const loadOrders = async () => {
  isLoading.value = true
  
  try {
    const data = await API.orders.getAll()
    orders.value = data
  } catch (error) {
    console.error('Failed to load orders:', error)
  } finally {
    isLoading.value = false
  }
}

const createNewOrder = async () => {
  isLoading.value = true
  
  try {
    const newOrder = await API.orders.create()
    router.push(`/orders/${newOrder.id}`)
  } catch (error) {
    console.error('Failed to create order:', error)
  } finally {
    isLoading.value = false
  }
}

const openOrder = (orderId: number) => {
  router.push(`/orders/${orderId}`)
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.buyer-orders {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.orders-container {
  max-width: 900px;
  margin: 0 auto;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  color: white;
}

.orders-header h1 {
  font-size: 32px;
  font-weight: 700;
}

.new-order-button {
  background: white;
  color: #007AFF;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.new-order-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.new-order-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.empty-state {
  background: white;
  border-radius: 20px;
  padding: 64px 32px;
  text-align: center;
}

.empty-state p {
  font-size: 18px;
  color: #6B7280;
  margin-bottom: 24px;
}

.create-button {
  background: linear-gradient(180deg, #007AFF 0%, #0051D0 100%);
  color: white;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 14px 32px;
  cursor: pointer;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.order-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.order-id {
  font-size: 18px;
  font-weight: 700;
  color: #1C1C1E;
}

.order-status {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
}

.order-status.draft {
  background: #FEF3C7;
  color: #92400E;
}

.order-status.submitted {
  background: #D1FAE5;
  color: #065F46;
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.info-item {
  display: flex;
  gap: 8px;
}

.label {
  color: #6B7280;
  font-size: 14px;
}

.value {
  color: #1C1C1E;
  font-size: 14px;
}

.order-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid #E5E5EA;
}

.total-label {
  font-size: 16px;
  color: #6B7280;
}

.total-value {
  font-size: 24px;
  font-weight: 700;
  color: #1C1C1E;
}
</style>

