<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  auth: 'guest',
})

const { showSuccessMessage, showErrorMessage } = useMessages()
const { addElement } = useFormKitSchema()
const { t } = useI18n()
// const router = useRouter()
const horizontal = ref(false)
const isLoading = ref(false)
const { credentials, login, errorMsg } = useLogin({
  credentials: {
    email: '',
    password: '',
  },
})

// Schema for login form
const loginSchema = reactive([
  addElement('h3', t('email')),
  {
    $formkit: 'primeInputText',
    name: 'email',
    validation: 'required|email',
    placeholder: 'you@example.com',
    iconPrefix: 'pi pi-envelope',
  },
  addElement('h3', t('password')),
  {
    $formkit: 'primePassword',
    name: 'password',
    validation: 'required',
    placeholder: '••••••••',
    iconPrefix: 'pi pi-lock',
  },
])

async function submitHandler() {
  isLoading.value = true
  try {
    await login()
    if (!errorMsg.value) {
      showSuccessMessage(t('welcome', 'Welcome'), t('login_success', 'Successfuly login'))
    }
    else {
      showErrorMessage(errorMsg.value, '')
    }
  }
  catch (error: any) {
    console.error(error)
    showErrorMessage(error?.message, 'Something wrong')
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div
    class="p-2 bg-gray-100 flex min-h-screen items-center justify-center dark:bg-gray-900"
  >
    <div
      class="p-4 rounded-2xl bg-white max-w-md w-full shadow-lg md:p-8 dark:bg-gray-800"
    >
      <h2 class="text-2xl font-semibold mb-6 text-center">
        {{ t("login", "Reimbursement App - Login") }}
      </h2>
      <FormKitDataEdit
        :data="credentials"
        :schema="loginSchema"
        :form-class="horizontal ? 'form-horizontal' : ''"
        :submit-label="isLoading ? 'Loading...' : t('login', 'Login')"
        :submit-icon="isLoading ? 'pi pi-spin pi-spinner' : ''"
        submit-class="w-full"
        @data-saved="submitHandler"
      />
    </div>
  </div>
</template>

<style scoped>
.form-horizontal .formkit {
  @apply flex flex-col;
}
</style>
