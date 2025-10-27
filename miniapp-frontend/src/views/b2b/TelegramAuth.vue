<template>
  <div class="telegram-auth">
    <div class="auth-container">
      <div class="auth-header">
        <h1>Авторизация</h1>
        <p class="subtitle">Войдите через Telegram для доступа к Ninja Goods</p>
      </div>

      <div v-if="!error" class="auth-content">
        <!-- Development Mode: Manual Input -->
        <div v-if="isDevMode && !telegramUser" class="manual-input-section">
          <h3 class="section-title-dev">Dev Mode: Enter Telegram Data</h3>
          <div class="form-group">
            <label>First Name</label>
            <input v-model="manualData.first_name" type="text" placeholder="Иван" />
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input v-model="manualData.last_name" type="text" placeholder="Иванов" />
          </div>
          <div class="form-group">
            <label>Username</label>
            <input v-model="manualData.username" type="text" placeholder="ivanov" />
          </div>
          <div class="form-group">
            <label>Telegram ID</label>
            <input v-model.number="manualData.id" type="number" placeholder="123456789" />
          </div>
        </div>

        <!-- Display telegram info -->
        <div v-else class="telegram-info">
          <div class="info-item">
            <span class="label">Имя:</span>
            <span class="value">{{ telegramUser?.first_name }} {{ telegramUser?.last_name || '' }}</span>
          </div>
          <div v-if="telegramUser?.username" class="info-item">
            <span class="label">Username:</span>
            <span class="value">@{{ telegramUser.username }}</span>
          </div>
        </div>

        <div class="button-group">
          <button 
            class="auth-button"
            @click="handleAuth"
            :disabled="isLoading"
          >
            <span v-if="isLoading">Авторизация...</span>
            <span v-else>Войти через Telegram</span>
          </button>
          
          <!-- Dev mode: Quick fill buttons -->
          <div v-if="isDevMode" class="dev-buttons">
            <button 
              class="dev-button" 
              @click="fillUserData(1)"
              :disabled="isLoading"
            >
              Пользователь 1
            </button>
            <button 
              class="dev-button" 
              @click="fillUserData(2)"
              :disabled="isLoading"
            >
              Пользователь 2
            </button>
          </div>
        </div>
      </div>

      <div v-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="retryAuth" class="retry-button">Повторить</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useB2BAuthStore } from '../../stores/b2bAuth'
import { getTelegramUser, getInitData } from '../../utils/telegram'

const isDevMode = import.meta.env.DEV
const router = useRouter()
const authStore = useB2BAuthStore()

const telegramUser = ref<any>(null)
const error = ref<string | null>(null)
const manualData = ref({
  first_name: '',
  last_name: '',
  username: '',
  id: 0,
})

const isLoading = computed(() => authStore.isLoading)

const handleAuth = async () => {
  let telegramAuthData

  const initData = getInitData()
  
  // In development mode, use manual input or mock data
  if (isDevMode && !initData) {
    if (manualData.value.id && manualData.value.first_name) {
      // Use manual input data
      telegramAuthData = {
        id: manualData.value.id,
        first_name: manualData.value.first_name,
        last_name: manualData.value.last_name || undefined,
        username: manualData.value.username || undefined,
        photo_url: undefined,
        auth_date: Math.floor(Date.now() / 1000),
        hash: 'dev_hash_for_testing',
      }
    } else if (telegramUser.value) {
      // Use mock data
      telegramAuthData = {
        id: telegramUser.value.id,
        first_name: telegramUser.value.first_name,
        last_name: telegramUser.value.last_name || undefined,
        username: telegramUser.value.username || undefined,
        photo_url: telegramUser.value.photo_url || undefined,
        auth_date: Math.floor(Date.now() / 1000),
        hash: 'dev_hash_for_testing',
      }
    } else {
      error.value = 'Заполните данные пользователя'
      return
    }
  } else {
    if (!initData) {
      error.value = 'Telegram initData не найден'
      return
    }

    // Parse initData
    const params = new URLSearchParams(initData)
    const userParam = params.get('user')
    
    if (!userParam) {
      error.value = 'Не удалось получить данные Telegram'
      return
    }

    const userData = JSON.parse(userParam)
    
    telegramAuthData = {
      id: userData.id,
      first_name: userData.first_name,
      last_name: userData.last_name || undefined,
      username: userData.username || undefined,
      photo_url: userData.photo_url || undefined,
      auth_date: parseInt(params.get('auth_date') || '0'),
      hash: params.get('hash') || '',
    }
  }

  const success = await authStore.authenticateWithTelegram(telegramAuthData)
  
  if (success) {
    // Check if user needs to complete registration
    if (!authStore.hasRole) {
      router.push('/role-selection')
    } else if (authStore.isBuyer && !authStore.hasOrganization) {
      router.push('/organization')
    } else {
      router.push('/')
    }
  } else {
    error.value = authStore.error || 'Ошибка авторизации'
  }
}

const retryAuth = () => {
  error.value = null
  handleAuth()
}

const fillUserData = async (userNumber: number) => {
  if (userNumber === 1) {
    manualData.value = {
      first_name: 'Иван',
      last_name: 'Петров',
      username: 'ivanov',
      id: 100000001,
    }
  } else {
    manualData.value = {
      first_name: 'Мария',
      last_name: 'Сидорова',
      username: 'sidorova',
      id: 100000002,
    }
  }
  // Auto-trigger auth after filling
  setTimeout(() => handleAuth(), 100)
}

onMounted(async () => {
  const user = getTelegramUser()
  if (user) {
    telegramUser.value = user
    // Auto-authenticate if we have real Telegram user data (production mode)
    if (!isDevMode) {
      handleAuth()
    }
  } else {
    // Don't auto-create user in dev mode - let user enter manually
    if (!isDevMode) {
      error.value = 'Telegram пользователь не найден'
    }
  }
})
</script>

<style scoped>
.telegram-auth {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
}

.auth-container {
  background: white;
  border-radius: 24px;
  padding: 48px 40px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.auth-header {
  text-align: center;
  margin-bottom: 32px;
}

.auth-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 16px;
  color: #8E8E93;
}

.manual-input-section {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
}

.section-title-dev {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #6B7280;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #E5E5EA;
  border-radius: 8px;
  font-size: 15px;
  color: #1C1C1E;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #34C759;
  box-shadow: 0 0 0 3px rgba(52, 199, 89, 0.1);
}

.telegram-info {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #E5E5EA;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  font-weight: 600;
  color: #6B7280;
}

.value {
  color: #1C1C1E;
}

.auth-button {
  width: 100%;
  background: linear-gradient(180deg, #34C759 0%, #28B84A 100%);
  color: white;
  font-size: 17px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.auth-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(52, 199, 89, 0.3);
}

.auth-button:active:not(:disabled) {
  transform: translateY(0);
}

.auth-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.dev-buttons {
  display: flex;
  gap: 12px;
}

.dev-button {
  flex: 1;
  background: white;
  color: #007AFF;
  font-size: 15px;
  font-weight: 600;
  border: 2px solid #007AFF;
  border-radius: 12px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.dev-button:hover:not(:disabled) {
  background: #007AFF;
  color: white;
}

.dev-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
}

.error-message p {
  color: #DC2626;
  margin-bottom: 16px;
}

.retry-button {
  background: #DC2626;
  color: white;
  font-size: 15px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  cursor: pointer;
}
</style>

