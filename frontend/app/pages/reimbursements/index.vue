<script setup lang="ts">
import type { ApprovalReimbursementPayload, SubmissionReimbursementPayload } from '~/types/Reimbursement'
import { FilterMatchMode } from '@primevue/core/api'
import { useMessages } from '~/composables/messages'
import { useCategoryStore } from '~/stores/category'
import { useReimbursementStore } from '~/stores/reimbursement'

definePageMeta({
  auth: true,
})

const reimbursementStore = useReimbursementStore()
const categoryStore = useCategoryStore()
const authStore = useAuth()
const { showErrorMessage, showSuccessMessage } = useMessages()
const { confirmDelete } = useConfirmation()

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
})

const isDialogVisible = ref(false)
const isDetailDialogVisible = ref(false)
const isImageDialogVisible = ref(false)

const selectedImageUrl = ref<string | null>(null)
const isSubmitting = ref(false)
const selectedId = ref<number | null>(null)
const approvalReason = ref<string | null>(null)
const approvalStatus = ref<'approved' | 'rejected'>('approved')

const submissionForm = reactive<SubmissionReimbursementPayload>({
  title: '',
  description: '',
  amount: 0,
  category_id: 0,
  attachment: null!,
})

onMounted(async () => {
  await reimbursementStore.fetchAll()
  await categoryStore.fetchAll()
})

watch(() => reimbursementStore.isFailed, (val) => {
  if (val)
    reimbursementStore.getMessage && showErrorMessage('Failed', reimbursementStore.getMessage || 'Failed')
})
watch(() => reimbursementStore.isSuccess, (val) => {
  if (val)
    return reimbursementStore.getMessage && showSuccessMessage('Success', reimbursementStore.getMessage || 'Success')
})

function resetForm() {
  submissionForm.title = ''
  submissionForm.description = ''
  submissionForm.amount = 0
  submissionForm.category_id = 0
  submissionForm.attachment = null!

  isDetailDialogVisible.value = false
  isDialogVisible.value = false
  isImageDialogVisible.value = false
}

function openSubmissionDialog() {
  resetForm()
  isDialogVisible.value = true
}

function openAttachmentDetailDialog(url: string) {
  selectedImageUrl.value = url
  isImageDialogVisible.value = true
}

async function submitReimbursement() {
  isSubmitting.value = true
  await reimbursementStore.submission({ ...submissionForm })
  isSubmitting.value = false

  if (!reimbursementStore.isFailed) {
    isDialogVisible.value = false
    await reimbursementStore.fetchAll()
  }
}

function openDetailDialog(id: number) {
  reimbursementStore.fetchById(id)
  isDetailDialogVisible.value = true
}

const { confirmAction } = useConfirmation()
function approve(id: number) {
  confirmAction(() => {
    selectedId.value = id
    approvalStatus.value = 'approved'
    submitApproval()
  })
}

function reject(id: number) {
  confirmAction(() => {
    selectedId.value = id
    approvalStatus.value = 'rejected'
    submitApproval()
  })
}

async function submitApproval() {
  if (!selectedId.value)
    return

  const item = reimbursementStore.reimbursements.find(r => r.id === selectedId.value)
  if (!item)
    return

  const payload: ApprovalReimbursementPayload = {
    approval_reason: approvalReason.value,
    status: approvalStatus.value,
  }

  await reimbursementStore.approve(item.id, payload)

  if (!reimbursementStore.isFailed) {
    resetForm()
    await reimbursementStore.fetchAll()
  }
}

async function deleteItem(id: number) {
  confirmDelete(id, async () => {
    await reimbursementStore.delete(id)
    if (!reimbursementStore.error)
      await reimbursementStore.fetchAll()
  })
}
</script>

