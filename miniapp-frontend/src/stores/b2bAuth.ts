import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { b2bAPI } from '../utils/b2bApi'

export type B2BUserRole = 'buyer' | 'employee' | null

export interface B2BUser {
  id: number
  telegram_id: number
  first_name: string
  last_name: string
  username: string
  role: B2BUserRole
  organization_id: number | null
}

export const useB2BAuthStore = defineStore('b2bAuth', () => {
  const user = ref<B2BUser | null>(null)
  const token = ref<string | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isBuyer = computed(() => user.value?.role === 'buyer')
  const isEmployee = computed(() => user.value?.role === 'employee')
  const hasRole = computed(() => !!user.value?.role)
  const hasOrganization = computed(() => !!user.value?.organization_id)

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
      const response = await b2bAPI.auth.telegram(telegramData)
      
      if (response.success) {
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('b2b_token', token.value)
        return true
      }
      
      throw new Error('Authentication failed')
    } catch (err) {
      console.error('B2B Auth error:', err)
      error.value = err instanceof Error ? err.message : 'Authentication error'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Set user role
  const setRole = async (role: 'buyer' | 'employee') => {
    isLoading.value = true
    error.value = null

    try {
      const response = await b2bAPI.auth.setRole(role)
      
      if (response.success) {
        user.value = response.data
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
      const response = await b2bAPI.auth.me()
      
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
    const storedToken = localStorage.getItem('b2b_token')
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
    localStorage.removeItem('b2b_token')
  }

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    isBuyer,
    isEmployee,
    hasRole,
    hasOrganization,
    authenticateWithTelegram,
    setRole,
    getCurrentUser,
    initFromStorage,
    logout,
  }
})

