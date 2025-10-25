<template>
  <div class="admin-layout">
        <aside class="sidebar">
          <div class="sidebar-content">
            <div class="sidebar-header">
              <div class="brand-logo">
                <div class="logo-icon">N</div>
                <div class="brand-text">
                  <h2>Ninja Goods</h2>
                  <p>Админ-панель</p>
                </div>
              </div>
            </div>
            
            <nav class="sidebar-nav">
              <router-link to="/dashboard" class="nav-item" :class="{ active: $route.path === '/dashboard' }">
                <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3v18h18V3H3zm16 16H5V5h14v14z"/>
                  <path d="M7 7h4v4H7V7zm6 0h4v4h-4V7zm-6 6h4v4H7v-4zm6 0h4v4h-4v-4z"/>
                </svg>
                <span class="nav-text">Дашборд</span>
              </router-link>
              <router-link to="/categories" class="nav-item" :class="{ active: $route.path === '/categories' }">
                <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                <span class="nav-text">Категории</span>
              </router-link>
              <router-link to="/products" class="nav-item" :class="{ active: $route.path === '/products' }">
                <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                  <line x1="3" y1="6" x2="21" y2="6"/>
                  <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span class="nav-text">Товары</span>
              </router-link>
              <router-link to="/orders" class="nav-item" :class="{ active: $route.path === '/orders' }">
                <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                  <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                </svg>
                <span class="nav-text">Заказы</span>
              </router-link>
            </nav>
          </div>
          
          <div class="sidebar-footer">
            <button @click="handleLogout" class="logout-btn">
              <svg class="logout-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              <span class="logout-text">Выйти</span>
            </button>
          </div>
        </aside>
    
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
}

.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #F9FAFB 0%, #F2F3F5 100%);
  box-shadow: 2px 0 16px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1000;
  border-right: 1px solid rgba(0, 0, 0, 0.06);
}

.sidebar-content {
  flex: 1;
  padding: 32px 0;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 0 32px 32px 32px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  margin-bottom: 32px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #007AFF 0%, #34C759 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
  font-weight: 700;
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
  box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
}

.brand-text h2 {
  color: #1C1C1E;
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
  letter-spacing: -0.01em;
}

.brand-text p {
  font-size: 14px;
  color: #8E8E93;
  margin: 4px 0 0 0;
  font-weight: 400;
}

.sidebar-nav {
  flex: 1;
  padding: 0 20px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #1C1C1E;
  text-decoration: none;
  border-radius: 10px;
  margin-bottom: 4px;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  font-size: 15px;
  font-weight: 500;
  position: relative;
  overflow: hidden;
}

.nav-item:hover {
  background: rgba(0, 122, 255, 0.08);
  color: #007AFF;
  transform: translateX(2px);
}

.nav-item:hover .nav-icon {
  transform: translateX(2px);
}

.nav-item.active {
  background: linear-gradient(180deg, #007AFF 0%, #0062CC 100%);
  color: white;
  box-shadow: 0 4px 8px rgba(0, 122, 255, 0.3);
  transform: translateX(0);
}

.nav-item.active .nav-icon {
  transform: translateX(0);
}

.nav-icon {
  width: 20px;
  height: 20px;
  transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  flex-shrink: 0;
}

.nav-text {
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
}

.sidebar-footer {
  padding: 16px 32px 32px 32px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  background: linear-gradient(180deg, transparent 0%, rgba(255, 255, 255, 0.5) 100%);
}

.logout-btn {
  width: 100%;
  background: linear-gradient(180deg, #FF3B30 0%, #D93025 100%);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 14px 20px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
  box-shadow: 0 2px 8px rgba(255, 59, 48, 0.3);
}

.logout-btn:hover {
  box-shadow: 0 0 12px rgba(255, 59, 48, 0.4);
  transform: scale(0.98);
}

.logout-btn:active {
  transform: scale(0.95);
  box-shadow: 0 2px 6px rgba(255, 59, 48, 0.3);
}

.logout-icon {
  width: 18px;
  height: 18px;
  transition: transform 0.2s ease;
}

.logout-btn:hover .logout-icon {
  transform: translateX(1px);
}

.logout-text {
  font-weight: 600;
}

.main-content {
  flex: 1;
  padding: 32px;
  margin-left: 280px;
  max-width: calc(100% - 280px);
  min-height: 100vh;
  background: #F9FAFB;
}

/* Адаптивность */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    border-right: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }
  
  .main-content {
    margin-left: 0;
    max-width: 100%;
    padding: 20px;
  }
  
  .sidebar-content {
    padding: 24px 0;
  }
  
  .sidebar-header {
    padding: 0 24px 24px 24px;
  }
  
  .sidebar-nav {
    padding: 0 16px;
  }
  
  .sidebar-footer {
    padding: 16px 24px 24px 24px;
  }
  
  .brand-logo {
    gap: 10px;
  }
  
  .logo-icon {
    width: 36px;
    height: 36px;
    font-size: 16px;
  }
  
  .brand-text h2 {
    font-size: 18px;
  }
  
  .nav-item {
    padding: 10px 16px;
    font-size: 14px;
  }
  
  .logout-btn {
    padding: 12px 16px;
    font-size: 14px;
  }
}
</style>
