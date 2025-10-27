<template>
  <div class="organization-registration">
    <div class="registration-container">
      <div class="registration-header">
        <h1>Регистрация организации</h1>
        <p class="subtitle">Заполните данные вашей компании</p>
      </div>

      <form @submit.prevent="submitForm" class="registration-form">
        <div class="form-section">
          <h3 class="section-title">Основная информация</h3>
          
          <div class="form-group">
            <label for="name">Название организации *</label>
            <input 
              id="name" 
              v-model="form.name" 
              type="text" 
              placeholder="ООО «Ваша Компания»"
              required 
            />
          </div>

          <div class="form-group">
            <label for="legal_name">Полное юридическое название *</label>
            <input 
              id="legal_name" 
              v-model="form.legal_name" 
              type="text" 
              placeholder="Общество с ограниченной ответственностью «Ваша Компания»"
              required 
            />
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">Реквизиты</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="inn">ИНН *</label>
              <input 
                id="inn" 
                v-model="form.inn" 
                type="text" 
                placeholder="1234567890"
                required 
              />
            </div>

            <div class="form-group">
              <label for="kpp">КПП *</label>
              <input 
                id="kpp" 
                v-model="form.kpp" 
                type="text" 
                placeholder="123456789"
                required 
              />
            </div>
          </div>

          <div class="form-group">
            <label for="ogrn">ОГРН *</label>
            <input 
              id="ogrn" 
              v-model="form.ogrn" 
              type="text" 
              placeholder="1234567890123"
              required 
            />
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">Контакты</h3>
          
          <div class="form-group">
            <label for="address_legal">Юридический адрес *</label>
            <input 
              id="address_legal" 
              v-model="form.address_legal" 
              type="text" 
              placeholder="Город, улица, дом"
              required 
            />
          </div>

          <div class="form-group">
            <label for="address_actual">Фактический адрес *</label>
            <input 
              id="address_actual" 
              v-model="form.address_actual" 
              type="text" 
              placeholder="Город, улица, дом"
              required 
            />
          </div>

          <div class="form-group">
            <label for="phone">Телефон *</label>
            <input 
              id="phone" 
              v-model="form.phone" 
              type="tel" 
              placeholder="+7 (999) 123-45-67"
              required 
            />
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="company@example.com"
              required 
            />
          </div>
        </div>

        <div class="form-group">
          <label for="comment">Комментарий</label>
          <textarea 
            id="comment" 
            v-model="form.comment" 
            rows="3"
            placeholder="Дополнительная информация..."
          ></textarea>
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button 
          type="submit" 
          class="submit-button"
          :disabled="isLoading"
        >
          <span v-if="isLoading">Регистрация...</span>
          <span v-else>Зарегистрировать организацию</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useB2BAuthStore } from '../../stores/b2bAuth'
import { b2bAPI } from '../../utils/b2bApi'
import type { CreateOrganizationData } from '../../utils/b2bApi'

const router = useRouter()
const authStore = useB2BAuthStore()

const isLoading = ref(false)
const error = ref<string | null>(null)

const form = reactive<CreateOrganizationData>({
  name: '',
  legal_name: '',
  inn: '',
  kpp: '',
  ogrn: '',
  address_legal: '',
  address_actual: '',
  phone: '',
  email: '',
  comment: '',
})

const submitForm = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await b2bAPI.organization.create(form)
    
    if (response.success) {
      // Update store with new organization
      const authStore = useB2BAuthStore()
      await authStore.getCurrentUser()
      
      router.push('/')
    } else {
      error.value = 'Не удалось зарегистрировать организацию'
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Произошла ошибка'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.organization-registration {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(180deg, #FAFAFA 0%, #F0F0F3 100%);
}

.registration-container {
  max-width: 700px;
  margin: 40px auto;
  background: white;
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.registration-header {
  text-align: center;
  margin-bottom: 32px;
}

.registration-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1C1C1E;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 16px;
  color: #8E8E93;
}

.registration-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-section {
  padding: 24px;
  background: #F9FAFB;
  border-radius: 16px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #1C1C1E;
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
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

.form-group input,
.form-group textarea {
  padding: 12px 16px;
  border: 1px solid #E5E5EA;
  border-radius: 8px;
  font-size: 15px;
  color: #1C1C1E;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #007AFF;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.submit-button {
  width: 100%;
  background: linear-gradient(180deg, #007AFF 0%, #0051D0 100%);
  color: white;
  font-size: 17px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 122, 255, 0.3);
}

.submit-button:disabled {
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
}
</style>

