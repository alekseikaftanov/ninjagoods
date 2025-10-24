import axios from 'axios'
import { getInitData } from './telegram'
import type { Category, Product, Order, User } from '../types'

const API_BASE_URL = 'http://localhost:8001/api'

// Создание экземпляра axios с базовой конфигурацией
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Интерцептор для добавления Telegram initData
api.interceptors.request.use((config) => {
  const initData = getInitData()
  if (initData) {
    config.headers['X-Telegram-Init-Data'] = initData
  }
  return config
})

// Интерцептор для обработки ошибок
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error.response?.data || error.message)
    return Promise.reject(error)
  }
)

// API для аутентификации
export const authAPI = {
  // Регистрация/логин через Telegram
  telegram: async (userData: {
    telegram_id: string
    name: string
    phone: string
  }) => {
    const response = await api.post('/auth/telegram', userData)
    return response.data
  },
}

// API для категорий
export const categoriesAPI = {
  // Получить все категории
  getAll: async (): Promise<Category[]> => {
    const response = await api.get('/categories')
    return response.data.data
  },
}

// API для товаров
export const productsAPI = {
  // Получить все товары
  getAll: async (): Promise<Product[]> => {
    const response = await api.get('/products')
    return response.data.data
  },
  
  // Получить товары по категории
  getByCategory: async (categoryId: number): Promise<Product[]> => {
    const response = await api.get(`/products?category_id=${categoryId}`)
    return response.data.data
  },
  
  // Получить товар по ID
  getById: async (id: number): Promise<Product> => {
    const response = await api.get(`/products/${id}`)
    return response.data.data
  },
}

// API для заказов
export const ordersAPI = {
  // Создать заказ
  create: async (orderData: {
    user_id: number
    items: Array<{
      product_id: number
      quantity: number
    }>
    comment?: string
  }): Promise<Order> => {
    const response = await api.post('/orders', orderData)
    return response.data.data
  },
}

export default api
