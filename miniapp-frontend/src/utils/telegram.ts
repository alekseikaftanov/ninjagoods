import type { TelegramWebApp } from '../types'

// Получение экземпляра Telegram WebApp
export function getTelegramWebApp(): TelegramWebApp | null {
  if (typeof window !== 'undefined' && (window as any).Telegram?.WebApp) {
    return (window as any).Telegram.WebApp as TelegramWebApp
  }
  return null
}

// Инициализация Telegram WebApp
export function initTelegramWebApp(): TelegramWebApp | null {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.ready()
    tg.expand()
    
    // Настройка темы
    document.body.style.backgroundColor = tg.backgroundColor
    document.body.style.color = tg.themeParams.text_color || '#000000'
    
    return tg
  }
  return null
}

// Получение данных пользователя
export function getTelegramUser() {
  const tg = getTelegramWebApp()
  return tg?.initDataUnsafe?.user || null
}

// Получение initData для API
export function getInitData(): string {
  const tg = getTelegramWebApp()
  return tg?.initData || ''
}

// Показать главную кнопку
export function showMainButton(text: string, onClick: () => void) {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.MainButton.setText(text)
    tg.MainButton.show()
    tg.MainButton.onClick(onClick)
  }
}

// Скрыть главную кнопку
export function hideMainButton() {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.MainButton.hide()
  }
}

// Показать кнопку "Назад"
export function showBackButton(onClick: () => void) {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.BackButton.show()
    tg.BackButton.onClick(onClick)
  }
}

// Скрыть кнопку "Назад"
export function hideBackButton() {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.BackButton.hide()
  }
}

// Тактильная обратная связь
export function hapticFeedback(type: 'light' | 'medium' | 'heavy' | 'rigid' | 'soft' | 'error' | 'success' | 'warning') {
  const tg = getTelegramWebApp()
  if (tg) {
    if (['error', 'success', 'warning'].includes(type)) {
      tg.HapticFeedback.notificationOccurred(type as 'error' | 'success' | 'warning')
    } else {
      tg.HapticFeedback.impactOccurred(type as 'light' | 'medium' | 'heavy' | 'rigid' | 'soft')
    }
  }
}

// Закрыть приложение
export function closeApp() {
  const tg = getTelegramWebApp()
  if (tg) {
    tg.close()
  }
}