<template>
  <div>
    <div class="card">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl">
          Reimbursements
        </h2>
        <div>
          <Button label="Submit Reimbursement" icon="pi pi-plus" @click="openSubmissionDialog" />
        </div>
      </div>

      <DataTable
        :value="reimbursementStore.reimbursements" :filters="filters" data-key="id" :paginator="true"
        :rows="10" striped-rows :global-filter-fields="['title', 'description']"
        :loading="reimbursementStore.isLoading"
      >
        <Column header="Submitted By">
          <template #body="{ data }">
            {{ data.user?.name || '-' }}
          </template>
        </Column>
        <Column field="title" header="Title" />
        <Column field="amount" header="Amount" />
        <Column field="status" header="Status" />
        <Column field="created_at" header="Submitted At" />
        <Column header="Action">
          <template #body="{ data }">
            <div class="flex justify-between">
              <Button icon="pi pi-eye" class="p-button-text p-button-sm" @click="openDetailDialog(data.id)" />
              <Button
                v-if="authStore" icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger"
                @click="deleteItem(data.id)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Submission Dialog -->
    <Dialog v-model:visible="isDialogVisible" header="Submit Reimbursement" modal class="w-1/3">
      <div class="p-fluid">
        <div class="field">
          <label>Title</label>
          <InputText v-model="submissionForm.title" />
        </div>
        <div class="field">
          <label>Description</label>
          <Textarea v-model="submissionForm.description" auto-resize />
        </div>
        <div class="field">
          <label>Amount</label>
          <InputNumber v-model="submissionForm.amount" mode="currency" currency="IDR" locale="id-ID" />
        </div>
        <div class="field">
          <label>Category</label>
          <Dropdown
            v-model="submissionForm.category_id" :options="categoryStore.categories" option-label="name"
            option-value="id" placeholder="Select a category"
          />
        </div>
        <div class="field">
          <label>Attachment</label>
          <input type="file" class="p-inputtext-sm" @change="e => submissionForm.attachment = e.target.files[0]">
        </div>

        <div class="mt-4 flex gap-2 justify-end">
          <Button label="Cancel" class="p-button-text" @click="isDialogVisible = false" />
          <Button label="Submit" :loading="isSubmitting" @click="submitReimbursement" />
        </div>
      </div>
    </Dialog>

    <Dialog v-model:visible="isDetailDialogVisible" header="Detail Reimbursement" modal class="w-1/3">
      <div v-if="!reimbursementStore.isLoading && reimbursementStore.item" class="space-y-2">
        <p><strong>Submitted by:</strong> {{ reimbursementStore.item?.user?.name ?? '-' }}</p>
        <p><strong>Title:</strong> {{ reimbursementStore.item.title }}</p>
        <p><strong>Description:</strong> {{ reimbursementStore.item.description }}</p>
        <p><strong>Amount:</strong> {{ reimbursementStore.item.amount }}</p>
        <p><strong>Category ID:</strong> {{ reimbursementStore.item.category_id }}</p>
        <p><strong>Status:</strong> {{ reimbursementStore.item.status }}</p>
        <p><strong>Approval Reason:</strong> {{ reimbursementStore.item.approval_reason ?? '-' }}</p>
        <p>
          <strong>Attachment:</strong>
          <Button
            v-if="reimbursementStore.item.attachment_url"
            variant="text"
            severity="primary"
            @click="openAttachmentDetailDialog(reimbursementStore.item.attachment_url)"
          >
            View Attachment
          </Button>
          <span v-else class="text-gray-500">No attachment</span>
        </p>
        <hr>
        <Textarea
          v-if="reimbursementStore.item.status === 'pending'" v-model="approvalReason"
          placeholder="Type any reason (optional)" class="w-full"
        />
        <div class="flex gap-x-2 items-center">
          <template v-if="reimbursementStore.item.status === 'pending'">
            <Button
              v-if="authStore" label="Approve" icon="pi pi-check-circle" severity="success"
              @click="approve(reimbursementStore.item.id)"
            />
            <Button
              v-if="authStore" label="Reject" icon="pi pi-times-circle" severity="danger"
              @click="reject(reimbursementStore.item.id)"
            />
          </template>
          <template v-else>
            <span
              class="italic"
              :class="reimbursementStore.item.status === 'approved' ? 'text-green-600' : 'text-red-600'"
            >
              {{ reimbursementStore.item.status === 'approved' ? 'Already approved' : 'Already rejected' }}
            </span>
          </template>
        </div>
      </div>
    </Dialog>
    <Dialog v-model:visible="isImageDialogVisible" modal header="Attachment" class="w-auto">
      <Image v-if="selectedImageUrl" :src="selectedImageUrl" alt="Image" width="250" />
    </Dialog>
  </div>
</template>
