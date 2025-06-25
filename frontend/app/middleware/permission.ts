import { defineNuxtRouteMiddleware, useAuth } from '#imports'

export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()
  const { user } = auth
  const requiredPermission = to.meta.permission as string | string[]
  const permissions = user?.value?.permissions || []

  const hasAccess = Array.isArray(requiredPermission)
    ? requiredPermission.some(perm => permissions.includes(perm))
    : permissions.includes(requiredPermission)

  if (requiredPermission && !hasAccess) {
    return navigateTo('/unauthorized')
  }
})
