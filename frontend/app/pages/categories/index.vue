<script setup lang="ts">
import type { Category } from '@/types/Category'
import { FilterMatchMode } from '@primevue/core/api'

definePageMeta({
  auth: true,
  middleware: [
    'permission',
  ],
  permission: [
    'category.view',
  ],
})

const store = useCategoryStore()
const filters = ref({ global: { value: null, matchMode: FilterMatchMode.CONTAINS }, name: { value: null, matchMode: FilterMatchMode.CONTAINS } })
const isDialogVisible = ref(false)
const isEditing = ref(false)

const formModel = reactive({ id: null as number | null, name: '', limit_per_month: null as number | null })
const { t } = useI18n()
const { showSuccessMessage, showErrorMessage } = useMessages()
const { confirmDelete } = useConfirmation()
// const { addElement } = useFormKitSchema()

const formSchema = reactive([
  { label: 'Name', $formkit: 'primeInputText', name: 'name', validation: 'required|string', placeholder: t('enterValue', 'Please enter a value…') },
  { label: 'Limit per month', $formkit: 'primeInputNumber', mode: 'currency', currency: 'IDR', name: 'limit_per_month', validation: 'required|integer', placeholder: t('enterValue', 'Please enter a value…'), class: 'w-full' },
])

const dialogTitle = computed(() => isEditing.value ? t('editCategory', 'Edit Category') : t('newCategory', 'New Category'))

onMounted(() => store.fetchAll())

watch(() => store.isFailed, failed => failed && store.getMessage && showErrorMessage('Failed', store?.getMessage))
watch(() => store.isSuccess, success => success && store.getMessage && showSuccessMessage('Success', store?.getMessage))

function openDialog(cat?: typeof formModel) {
  isEditing.value = !!cat
  if (cat)
    Object.assign(formModel, cat)
  else Object.assign(formModel, { id: null, name: '', limit_per_month: null })
  isDialogVisible.value = true
}

async function submitForm() {
  if (isEditing.value && formModel.id)
    await store.update(formModel as Category)
  else await store.store(formModel as Omit<typeof formModel, 'id'>)

  if (!store.error) {
    isDialogVisible.value = false
    await store.fetchAll()
  }
}

function removeCategory(id: number) {
  confirmDelete(id, async () => {
    await store.delete(id)
    if (!store.error)
      await store.fetchAll()
  })
}
</script>

<template>
  <div class="card">
    <div class="mb-4 flex justify-between">
      <span class="text-xl" v-text="$t('category', 'Category')" />
      <Button v-has-ability-to="'category.create'" :label="$t('add', 'Add')" icon="pi pi-plus" @click="openDialog()" />
    </div>

    <DataTable
      v-model:filters="filters"
      :value="store.categories"
      data-key="id"
      :paginator
      :rows="10"
      striped-rows
      :global-filter-fields="['name']"
    >
      <template #header>
        <div class="flex justify-between">
          <InputText
            v-model="filters.global.value"
            :placeholder="$t('search', 'Search…')"
            class="p-inputtext-sm w-2/4"
          />
        </div>
      </template>

      <Column field="name" :header="$t('name', 'Name')" sortable />
      <Column field="limit_per_month" :header="$t('limitPerMonth', 'Limit per month')" sortable />
      <Column :header="$t('action', 'Action')">
        <template #body="{ data }">
          <div class="flex gap-2 items-center">
            <Button
              v-has-ability-to="'category.update'"
              icon="pi pi-pencil"
              class="p-button-text p-button-sm"
              @click="openDialog(data)"
            />
            <Button
              v-has-ability-to="'category.delete'"
              icon="pi pi-trash"
              class="p-button-text p-button-sm p-button-danger"
              @click="removeCategory(data.id)"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="isDialogVisible" :header="dialogTitle" modal>
      <div class="p-fluid grid">
        <FormKitDataEdit
          :data="formModel"
          :schema="formSchema"
          :submit-label="$t('submit', 'Submit')"
          no-submit-button
          @data-saved="submitForm"
        />
      </div>
    </Dialog>
  </div>
</template>
