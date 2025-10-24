<template>
  <div id="app" class="app">
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // Попытка аутентификации
  await authStore.authenticateWithTelegram()
})
</script>

<style>
/* Apple-Style E-commerce App */
:root {
  /* Apple Color Palette */
  --bg-primary: #F2F2F7;
  --bg-secondary: #FFFFFF;
  --bg-tertiary: #F9F9F9;
  --text-primary: #1C1C1E;
  --text-secondary: #8E8E93;
  --text-tertiary: #C7C7CC;
  --accent-blue: #007AFF;
  --accent-blue-light: rgba(0, 122, 255, 0.1);
  --accent-green: #34C759;
  --accent-red: #FF3B30;
  --border-color: rgba(60, 60, 67, 0.18);
  --shadow-light: rgba(0, 0, 0, 0.1);
  --shadow-medium: rgba(0, 0, 0, 0.15);
  
  /* Typography */
  --font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', system-ui, sans-serif;
  --font-size-large: 28px;
  --font-size-medium: 20px;
  --font-size-regular: 17px;
  --font-size-small: 15px;
  --font-size-caption: 13px;
  
  /* Spacing */
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  
  /* Border radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family);
  background-color: var(--bg-primary);
  color: var(--text-primary);
  line-height: 1.47;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: var(--font-size-regular);
}

#app {
  min-height: 100vh;
  background-color: var(--bg-primary);
}

.app {
  padding: var(--spacing-md);
  max-width: 100%;
  margin: 0 auto;
}

/* Apple-Style Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: var(--radius-md);
  font-size: var(--font-size-regular);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  text-decoration: none;
  min-height: 44px;
  border: none;
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: var(--accent-blue);
  color: white;
  box-shadow: 0 1px 3px rgba(0, 122, 255, 0.3);
}

.btn-primary:hover {
  background: #0056CC;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
  box-shadow: 0 1px 3px rgba(0, 122, 255, 0.3);
}

.btn-secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-secondary:hover {
  background: var(--bg-tertiary);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-danger {
  background: var(--accent-red);
  color: white;
  box-shadow: 0 1px 3px rgba(255, 59, 48, 0.3);
}

.btn-danger:hover {
  background: #D70015;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(255, 59, 48, 0.4);
}

/* Apple-Style Cards */
.card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  margin-bottom: var(--spacing-md);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid var(--border-color);
}

/* Apple-Style Typography */
h1, h2, h3 {
  color: var(--text-primary);
  margin-bottom: var(--spacing-md);
  font-weight: 600;
}

h1 {
  font-size: var(--font-size-large);
  line-height: 1.2;
}

h2 {
  font-size: var(--font-size-medium);
  line-height: 1.3;
}

h3 {
  font-size: var(--font-size-regular);
  line-height: 1.4;
}

/* Списки */
.list {
  list-style: none;
}

.list-item {
  padding: 16px;
  border-bottom: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.list-item:hover {
  background-color: var(--tg-theme-secondary-bg-color, #f0f0f0);
}

.list-item:last-child {
  border-bottom: none;
}

/* Изображения */
.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 12px;
}

.category-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  margin-right: 12px;
}

/* Формы */
.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--tg-theme-text-color, #000000);
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--tg-theme-hint-color, #e0e0e0);
  border-radius: 8px;
  font-size: 16px;
  background-color: var(--tg-theme-bg-color, #ffffff);
  color: var(--tg-theme-text-color, #000000);
}

.form-input:focus {
  outline: none;
  border-color: var(--tg-theme-button-color, #007AFF);
}

/* Утилиты */
.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.mb-16 {
  margin-bottom: 16px;
}

.mt-16 {
  margin-top: 16px;
}

.flex {
  display: flex;
}

.flex-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.flex-column {
  display: flex;
  flex-direction: column;
}

.gap-8 {
  gap: 8px;
}

.gap-16 {
  gap: 16px;
}

/* Адаптивность */
@media (max-width: 480px) {
  .miniapp {
    padding: 12px;
  }
  
  .card {
    padding: 12px;
  }
  
  h1 {
    font-size: 20px;
  }
  
  h2 {
    font-size: 18px;
  }
}
</style>
