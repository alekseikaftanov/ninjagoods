<template>
  <div class="order-success">
    <div class="success-content">
      <div class="success-icon">✅</div>
      <h1>Заказ оформлен!</h1>
      <p>Спасибо за ваш заказ. Мы свяжемся с вами в ближайшее время.</p>
      
      <div class="success-actions">
        <router-link to="/categories" class="btn btn-primary">
          Продолжить покупки
        </router-link>
        <router-link to="/" class="btn btn-secondary">
          На главную
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { hapticFeedback } from '../utils/telegram'

onMounted(() => {
  hapticFeedback('success')
})
</script>

<style scoped>
.order-success {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
}

.success-content {
  max-width: 400px;
  padding: 32px;
}

.success-icon {
  font-size: 64px;
  margin-bottom: 24px;
}

.success-content h1 {
  font-size: 24px;
  margin-bottom: 16px;
  color: var(--tg-theme-text-color, #000000);
}

.success-content p {
  font-size: 16px;
  color: var(--tg-theme-hint-color, #666666);
  margin-bottom: 32px;
  line-height: 1.5;
}

.success-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

@media (max-width: 480px) {
  .success-content {
    padding: 24px;
  }
  
  .success-icon {
    font-size: 48px;
  }
  
  .success-content h1 {
    font-size: 20px;
  }
  
  .success-content p {
    font-size: 14px;
  }
}
</style>
