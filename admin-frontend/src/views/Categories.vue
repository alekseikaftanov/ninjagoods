<template>
  <AdminLayout>
    <div class="categories">
      <div class="page-header">
        <h1>Категории</h1>
        <button @click="showCreateForm = true" class="btn btn-primary">
          + Добавить категорию
        </button>
      </div>

      <!-- Сортировка -->
      <SortControls 
        @sort-change="handleSortChange"
        :default-sort-by="sortBy"
        :default-sort-order="sortOrder"
      />
      
      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Название</th>
              <th>Родительская категория</th>
              <th>Товары</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categories" :key="category.id" @click="editCategory(category)" class="clickable-row">
              <td>{{ category.id }}</td>
              <td>
                <div class="product-name">{{ category.name }}</div>
              </td>
              <td>{{ category.parent?.name || '-' }}</td>
              <td>{{ category.products?.length || 0 }}</td>
              <td>
                <button @click.stop="editCategory(category)" class="btn-action btn-edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button @click.stop="deleteCategory(category.id)" class="btn-action btn-delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3,6 5,6 21,6"/>
                    <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Модальное окно создания/редактирования -->
      <div v-if="showCreateForm || editingCategory" class="modal-overlay" @click="closeForm">
        <div class="modal" @click.stop>
          <h2>{{ editingCategory ? 'Редактировать категорию' : 'Добавить категорию' }}</h2>
          
          <form @submit.prevent="saveCategory">
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
              <label class="form-label">Родительская категория</label>
              <select v-model="form.parent_id" class="form-input">
                <option value="">Без родительской категории</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
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
import SortControls from '../components/SortControls.vue'
import axios from 'axios'

interface Category {
  id: number
  name: string
  parent_id?: number
  parent?: Category
  products?: any[]
}

const categories = ref<Category[]>([])
const showCreateForm = ref(false)
const editingCategory = ref<Category | null>(null)
const sortBy = ref('id')
const sortOrder = ref('asc')
const loading = ref(false)

const form = ref({
  name: '',
  parent_id: ''
})

const loadCategories = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/admin/categories', {
      params: {
        sort_by: sortBy.value,
        sort_order: sortOrder.value
      }
    })
    categories.value = response.data.data
  } catch (error) {
    console.error('Ошибка загрузки категорий:', error)
  }
}

const handleSortChange = (newSortBy: string, newSortOrder: string) => {
  sortBy.value = newSortBy
  sortOrder.value = newSortOrder
  loadCategories()
}

const saveCategory = async () => {
  loading.value = true
  
  try {
    const data = {
      name: form.value.name,
      parent_id: form.value.parent_id || null
    }
    
    if (editingCategory.value) {
      await axios.put(`http://localhost:8000/api/admin/categories/${editingCategory.value.id}`, data)
    } else {
      await axios.post('http://localhost:8000/api/admin/categories', data)
    }
    
    await loadCategories()
    closeForm()
  } catch (error) {
    console.error('Ошибка сохранения категории:', error)
  } finally {
    loading.value = false
  }
}

const editCategory = (category: Category) => {
  editingCategory.value = { ...category }
  form.value = {
    name: category.name,
    parent_id: category.parent_id?.toString() || ''
  }
  showCreateForm.value = true
}

const deleteCategory = async (id: number) => {
  if (confirm('Вы уверены, что хотите удалить эту категорию?')) {
    try {
      await axios.delete(`http://localhost:8000/api/admin/categories/${id}`)
      await loadCategories()
    } catch (error) {
      console.error('Ошибка удаления категории:', error)
    }
  }
}

const closeForm = () => {
  showCreateForm.value = false
  editingCategory.value = null
  form.value = { name: '', parent_id: '' }
}

onMounted(() => {
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
  max-width: 500px;
  box-shadow: 0 8px 32px var(--shadow-medium);
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
</style>
