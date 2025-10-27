import axios from 'axios'

const API_BASE_URL = 'http://localhost:8001/api'

const b2bApi = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Add JWT token to requests
b2bApi.interceptors.request.use((config) => {
  const token = localStorage.getItem('b2b_token')
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
      localStorage.removeItem('b2b_token')
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

// Organization Types
interface Organization {
  id: number
  name: string
  legal_name: string
  inn: string
  kpp: string
  ogrn: string
  address_legal: string
  address_actual: string
  phone: string
  email: string
  comment?: string
  created_by: number
  created_at: string
  updated_at: string
}

interface CreateOrganizationData {
  name: string
  legal_name: string
  inn: string
  kpp: string
  ogrn: string
  address_legal: string
  address_actual: string
  phone: string
  email: string
  comment?: string
}

// Order Types
interface Order {
  id: number
  organization_id: number
  buyer_id: number | null
  status: 'draft' | 'submitted'
  submitted_at: string | null
  total: number
  created_at: string
  updated_at: string
  orderItems?: OrderItem[]
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
      const response = await b2bApi.post('/b2b/auth/telegram', data)
      return response.data
    },
    setRole: async (role: 'buyer' | 'employee') => {
      const response = await b2bApi.post('/b2b/auth/role', { role })
      return response.data
    },
    me: async () => {
      const response = await b2bApi.get('/b2b/auth/me')
      return response.data
    },
  },

  organization: {
    create: async (data: CreateOrganizationData) => {
      const response = await b2bApi.post('/b2b/organization', data)
      return response.data
    },
    get: async () => {
      const response = await b2bApi.get('/b2b/organization')
      return response.data
    },
    update: async (data: Partial<CreateOrganizationData>) => {
      const response = await b2bApi.put('/b2b/organization', data)
      return response.data
    },
    generateInvite: async () => {
      const response = await b2bApi.post('/b2b/organization/invite')
      return response.data
    },
  },

  invite: {
    validate: async (token: string) => {
      const response = await b2bApi.get(`/b2b/invites/validate?token=${token}`)
      return response.data
    },
    join: async (token: string) => {
      const response = await b2bApi.post('/b2b/invites/join', { token })
      return response.data
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

export type { Organization, CreateOrganizationData, Order, OrderItem, CreateOrderItemData }

