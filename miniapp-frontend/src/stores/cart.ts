import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { CartItem, Product } from '../types'

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])

  // Общее количество товаров в корзине
  const totalItems = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  // Общая стоимость корзины
  const totalPrice = computed(() => {
    return items.value.reduce((total, item) => {
      return total + (item.product.price * item.quantity)
    }, 0)
  })

  // Проверить, есть ли товар в корзине
  const isInCart = (productId: number) => {
    return items.value.some(item => item.product.id === productId)
  }

  // Получить количество товара в корзине
  const getQuantity = (productId: number) => {
    const item = items.value.find(item => item.product.id === productId)
    return item ? item.quantity : 0
  }

  // Добавить товар в корзину
  const addToCart = (product: Product, quantity: number = 1) => {
    const existingItem = items.value.find(item => item.product.id === product.id)
    
    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({
        product,
        quantity
      })
    }
  }

  // Обновить количество товара
  const updateQuantity = (productId: number, quantity: number) => {
    const item = items.value.find(item => item.product.id === productId)
    if (item) {
      if (quantity <= 0) {
        removeFromCart(productId)
      } else {
        item.quantity = quantity
      }
    }
  }

  // Удалить товар из корзины
  const removeFromCart = (productId: number) => {
    const index = items.value.findIndex(item => item.product.id === productId)
    if (index > -1) {
      items.value.splice(index, 1)
    }
  }

  // Очистить корзину
  const clearCart = () => {
    items.value = []
  }

  // Получить товары для заказа
  const getOrderItems = () => {
    return items.value.map(item => ({
      product_id: item.product.id,
      quantity: item.quantity,
      price: item.product.price
    }))
  }

  return {
    items,
    totalItems,
    totalPrice,
    isInCart,
    getQuantity,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    getOrderItems,
  }
})
