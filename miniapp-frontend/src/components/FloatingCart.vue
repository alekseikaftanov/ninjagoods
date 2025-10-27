<template>
  <div class="floating-cart" :class="{ 'has-items': cartStore.totalItems > 0 }">
    <!-- Cart Info Header -->
    <div class="cart-info-header">
      <div class="cart-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7 18C5.9 18 5.01 18.9 5.01 20C5.01 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM1 2V4H3L6.6 11.59L5.25 14.04C5.09 14.32 5 14.65 5 15C5 16.1 5.9 17 7 17H19V15H7.42C7.28 15 7.17 14.89 7.17 14.75L7.2 14.63L8.1 13H15.55C16.3 13 16.96 12.59 17.3 11.97L20.88 5.5C21.25 4.84 20.76 4 20 4H5.21L4.27 2M17 18C15.9 18 15.01 18.9 15.01 20C15.01 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18Z" fill="currentColor"/>
        </svg>
        <span v-if="cartStore.totalItems > 0" class="cart-badge">{{ cartStore.totalItems }}</span>
      </div>
      <div class="cart-text">
        <span class="cart-title">Корзина</span>
        <span class="cart-total">{{ cartStore.totalPrice.toFixed(2) }} ₽</span>
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

    <!-- Action Button -->
    <div class="cart-action">
      <button 
        v-if="cartStore.totalItems > 0" 
        @click="checkout" 
        class="btn-checkout"
      >
        Перейти к оформлению
      </button>
      <div v-else class="empty-state">
        Добавьте товары, чтобы оформить заказ
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
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  border-radius: 16px 16px 0 0;
  box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  padding: 20px 32px;
  height: auto;
  min-height: 120px;
}

/* Cart Info Header */
.cart-info-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.cart-icon {
  position: relative;
  background: rgba(0, 122, 255, 0.1);
  color: #007AFF;
  border-radius: 8px;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.cart-icon svg {
  display: block;
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

.cart-text {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  padding-left: 12px;
}

.cart-title {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
}

.cart-total {
  font-size: 18px;
  font-weight: 700;
  color: #1C1C1E;
  margin-left: auto;
}

/* Expanded Cart (hidden for now) */
.cart-expanded {
  display: none;
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

/* Cart Action */
.cart-action {
  margin-top: 16px;
}

.btn-checkout {
  width: 100%;
  background: linear-gradient(180deg, #34C759 0%, #28B84A 100%);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 17px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 4px 8px rgba(52, 199, 89, 0.3);
  text-align: center;
}

.btn-checkout:hover {
  filter: brightness(1.05);
  box-shadow: 0 6px 12px rgba(52, 199, 89, 0.4);
}

.btn-checkout:active {
  transform: scale(0.97);
}

.checkout-icon {
  font-size: 16px;
}

/* Empty State */
.empty-state {
  background: rgba(0, 0, 0, 0.03);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  color: #8E8E93;
  font-weight: 500;
  font-size: 14px;
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
  .floating-cart {
    padding: 16px 20px;
  }
  
  .cart-icon {
    width: 32px;
    height: 32px;
    padding: 6px;
  }
  
  .cart-icon svg {
    width: 20px;
    height: 20px;
  }
  
  .cart-title {
    font-size: 14px;
  }
  
  .cart-total {
    font-size: 16px;
  }
  
  .btn-checkout {
    font-size: 16px;
    padding: 12px;
  }
  
  .empty-state {
    font-size: 13px;
    padding: 14px;
  }
}
</style>