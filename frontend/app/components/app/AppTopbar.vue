<script setup lang="ts">
// const { doLogout } = useLogout()
</script>

<template>
  <nav>
    <Toolbar>
      <template #start />

      <template #end>
        <AppColorMode class="ml-6 mr-2" />
        <!-- <Button icon="pi pi-sign-out" severity="danger" @click="doLogout()" /> -->
        <nuxt-link :to="{ path: '/logout' }">
          <Button icon="pi pi-sign-out" severity="danger" />
        </nuxt-link>
      </template>
    </Toolbar>
  </nav>
</template>

<style scoped lang="scss">

</style>
