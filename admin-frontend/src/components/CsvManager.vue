<template>
  <div class="csv-manager">
    <div class="csv-section">
      <h3>Экспорт товаров</h3>
      <p>Скачайте все товары в формате CSV для редактирования или резервного копирования.</p>
      <button @click="exportProducts" :disabled="isExporting" class="btn btn-primary">
        <span v-if="isExporting">Экспорт...</span>
        <span v-else>📥 Экспорт в CSV</span>
      </button>
    </div>

    <div class="csv-section">
      <h3>Импорт товаров</h3>
      <p>Загрузите товары из CSV файла. Сначала скачайте шаблон для правильного формата.</p>
      
      <div class="csv-actions">
        <div class="file-upload">
          <input 
            ref="fileInput" 
            type="file" 
            accept=".csv" 
            @change="handleFileUpload"
            style="display: none"
          >
        </div>

        <!-- Drag and Drop Zone -->
        <div 
          class="drag-drop-zone"
          :class="{ 'drag-over': isDragOver, 'drag-error': dragError }"
          @dragover.prevent="handleDragOver"
          @dragleave.prevent="handleDragLeave"
          @drop.prevent="handleDrop"
        >
          <div class="drag-content">
            <div class="drag-icon">📁</div>
            <div class="drag-text">
              <h4>Перетащите CSV файл сюда</h4>
              <p>или</p>
              <button @click="$refs.fileInput.click()" class="btn btn-link">
                выберите файл
              </button>
            </div>
            <div class="drag-hint">
              Поддерживаются только файлы .csv до 10MB
            </div>
          </div>
        </div>

        <button @click="downloadTemplate" class="btn btn-secondary">
          📋 Скачать шаблон
        </button>
      </div>

      <div v-if="importResults" class="import-results">
        <h4>Результаты импорта:</h4>
        <div class="results-summary">
          <span class="success">✅ Успешно: {{ importResults.success }}</span>
          <span class="error">❌ Ошибки: {{ importResults.errors }}</span>
          <span class="skipped">⏭️ Пропущено: {{ importResults.skipped }}</span>
        </div>
        
        <div v-if="importResults.details.length > 0" class="results-details">
          <h5>Детали:</h5>
          <div class="details-list">
            <div 
              v-for="detail in importResults.details" 
              :key="detail.row"
              :class="['detail-item', detail.status]"
            >
              <strong>Строка {{ detail.row }}:</strong> {{ detail.message }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="csv-section">
      <h3>Формат CSV файла</h3>
      <div class="csv-format">
        <p>CSV файл должен содержать следующие колонки (в указанном порядке):</p>
        <ul>
          <li><strong>name</strong> - Название товара (обязательно)</li>
          <li><strong>photo_url</strong> - URL фотографии (обязательно)</li>
          <li><strong>description</strong> - Описание товара (обязательно)</li>
          <li><strong>unit</strong> - Единица измерения: "штуки" или "килограммы" (обязательно)</li>
          <li><strong>price</strong> - Цена за единицу (обязательно, число)</li>
          <li><strong>min_order</strong> - Минимальный заказ (обязательно, число)</li>
          <li><strong>category_name</strong> - Название категории (опционально)</li>
          <li><strong>category_id</strong> - ID категории (опционально)</li>
        </ul>
        <p><em>Примечание: Для определения категории используйте либо category_name, либо category_id. Поддерживаются разделители: запятые (,) или точки с запятой (;).</em></p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

const isExporting = ref(false)
const importResults = ref(null)
const fileInput = ref(null)
const isDragOver = ref(false)
const dragError = ref(false)

const exportProducts = async () => {
  isExporting.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/admin/products/csv/export')
    
    if (response.data.success) {
      const blob = new Blob([response.data.data.csv_content], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', response.data.data.filename)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      alert(`Экспорт завершен! Скачано ${response.data.data.products_count} товаров.`)
    } else {
      alert('Ошибка при экспорте: ' + response.data.message)
    }
  } catch (error: any) {
    console.error('Export error:', error)
    alert('Ошибка при экспорте: ' + (error.response?.data?.message || error.message))
  } finally {
    isExporting.value = false
  }
}

const downloadTemplate = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/admin/products/csv/template')
    
    if (response.data.success) {
      const blob = new Blob([response.data.data.csv_content], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', response.data.data.filename)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    } else {
      alert('Ошибка при скачивании шаблона: ' + response.data.message)
    }
  } catch (error: any) {
    console.error('Template download error:', error)
    alert('Ошибка при скачивании шаблона: ' + (error.response?.data?.message || error.message))
  }
}

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return
  
  // Валидация файла
  if (!validateFile(file)) {
    target.value = ''
    return
  }
  
  // Обработка файла
  await processFile(file)
  
  // Очищаем input
  target.value = ''
}

const readFileAsText = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => resolve(e.target?.result as string)
    reader.onerror = (e) => reject(e)
    reader.readAsText(file, 'UTF-8')
  })
}

// Drag and Drop методы
const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = true
  dragError.value = false
}

const handleDragLeave = (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = false
  dragError.value = false
}

const handleDrop = async (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = false
  dragError.value = false
  
  const files = event.dataTransfer?.files
  if (!files || files.length === 0) return
  
  const file = files[0]
  
  // Валидация файла
  if (!validateFile(file)) {
    return
  }
  
  // Обработка файла
  await processFile(file)
}

