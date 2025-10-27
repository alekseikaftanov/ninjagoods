import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { API } from '../utils/restaurantApi'

export type UserRole = 'customer' | 'buyer' | 'employee' | null

export interface User {
  id: number
  telegram_id: number
  first_name: string
  last_name: string
  username: string
  role: UserRole
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isCustomer = computed(() => user.value?.role === 'customer')
  const isBuyer = computed(() => user.value?.role === 'buyer')
  const isEmployee = computed(() => user.value?.role === 'employee')
  const hasRole = computed(() => !!user.value?.role)

  // Authenticate with Telegram
  const authenticateWithTelegram = async (telegramData: {
    id: number
    first_name: string
    last_name?: string
    username?: string
    photo_url?: string
    auth_date: number
    hash: string
  }) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await API.auth.telegram(telegramData)
      
      if (response.success) {
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('jwt_token', token.value)
        return true
      }
      
      throw new Error('Authentication failed')
    } catch (err) {
      console.error('Auth error:', err)
      error.value = err instanceof Error ? err.message : 'Authentication error'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Set user role
  const setRole = async (role: 'customer' | 'buyer' | 'employee') => {
    isLoading.value = true
    error.value = null

    try {
      const response = await API.auth.setRole(role)
      
      if (response.success) {
        user.value = response.data.user
        return true
      }
      
      throw new Error('Failed to set role')
    } catch (err) {
      console.error('Set role error:', err)
      error.value = err instanceof Error ? err.message : 'Failed to set role'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Get current user
  const getCurrentUser = async () => {
    isLoading.value = true
    error.value = null

    try {
      const response = await API.auth.me()
      
      if (response.success) {
        user.value = response.data
        return true
      }
      
      throw new Error('Failed to get user')
    } catch (err) {
      console.error('Get user error:', err)
      error.value = err instanceof Error ? err.message : 'Failed to get user'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Initialize from local storage
  const initFromStorage = () => {
    const storedToken = localStorage.getItem('jwt_token')
    if (storedToken) {
      token.value = storedToken
      getCurrentUser()
    }
  }

  // Logout
  const logout = () => {
    user.value = null
    token.value = null
    error.value = null
    localStorage.removeItem('jwt_token')
  }

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    isCustomer,
    isBuyer,
    isEmployee,
    hasRole,
    authenticateWithTelegram,
    setRole,
    getCurrentUser,
    initFromStorage,
    logout,
  }
})
