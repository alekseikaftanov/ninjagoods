import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('admin_token'))
  const isAuthenticated = ref(!!token.value)

  const login = async (email: string, password: string) => {
    try {
      const response = await axios.post('http://localhost:8000/api/admin/login', {
        email,
        password
      })

      if (response.data.success) {
        token.value = response.data.token
        user.value = response.data.user
        isAuthenticated.value = true
        localStorage.setItem('admin_token', token.value)
        return response.data
      } else {
        throw new Error(response.data.message || 'Ошибка входа')
      }
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Ошибка входа')
    }
  }

  const logout = () => {
    token.value = null
    user.value = null
    isAuthenticated.value = false
    localStorage.removeItem('admin_token')
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout
  }
})
