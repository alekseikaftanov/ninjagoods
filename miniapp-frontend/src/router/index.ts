import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore as useOldAuthStore } from '../stores/auth'
import { useAuthStore } from '../stores/mainAuth'
import Home from '../views/Home.vue'
import Categories from '../views/Categories.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import OrderSuccess from '../views/OrderSuccess.vue'
import TelegramAuth from '../views/TelegramAuth.vue'
import RoleSelection from '../views/RoleSelection.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Login route - entry point for all users
    {
      path: '/login',
      name: 'Login',
      component: TelegramAuth,
    },
    // Main app routes (require authentication)
    {
      path: '/',
      name: 'Home',
      component: Home,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories',
      name: 'Categories',
      component: Categories,
      meta: { requiresAuth: true },
    },
    {
      path: '/products/:categoryId?',
      name: 'Products',
      component: Products,
      meta: { requiresAuth: true },
    },
    {
      path: '/product/:id',
      name: 'ProductDetail',
      component: ProductDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/cart',
      name: 'Cart',
      component: Cart,
      meta: { requiresAuth: true },
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: Checkout,
      meta: { requiresAuth: true },
    },
    {
      path: '/order-success',
      name: 'OrderSuccess',
      component: OrderSuccess,
      meta: { requiresAuth: true },
    },
    // Organization and management routes
    {
      path: '/role-selection',
      name: 'RoleSelection',
      component: RoleSelection,
      meta: { requiresAuth: true },
    },
    {
      path: '/invite',
      name: 'Invite',
      component: () => import('../views/InviteJoin.vue'),
      meta: { requiresAuth: true, requiresEmployee: true },
    },
    {
      path: '/orders',
      name: 'Orders',
      component: () => import('../views/Orders.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/restaurant/management',
      name: 'RestaurantManagement',
      component: () => import('../views/RestaurantManagement.vue'),
      meta: { requiresAuth: true, requiresBuyer: true },
    },
  ],
})

// Навигационные хуки для Telegram WebApp
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Main app routes require authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next('/login')
      return
    }
    
    if (to.meta.requiresBuyer && !authStore.isBuyer) {
      next('/role-selection')
      return
    }
    
    if (to.meta.requiresEmployee && !authStore.isEmployee) {
      next('/role-selection')
      return
    }
  }
  
  next()
})

export default router
