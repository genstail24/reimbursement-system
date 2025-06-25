<script setup lang="ts">
import type { ApprovalReimbursementPayload, SubmissionReimbursementPayload } from '~/types/Reimbursement'
import { FilterMatchMode } from '@primevue/core/api'
import { useMessages } from '~/composables/messages'
import { useCategoryStore } from '~/stores/category'
import { useReimbursementStore } from '~/stores/reimbursement'

definePageMeta({
  auth: true,
  middleware: [
    'permission',
  ],
  permission: [
    'reimbursement.view',
  ],
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

const isAdmin = computed(() => !authStore.user.value?.roles?.includes('admin'))

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
function getStatusSeverity(status: string) {
  if (status === 'approved')
    return 'success'
  else if (status === 'rejected')
    return 'danger'
  return 'info'
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
          <Button v-has-ability-to="'reimbursement.create'" label="Submit Reimbursement" icon="pi pi-plus" @click="openSubmissionDialog" />
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
        <Column header="Status">
          <template #body="{ data }">
            <Tag
              :value="data.status"
              :severity="getStatusSeverity(data.status)"
              rounded
            />
          </template>
        </Column>
        <Column field="reviewed_by.name" header="Reviewed By" />
        <Column field="created_at" header="Submitted At" />
        <Column field="deleted_at" header="Deleted At" :hidden="isAdmin" />
        <Column header="Action">
          <template #body="{ data }">
            <div class="w-full">
              <div v-if="data.deleted_at" class="flex justify-between">
                <Tag severity="danger" value="This item has been deleted" />
              </div>
              <div v-else>
                <Button v-has-ability-to="'reimbursement.view'" icon="pi pi-eye" class="p-button-text p-button-sm" @click="openDetailDialog(data.id)" />
                <Button
                  v-has-ability-to="'reimbursement.delete'" icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger"
                  @click="deleteItem(data.id)"
                />
              </div>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Submission Dialog -->
    <Dialog v-model:visible="isDialogVisible" header="Submit Reimbursement" modal>
      <div class="mb-4 flex gap-4 items-center">
        <label class="font-semibold w-24">Title</label>
        <InputText v-model="submissionForm.title" class="flex-auto" />
      </div>
      <div class="mb-4 flex gap-4 items-center">
        <label class="font-semibold w-24">Description</label>
        <Textarea v-model="submissionForm.description" class="flex-auto" auto-resize />
      </div>
      <div class="mb-4 flex gap-4 items-center">
        <label class="font-semibold w-24">Amount</label>
        <InputNumber v-model="submissionForm.amount" class="flex-auto" mode="currency" currency="IDR" locale="id-ID" />
      </div>
      <div class="mb-4 flex gap-4 items-center">
        <label class="font-semibold w-24">Category</label>
        <Dropdown
          v-model="submissionForm.category_id" class="flex-auto" :options="categoryStore.categories" option-label="name"
          option-value="id" placeholder="Select a category"
        />
      </div>
      <div class="mb-4 flex gap-4 items-center">
        <label class="font-semibold w-24">Attachment</label>
        <input type="file" class="p-inputtext-sm" @change="e => submissionForm.attachment = e.target.files[0]">
      </div>

      <div class="mt-4 flex gap-2 justify-end">
        <Button label="Cancel" class="p-button-text" @click="isDialogVisible = false" />
        <Button label="Submit" :loading="isSubmitting" @click="submitReimbursement" />
      </div>
    </Dialog>

    <Dialog
      v-model:visible="isDetailDialogVisible"
      header="Detail Reimbursement"
      modal
      :style="{ width: '50rem' }"
      class="mx-2"
    >
      <div v-if="!reimbursementStore.isLoading && reimbursementStore.item" class="space-y-4">
        <!-- Submitted by -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Submitted by</label>
          <span>{{ reimbursementStore.item.user?.name ?? '-' }}</span>
        </div>

        <!-- Title -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Title</label>
          <span>{{ reimbursementStore.item.title }}</span>
        </div>

        <!-- Description -->
        <div class="flex flex-col">
          <label class="font-semibold pt-1 w-32">Description</label>
          <span class="whitespace-pre-wrap">{{ reimbursementStore.item.description }}</span>
        </div>

        <!-- Amount -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Amount</label>
          <span>{{ reimbursementStore.item.amount }}</span>
        </div>

        <!-- Category -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Category ID</label>
          <span>{{ reimbursementStore.item.category_id }}</span>
        </div>

        <!-- Status -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Status</label>
          <span>{{ reimbursementStore.item.status }}</span>
        </div>

        <!-- Approval Reason -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Approval Reason</label>
          <span>{{ reimbursementStore.item.approval_reason ?? '-' }}</span>
        </div>

        <!-- Reviewed By -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Reviewed By</label>
          <span>{{ reimbursementStore.item.reviewed_by?.name ?? '-' }}</span>
        </div>

        <!-- Attachment -->
        <div class="flex flex-col">
          <label class="font-semibold w-32">Attachment</label>
          <div>
            <Button
              v-if="reimbursementStore.item.attachment_url"
              variant="text"
              severity="primary"
              @click="openAttachmentDetailDialog(reimbursementStore.item.attachment_url)"
            >
              View Attachment
            </Button>
            <span v-else class="text-gray-500">No attachment</span>
          </div>
        </div>

        <hr>

        <!-- Approval actions / status note -->
        <div class="space-y-2">
          <Textarea
            v-if="reimbursementStore.item.status === 'pending'"
            v-model="approvalReason"
            placeholder="Type any reason (optional)"
            class="w-full"
            auto-resize
          />

          <div class="flex gap-2 items-center">
            <template v-if="reimbursementStore.item.status === 'pending'">
              <Button
                v-if="authStore"
                label="Approve"
                icon="pi pi-check-circle"
                severity="success"
                @click="approve(reimbursementStore.item.id)"
              />
              <Button
                v-if="authStore"
                label="Reject"
                icon="pi pi-times-circle"
                severity="danger"
                @click="reject(reimbursementStore.item.id)"
              />
            </template>
            <template v-else>
              <span
                class="italic"
                :class="{
                  'text-green-600': reimbursementStore.item.status === 'approved',
                  'text-red-600': reimbursementStore.item.status === 'rejected',
                }"
              >
                {{ reimbursementStore.item.status === 'approved'
                  ? 'Already approved'
                  : 'Already rejected' }}
              </span>
            </template>
          </div>
        </div>
      </div>
    </Dialog>

    <Dialog v-model:visible="isImageDialogVisible" modal header="Attachment" class="w-auto">
      <Image v-if="selectedImageUrl" :src="selectedImageUrl" alt="Image" width="250" />
    </Dialog>
  </div>
</template>
