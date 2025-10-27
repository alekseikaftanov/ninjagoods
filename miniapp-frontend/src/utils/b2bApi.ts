import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

const b2bApi = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Add JWT token to requests
b2bApi.interceptors.request.use((config) => {
  const token = localStorage.getItem('jwt_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle errors
b2bApi.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('jwt_token')
      // Redirect to login
      if (window.location.pathname !== '/b2b/login') {
        window.location.href = '/b2b/login'
      }
    }
    console.error('B2B API Error:', error.response?.data || error.message)
    return Promise.reject(error)
  }
)

// Telegram Data Types
interface TelegramAuthData {
  id: number
  first_name: string
  last_name?: string
  username?: string
  photo_url?: string
  auth_date: number
  hash: string
}

// Restaurant Types
interface Restaurant {
  id: number
  name: string
  legal_name: string
  inn: string
  kpp: string
  ogrn: string
  address_legal: string
  address_actual: string
  phone: string
  created_by: number
  created_at: string
  updated_at: string
  employees?: any[]
  buyer?: any
}

interface CreateRestaurantData {
  name: string
  legal_name: string
  inn: string
  kpp?: string
  ogrn: string
  address_legal: string
  address_actual: string
  phone: string
}

// Order Types
interface Order {
  id: number
  restaurant_id: number
  buyer_id: number | null
  status: 'draft' | 'submitted'
  submitted_at: string | null
  total: number
  created_at: string
  updated_at: string
  orderItems?: OrderItem[]
  restaurant?: Restaurant
}

interface OrderItem {
  id: number
  order_id: number
  employee_id: number
  product_name: string
  quantity: number
  comment?: string
  created_at: string
  updated_at: string
}

interface CreateOrderItemData {
  product_id: number
  quantity: number
  comment?: string
}

// API methods
export const b2bAPI = {
  auth: {
    telegram: async (data: TelegramAuthData) => {
      const response = await b2bApi.post('/auth/telegram', data)
      return response.data
    },
    setRole: async (role: 'buyer' | 'employee' | 'customer') => {
      const response = await b2bApi.post('/auth/role', { role })
      return response.data
    },
    me: async () => {
      const response = await b2bApi.get('/auth/me')
      return response.data
    },
  },

  restaurants: {
    getAll: async (): Promise<Restaurant[]> => {
      const response = await b2bApi.get('/restaurants')
      return response.data.data
    },
    create: async (data: CreateRestaurantData): Promise<Restaurant> => {
      const response = await b2bApi.post('/restaurants', data)
      return response.data.data
    },
    getById: async (id: number): Promise<Restaurant> => {
      const response = await b2bApi.get(`/restaurants/${id}`)
      return response.data.data
    },
    update: async (id: number, data: Partial<CreateRestaurantData>): Promise<Restaurant> => {
      const response = await b2bApi.put(`/restaurants/${id}`, data)
      return response.data.data
    },
    delete: async (id: number) => {
      const response = await b2bApi.delete(`/restaurants/${id}`)
      return response.data
    },
    getEmployees: async (id: number) => {
      const response = await b2bApi.get(`/restaurants/${id}/employees`)
      return response.data.data
    },
    addEmployee: async (id: number, userId: number) => {
      const response = await b2bApi.post(`/restaurants/${id}/employees`, { user_id: userId })
      return response.data.data
    },
    removeEmployee: async (id: number, employeeId: number) => {
      const response = await b2bApi.delete(`/restaurants/${id}/employees/${employeeId}`)
      return response.data
    },
  },

  invite: {
    validate: async (token: string) => {
      const response = await b2bApi.get(`/invites/validate?token=${token}`)
      return response.data
    },
    join: async (token: string) => {
      const response = await b2bApi.post('/invites/join', { token })
      return response.data
    },
    generate: async (restaurantId: number, expiresInDays?: number) => {
      const response = await b2bApi.post('/invites/generate', {
        restaurant_id: restaurantId,
        expires_in_days: expiresInDays,
      })
      return response.data
    },
    getAll: async (restaurantId: number) => {
      const response = await b2bApi.get(`/invites?restaurant_id=${restaurantId}`)
      return response.data.data
    },
  },

  orders: {
    getAll: async (): Promise<Order[]> => {
      const response = await b2bApi.get('/b2b/orders')
      return response.data.data
    },
    getById: async (id: number): Promise<Order> => {
      const response = await b2bApi.get(`/b2b/orders/${id}`)
      return response.data.data
    },
    create: async (): Promise<Order> => {
      const response = await b2bApi.post('/b2b/orders')
      return response.data.data
    },
    addItem: async (orderId: number, item: CreateOrderItemData): Promise<OrderItem> => {
      const response = await b2bApi.post(`/b2b/orders/${orderId}/items`, item)
      return response.data.data
    },
    deleteItem: async (orderId: number, itemId: number) => {
      const response = await b2bApi.delete(`/b2b/orders/${orderId}/items/${itemId}`)
      return response.data
    },
    submit: async (orderId: number) => {
      const response = await b2bApi.post(`/b2b/orders/${orderId}/submit`)
      return response.data.data
    },
  },
}

export type { Restaurant, CreateRestaurantData, Order, OrderItem, CreateOrderItemData }

