<template>
  <div class="invite-join">
    <div class="invite-container">
      <div class="invite-header">
        <h1>Присоединиться к организации</h1>
        <p class="subtitle">Введите код приглашения для доступа к системе заказов</p>
      </div>

      <div class="invite-form">
        <div class="form-group">
          <label for="invite_token">Код приглашения</label>
          <input 
            id="invite_token"
            v-model="inviteToken"
            type="text"
            placeholder="Введите код приглашения"
            @keyup.enter="joinOrganization"
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button 
          class="join-button"
          @click="joinOrganization"
          :disabled="!inviteToken || isLoading"
        >
          <span v-if="isLoading">Присоединение...</span>
          <span v-else>Присоединиться</span>
        </button>

        <div class="help-text">
          <p>Получите код приглашения от закупщика вашей организации</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { API } from '../utils/restaurantApi'

const router = useRouter()

const inviteToken = ref('')
const isLoading = ref(false)
const error = ref<string | null>(null)

const joinOrganization = async () => {
  if (!inviteToken.value) {
    error.value = 'Введите код приглашения'
    return
  }

  isLoading.value = true
  error.value = null

  try {
    const response = await API.invite.join(inviteToken.value)
    
    if (response.success) {
      router.push('/orders')
    } else {
      error.value = 'Не удалось присоединиться к организации'
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Неверный код приглашения'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.invite-join {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
}

.invite-container {
  background: white;
  border-radius: 24px;
  padding: 48px 40px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.invite-header {
  text-align: center;
  margin-bottom: 32px;
}

.invite-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 16px;
  color: #8E8E93;
}

.invite-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #6B7280;
}

.form-group input {
  padding: 14px 16px;
  border: 1px solid #E5E5EA;
  border-radius: 12px;
  font-size: 16px;
  color: #1C1C1E;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #007AFF;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
}

.join-button {
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
  margin-top: 8px;
}

.join-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(52, 199, 89, 0.3);
}

.join-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  border-radius: 8px;
  padding: 12px;
  color: #DC2626;
  font-size: 14px;
  text-align: center;
}

.help-text {
  text-align: center;
  margin-top: 16px;
}

.help-text p {
  font-size: 13px;
  color: #8E8E93;
}
</style>

