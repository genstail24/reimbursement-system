<script setup lang="ts">
import type {
  CreateRolePayload,
  Role,
  SyncPermissionsPayload,
  UpdateRolePayload,
} from '~/types/Role'
import { FilterMatchMode } from '@primevue/core/api'

import { onMounted, reactive, ref, watch } from 'vue'
import { useMessages } from '~/composables/messages'
import { useRoleStore } from '~/stores/role'

const store = useRoleStore()
const { showErrorMessage, showSuccessMessage } = useMessages()
const { confirmDelete } = useConfirmation()

const filters = ref({
  global: { value: null as string | null, matchMode: FilterMatchMode.CONTAINS },
})

const isDialogVisible = ref(false)
const isPermsDialogVisible = ref(false)
const isEditing = ref(false)

const form = reactive<
  Partial<CreateRolePayload & UpdateRolePayload & SyncPermissionsPayload>
>({
  id: null,
  name: '',
  guard_name: '',
  permissions: [] as string[],
})

const allPermissions = ref<string[]>([])
onMounted(async () => {
  await store.fetchAll()
  const res = await useNuxtApp().$auth.$fetch<{ data: { name: string }[] }>(
    '/permissions',
  )
  allPermissions.value = res.data.map(p => p.name)
})

watch(
  () => store.isFailed,
  f => f && showErrorMessage(store.getMessage ?? 'Error'),
)
watch(
  () => store.isSuccess,
  s => s && showSuccessMessage(store.getMessage ?? 'Success'),
)

function openDialog(r?: Role) {
  isEditing.value = !!r
  if (r) {
    form.id = r.id
    form.name = r.name
    form.guard_name = r.guard_name
  }
  else {
    form.id = null
    form.name = ''
    form.guard_name = ''
  }
  store.resetStatus()
  isDialogVisible.value = true
}
function closeDialog() {
  isDialogVisible.value = false
}

async function openPermsDialog(r: Role) {
  await store.fetchById(r.id)
  form.id = r.id
  form.permissions = [...(store.item?.permissions || [])]
  store.resetStatus()
  isPermsDialogVisible.value = true
}
function closePermsDialog() {
  isPermsDialogVisible.value = false
}

async function removeRole(id: number) {
  confirmDelete(id, async () => {
    await store.delete(id)
    if (!store.error)
      await store.fetchAll()
  })
}

async function submitRole() {
  if (isEditing.value && form.id != null) {
    await store.update({
      id: form.id,
      name: form.name!,
      guard_name: form.guard_name!,
    } as UpdateRolePayload)
  }
  else {
    await store.create({
      name: form.name!,
      guard_name: form.guard_name!,
    } as CreateRolePayload)
  }
  if (!store.isFailed) {
    closeDialog()
    await store.fetchAll()
  }
}

async function submitPerms() {
  if (form.id != null) {
    await store.syncPermissions(form.id, {
      permissions: form.permissions!,
    } as SyncPermissionsPayload)
    if (!store.isFailed) {
      closePermsDialog()
      await store.fetchAll()
    }
  }
}
</script>

<template>
  <div class="card">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl">
        Roles Management
      </h2>
      <Button label="New Role" icon="pi pi-plus" @click="openDialog()" />
    </div>

    <DataTable
      :value="store.items"
      :filters="filters"
      data-key="id"
      :paginator="true"
      :rows="10"
      striped-rows
      :global-filter-fields="['name', 'permissions']"
      :loading="store.isLoading"
      class="p-datatable-sm"
    >
      <Column field="id" header="ID" style="width: 5%" />
      <Column field="name" header="Name" sortable />
      <Column header="Permissions">
        <template #body="{ data }">
          <div class="flex flex-wrap gap-1">
            <Tag
              v-for="perm in data.permissions"
              :key="perm"
              :value="perm"
              class="p-mr-1"
            />
          </div>
        </template>
      </Column>
      <Column header="Action" style="width: 15%">
        <template #body="{ data }">
          <Button
            icon="pi pi-pencil"
            class="p-button-text p-button-sm"
            @click="openDialog(data)"
          />
          <Button
            icon="pi pi-key"
            class="p-button-text p-button-sm"
            @click="openPermsDialog(data)"
          />
          <Button
            icon="pi pi-trash"
            class="p-button-text p-button-sm p-button-danger"
            @click="removeRole(data.id)"
          />
        </template>
      </Column>
    </DataTable>

    <!-- Create / Edit Role Dialog -->
    <Dialog
      v-model:visible="isDialogVisible"
      :header="isEditing ? 'Edit Role' : 'New Role'"
      modal
      class="w-1/3"
    >
      <div class="p-fluid grid">
        <div class="field col-12">
          <label for="name">Name</label>
          <InputText id="name" v-model="form.name" />
        </div>
        <div class="field col-12">
          <label for="guard">Guard Name</label>
          <InputText id="guard" v-model="form.guard_name" />
        </div>
        <div class="col-12 mt-4 flex gap-2 justify-end">
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="p-button-text"
            @click="closeDialog()"
          />
          <Button
            label="Save"
            icon="pi pi-check"
            :loading="store.isLoading"
            @click="submitRole()"
          />
        </div>
      </div>
    </Dialog>

    <!-- Assign Permissions Dialog -->
    <Dialog
      v-model:visible="isPermsDialogVisible"
      header="Assign Permissions"
      modal
      class="w-1/3"
    >
      <div class="p-fluid grid">
        <div class="field col-12">
          <label for="perms">Permissions</label>
          <MultiSelect
            id="perms"
            v-model="form.permissions"
            :options="allPermissions"
            placeholder="Select permissions"
            display="chip"
          />
        </div>
        <div class="col-12 mt-4 flex gap-2 justify-end">
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="p-button-text"
            @click="closePermsDialog()"
          />
          <Button
            label="Sync"
            icon="pi pi-check"
            :loading="store.isLoading"
            @click="submitPerms()"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>