const validateFile = (file: File): boolean => {
  // Проверяем расширение файла
  if (!file.name.toLowerCase().endsWith('.csv')) {
    dragError.value = true
    alert('Пожалуйста, выберите CSV файл (.csv)')
    return false
  }
  
  // Проверяем тип файла
  if (!file.type.includes('text/csv') && !file.type.includes('text/plain') && file.type !== '') {
    dragError.value = true
    alert('Выбранный файл не является CSV файлом. Пожалуйста, выберите файл с расширением .csv')
    return false
  }
  
  // Проверяем размер файла (максимум 10MB)
  if (file.size > 10 * 1024 * 1024) {
    dragError.value = true
    alert('Файл слишком большой. Максимальный размер: 10MB')
    return false
  }
  
  return true
}

const processFile = async (file: File) => {
  try {
    const csvContent = await readFileAsText(file)
    
    const response = await axios.post('http://localhost:8000/api/admin/products/csv/import', {
      csv_content: csvContent
    })
    
    if (response.data.success) {
      importResults.value = response.data.data
      
      const { success, errors, skipped } = response.data.data
      alert(`Импорт завершен!\n✅ Успешно: ${success}\n❌ Ошибки: ${errors}\n⏭️ Пропущено: ${skipped}`)
    } else {
      alert('Ошибка при импорте: ' + response.data.message)
    }
  } catch (error: any) {
    console.error('Import error:', error)
    alert('Ошибка при импорте: ' + (error.response?.data?.message || error.message))
  }
}
</script>

<style scoped>
.csv-manager {
  max-width: 800px;
  margin: 0 auto;
}

.csv-section {
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  padding: var(--spacing-lg);
  margin-bottom: var(--spacing-lg);
  box-shadow: 0 2px 8px var(--shadow-light);
}

.csv-section h3 {
  margin-bottom: var(--spacing-md);
  color: var(--text-primary);
  font-size: var(--font-size-medium);
}

.csv-section p {
  margin-bottom: var(--spacing-md);
  color: var(--text-secondary);
  line-height: 1.5;
}

.csv-actions {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.csv-actions .btn {
  align-self: center;
}

.file-upload {
  position: relative;
}

.import-results {
  margin-top: var(--spacing-lg);
  padding: var(--spacing-md);
  background: var(--bg-primary);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
}

.results-summary {
  display: flex;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-md);
  flex-wrap: wrap;
}

.results-summary span {
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-small);
  font-weight: 500;
}

.success {
  background: rgba(52, 199, 89, 0.1);
  color: #34C759;
}

.error {
  background: rgba(255, 59, 48, 0.1);
  color: #FF3B30;
}

.skipped {
  background: rgba(255, 149, 0, 0.1);
  color: #FF9500;
}

.results-details {
  margin-top: var(--spacing-md);
}

.results-details h5 {
  margin-bottom: var(--spacing-sm);
  color: var(--text-primary);
}

.details-list {
  max-height: 200px;
  overflow-y: auto;
}

.detail-item {
  padding: var(--spacing-xs) var(--spacing-sm);
  margin-bottom: var(--spacing-xs);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-small);
}

.detail-item.success {
  background: rgba(52, 199, 89, 0.05);
  border-left: 3px solid #34C759;
}

.detail-item.error {
  background: rgba(255, 59, 48, 0.05);
  border-left: 3px solid #FF3B30;
}

.detail-item.skipped {
  background: rgba(255, 149, 0, 0.05);
  border-left: 3px solid #FF9500;
}

.csv-format {
  background: var(--bg-primary);
  padding: var(--spacing-md);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
}

.csv-format ul {
  margin: var(--spacing-md) 0;
  padding-left: var(--spacing-lg);
}

.csv-format li {
  margin-bottom: var(--spacing-xs);
  color: var(--text-secondary);
}

.csv-format strong {
  color: var(--text-primary);
}

.csv-format em {
  color: var(--text-secondary);
  font-style: italic;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Drag and Drop стили */
.drag-drop-zone {
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-md);
  padding: var(--spacing-xl);
  margin: var(--spacing-md) 0;
  text-align: center;
  background: var(--bg-primary);
  transition: all 0.3s ease;
  cursor: pointer;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.drag-drop-zone:hover {
  border-color: var(--accent-blue);
  background: var(--accent-blue-light);
}

.drag-drop-zone.drag-over {
  border-color: var(--accent-blue);
  background: var(--accent-blue-light);
  transform: scale(1.02);
  box-shadow: 0 4px 16px rgba(0, 122, 255, 0.2);
}

.drag-drop-zone.drag-error {
  border-color: #FF3B30;
  background: rgba(255, 59, 48, 0.1);
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.drag-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-md);
}

.drag-icon {
  font-size: 48px;
  opacity: 0.6;
  transition: all 0.3s ease;
}

.drag-drop-zone:hover .drag-icon,
.drag-drop-zone.drag-over .drag-icon {
  opacity: 1;
  transform: scale(1.1);
}

.drag-text h4 {
  margin: 0;
  color: var(--text-primary);
  font-size: var(--font-size-medium);
}

.drag-text p {
  margin: var(--spacing-xs) 0;
  color: var(--text-secondary);
}

.btn-link {
  background: none;
  border: none;
  color: var(--accent-blue);
  text-decoration: underline;
  cursor: pointer;
  font-size: var(--font-size-regular);
  padding: 0;
}

.btn-link:hover {
  color: #0056CC;
}

.drag-hint {
  font-size: var(--font-size-small);
  color: var(--text-secondary);
  opacity: 0.8;
}
</style>
