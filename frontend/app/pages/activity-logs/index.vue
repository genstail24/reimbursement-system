<script setup lang="ts">
import { FilterMatchMode } from '@primevue/core/api'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import { onMounted, ref, watch } from 'vue'
import { useMessages } from '~/composables/messages'
import { useActivityLogStore } from '~/stores/activity-log'

definePageMeta({
  auth: true,
  middleware: [
    'permission',
  ],
  permission: [
    'activity-log.view',
  ],
})

const store = useActivityLogStore()
const { showErrorMessage } = useMessages()

const filters = ref({
  global: { value: null as string | null, matchMode: FilterMatchMode.CONTAINS },
})

const isDialogVisible = ref(false)
const isSubjectModalVisible = ref(false)
const selectedSubject = ref<Record<string, any> | null>(null)
const isImageVisible = ref(false)
const imageUrl = ref<string | null>(null)

function viewImage(url: string) {
  imageUrl.value = url
  isImageVisible.value = true
}

function openSubjectModal(subject: Record<string, any>) {
  selectedSubject.value = subject
  isSubjectModalVisible.value = true
}

onMounted(() => {
  store.fetchAll()
})

watch(
  () => store.isFailed,
  (failed) => {
    if (failed)
      showErrorMessage(store.getMessage || 'Failed to load logs')
  },
)

// open detail dialog (will fetch single item)
async function openDialog(id: number) {
  isDialogVisible.value = true
  await store.fetchById(id)
}
</script>

<template>
  <div>
    <div class="card">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl">
          Activity Logs
        </h2>
        <InputText v-model="filters.global.value" placeholder="Search…" class="p-inputtext-sm w-1/3" />
      </div>

      <DataTable
        :value="store.items" :filters="filters" data-key="id" :paginator="true" :rows="10" striped-rows
        :global-filter-fields="['log_name', 'description', 'subject_type']" :loading="store.isLoading"
      >
        <Column field="id" header="ID" :sortable="true" style="width: 5%" />
        <Column field="description" header="Description" sortable />
        <Column field="causer.name" header="Causer" />
        <Column field="created_at" header="Created At" sortable />
        <Column header="Action" style="width: 10%">
          <template #body="{ data }">
            <Button icon="pi pi-eye" class="p-button-text p-button-sm" @click="openDialog(data.id)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="isDialogVisible" header="Activity Log Detail" modal :closable="true" class="w-1/2">
      <div v-if="store.isLoading" class="py-6 text-center">
        Loading…
      </div>

      <div v-else-if="store.item" class="space-y-2">
        <p><strong>ID:</strong> {{ store.item.id }}</p>
        <p><strong>Description:</strong> {{ store.item.description }}</p>
        <p>
          <strong>Subject:</strong> {{ store.item.subject_type }} #{{
            store.item.subject_id
          }}
          <Button variant="text" severity="primary" @click="openSubjectModal(store.item?.subject)">
            View Subject Detail
          </Button>
        </p>
        <p>
          <strong>Causer:</strong>
          {{ store.item?.causer?.name || '' }}
        </p>
        <p><strong>Created At:</strong> {{ store.item.created_at }}</p>
      </div>

      <div v-else class="text-gray-500 py-6 text-center">
        No data.
      </div>
    </Dialog>
    <Dialog v-model:visible="isSubjectModalVisible" header="Subject Detail" modal class="w-1/2">
      <div v-if="selectedSubject" class="space-y-2">
        <div v-for="(value, key) in selectedSubject" :key="key" class="py-1 border-b flex justify-between">
          <span class="font-medium capitalize">{{ key.replace(/_/g, ' ') }}</span>
          <span>
            <template
              v-if="typeof value === 'string' && value.startsWith('http') && /\.(jpg|jpeg|png|gif)$/i.test(value)"
            >
              <button class="text-blue-500 underline" @click="viewImage(value)">
                View Image
              </button>
            </template>
            <template v-else>
              {{ value ?? '-' }}
            </template>
          </span>
        </div>
      </div>

      <div v-else class="text-gray-500 py-4 text-center">
        No subject data
      </div>
    </Dialog>
    <Dialog v-model:visible="isImageVisible" modal header="Attachment" class="w-[500px]">
      <Image :src="imageUrl" alt="Attachment" class="h-auto w-full" />
    </Dialog>
  </div>
</template>
