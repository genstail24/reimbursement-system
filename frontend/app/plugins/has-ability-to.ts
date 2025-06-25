export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('has-ability-to', {
    mounted(el, binding) {
      const auth = useAuth()
      const { user } = auth
      const userPermissions = user?.value?.permissions || []

      const requiredPermissions = Array.isArray(binding.value)
        ? binding.value
        : [binding.value]

      const hasPermission = requiredPermissions.some(perm =>
        userPermissions.includes(perm),
      )

      if (!hasPermission) {
        el.style.display = 'none'
      }
    },
  })
})
