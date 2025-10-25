<template>
  <div class="checkout-page">
    <div class="checkout-header">
      <button @click="goBack" class="back-btn">
        <span>←</span>
        Назад
      </button>
      <h1>Оформление заказа</h1>
    </div>

    <div class="checkout-content">
      <!-- Order Summary -->
      <div class="order-summary">
        <h2>Ваш заказ</h2>
        <div class="order-items">
          <div 
            v-for="item in cartStore.items" 
            :key="item.product.id" 
            class="order-item"
          >
            <img :src="item.product.photo_url" :alt="item.product.name" class="item-image" />
            <div class="item-info">
              <div class="item-name">{{ item.product.name }}</div>
              <div class="item-details">
                <span class="item-price">{{ item.product.price }} ₽</span>
                <span class="item-quantity">× {{ item.quantity }}</span>
              </div>
            </div>
            <div class="item-total">{{ (item.product.price * item.quantity) }} ₽</div>
          </div>
        </div>
        
        <div class="order-total">
          <div class="total-line">
            <span>Товаров:</span>
            <span>{{ cartStore.totalItems }}</span>
          </div>
          <div class="total-line final">
            <span>Итого:</span>
            <span>{{ cartStore.totalPrice }} ₽</span>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="contact-info">
        <h2>Контактная информация</h2>
        <div class="form-group">
          <label>Имя</label>
          <input v-model="orderData.name" type="text" placeholder="Введите ваше имя" />
        </div>
        <div class="form-group">
          <label>Телефон</label>
          <input v-model="orderData.phone" type="tel" placeholder="+7 (999) 123-45-67" />
        </div>
        <div class="form-group">
          <label>Комментарий к заказу</label>
          <textarea v-model="orderData.comment" placeholder="Дополнительные пожелания (необязательно)"></textarea>
        </div>
      </div>

      <!-- Submit Button -->
      <button @click="submitOrder" class="submit-btn" :disabled="!canSubmit">
        <span v-if="isSubmitting">Отправляем...</span>
        <span v-else>Подтвердить заказ</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import axios from 'axios'

const router = useRouter()
const cartStore = useCartStore()

const orderData = ref({
  name: '',
  phone: '',
  comment: ''
})

const isSubmitting = ref(false)

const canSubmit = computed(() => {
  return orderData.value.name.trim() && 
         orderData.value.phone.trim() && 
         cartStore.totalItems > 0
})

const goBack = () => {
  router.back()
}

const submitOrder = async () => {
  if (!canSubmit.value) return

  isSubmitting.value = true

  try {
    // Сначала создаем пользователя
    const userResponse = await axios.post('http://localhost:8001/api/auth/telegram', {
      telegram_id: `user_${Date.now()}`, // Временный ID для демо
      name: orderData.value.name,
      phone: orderData.value.phone
    })

    const userId = userResponse.data.user.id

    // Затем создаем заказ
    const orderResponse = await axios.post('http://localhost:8001/api/orders', {
      user_id: userId,
      items: cartStore.getOrderItems(),
      comment: orderData.value.comment
    })

    // Очищаем корзину
    cartStore.clearCart()

    // Показываем успех и возвращаемся
    alert('Заказ успешно оформлен! Номер заказа: #' + orderResponse.data.data.id)
    router.push('/')

  } catch (error) {
    console.error('Ошибка при оформлении заказа:', error)
    alert('Произошла ошибка при оформлении заказа. Попробуйте еще раз.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.checkout-page {
  min-height: 100vh;
  background: #F9FAFB;
  padding-bottom: 100px;
}

.checkout-header {
  background: white;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 16px;
}

.back-btn {
  background: none;
  border: none;
  color: #007AFF;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 8px;
  border-radius: 8px;
  transition: background 0.2s ease;
}

.back-btn:hover {
  background: rgba(0, 122, 255, 0.1);
}

.checkout-header h1 {
  font-size: 24px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0;
}

.checkout-content {
  max-width: 600px;
  margin: 0 auto;
  padding: 24px;
}

.order-summary {
  background: white;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.order-summary h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 20px 0;
}

.order-items {
  margin-bottom: 20px;
}

.order-item {
  display: flex;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  object-fit: cover;
  margin-right: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.item-info {
  flex: 1;
  margin-right: 16px;
}

.item-name {
  font-size: 16px;
  font-weight: 500;
  color: #1C1C1E;
  margin-bottom: 4px;
}

.item-details {
  display: flex;
  gap: 8px;
  font-size: 14px;
  color: #8E8E93;
}

.item-total {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
}

.order-total {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 16px;
}

.total-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 14px;
  color: #8E8E93;
}

.total-line.final {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
  margin-top: 12px;
}

.contact-info {
  background: white;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.contact-info h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1C1C1E;
  margin: 0 0 20px 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #1C1C1E;
  margin-bottom: 8px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  font-size: 16px;
  background: #F9FAFB;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #007AFF;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

.submit-btn {
  width: 100%;
  background: #34C759;
  color: white;
  border: none;
  border-radius: 16px;
  padding: 16px 20px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(52, 199, 89, 0.3);
}

.submit-btn:hover:not(:disabled) {
  background: #30D158;
  box-shadow: 0 0 16px rgba(52, 199, 89, 0.4);
  transform: translateY(-1px);
}

.submit-btn:active:not(:disabled) {
  transform: scale(0.97);
}

.submit-btn:disabled {
  background: #8E8E93;
  cursor: not-allowed;
  box-shadow: none;
}

/* Responsive */
@media (max-width: 480px) {
  .checkout-header {
    padding: 16px 20px;
  }
  
  .checkout-header h1 {
    font-size: 20px;
  }
  
  .checkout-content {
    padding: 20px;
  }
  
  .order-summary,
  .contact-info {
    padding: 20px;
  }
  
  .item-image {
    width: 48px;
    height: 48px;
  }
  
  .item-name {
    font-size: 14px;
  }
  
  .item-details {
    font-size: 12px;
  }
  
  .submit-btn {
    font-size: 16px;
    padding: 14px 18px;
  }
}
</style>