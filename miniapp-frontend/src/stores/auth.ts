import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '../utils/api'
import { getTelegramUser } from '../utils/telegram'
import type { User } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('jwt_token'))
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const needsRoleSelection = ref(false)

  const isAuthenticated = computed(() => !!user.value && !!token.value)

  // Автоматическая аутентификация через Telegram (новая B2B система)
  const authenticateWithTelegram = async () => {
    isLoading.value = true
    error.value = null

    try {
      const telegramUser = getTelegramUser()
      
      // Если нет Telegram пользователя (режим разработки), создаем mock данные
      if (!telegramUser) {
        console.log('Development mode: Creating mock authentication')
        const mockResponse = {
          success: true,
          data: {
            user: {
              id: 1,
              telegram_id: 123456,
              first_name: 'Тестовый',
              last_name: 'Пользователь',
              username: 'testuser',
              name: 'Тестовый Пользователь',
              role: 'customer',
              organization_id: null
            },
            token: 'mock_jwt_token_dev',
            needs_role_selection: false,
            needs_organization: false
          }
        }
        
        user.value = mockResponse.data.user
        token.value = mockResponse.data.token
        localStorage.setItem('jwt_token', mockResponse.data.token)
        return true
      }

      // Получаем хеш и auth_date из Telegram WebApp
      const authDate = Math.floor(Date.now() / 1000)
      const hash = 'mock_hash' // В production это будет реальный хеш от Telegram
      
      const response = await authAPI.telegram({
        id: telegramUser.id,
        first_name: telegramUser.first_name,
        last_name: telegramUser.last_name,
        username: telegramUser.username,
        photo_url: telegramUser.photo_url,
        auth_date: authDate,
        hash: hash
      })

      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
        needsRoleSelection.value = response.data.needs_role_selection
        
        // Сохраняем токен в localStorage
        localStorage.setItem('jwt_token', response.data.token)
        
        return true
      } else {
        throw new Error('Authentication failed')
      }
    } catch (err) {
      console.error('Auth error:', err)
      error.value = err instanceof Error ? err.message : 'Authentication error'
      
      // В режиме разработки создаем mock при ошибке
      if (process.env.NODE_ENV === 'development') {
        console.log('Development mode: Creating mock user after error')
        user.value = {
          id: 1,
          telegram_id: 123456,
          first_name: 'Тестовый',
          last_name: 'Пользователь',
          name: 'Тестовый Пользователь',
          role: 'customer'
        }
        token.value = 'mock_jwt_token_dev'
        localStorage.setItem('jwt_token', 'mock_jwt_token_dev')
        return true
      }
      
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Установка роли пользователя
  const setRole = async (role: 'customer' | 'buyer' | 'employee') => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authAPI.setRole(role)
      
      if (response.success) {
        user.value = response.data.user
        needsRoleSelection.value = false
        return true
      } else {
        throw new Error('Failed to set role')
      }
    } catch (err) {
      console.error('Set role error:', err)
      error.value = err instanceof Error ? err.message : 'Failed to set role'
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Выход из системы
  const logout = () => {
    user.value = null
    token.value = null
    error.value = null
    needsRoleSelection.value = false
    localStorage.removeItem('jwt_token')
  }

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    needsRoleSelection,
    authenticateWithTelegram,
    setRole,
    logout,
  }
})
