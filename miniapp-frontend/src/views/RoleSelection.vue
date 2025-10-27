<template>
  <div class="role-selection">
    <div class="selection-container">
      <div class="selection-header">
        <h1>Выберите роль</h1>
        <p class="subtitle">Как вы хотите использовать систему?</p>
      </div>

      <div class="role-cards">
        <div class="role-card" @click="selectRole('customer')">
          <div class="role-icon customer">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <h3>Покупатель</h3>
          <p>Покупайте товары для себя</p>
          <button class="card-button" :disabled="isLoading">Выбрать</button>
        </div>

        <div class="role-card" @click="selectRole('buyer')">
          <div class="role-icon buyer">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Закупщик</h3>
          <p>Создавайте заказы от лица своей компании</p>
          <button class="card-button" :disabled="isLoading">Выбрать</button>
        </div>

        <div class="role-card" @click="selectRole('employee')">
          <div class="role-icon employee">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Сотрудник</h3>
          <p>Добавляйте товары в заказ по приглашению</p>
          <button class="card-button" :disabled="isLoading">Выбрать</button>
        </div>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/mainAuth'

const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(false)
const error = ref<string | null>(null)

const selectRole = async (role: 'customer' | 'buyer' | 'employee') => {
  isLoading.value = true
  error.value = null

  try {
    const success = await authStore.setRole(role)
    
    if (success) {
      // После выбора роли:
      // - Покупатель/Закупщик идут в главное меню (создание ресторанов - в настройках для закупщика)
      // - Сотрудник идет на страницу ввода инвайта (нужно присоединиться к ресторану)
      if (role === 'employee') {
        router.push('/invite')
      } else {
        router.push('/')
      }
    } else {
      error.value = authStore.error || 'Не удалось установить роль'
    }
  } catch (err) {
    error.value = 'Произошла ошибка'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.role-selection {
  min-height: 100vh;
  padding: 40px 20px;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
}

.selection-container {
  max-width: 800px;
  margin: 0 auto;
}

.selection-header {
  text-align: center;
  margin-bottom: 48px;
  color: #1C1C1E;
}

.selection-header h1 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 12px;
}

.subtitle {
  font-size: 18px;
  color: #6B7280;
}

.role-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.role-card {
  background: white;
  border-radius: 20px;
  padding: 32px 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.role-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.role-icon {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
}

.role-icon.customer {
  background: linear-gradient(135deg, #007AFF 0%, #0051D0 100%);
  color: white;
}

.role-icon.buyer {
  background: linear-gradient(135deg, #FF9500 0%, #FF7A00 100%);
  color: white;
}

.role-icon.employee {
  background: linear-gradient(135deg, #34C759 0%, #28B84A 100%);
  color: white;
}

.role-icon svg {
  width: 40px;
  height: 40px;
}

.role-card h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 12px;
}

.role-card p {
  font-size: 15px;
  color: #6B7280;
  margin-bottom: 24px;
  flex-grow: 1;
}

.card-button {
  width: 100%;
  background: #007AFF;
  color: white;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.card-button:hover:not(:disabled) {
  background: #0051D0;
  transform: translateY(-2px);
}

.card-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: rgba(255, 255, 255, 0.95);
  color: #DC2626;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  font-weight: 500;
}
</style>

