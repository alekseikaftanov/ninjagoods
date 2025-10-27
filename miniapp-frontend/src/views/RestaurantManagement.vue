<template>
  <div class="restaurant-management">
    <div class="management-container">
      <div class="management-header">
        <h1>Управление ресторанами</h1>
        <button @click="showCreateForm = true" class="add-restaurant-button">
          + Добавить ресторан
        </button>
      </div>

      <!-- Create Restaurant Form -->
      <div v-if="showCreateForm" class="modal-overlay" @click="showCreateForm = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Новый ресторан</h2>
            <button @click="showCreateForm = false" class="close-button">✕</button>
          </div>
          
          <form @submit.prevent="createRestaurant" class="restaurant-form">
            <div class="form-section">
              <h3 class="form-section-title">Основная информация</h3>
              
              <div class="form-group">
                <label>Название ресторана <span class="required">*</span></label>
                <input 
                  v-model="newRestaurant.name" 
                  type="text" 
                  required 
                  placeholder="Например: Кафе Чайка" 
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label>Юридическое название <span class="required">*</span></label>
                <input 
                  v-model="newRestaurant.legal_name" 
                  type="text" 
                  required 
                  placeholder="Например: ООО Чайка" 
                  class="form-input"
                />
              </div>
            </div>

            <div class="form-section">
              <h3 class="form-section-title">Реквизиты</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label>ИНН <span class="required">*</span></label>
                  <input 
                    v-model="newRestaurant.inn" 
                    type="text" 
                    required 
                    maxlength="12" 
                    placeholder="10 или 12 цифр" 
                    class="form-input"
                  />
                </div>

                <div class="form-group">
                  <label>КПП</label>
                  <input 
                    v-model="newRestaurant.kpp" 
                    type="text" 
                    maxlength="9" 
                    placeholder="9 цифр" 
                    class="form-input"
                  />
                </div>

                <div class="form-group">
                  <label>ОГРН <span class="required">*</span></label>
                  <input 
                    v-model="newRestaurant.ogrn" 
                    type="text" 
                    required 
                    maxlength="15" 
                    placeholder="13 или 15 цифр" 
                    class="form-input"
                  />
                </div>
              </div>
            </div>

            <div class="form-section">
              <h3 class="form-section-title">Адреса</h3>
              
              <div class="form-group">
                <label>Юридический адрес <span class="required">*</span></label>
                <textarea 
                  v-model="newRestaurant.address_legal" 
                  required 
                  placeholder="Укажите полный юридический адрес"
                  class="form-textarea"
                ></textarea>
              </div>

              <div class="form-group">
                <label>Фактический адрес <span class="required">*</span></label>
                <textarea 
                  v-model="newRestaurant.address_actual" 
                  required 
                  placeholder="Укажите адрес фактического местонахождения"
                  class="form-textarea"
                ></textarea>
              </div>
            </div>

            <div class="form-section">
              <h3 class="form-section-title">Контакты</h3>
              
              <div class="form-group">
                <label>Телефон <span class="required">*</span></label>
                <input 
                  v-model="newRestaurant.phone" 
                  type="tel" 
                  required 
                  placeholder="+7 (___) ___-__-__" 
                  class="form-input"
                />
              </div>
            </div>

            <div v-if="createError" class="error-message">{{ createError }}</div>

            <div class="form-actions">
              <button type="button" @click="showCreateForm = false" class="btn-cancel" :disabled="isCreating">
                Отмена
              </button>
              <button type="submit" class="btn-submit" :disabled="isCreating">
                <span v-if="isCreating">Создание...</span>
                <span v-else>✓ Создать ресторан</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Загрузка...</p>
      </div>

      <!-- Restaurants List -->
      <div v-else-if="restaurants.length > 0" class="restaurants-list">
        <div v-for="restaurant in restaurants" :key="restaurant.id" class="restaurant-card">
          <!-- Restaurant Info -->
          <div class="restaurant-section">
            <h2 class="section-title">{{ restaurant.name }}</h2>
            
            <div class="restaurant-details">
              <div class="detail-row">
                <span class="label">Юридическое название:</span>
                <span class="value">{{ restaurant.legal_name }}</span>
              </div>
              <div class="detail-row">
                <span class="label">ИНН:</span>
                <span class="value">{{ restaurant.inn }}</span>
              </div>
              <div class="detail-row">
                <span class="label">КПП:</span>
                <span class="value">{{ restaurant.kpp || '—' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">ОГРН:</span>
                <span class="value">{{ restaurant.ogrn }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Юридический адрес:</span>
                <span class="value">{{ restaurant.address_legal }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Фактический адрес:</span>
                <span class="value">{{ restaurant.address_actual }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Телефон:</span>
                <span class="value">{{ restaurant.phone }}</span>
              </div>
            </div>
          </div>

          <!-- Invites Section -->
          <div class="restaurant-section">
            <h3 class="subsection-title">Приглашения</h3>
            
            <button 
              class="generate-invite-button" 
              @click="generateInvite(restaurant.id)"
              :disabled="generatingInviteFor === restaurant.id"
            >
              {{ generatingInviteFor === restaurant.id ? 'Генерация...' : '+ Создать приглашение' }}
            </button>

            <div v-if="invites[restaurant.id]" class="invite-display">
              <div class="invite-url-container">
                <input 
                  type="text" 
                  :value="getInviteLink(invites[restaurant.id])" 
                  readonly 
                  class="invite-url-input"
                />
                <button @click="copyInviteUrl(restaurant.id)" class="copy-button">
                  {{ copiedInvites[restaurant.id] ? '✓ Скопировано' : 'Копировать' }}
                </button>
              </div>
              <p class="invite-expiry">
                Действует до: {{ formatDate(invites[restaurant.id].expires_at) }}
              </p>
            </div>
          </div>

          <!-- Employees Section -->
          <div class="restaurant-section">
            <h3 class="subsection-title">Сотрудники</h3>
            
            <div v-if="!restaurant.employees || restaurant.employees.length === 0" class="empty-state">
              <p>Сотрудников пока нет</p>
              <p class="hint">Создайте приглашение и отправьте его сотруднику</p>
            </div>

            <div v-else class="employees-list">
              <div 
                v-for="employee in restaurant.employees" 
                :key="employee.id"
                class="employee-card"
              >
                <div class="employee-info">
                  <div class="employee-name">{{ employee.first_name }} {{ employee.last_name }}</div>
                  <div class="employee-username">@{{ employee.username || 'без username' }}</div>
                </div>
                <button 
                  @click="removeEmployee(restaurant.id, employee.id)" 
                  class="remove-employee-button"
                  :disabled="removingEmployee === `${restaurant.id}-${employee.id}`"
                >
                  {{ removingEmployee === `${restaurant.id}-${employee.id}` ? '...' : '✕' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-restaurants">
        <div class="empty-icon">🏢</div>
        <h2>У вас пока нет ресторанов</h2>
        <p>Создайте первый ресторан, чтобы начать работу</p>
        <button @click="showCreateForm = true" class="btn-primary-large">
          + Добавить первый ресторан
        </button>
      </div>

      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Bottom Actions -->
      <div class="button-container">
        <button @click="handleLogout" class="logout-button">
          Выйти из системы
        </button>
        <button @click="goBack" class="back-button">
          ← Назад к каталогу
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/mainAuth'
import { API } from '../utils/restaurantApi'
import type { Restaurant, CreateRestaurantData } from '../utils/restaurantApi'

const router = useRouter()
const authStore = useAuthStore()

const restaurants = ref<Restaurant[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)

// Create restaurant state
const showCreateForm = ref(false)
const isCreating = ref(false)
const createError = ref<string | null>(null)
const newRestaurant = reactive<CreateRestaurantData>({
  name: '',
  legal_name: '',
  inn: '',
  kpp: '',
  ogrn: '',
  address_legal: '',
  address_actual: '',
  phone: '',
})

// Invites state
const invites = ref<Record<number, any>>({})
const generatingInviteFor = ref<number | null>(null)
const copiedInvites = ref<Record<number, boolean>>({})

// Employees state
const removingEmployee = ref<string | null>(null)

const loadRestaurants = async () => {
  isLoading.value = true
  error.value = null

  try {
    const data = await API.restaurants.getAll()
    restaurants.value = data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось загрузить рестораны'
  } finally {
    isLoading.value = false
  }
}

const createRestaurant = async () => {
  isCreating.value = true
  createError.value = null

  try {
    const created = await API.restaurants.create(newRestaurant)
    restaurants.value.push(created)
    
    // Reset form
    Object.assign(newRestaurant, {
      name: '',
      legal_name: '',
      inn: '',
      kpp: '',
      ogrn: '',
      address_legal: '',
      address_actual: '',
      phone: '',
    })
    
    showCreateForm.value = false
  } catch (err: any) {
    createError.value = err.response?.data?.message || 'Не удалось создать ресторан'
  } finally {
    isCreating.value = false
  }
}

const generateInvite = async (restaurantId: number) => {
  generatingInviteFor.value = restaurantId
  error.value = null

  try {
    const response = await API.invite.generate(restaurantId, 7)
    
    if (response.success) {
      invites.value[restaurantId] = response.data.invite
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось создать приглашение'
  } finally {
    generatingInviteFor.value = null
  }
}

const getInviteLink = (invite: any) => {
  return `${window.location.origin}/invite?token=${invite.token}`
}

const copyInviteUrl = async (restaurantId: number) => {
  const invite = invites.value[restaurantId]
  if (!invite) return

  const link = getInviteLink(invite)
  
  try {
    await navigator.clipboard.writeText(link)
    copiedInvites.value[restaurantId] = true
    setTimeout(() => {
      copiedInvites.value[restaurantId] = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const removeEmployee = async (restaurantId: number, employeeId: number) => {
  removingEmployee.value = `${restaurantId}-${employeeId}`
  error.value = null

  try {
    await API.restaurants.removeEmployee(restaurantId, employeeId)
    
    // Update local state
    const restaurant = restaurants.value.find(r => r.id === restaurantId)
    if (restaurant && restaurant.employees) {
      restaurant.employees = restaurant.employees.filter(e => e.id !== employeeId)
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось удалить сотрудника'
  } finally {
    removingEmployee.value = null
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const goBack = () => {
  router.push('/')
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

onMounted(() => {
  loadRestaurants()
})
</script>

<style scoped>
.restaurant-management {
  min-height: 100vh;
  background: #f5f5f7;
  padding: 20px;
}

.management-container {
  max-width: 1200px;
  margin: 0 auto;
}

.management-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.management-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: #1d1d1f;
}

.add-restaurant-button {
  background: linear-gradient(135deg, #34C759 0%, #28B84A 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.add-restaurant-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(52, 199, 89, 0.3);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 24px;
  padding: 0;
  max-width: 680px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(30px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  border-bottom: 1px solid #f0f0f3;
  background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
}

.modal-header h2 {
  font-size: 26px;
  font-weight: 700;
  color: #1C1C1E;
  margin: 0;
}

.close-button {
  background: rgba(0, 0, 0, 0.05);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  font-size: 20px;
  color: #6e6e73;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-button:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #1C1C1E;
  transform: rotate(90deg);
}

/* Form */
.restaurant-form {
  padding: 32px;
  max-height: calc(90vh - 100px);
  overflow-y: auto;
}

.form-section {
  margin-bottom: 32px;
}

.form-section:last-of-type {
  margin-bottom: 24px;
}

.form-section-title {
  font-size: 18px;
  font-weight: 700;
  color: #1C1C1E;
  margin: 0 0 20px 0;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f3;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 15px;
  font-weight: 600;
  color: #1C1C1E;
}

.required {
  color: #FF3B30;
  font-weight: 700;
}

.form-input,
.form-textarea {
  padding: 14px 16px;
  border: 2px solid #E5E5E7;
  border-radius: 12px;
  font-size: 16px;
  color: #1C1C1E;
  background: white;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: #999;
  font-size: 15px;
  font-weight: 400;
}

.form-input:hover,
.form-textarea:hover {
  border-color: #C7C7CC;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #007AFF;
  box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 90px;
  line-height: 1.5;
}

.form-actions {
  display: flex;
  gap: 12px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f3;
}

.btn-cancel {
  flex: 1;
  padding: 16px;
  border: 2px solid #E5E5E7;
  background: white;
  color: #1C1C1E;
  border-radius: 14px;
  font-size: 17px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-cancel:hover:not(:disabled) {
  background: #F9F9F9;
  border-color: #C7C7CC;
  transform: translateY(-1px);
}

.btn-cancel:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit {
  flex: 1;
  padding: 16px;
  border: none;
  background: linear-gradient(135deg, #34C759 0%, #28B84A 100%);
  color: white;
  border-radius: 14px;
  font-size: 17px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(52, 199, 89, 0.25);
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(52, 199, 89, 0.35);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(52, 199, 89, 0.25);
}

.btn-submit:disabled,
.btn-cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Restaurants List */
.restaurants-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.restaurant-card {
  background: white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.restaurant-section {
  margin-bottom: 32px;
}

.restaurant-section:last-child {
  margin-bottom: 0;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  color: #1d1d1f;
  margin-bottom: 20px;
}

.subsection-title {
  font-size: 18px;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 16px;
}

.restaurant-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f7;
}

.detail-row .label {
  font-weight: 600;
  color: #6e6e73;
}

.detail-row .value {
  color: #1d1d1f;
  text-align: right;
}

/* Invites */
.generate-invite-button {
  background: #34C759;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 16px;
}

.generate-invite-button:hover:not(:disabled) {
  background: #28B84A;
}

.generate-invite-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.invite-display {
  background: #f5f5f7;
  border-radius: 12px;
  padding: 16px;
}

.invite-url-container {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.invite-url-input {
  flex: 1;
  padding: 10px;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 14px;
  background: white;
}

.copy-button {
  padding: 10px 16px;
  border: none;
  background: #007AFF;
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.copy-button:hover {
  background: #0051D0;
}

.invite-expiry {
  font-size: 12px;
  color: #6e6e73;
}

/* Employees */
.employees-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.employee-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f5f5f7;
  border-radius: 12px;
}

.employee-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.employee-name {
  font-weight: 600;
  color: #1d1d1f;
}

.employee-username {
  font-size: 14px;
  color: #6e6e73;
}

.remove-employee-button {
  width: 32px;
  height: 32px;
  border: none;
  background: #FF3B30;
  color: white;
  border-radius: 50%;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.remove-employee-button:hover:not(:disabled) {
  background: #D70015;
}

.remove-employee-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Empty States */
.empty-state {
  text-align: center;
  padding: 32px;
  color: #6e6e73;
}

.empty-state .hint {
  font-size: 14px;
  margin-top: 8px;
}

.empty-restaurants {
  text-align: center;
  padding: 64px 32px;
  background: white;
  border-radius: 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.empty-restaurants h2 {
  font-size: 24px;
  font-weight: 700;
  color: #1d1d1f;
  margin-bottom: 8px;
}

.empty-restaurants p {
  color: #6e6e73;
  margin-bottom: 24px;
}

.btn-primary-large {
  padding: 16px 32px;
  border: none;
  background: #007AFF;
  color: white;
  border-radius: 12px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary-large:hover {
  background: #0051D0;
  transform: translateY(-2px);
}

/* Loading & Error */
.loading-state {
  text-align: center;
  padding: 64px 32px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f5f5f7;
  border-top-color: #007AFF;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  background: rgba(255, 59, 48, 0.1);
  color: #FF3B30;
  padding: 16px;
  border-radius: 12px;
  margin: 16px 0;
  text-align: center;
  font-weight: 500;
}

/* Bottom Actions */
.button-container {
  display: flex;
  gap: 16px;
  margin-top: 32px;
}

.back-button {
  flex: 1;
  padding: 14px;
  border: 1px solid #d2d2d7;
  background: white;
  color: #007AFF;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.back-button:hover {
  background: #f5f5f7;
}

.logout-button {
  flex: 1;
  padding: 14px;
  border: none;
  background: #FF3B30;
  color: white;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-button:hover {
  background: #D70015;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 0;
    align-items: flex-end;
  }
  
  .modal-content {
    border-radius: 24px 24px 0 0;
    max-height: 95vh;
    animation: slideUpMobile 0.3s ease;
  }
  
  @keyframes slideUpMobile {
    from { 
      transform: translateY(100%);
    }
    to { 
      transform: translateY(0);
    }
  }
  
  .modal-header {
    padding: 20px 24px;
  }
  
  .modal-header h2 {
    font-size: 22px;
  }
  
  .restaurant-form {
    padding: 24px;
  }
  
  .form-section {
    margin-bottom: 24px;
  }
  
  .form-section-title {
    font-size: 16px;
    margin-bottom: 16px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-group label {
    font-size: 14px;
  }
  
  .form-input,
  .form-textarea {
    font-size: 16px;
    padding: 12px 14px;
  }
  
  .form-input::placeholder,
  .form-textarea::placeholder {
    font-size: 14px;
  }
  
  .form-actions {
    flex-direction: column-reverse;
    gap: 12px;
    padding-top: 20px;
  }
  
  .btn-cancel,
  .btn-submit {
    width: 100%;
    padding: 14px;
    font-size: 16px;
  }
  
  .management-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .button-container {
    flex-direction: column;
  }
}
</style>

