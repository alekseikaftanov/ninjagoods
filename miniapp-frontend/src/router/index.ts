import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Home from '../views/Home.vue'
import Categories from '../views/Categories.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import OrderSuccess from '../views/OrderSuccess.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home,
    },
    {
      path: '/categories',
      name: 'Categories',
      component: Categories,
    },
    {
      path: '/products/:categoryId?',
      name: 'Products',
      component: Products,
    },
    {
      path: '/product/:id',
      name: 'ProductDetail',
      component: ProductDetail,
    },
    {
      path: '/cart',
      name: 'Cart',
      component: Cart,
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: Checkout,
    },
    {
      path: '/order-success',
      name: 'OrderSuccess',
      component: OrderSuccess,
    },
  ],
})

// Навигационные хуки для Telegram WebApp
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Если пользователь не аутентифицирован, попробуем аутентифицировать через Telegram
  if (!authStore.isAuthenticated) {
    authStore.authenticateWithTelegram()
  }
  
  next()
})

export default router
