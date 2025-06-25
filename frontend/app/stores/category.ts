import type { FetchError } from 'ofetch'
import type { Category } from '~/types/Category'
import { defineStore } from 'pinia'

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [] as Category[],
    loading: false,
    success: false,
    error: false,
    message: null as string | null,
    validationMessages: {} as Record<string, string[]>,
  }),

  getters: {
    isLoading: state => state.loading,
    isSuccess: state => state.success,
    isFailed: state => state.error,
    getMessage: state => state.message,
    getValidationMessages: state => state.validationMessages,
  },

  actions: {
    resetStatus() {
      this.loading = false
      this.success = false
      this.error = false
      this.message = null
      this.validationMessages = {}
    },

    async fetchAll() {
      this.resetStatus()
      this.loading = true
      try {
        const { data } = await useNuxtApp().$auth.$fetch<{ data: Category[] }>('/categories')
        this.categories = data || []
        this.success = true
        this.message = ''
      }
      catch (e: unknown) {
        const err = e as FetchError
        this.error = true
        this.message = err.data?.message ?? err.message
      }
      finally {
        this.loading = false
      }
    },

    async fetchById(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        const { data } = await useNuxtApp().$auth.$fetch<{ data: Category [] }>(`/categories/${id}`)
        this.categories = data || []
        this.success = true
        this.message = ''
      }
      catch (e: unknown) {
        const err = e as FetchError<{ message?: string }>
        this.error = true
        this.message = err.data?.message ?? err.message
      }
      finally {
        this.loading = false
      }
    },

    async store(payload: Omit<Category, 'id'>) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>('/categories', {
          method: 'POST',
          body: payload,
        })
        this.success = true
        this.message = res.message
      }
      catch (e: unknown) {
        const err = e as FetchError
        this.error = true
        const json = err.data as { message?: string, errors?: Record<string, string[]> }
        if (err.status === 422 && json.errors) {
          this.validationMessages = json.errors
          this.message = json.message ?? 'Validation failed'
        }
        else {
          this.message = json.message ?? err.message
        }
      }
      finally {
        this.loading = false
      }
    },

    async update(payload: Category) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/categories/${payload.id}`, {
          method: 'PUT',
          body: payload,
        })
        this.success = true
        this.message = res.message
      }
      catch (e: unknown) {
        const err = e as FetchError
        this.error = true
        const json = err.data as { message?: string, errors?: Record<string, string[]> }
        if (err.status === 422 && json.errors) {
          this.validationMessages = json.errors
          this.message = json.message ?? 'Validation failed'
        }
        else {
          this.message = json.message ?? err.message
        }
      }
      finally {
        this.loading = false
      }
    },

    async delete(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        await useNuxtApp().$auth.$fetch(`/categories/${id}`, { method: 'DELETE' })
        this.success = true
        this.message = 'Deleted successfully'
      }
      catch (e: unknown) {
        const err = e as FetchError
        this.error = true
        this.message = err.data?.message ?? err.message
      }
      finally {
        this.loading = false
      }
    },
  },
})
