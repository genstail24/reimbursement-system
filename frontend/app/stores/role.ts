import type { FetchError } from 'ofetch'
import type {
  CreateRolePayload,
  Role,
  SyncPermissionsPayload,
  UpdateRolePayload,
} from '~/types/Role'
// stores/role.ts
import { defineStore } from 'pinia'

export const useRoleStore = defineStore('role', {
  state: () => ({
    items: [] as Role[],
    item: null as Role | null,

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
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: Role[]
        }>('/roles')
        this.items = res.data || []
        this.success = true
        this.message = res.message
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

    async fetchById(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: Role
        }>(`/roles/${id}`)
        this.item = res.data
        this.success = true
        this.message = res.message
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

    async store(payload: CreateRolePayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>('/roles', {
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

    async update(payload: UpdateRolePayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/roles/${payload.id}`, {
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

    async delete(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        await useNuxtApp().$auth.$fetch(`/roles/${id}`, { method: 'DELETE' })
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

    async syncPermissions(id: number, payload: SyncPermissionsPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/roles/${id}/permissions`, {
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
