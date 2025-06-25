import type { FetchError } from 'ofetch'
import type { ApprovalReimbursementPayload, Reimbursement, SubmissionReimbursementPayload } from '~/types/Reimbursement'
import { defineStore } from 'pinia'

export const useReimbursementStore = defineStore('reimbursement', {
  state: () => ({
    reimbursements: [] as Reimbursement[],
    item: null as Reimbursement | null,
    loading: false,
    success: false,
    error: false,
    message: null as string | null,
  }),

  getters: {
    isLoading: state => state.loading,
    isSuccess: state => state.success,
    isFailed: state => state.error,
    getMessage: state => state.message,
  },

  actions: {
    resetStatus() {
      this.loading = false
      this.success = false
      this.error = false
      this.message = null
    },

    async fetchAll() {
      this.resetStatus()
      this.loading = true
      try {
        const { data } = await useNuxtApp().$auth.$fetch<{ data: Reimbursement[] }>('/reimbursements')
        this.reimbursements = data || []
        this.success = true
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
        const { data } = await useNuxtApp().$auth.$fetch<{ data: Reimbursement }>(`/reimbursements/${id}`)
        this.item = data
        this.success = true
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

    async submission(payload: SubmissionReimbursementPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const form = new FormData()
        form.append('title', payload.title)
        form.append('description', payload.description)
        form.append('amount', String(payload.amount))
        form.append('category_id', String(payload.category_id))
        form.append('attachment', payload.attachment)

        const res = await useNuxtApp().$auth.$fetch<{ message: string }>('/reimbursements/submission', {
          method: 'POST',
          body: form,
        })

        this.success = true
        this.message = res.message
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

    async approve(id: number, payload: ApprovalReimbursementPayload) {
      this.resetStatus()
      this.loading = true
      try {
        const res = await useNuxtApp().$auth.$fetch<{ message: string }>(`/reimbursements/${id}/approval`, {
          method: 'PUT',
          body: payload,
        })

        this.success = true
        this.message = res.message
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

    async delete(id: number) {
      this.resetStatus()
      this.loading = true
      try {
        await useNuxtApp().$auth.$fetch(`/reimbursements/${id}`, { method: 'DELETE' })
        this.success = true
        this.message = 'Reimbursement deleted'
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
