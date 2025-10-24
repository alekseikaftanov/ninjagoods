<template>
  <AdminLayout>
    <div class="products">
      <div class="page-header">
        <h1>Товары</h1>
        <button @click="showCreateForm = true" class="btn btn-primary">
          + Добавить товар
        </button>
      </div>
      
      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Фото</th>
              <th>Название</th>
              <th>Категория</th>
              <th>Цена</th>
              <th>Единица</th>
              <th>Мин. заказ</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products" :key="product.id">
              <td>{{ product.id }}</td>
              <td>
                <img :src="product.photo_url" :alt="product.name" class="product-image" />
              </td>
              <td>{{ product.name }}</td>
              <td>{{ product.category?.name || '-' }}</td>
              <td>{{ product.price }} ₽</td>
              <td>{{ product.unit }}</td>
              <td>{{ product.min_order }}</td>
              <td>
                <button @click="editProduct(product)" class="btn btn-secondary">
                  ✏️
                </button>
                <button @click="deleteProduct(product.id)" class="btn btn-secondary">
                  🗑️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Модальное окно создания/редактирования -->
      <div v-if="showCreateForm || editingProduct" class="modal-overlay" @click="closeForm">
        <div class="modal" @click.stop>
          <h2>{{ editingProduct ? 'Редактировать товар' : 'Добавить товар' }}</h2>
          
          <form @submit.prevent="saveProduct">
            <div class="form-group">
              <label class="form-label">Название</label>
              <input
                v-model="form.name"
                type="text"
                class="form-input"
                required
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">URL фото</label>
              <input
                v-model="form.photo_url"
                type="url"
                class="form-input"
                required
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Описание</label>
              <textarea
                v-model="form.description"
                class="form-input"
                rows="3"
                required
              ></textarea>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Цена (₽)</label>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                  required
                />
              </div>
              
              <div class="form-group">
                <label class="form-label">Мин. заказ</label>
                <input
                  v-model="form.min_order"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="form-input"
                  required
                />
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Единица измерения</label>
                <select v-model="form.unit" class="form-input" required>
                  <option value="штуки">Штуки</option>
                  <option value="килограммы">Килограммы</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Категория</label>
                <select v-model="form.category_id" class="form-input" required>
                  <option value="">Выберите категорию</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" @click="closeForm" class="btn btn-secondary">
                Отмена
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                {{ loading ? 'Сохранение...' : 'Сохранить' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '../components/AdminLayout.vue'
import axios from 'axios'

interface Product {
  id: number
  name: string
  photo_url: string
  description: string
  price: number
  min_order: number
  unit: string
  category_id: number
  category?: { name: string }
}

interface Category {
  id: number
  name: string
}

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const showCreateForm = ref(false)
const editingProduct = ref<Product | null>(null)
const loading = ref(false)

const form = ref({
  name: '',
  photo_url: '',
  description: '',
  price: 0,
  min_order: 1,
  unit: 'штуки',
  category_id: ''
})

const loadProducts = async () => {
  try {
    const response = await axios.get('http://localhost:8001/api/admin/products')
    products.value = response.data.data
  } catch (error) {
    console.error('Ошибка загрузки товаров:', error)
  }
}

const loadCategories = async () => {
  try {
    const response = await axios.get('http://localhost:8001/api/admin/categories')
    categories.value = response.data.data
  } catch (error) {
    console.error('Ошибка загрузки категорий:', error)
  }
}

const saveProduct = async () => {
  loading.value = true
  
  try {
    const data = {
      ...form.value,
      price: parseFloat(form.value.price.toString()),
      min_order: parseFloat(form.value.min_order.toString()),
      category_id: parseInt(form.value.category_id.toString())
    }
    
    if (editingProduct.value) {
      await axios.put(`http://localhost:8001/api/admin/products/${editingProduct.value.id}`, data)
    } else {
      await axios.post('http://localhost:8001/api/admin/products', data)
    }
    
    await loadProducts()
    closeForm()
  } catch (error) {
    console.error('Ошибка сохранения товара:', error)
  } finally {
    loading.value = false
  }
}

const editProduct = (product: Product) => {
  editingProduct.value = product
  form.value = {
    name: product.name,
    photo_url: product.photo_url,
    description: product.description,
    price: product.price,
    min_order: product.min_order,
    unit: product.unit,
    category_id: product.category_id.toString()
  }
}

const deleteProduct = async (id: number) => {
  if (confirm('Вы уверены, что хотите удалить этот товар?')) {
    try {
      await axios.delete(`http://localhost:8001/api/admin/products/${id}`)
      await loadProducts()
    } catch (error) {
      console.error('Ошибка удаления товара:', error)
    }
  }
}

const closeForm = () => {
  showCreateForm.value = false
  editingProduct.value = null
  form.value = {
    name: '',
    photo_url: '',
    description: '',
    price: 0,
    min_order: 1,
    unit: 'штуки',
    category_id: ''
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-xl);
}

.product-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: var(--radius-sm);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px var(--shadow-medium);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.form-actions {
  display: flex;
  gap: var(--spacing-md);
  justify-content: flex-end;
  margin-top: var(--spacing-lg);
}

.btn {
  margin: 0 var(--spacing-xs);
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
}
</style>
