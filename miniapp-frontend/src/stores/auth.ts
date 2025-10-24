import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '../utils/api'
import { getTelegramUser } from '../utils/telegram'
import type { User } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value)

  // Автоматическая аутентификация через Telegram
  const authenticateWithTelegram = async () => {
    isLoading.value = true
    error.value = null

    try {
      const telegramUser = getTelegramUser()
      
      // Если нет Telegram пользователя (режим разработки), создаем mock пользователя
      if (!telegramUser) {
        console.log('Development mode: Creating mock user')
        user.value = {
          id: 1,
          telegram_id: 'dev_user_123',
          name: 'Тестовый пользователь',
          phone: '+7 (999) 123-45-67'
        }
        return true
      }

      const response = await authAPI.telegram({
        telegram_id: telegramUser.id.toString(),
        name: `${telegramUser.first_name} ${telegramUser.last_name || ''}`.trim(),
        phone: '', // Telegram не предоставляет номер телефона
      })

      if (response.success) {
        user.value = response.user
        return true
      } else {
        throw new Error('Authentication failed')
      }
    } catch (err) {
      console.error('Auth error:', err)
      error.value = err instanceof Error ? err.message : 'Authentication error'
      
      // В режиме разработки создаем mock пользователя при ошибке
      if (process.env.NODE_ENV === 'development') {
        console.log('Development mode: Creating mock user after error')
        user.value = {
          id: 1,
          telegram_id: 'dev_user_123',
          name: 'Тестовый пользователь',
          phone: '+7 (999) 123-45-67'
        }
        return true
      }
      
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Выход из системы
  const logout = () => {
    user.value = null
    error.value = null
  }

  return {
    user,
    isLoading,
    error,
    isAuthenticated,
    authenticateWithTelegram,
    logout,
  }
})
