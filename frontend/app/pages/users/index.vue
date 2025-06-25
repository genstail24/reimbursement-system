<script setup lang="ts">
import type {
  CreateUserPayload,
  SyncRolesPayload,
  UpdateUserPayload,
  User,
} from '~/types/User'
import { FilterMatchMode } from '@primevue/core/api'

import { onMounted, reactive, ref, watch } from 'vue'
import { useMessages } from '~/composables/messages'
import { useUserStore } from '~/stores/user'

const store = useUserStore()
const { showErrorMessage, showSuccessMessage } = useMessages()
const { confirmDelete } = useConfirmation()

const filters = ref({
  global: { value: null as string | null, matchMode: FilterMatchMode.CONTAINS },
})

const isDialogVisible = ref(false)
const isEditing = ref(false)

const formModel = reactive<
  Partial<CreateUserPayload & UpdateUserPayload & SyncRolesPayload>
>({
  id: null,
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [] as string[],
})

const allRoles = ref<string[]>(['admin', 'manager', 'employee'])

onMounted(() => {
  store.fetchAll()
})

watch(
  () => store.isFailed,
  (failed) => {
    if (failed)
      showErrorMessage(store.getMessage ?? 'Operation failed')
  },
)
watch(
  () => store.isSuccess,
  (ok) => {
    if (ok)
      showSuccessMessage(store.getMessage ?? 'Success')
  },
)

function openDialog(user?: User) {
  isEditing.value = !!user
  if (user) {
    formModel.id = user.id
    formModel.name = user.name
    formModel.email = user.email
    formModel.password = ''
    formModel.password_confirmation = ''
    formModel.roles = [...user.roles]
  }
  else {
    formModel.id = null
    formModel.name = ''
    formModel.email = ''
    formModel.password = ''
    formModel.password_confirmation = ''
    formModel.roles = []
  }
  store.resetStatus()
  isDialogVisible.value = true
}

async function removeUser(id: number) {
  confirmDelete(id, async () => {
    await store.delete(id)
    if (!store.error)
      await store.fetchAll()
  })
}

async function submitForm() {
  if (isEditing.value && formModel.id != null) {
    await store.update({
      id: formModel.id,
      name: formModel.name!,
      email: formModel.email!,
    } as UpdateUserPayload)

    await store.syncRoles(formModel.id, {
      roles: formModel.roles,
    } as SyncRolesPayload)
  }
  else {
    await store.store({
      name: formModel.name!,
      email: formModel.email!,
      password: formModel.password!,
      password_confirmation: formModel.password_confirmation!,
      roles: formModel.roles!,
    } as CreateUserPayload)
  }

  if (!store.isFailed) {
    isDialogVisible.value = false
    await store.fetchAll()
  }
}
</script>

<template>
  <div>
    <div class="card">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl">
          Users
        </h2>
        <Button label="New User" icon="pi pi-plus" @click="openDialog()" />
      </div>

      <DataTable
        :value="store.users"
        :filters="filters"
        data-key="id"
        :paginator="true"
        :rows="10"
        striped-rows
        :global-filter-fields="['name', 'email', 'roles']"
        :loading="store.isLoading"
        class="p-datatable-sm"
      >
        <Column field="id" header="ID" style="width: 5%" />
        <Column field="name" header="Name" sortable />
        <Column field="email" header="Email" sortable />
        <Column header="Roles">
          <template #body="{ data }">
            <div class="flex flex-wrap gap-2">
              <Tag
                v-for="role in data.roles"
                :key="role"
                :value="role"
                class="p-mr-2"
              />
            </div>
          </template>
        </Column>
        <Column header="Action" style="width: 12%">
          <template #body="{ data }">
            <Button
              icon="pi pi-pencil"
              class="p-button-text p-button-sm"
              @click="openDialog(data)"
            />
            <Button
              icon="pi pi-trash"
              class="p-button-text p-button-sm p-button-danger"
              @click="removeUser(data.id)"
            />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Create / Edit Dialog -->
    <Dialog
      v-model:visible="isDialogVisible"
      :header="isEditing ? 'Edit User' : 'New User'"
      modal
      class="w-1/3"
      :closable="false"
    >
      <div class="p-fluid grid">
        <div class="col-12 field">
          <label for="name">Name</label>
          <InputText id="name" v-model="formModel.name" />
        </div>
        <div class="field col-12">
          <label for="email">Email</label>
          <InputText id="email" v-model="formModel.email" />
        </div>
        <!-- only show passwords on create -->
        <div v-if="!isEditing" class="field col-12">
          <label for="password">Password</label>
          <Password
            id="password"
            v-model="formModel.password"
            feedback="false"
          />
        </div>
        <div v-if="!isEditing" class="field col-12">
          <label for="password_confirmation">Confirm Password</label>
          <Password
            id="password_confirmation"
            v-model="formModel.password_confirmation"
            feedback="false"
          />
        </div>

        <div class="field col-12">
          <label for="roles">Roles</label>
          <MultiSelect
            id="roles"
            v-model="formModel.roles"
            :options="allRoles"
            placeholder="Select roles"
            display="chip"
          />
        </div>

        <div class="col-12 mt-4 flex gap-2 justify-end">
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="p-button-text"
            @click="isDialogVisible = false"
          />
          <Button
            label="Save"
            icon="pi pi-check"
            :loading="store.isLoading"
            @click="submitForm"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>
