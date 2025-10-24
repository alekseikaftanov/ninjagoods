<template>
  <div class="checkout">
    <div class="header">
      <h1>📝 Оформление заказа</h1>
    </div>

    <div v-if="cartStore.items.length === 0" class="empty-cart">
      <p>Корзина пуста</p>
      <router-link to="/categories" class="btn btn-primary">
        Перейти к каталогу
      </router-link>
    </div>

    <div v-else class="checkout-content">
      <!-- Информация о заказе -->
      <div class="order-summary">
        <h2>Ваш заказ</h2>
        <div class="order-items">
          <div
            v-for="item in cartStore.items"
            :key="item.product.id"
            class="order-item"
          >
            <div class="item-info">
              <h4>{{ item.product.name }}</h4>
              <p>{{ item.quantity }} × {{ item.product.price }} ₽</p>
            </div>
            <div class="item-total">
              {{ (item.quantity * item.product.price).toFixed(2) }} ₽
            </div>
          </div>
        </div>
        
        <div class="order-total">
          <div class="total-row">
            <span>Итого:</span>
            <span>{{ cartStore.totalPrice.toFixed(2) }} ₽</span>
          </div>
        </div>
      </div>

      <!-- Форма заказа -->
      <div class="order-form">
        <h2>Контактная информация</h2>
        
        <div class="form-group">
          <label class="form-label">Имя</label>
          <input
            v-model="orderForm.name"
            type="text"
            class="form-input"
            placeholder="Ваше имя"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label">Телефон</label>
          <input
            v-model="orderForm.phone"
            type="tel"
            class="form-input"
            placeholder="+7 (999) 123-45-67"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label">Комментарий к заказу</label>
          <textarea
            v-model="orderForm.comment"
            class="form-input"
            rows="3"
            placeholder="Дополнительные пожелания..."
          ></textarea>
        </div>
      </div>

      <!-- Кнопки действий -->
      <div class="checkout-actions">
        <router-link to="/cart" class="btn btn-secondary">
          Назад в корзину
        </router-link>
        <button
          @click="submitOrder"
          :disabled="!isFormValid || isSubmitting"
          class="btn btn-primary"
        >
          <span v-if="isSubmitting">Обработка...</span>
          <span v-else>Подтвердить заказ</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { ordersAPI } from '../utils/api'
import { hapticFeedback } from '../utils/telegram'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const isSubmitting = ref(false)

const orderForm = ref({
  name: '',
  phone: '',
  comment: ''
})

const isFormValid = computed(() => {
  return orderForm.value.name.trim() && orderForm.value.phone.trim()
})

const submitOrder = async () => {
  if (!isFormValid.value || !authStore.user) return

  isSubmitting.value = true

  try {
    const orderData = {
      user_id: authStore.user.id,
      items: cartStore.getOrderItems(),
      comment: orderForm.value.comment || undefined
    }

    const order = await ordersAPI.create(orderData)
    
    hapticFeedback('success')
    
    // Очищаем корзину
    cartStore.clearCart()
    
    // Переходим на страницу успеха
    router.push('/order-success')
    
  } catch (error) {
    console.error('Order submission error:', error)
    hapticFeedback('error')
    alert('Ошибка при оформлении заказа. Попробуйте снова.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  // Заполняем форму данными пользователя
  if (authStore.user) {
    orderForm.value.name = authStore.user.name
    orderForm.value.phone = authStore.user.phone
  }
})
</script>

<style scoped>
.checkout {
  max-width: 100%;
}

.header {
  margin-bottom: 24px;
}

.checkout-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-summary {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  padding: 16px;
}

.order-summary h2 {
  margin-bottom: 16px;
  font-size: 18px;
}

.order-items {
  margin-bottom: 16px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--tg-theme-hint-color, #e0e0e0);
}

.order-item:last-child {
  border-bottom: none;
}

.item-info h4 {
  font-size: 14px;
  margin-bottom: 2px;
}

.item-info p {
  font-size: 12px;
  color: var(--tg-theme-hint-color, #666666);
}

.item-total {
  font-weight: 600;
  color: var(--tg-theme-button-color, #007AFF);
}

.order-total {
  border-top: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  padding-top: 12px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 18px;
  font-weight: 600;
  color: var(--tg-theme-button-color, #007AFF);
}

.order-form {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  padding: 16px;
}

.order-form h2 {
  margin-bottom: 16px;
  font-size: 18px;
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  border-radius: 8px;
  font-size: 16px;
  background-color: var(--tg-theme-bg-color, #ffffff);
  color: var(--tg-theme-text-color, #000000);
}

.form-input:focus {
  outline: none;
  border-color: var(--tg-theme-button-color, #007AFF);
}

.form-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.checkout-actions {
  display: flex;
  gap: 12px;
}

.checkout-actions .btn {
  flex: 1;
}

.empty-cart {
  text-align: center;
  padding: 32px;
}

.empty-cart p {
  margin-bottom: 16px;
  color: var(--tg-theme-hint-color, #666666);
}

@media (max-width: 480px) {
  .checkout-actions {
    flex-direction: column;
  }
}
</style>
