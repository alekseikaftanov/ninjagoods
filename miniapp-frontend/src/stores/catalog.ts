import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoriesAPI, productsAPI } from '../utils/api'
import type { Category, Product } from '../types'

export const useCatalogStore = defineStore('catalog', () => {
  const categories = ref<Category[]>([])
  const products = ref<Product[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Загрузить категории
  const loadCategories = async () => {
    isLoading.value = true
    error.value = null

    try {
      categories.value = await categoriesAPI.getAll()
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load categories'
    } finally {
      isLoading.value = false
    }
  }

  // Загрузить все товары
  const loadProducts = async () => {
    isLoading.value = true
    error.value = null

    try {
      products.value = await productsAPI.getAll()
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load products'
    } finally {
      isLoading.value = false
    }
  }

  // Загрузить товары по категории
  const loadProductsByCategory = async (categoryId: number) => {
    isLoading.value = true
    error.value = null

    try {
      products.value = await productsAPI.getByCategory(categoryId)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load products'
    } finally {
      isLoading.value = false
    }
  }

  // Получить товар по ID
  const getProductById = async (id: number): Promise<Product | null> => {
    try {
      return await productsAPI.getById(id)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load product'
      return null
    }
  }

  // Получить главные категории (без родителя)
  const getMainCategories = () => {
    return categories.value.filter(category => !category.parent_id)
  }

  // Получить подкатегории
  const getSubCategories = (parentId: number) => {
    return categories.value.filter(category => category.parent_id === parentId)
  }

  return {
    categories,
    products,
    isLoading,
    error,
    loadCategories,
    loadProducts,
    loadProductsByCategory,
    getProductById,
    getMainCategories,
    getSubCategories,
  }
})
