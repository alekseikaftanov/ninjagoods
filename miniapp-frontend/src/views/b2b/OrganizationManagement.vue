<template>
  <div class="organization-management">
    <div class="management-container">
      <div class="management-header">
        <h1>Управление организацией</h1>
      </div>

      <!-- Organization Info Section -->
      <section class="info-section">
        <h2 class="section-title">Информация об организации</h2>
        
        <div v-if="organization" class="organization-details">
          <div class="detail-row">
            <span class="label">Название:</span>
            <span class="value">{{ organization.name }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Юридическое название:</span>
            <span class="value">{{ organization.legal_name }}</span>
          </div>
          <div class="detail-row">
            <span class="label">ИНН:</span>
            <span class="value">{{ organization.inn }}</span>
          </div>
          <div class="detail-row">
            <span class="label">КПП:</span>
            <span class="value">{{ organization.kpp }}</span>
          </div>
          <div class="detail-row">
            <span class="label">ОГРН:</span>
            <span class="value">{{ organization.ogrn }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Юридический адрес:</span>
            <span class="value">{{ organization.address_legal }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Фактический адрес:</span>
            <span class="value">{{ organization.address_actual }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Телефон:</span>
            <span class="value">{{ organization.phone }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Email:</span>
            <span class="value">{{ organization.email }}</span>
          </div>
          <div v-if="organization.comment" class="detail-row">
            <span class="label">Комментарий:</span>
            <span class="value">{{ organization.comment }}</span>
          </div>
        </div>
      </section>

      <!-- Invite Management Section -->
      <section class="invite-section">
        <h2 class="section-title">Приглашения</h2>
        
        <div class="invite-actions">
          <button 
            class="generate-invite-button" 
            @click="generateInvite"
            :disabled="isGeneratingInvite"
          >
            <span v-if="isGeneratingInvite">Генерация...</span>
            <span v-else>+ Создать приглашение</span>
          </button>
        </div>

        <div v-if="currentInvite" class="invite-display">
          <div class="invite-url-container">
            <input 
              type="text" 
              :value="currentInvite.invite_url" 
              readonly 
              class="invite-url-input"
              ref="inviteUrlInput"
            />
            <button @click="copyInviteUrl" class="copy-button">
              {{ copied ? '✓ Скопировано' : 'Копировать' }}
            </button>
          </div>
          <p class="invite-expiry">
            Приглашение действует до: {{ formatDate(currentInvite.expires_at) }}
          </p>
        </div>
      </section>

      <!-- Employees Section -->
      <section class="employees-section">
        <h2 class="section-title">Сотрудники</h2>
        
        <div v-if="employees.length === 0" class="empty-state">
          <p>Сотрудников пока нет</p>
        </div>

        <div v-else class="employees-list">
          <div 
            v-for="employee in employees" 
            :key="employee.id" 
            class="employee-card"
          >
            <div class="employee-info">
              <div class="employee-name">
                {{ employee.first_name }} {{ employee.last_name }}
              </div>
              <div class="employee-username" v-if="employee.username">
                @{{ employee.username }}
              </div>
            </div>
            <button 
              class="delete-button"
              @click="deleteEmployee(employee.id)"
              :disabled="isDeleting"
            >
              Удалить
            </button>
          </div>
        </div>
      </section>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useB2BAuthStore } from '../../stores/b2bAuth'
import { b2bAPI } from '../../utils/b2bApi'
import type { Organization } from '../../utils/b2bApi'

const router = useRouter()
const authStore = useB2BAuthStore()

const organization = ref<Organization | null>(null)
const employees = ref<any[]>([])
const currentInvite = ref<any>(null)
const error = ref<string | null>(null)
const isLoading = ref(false)
const isGeneratingInvite = ref(false)
const isDeleting = ref(false)
const copied = ref(false)

const loadOrganization = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await b2bAPI.organization.get()
    if (response.success) {
      organization.value = response.data
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось загрузить данные организации'
  } finally {
    isLoading.value = false
  }
}

const loadEmployees = async () => {
  // TODO: Load employees from API
  // For now, mock data
  employees.value = []
}

const generateInvite = async () => {
  isGeneratingInvite.value = true
  error.value = null

  try {
    const response = await b2bAPI.organization.generateInvite()
    if (response.success) {
      currentInvite.value = response.data
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось создать приглашение'
  } finally {
    isGeneratingInvite.value = false
  }
}

const copyInviteUrl = () => {
  const input = document.createElement('input')
  input.value = currentInvite.value.invite_url
  document.body.appendChild(input)
  input.select()
  document.execCommand('copy')
  document.body.removeChild(input)
  
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const deleteEmployee = async (employeeId: number) => {
  if (!confirm('Вы уверены, что хотите удалить этого сотрудника?')) {
    return
  }

  isDeleting.value = true
  error.value = null

  try {
    // TODO: Implement employee deletion API call
    // await b2bAPI.organization.deleteEmployee(employeeId)
    employees.value = employees.value.filter(e => e.id !== employeeId)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Не удалось удалить сотрудника'
  } finally {
    isDeleting.value = false
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
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
  loadOrganization()
  loadEmployees()
})
</script>

<style scoped>
.organization-management {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
}

.management-container {
  max-width: 900px;
  margin: 0 auto;
}

.management-header {
  margin-bottom: 32px;
  color: #1C1C1E;
}

.management-header h1 {
  font-size: 32px;
  font-weight: 700;
}

section {
  background: white;
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 24px;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 24px;
}

.organization-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  gap: 16px;
}

.label {
  font-weight: 600;
  color: #6B7280;
  min-width: 180px;
}

.value {
  color: #1C1C1E;
}

.invite-actions {
  margin-bottom: 24px;
}

.generate-invite-button {
  background: linear-gradient(180deg, #34C759 0%, #28B84A 100%);
  color: white;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.generate-invite-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(52, 199, 89, 0.3);
}

.generate-invite-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.invite-display {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 20px;
}

.invite-url-container {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
}

.invite-url-input {
  flex: 1;
  padding: 12px;
  border: 1px solid #E5E5EA;
  border-radius: 8px;
  font-size: 14px;
  color: #1C1C1E;
  background: white;
}

.copy-button {
  background: #007AFF;
  color: white;
  font-size: 14px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  cursor: pointer;
  white-space: nowrap;
}

.copy-button:hover {
  background: #0051D0;
}

.invite-expiry {
  font-size: 13px;
  color: #6B7280;
  margin: 0;
}

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
  background: #F9FAFB;
  border-radius: 12px;
}

.employee-name {
  font-size: 16px;
  font-weight: 600;
  color: #1C1C1E;
  margin-bottom: 4px;
}

.employee-username {
  font-size: 14px;
  color: #6B7280;
}

.delete-button {
  background: #DC2626;
  color: white;
  font-size: 14px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.delete-button:hover:not(:disabled) {
  background: #B91C1C;
}

.delete-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6B7280;
}

.error-message {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  border-radius: 12px;
  padding: 16px;
  color: #DC2626;
  font-size: 14px;
  margin-bottom: 24px;
}

.button-container {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.logout-button {
  background: #DC2626;
  color: white;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 12px 32px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-button:hover {
  background: #B91C1C;
}

.back-button {
  background: white;
  color: #007AFF;
  font-size: 16px;
  font-weight: 600;
  border: 2px solid #007AFF;
  border-radius: 12px;
  padding: 12px 32px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-button:hover {
  background: #007AFF;
  color: white;
}
</style>

