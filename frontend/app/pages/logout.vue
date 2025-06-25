<script setup lang="ts">
definePageMeta({
  layout: false,
  auth: false,
})

const { doLogout, errorMsg } = useLogout()
const { showErrorMessage } = useMessages()
const pending = ref(true)
const router = useRouter()

async function logout() {
  pending.value = true
  try {
    await doLogout()
    router.replace({ name: 'login' })
  }
  catch (e: any) {
    if (e.message)
      showErrorMessage(e?.message || errorMsg || 'Error')
  }
  finally {
    pending.value = false
  }
}

onMounted(logout)
</script>

<template>
  <div class="bg-gray-100 flex h-screen items-center justify-center">
    <div v-if="pending" class="text-center">
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
