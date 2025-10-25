<template>
  <div class="floating-cart" :class="{ 'has-items': cartStore.totalItems > 0 }">
    <!-- Fixed Cart Header -->
    <div class="cart-header" @click="toggleCart">
      <div class="cart-icon-container">
        <div class="cart-icon">
          <span class="cart-emoji">🛒</span>
          <span v-if="cartStore.totalItems > 0" class="cart-badge">{{ cartStore.totalItems }}</span>
        </div>
        <div class="cart-info">
          <div class="cart-title">Корзина</div>
          <div class="cart-total">{{ cartStore.totalPrice }} ₽</div>
        </div>
      </div>
      <div class="cart-arrow" v-if="cartStore.totalItems > 0">
        <span>{{ isExpanded ? '▼' : '▲' }}</span>
      </div>
    </div>
    
    <!-- Expanded Cart Content -->
    <div v-if="isExpanded && cartStore.totalItems > 0" class="cart-expanded">
      <div class="cart-items">
        <div 
          v-for="item in cartStore.items" 
          :key="item.product.id" 
          class="cart-item"
        >
          <img :src="item.product.photo_url" :alt="item.product.name" class="item-image" />
          <div class="item-info">
            <div class="item-name">{{ item.product.name }}</div>
            <div class="item-details">
              <span class="item-price">{{ item.product.price }} ₽</span>
              <span class="item-quantity">× {{ item.quantity }}</span>
            </div>
          </div>
          <div class="item-actions">
            <button @click.stop="cartStore.decreaseQuantity(item.product.id)" class="btn-quantity minus">-</button>
            <span class="quantity">{{ item.quantity }}</span>
            <button @click.stop="cartStore.increaseQuantity(item.product.id)" class="btn-quantity plus">+</button>
            <button @click.stop="cartStore.removeItem(item.product.id)" class="btn-remove">×</button>
          </div>
        </div>
      </div>
      
      <div class="cart-footer">
        <div class="cart-summary">
          <div class="summary-line items-count">
            <span>Товаров:</span>
            <span>{{ cartStore.totalItems }}</span>
          </div>
          <div class="summary-line total">
            <span>Итого:</span>
            <span>{{ cartStore.totalPrice }} ₽</span>
          </div>
        </div>
        <button @click="checkout" class="btn-checkout">
          <span>Оформить заказ</span>
          <span class="checkout-icon">✓</span>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!cartStore.totalItems" class="empty-cart">
      <div class="empty-content">
        <div class="empty-icon">🛒</div>
        <div class="empty-text">Добавьте товары в заказ</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useCartStore } from '../stores/cart'
import { useRouter } from 'vue-router'

const cartStore = useCartStore()
const router = useRouter()
const isExpanded = ref(false)

const toggleCart = () => {
  if (cartStore.totalItems > 0) {
    isExpanded.value = !isExpanded.value
  }
}

const checkout = () => {
  router.push('/checkout')
}
</script>

<style scoped>
.floating-cart {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #F9FAFB;
  border-radius: 20px 20px 0 0;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  backdrop-filter: blur(20px);
}

.floating-cart.has-items {
  background: linear-gradient(180deg, #34C759 0%, #28B84A 100%);
  color: white;
}

/* Cart Header */
.cart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cart-header:hover {
  background: rgba(255, 255, 255, 0.1);
}

.cart-icon-container {
  display: flex;
  align-items: center;
  gap: 16px;
}

.cart-icon {
  position: relative;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(20px);
  border-radius: 12px;
  padding: 8px;
}

.cart-emoji {
  font-size: 20px;
}

.cart-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #FF3B30;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
}

.cart-info {
  flex: 1;
}

.cart-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 2px;
  color: #1C1C1E;
}

.cart-total {
  font-size: 14px;
  font-weight: 500;
  color: #1C1C1E;
}

.floating-cart.has-items .cart-title,
.floating-cart.has-items .cart-total {
  color: white;
}

