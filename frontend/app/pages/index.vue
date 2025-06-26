<script setup lang="ts">
import { onMounted, ref } from 'vue'

definePageMeta({ auth: true })
const { showErrorMessage } = useMessages()

interface Metrics {
  totalRequests: number
  pending: number
  approved: number
  rejected: number
  totalAmount: number
}

const metrics = ref<Metrics>({
  totalRequests: 0,
  pending: 0,
  approved: 0,
  rejected: 0,
  totalAmount: 0,
})

const auth = useAuth()

onMounted(async () => {
  try {
    const { data } = await auth.$fetch('/reimbursements/metrics')
    metrics.value = data
  } catch (err: any) {
    const msg =
      err.response?.data?.message ||
      err.message ||
      'Failed to load reimbursement metrics'
    showErrorMessage(msg, '')
  }
})

function formatIDR(val: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(val)
}
</script>

<template>
  <div class="card surface-0 text-center">
    <div class="text-4xl font-bold mb-3">
      Reimbursement <span class="text-green-600">Dashboard</span>
    </div>
    <h2 class="text-2xl pb-6">
      A quick summary of your reimbursement status
    </h2>
    <div class="px-4 gap-4 grid grid-cols-2 lg:grid-cols-5 md:grid-cols-3">
      <AdvertiseBox
        header="Total Requests" :icon="metrics.totalRequests > 0 ? 'pi pi-list' : 'pi pi-list'"
        color="blue-500"
      >
        {{ metrics.totalRequests }} requests
      </AdvertiseBox>

      <AdvertiseBox header="Pending" icon="pi pi-clock" color="yellow-500">
        {{ metrics.pending }} pending
      </AdvertiseBox>

      <AdvertiseBox header="Approved" icon="pi pi-check-circle" color="green-600">
        {{ metrics.approved }} approved
      </AdvertiseBox>

      <AdvertiseBox header="Rejected" icon="pi pi-times-circle" color="red-600">
        {{ metrics.rejected }} rejected
      </AdvertiseBox>

      <AdvertiseBox header="Total Reimbursed" icon="pi pi-wallet" color="purple-500">
        {{ formatIDR(metrics.totalAmount) }}
      </AdvertiseBox>
    </div>
  </div>
</template>

<style scoped>
.card {
  padding: 2rem 1rem;
}
</style>
