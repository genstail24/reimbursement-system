<script setup lang="ts">
const router = useRouter()
definePageMeta({
  auth: false,
  layout: false,
})
</script>

<template>
  <div class="card">
    <div class="col-12 mt-10 text-center">
      <img src="/nuxt-logo.svg" alt="Nuxt logo" class="mb-5" style="width:81px; height:60px;">
    </div>
    <main class="text-8xl text-teal-700 px-4 py-10 text-center">
      <div>
        <p class="text-8xl text-red-600">
          <span class="i-codicon-error inline-block" />
        </p>
      </div>
    </main>
    <div style="alignment: center">
      <h1 class="text-2xl text-5xl font-bold text-center">
        403 Unauthorized ...
      </h1>
      <div class="col-12 mt-5 text-center">
        <Button icon="pi pi-arrow-left" label="Back to home" @click="router.push('/')" />
      </div>
    </div>
  </div>
</template>