.cart-arrow {
  font-size: 16px;
  opacity: 0.7;
  transition: transform 0.2s ease;
}

.cart-arrow:hover {
  transform: scale(1.1);
}

/* Expanded Cart */
.cart-expanded {
  background: #F9FAFB;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  max-height: 400px;
  overflow-y: auto;
  border-radius: 0 0 20px 20px;
}

.cart-items {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.cart-item:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.item-image {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  object-fit: cover;
  margin-right: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
  line-height: 1.4;
}

.item-details {
  display: flex;
  gap: 8px;
  font-size: 14px;
  color: #8E8E93;
}

.item-price {
  font-weight: 500;
}

.item-quantity {
  opacity: 0.8;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-quantity {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #F2F2F7;
  color: #6B7280;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-quantity:hover {
  background: #E5E5EA;
  color: #1C1C1E;
  transform: scale(1.05);
}

.btn-quantity:active {
  transform: scale(0.95);
}

.quantity {
  font-size: 16px;
  font-weight: 600;
  min-width: 24px;
  text-align: center;
  color: #1C1C1E;
}

.btn-remove {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: rgba(255, 59, 48, 0.1);
  color: #FF3B30;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.btn-remove:hover {
  background: rgba(255, 59, 48, 0.2);
  transform: scale(1.05);
}

.btn-remove:active {
  transform: scale(0.95);
}

/* Cart Footer */
.cart-footer {
  padding: 24px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  background: rgba(0, 0, 0, 0.02);
}

.cart-summary {
  margin-bottom: 20px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 14px;
}

.summary-line.items-count {
  color: #8E8E93;
}

.summary-line.total {
  font-weight: 600;
  font-size: 18px;
  color: #1C1C1E;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
  margin-top: 12px;
}

.btn-checkout {
  width: 100%;
  background: #34C759;
  color: white;
  border: none;
  border-radius: 16px;
  padding: 16px 20px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(52, 199, 89, 0.3);
}

.btn-checkout:hover {
  background: #30D158;
  box-shadow: 0 0 16px rgba(52, 199, 89, 0.4);
  transform: translateY(-1px);
}

.btn-checkout:active {
  transform: scale(0.97);
}

.checkout-icon {
  font-size: 16px;
}

/* Empty State */
.empty-cart {
  background: linear-gradient(180deg, #34C759 0%, #28B84A 100%);
  color: white;
  padding: 20px 24px;
  text-align: center;
  border-radius: 20px 20px 0 0;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.empty-icon {
  font-size: 32px;
  opacity: 0.9;
}

.empty-text {
  font-size: 16px;
  font-weight: 500;
  opacity: 0.9;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideOut {
  from { opacity: 1; transform: translateX(0); }
  to { opacity: 0; transform: translateX(-100%); }
}

.cart-item {
  animation: fadeIn 0.3s ease;
}

.cart-item.removing {
  animation: slideOut 0.3s ease forwards;
}

/* Responsive */
@media (max-width: 480px) {
  .cart-header {
    padding: 16px 20px;
  }
  
  .cart-icon {
    padding: 6px;
  }
  
  .cart-emoji {
    font-size: 18px;
  }
  
  .cart-title {
    font-size: 14px;
  }
  
  .cart-total {
    font-size: 12px;
  }
  
  .cart-items {
    padding: 20px;
    gap: 12px;
  }
  
  .cart-item {
    padding: 12px;
  }
  
  .item-image {
    width: 56px;
    height: 56px;
    margin-right: 12px;
  }
  
  .item-name {
    font-size: 14px;
  }
  
  .item-details {
    font-size: 12px;
  }
  
  .btn-quantity,
  .btn-remove {
    width: 28px;
    height: 28px;
    font-size: 14px;
  }
  
  .cart-footer {
    padding: 20px;
  }
  
  .btn-checkout {
    font-size: 16px;
    padding: 14px 18px;
  }
}
</style>