<script setup lang="ts">
definePageMeta({
  layout: false,
  auth: false,
})

const { doLogout, errorMsg } = useLogout()
const { showErrorMessage, showSuccessMessage } = useMessages()
const { t } = useI18n()
const isLoading = ref(true)
// const router = useRouter()

async function logout() {
  isLoading.value = true
  try {
    await doLogout()
    showSuccessMessage(
      t('logout_success_title', 'Successfully logged out'),
      t('logout_success_message', 'You have been safely signed out.'),
    )
    // router.replace({ name: 'login' })
  }
  catch (e: any) {
    if (e.message)
      showErrorMessage(e?.message || errorMsg || 'Error')
  }
  finally {
    isLoading.value = false
  }
}

onMounted(logout)
</script>

<template>
  <div class="bg-gray-100 flex h-screen items-center justify-center">
    <div v-if="isLoading" class="text-center">
      <ProgressSpinner />
      <p>Trying to logout…</p>
    </div>
  </div>
</template>

<style scoped>
/* Optional: styling full-screen background */
.h-screen {
  height: 100vh;
}
</style>
