<template>
  <div class="floating-cart" :class="{ 'has-items': cartStore.totalItems > 0 }">
    <div class="cart-content" @click="toggleCart">
      <div class="cart-icon">
        <span class="cart-emoji">🛒</span>
        <span v-if="cartStore.totalItems > 0" class="cart-badge">{{ cartStore.totalItems }}</span>
      </div>
      <div class="cart-info">
        <div class="cart-title">Корзина</div>
        <div class="cart-total">{{ cartStore.totalPrice }} ₽</div>
      </div>
      <div class="cart-arrow">
        <span>{{ isExpanded ? '▼' : '▲' }}</span>
      </div>
    </div>
    
    <!-- Expanded Cart -->
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
            <div class="item-price">{{ item.product.price }} ₽ × {{ item.quantity }}</div>
          </div>
          <div class="item-actions">
            <button @click.stop="cartStore.decreaseQuantity(item.product.id)" class="btn-quantity">-</button>
            <span class="quantity">{{ item.quantity }}</span>
            <button @click.stop="cartStore.increaseQuantity(item.product.id)" class="btn-quantity">+</button>
            <button @click.stop="cartStore.removeItem(item.product.id)" class="btn-remove">×</button>
          </div>
        </div>
      </div>
      
      <div class="cart-footer">
        <div class="cart-summary">
          <div class="summary-line">
            <span>Товаров:</span>
            <span>{{ cartStore.totalItems }}</span>
          </div>
          <div class="summary-line total">
            <span>Итого:</span>
            <span>{{ cartStore.totalPrice }} ₽</span>
          </div>
        </div>
        <button @click="checkout" class="btn-checkout">
          Оформить заказ
        </button>
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
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  transition: all 0.3s ease;
}

.floating-cart.has-items {
  background: linear-gradient(135deg, var(--accent-blue) 0%, #0056CC 100%);
  color: white;
}

.cart-content {
  display: flex;
  align-items: center;
  padding: var(--spacing-md);
  cursor: pointer;
  transition: all 0.2s ease;
}

.cart-content:hover {
  background: rgba(255, 255, 255, 0.1);
}

.cart-icon {
  position: relative;
  margin-right: var(--spacing-md);
}

.cart-emoji {
  font-size: 24px;
}

.cart-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: var(--accent-red);
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
}

.cart-info {
  flex: 1;
}

.cart-title {
  font-size: var(--font-size-regular);
  font-weight: 600;
  margin-bottom: 2px;
}

.cart-total {
  font-size: var(--font-size-small);
  opacity: 0.8;
}

.cart-arrow {
  font-size: 16px;
  opacity: 0.7;
}

.cart-expanded {
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  max-height: 400px;
  overflow-y: auto;
}

.cart-items {
  padding: var(--spacing-md);
}

.cart-item {
  display: flex;
  align-items: center;
  padding: var(--spacing-sm) 0;
  border-bottom: 1px solid var(--border-color);
}

.cart-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  margin-right: var(--spacing-sm);
}

.item-info {
  flex: 1;
  margin-right: var(--spacing-sm);
}

.item-name {
  font-size: var(--font-size-small);
  font-weight: 500;
  margin-bottom: 2px;
}

.item-price {
  font-size: var(--font-size-caption);
  color: var(--text-secondary);
}

.item-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
}

.btn-quantity {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-quantity:hover {
  background: var(--accent-blue);
  color: white;
  border-color: var(--accent-blue);
}

.quantity {
  font-size: var(--font-size-small);
  font-weight: 600;
  min-width: 20px;
  text-align: center;
}

.btn-remove {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1px solid var(--accent-red);
  background: var(--accent-red);
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background: #D70015;
  border-color: #D70015;
}

.cart-footer {
  padding: var(--spacing-md);
  border-top: 1px solid var(--border-color);
  background: var(--bg-tertiary);
}

.cart-summary {
  margin-bottom: var(--spacing-md);
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: var(--spacing-xs);
  font-size: var(--font-size-small);
}

.summary-line.total {
  font-weight: 600;
  font-size: var(--font-size-regular);
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-xs);
  margin-top: var(--spacing-sm);
}

.btn-checkout {
  width: 100%;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  font-size: var(--font-size-regular);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-checkout:hover {
  background: #0056CC;
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 480px) {
  .cart-content {
    padding: var(--spacing-sm) var(--spacing-md);
  }
  
  .cart-emoji {
    font-size: 20px;
  }
  
  .cart-title {
    font-size: var(--font-size-small);
  }
  
  .cart-total {
    font-size: var(--font-size-caption);
  }
  
  .item-image {
    width: 32px;
    height: 32px;
  }
  
  .item-name {
    font-size: var(--font-size-caption);
  }
  
  .btn-quantity,
  .btn-remove {
    width: 20px;
    height: 20px;
    font-size: 12px;
  }
}
</style>
