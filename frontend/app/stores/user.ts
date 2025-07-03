import type { FetchError } from 'ofetch'
import type { CreateUserPayload, SyncRolesPayload, UpdateUserPayload, User } from '@/types/User'
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [] as User[],
    user: null as User | null,

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

    /** fetch all users */
    async fetchAll() {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: User[]
        }>('/users')
        this.users = res.data || []
        this.users = res.data && res.data.map(item => ({
          ...item,
          roles: item.roles.map(role => role || ''),
        }))
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

    /** fetch single user */
    async fetchById(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: User
        }>(`/users/${id}`)
        this.user = res.data
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

    async store(payload: CreateUserPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>('/users', {
          method: 'POST',
          body: payload,
        })
        this.success = true
        this.message = res.message
      }
      catch (e: unknown) {
        const err = e as FetchError<{ message?: string, errors?: Record<string, string[]> }>
        this.error = true
        if (err.status === 422 && err.data?.errors) {
          this.validationMessages = err.data.errors
          this.message = err.data.message ?? 'Validation failed'
        }
        else {
          this.message = err.data?.message ?? err.message
        }
      }
      finally {
        this.loading = false
      }
    },

    /** update existing user (no roles) */
    async update(payload: UpdateUserPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/users/${payload.id}`, {
          method: 'PUT',
          body: payload,
        })
        this.success = true
        this.message = res.message
      }
      catch (e: unknown) {
        const err = e as FetchError<{ message?: string, errors?: Record<string, string[]> }>
        this.error = true
        if (err.status === 422 && err.data?.errors) {
          this.validationMessages = err.data.errors
          this.message = err.data.message ?? 'Validation failed'
        }
        else {
          this.message = err.data?.message ?? err.message
        }
      }
      finally {
        this.loading = false
      }
    },

    /** delete user */
    async delete(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        await useNuxtApp().$auth.$fetch(`/users/${id}`, { method: 'DELETE' })
        this.success = true
        this.message = 'Deleted successfully'
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

    /** sync roles for a user */
    async syncRoles(id: number, payload: SyncRolesPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/users/${id}/roles`, {
          method: 'PUT',
          body: payload,
        })
        this.success = true
        this.message = res.message
      }
      catch (e: unknown) {
        const err = e as FetchError<{ message?: string, errors?: Record<string, string[]> }>
        this.error = true
        if (err.status === 422 && err.data?.errors) {
          this.validationMessages = err.data.errors
          this.message = err.data.message ?? 'Validation failed'
        }
        else {
          this.message = err.data?.message ?? err.message
        }
      }
      finally {
        this.loading = false
      }
    },
  },
})
