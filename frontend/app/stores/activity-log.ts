import type { FetchError } from 'ofetch'
import type { ActivityLog } from '~/types/ActivityLog'
import { defineStore } from 'pinia'

export const useActivityLogStore = defineStore('activityLog', {
  state: () => ({
    items: [] as ActivityLog[],
    item: null as ActivityLog | null,

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

    /** Fetch all logs */
    async fetchAll() {
      this.resetStatus()
      this.loading = true

      try {
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: ActivityLog[]
        }>('/activity-logs')

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

    /** Fetch single log by ID */
    async fetchById(id: number) {
      this.resetStatus()
      this.loading = true

      try {
        const res = await useNuxtApp().$auth.$fetch<{
          message: string
          data: ActivityLog
        }>(`/activity-logs/${id}`)

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
  },
})
