<template>
  <div class="cart">
    <div class="header">
      <h1>🛒 Корзина</h1>
    </div>

    <div v-if="cartStore.items.length === 0" class="empty-cart">
      <div class="empty-icon">🛒</div>
      <h2>Корзина пуста</h2>
      <p>Добавьте товары из каталога</p>
      <router-link to="/categories" class="btn btn-primary">
        Перейти к каталогу
      </router-link>
    </div>

    <div v-else class="cart-content">
      <div class="cart-items">
        <div
          v-for="item in cartStore.items"
          :key="item.product.id"
          class="cart-item"
        >
          <img :src="item.product.photo_url" :alt="item.product.name" class="item-image" />
          
          <div class="item-info">
            <h3>{{ item.product.name }}</h3>
            <p class="item-price">{{ item.product.price }} ₽ за {{ item.product.unit }}</p>
            <p class="item-min-order">Мин. заказ: {{ item.product.min_order }} {{ item.product.unit }}</p>
          </div>

          <div class="item-controls">
            <div class="quantity-controls">
              <button
                @click="decreaseQuantity(item.product.id)"
                class="btn btn-secondary btn-small"
                :disabled="item.quantity <= item.product.min_order"
              >
                -
              </button>
              <span class="quantity">{{ item.quantity }}</span>
              <button
                @click="increaseQuantity(item.product.id)"
                class="btn btn-secondary btn-small"
              >
                +
              </button>
            </div>
            
            <button
              @click="removeFromCart(item.product.id)"
              class="btn btn-danger btn-small"
            >
              Удалить
            </button>
          </div>
        </div>
      </div>

      <div class="cart-summary">
        <div class="summary-row">
          <span>Товаров:</span>
          <span>{{ cartStore.totalItems }}</span>
        </div>
        <div class="summary-row total">
          <span>Итого:</span>
          <span>{{ cartStore.totalPrice.toFixed(2) }} ₽</span>
        </div>
      </div>

      <div class="cart-actions">
        <button @click="clearCart" class="btn btn-secondary">
          Очистить корзину
        </button>
        <router-link to="/checkout" class="btn btn-primary">
          Оформить заказ
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '../stores/cart'
import { hapticFeedback } from '../utils/telegram'

const cartStore = useCartStore()

const increaseQuantity = (productId: number) => {
  const currentQuantity = cartStore.getQuantity(productId)
  cartStore.updateQuantity(productId, currentQuantity + 1)
  hapticFeedback('light')
}

const decreaseQuantity = (productId: number) => {
  const currentQuantity = cartStore.getQuantity(productId)
  cartStore.updateQuantity(productId, currentQuantity - 1)
  hapticFeedback('light')
}

const removeFromCart = (productId: number) => {
  cartStore.removeFromCart(productId)
  hapticFeedback('medium')
}

const clearCart = () => {
  if (confirm('Очистить корзину?')) {
    cartStore.clearCart()
    hapticFeedback('medium')
  }
}
</script>

<style scoped>
.cart {
  max-width: 100%;
}

.header {
  margin-bottom: 24px;
}

.empty-cart {
  text-align: center;
  padding: 48px 24px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.empty-cart h2 {
  margin-bottom: 8px;
  color: var(--tg-theme-text-color, #000000);
}

.empty-cart p {
  color: var(--tg-theme-hint-color, #666666);
  margin-bottom: 24px;
}

.cart-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  flex-shrink: 0;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-info h3 {
  font-size: 16px;
  margin-bottom: 4px;
  line-height: 1.3;
}

.item-price {
  font-size: 14px;
  color: var(--tg-theme-button-color, #007AFF);
  font-weight: 600;
  margin-bottom: 2px;
}

.item-min-order {
  font-size: 12px;
  color: var(--tg-theme-hint-color, #666666);
}

.item-controls {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-end;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.quantity {
  font-size: 16px;
  font-weight: 600;
  min-width: 24px;
  text-align: center;
}

.btn-small {
  padding: 6px 10px;
  font-size: 14px;
  min-height: 32px;
}

.cart-summary {
  background: var(--tg-theme-secondary-bg-color, #f8f8f8);
  border-radius: 12px;
  padding: 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.summary-row.total {
  font-size: 18px;
  font-weight: 600;
  color: var(--tg-theme-button-color, #007AFF);
  border-top: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  padding-top: 8px;
  margin-top: 8px;
}

.cart-actions {
  display: flex;
  gap: 12px;
}

.cart-actions .btn {
  flex: 1;
}

@media (max-width: 480px) {
  .cart-item {
    flex-direction: column;
    align-items: stretch;
  }
  
  .item-image {
    width: 100%;
    height: 120px;
  }
  
  .item-controls {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
  
  .cart-actions {
    flex-direction: column;
  }
}
</style>
