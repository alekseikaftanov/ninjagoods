import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useB2BAuthStore } from '../stores/b2bAuth'
import Home from '../views/Home.vue'
import Categories from '../views/Categories.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import OrderSuccess from '../views/OrderSuccess.vue'
import TelegramAuth from '../views/b2b/TelegramAuth.vue'
import RoleSelection from '../views/b2b/RoleSelection.vue'

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
      meta: { requiresB2BAuth: true },
    },
    {
      path: '/organization',
      name: 'Organization',
      component: () => import('../views/b2b/OrganizationRegistration.vue'),
      meta: { requiresB2BAuth: true, requiresBuyer: true },
    },
    {
      path: '/invite',
      name: 'Invite',
      component: () => import('../views/b2b/InviteJoin.vue'),
      meta: { requiresB2BAuth: true, requiresEmployee: true },
    },
    {
      path: '/orders',
      name: 'Orders',
      component: () => import('../views/b2b/BuyerOrders.vue'),
      meta: { requiresB2BAuth: true },
    },
    {
      path: '/organization/management',
      name: 'OrganizationManagement',
      component: () => import('../views/b2b/OrganizationManagement.vue'),
      meta: { requiresB2BAuth: true, requiresBuyer: true },
    },
  ],
})

// Навигационные хуки для Telegram WebApp
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const b2bAuthStore = useB2BAuthStore()
  
  // Main app routes require B2B authentication
  if (to.meta.requiresAuth) {
    if (!b2bAuthStore.isAuthenticated) {
      next('/login')
      return
    }
  }
  
  // Organization routes protection
  if (to.meta.requiresB2BAuth) {
    if (!b2bAuthStore.isAuthenticated) {
      next('/login')
      return
    }
    
    if (to.meta.requiresBuyer && !b2bAuthStore.isBuyer) {
      next('/role-selection')
      return
    }
    
    if (to.meta.requiresEmployee && !b2bAuthStore.isEmployee) {
      next('/role-selection')
      return
    }
  }
  
  // Regular routes authentication (deprecated, kept for compatibility)
  if (!authStore.isAuthenticated) {
    authStore.authenticateWithTelegram()
  }
  
  next()
})

export default router
