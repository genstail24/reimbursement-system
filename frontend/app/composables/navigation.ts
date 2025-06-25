import { useAuth } from '#imports'
import { computed, h, markRaw } from 'vue'

export function useNavigationMenu() {
  const separator = h('hr')
  const { user } = useAuth()

  const menu = computed(() => {
    const permissions: string[] = user?.value?.permissions || []

    return [
      {
        href: '/',
        title: 'Home',
        icon: 'pi pi-fw pi-home',
      },
      {
        component: markRaw(separator),
      },
      {
        title: 'Reimbursement',
        icon: 'pi pi-wallet',
        href: '/reimbursements',
        permission: ['reimbursement.view'],
      },
      {
        title: 'Category',
        icon: 'pi pi-tags',
        href: '/categories',
        permission: ['category.create'],
      },
      {
        title: 'Activity Log',
        icon: 'pi pi-history',
        href: '/activity-logs',
        permission: ['activity-log.view'],
      },
      {
        title: 'User',
        icon: 'pi pi-user',
        href: '/users',
        permission: ['user.view'],
      },
      {
        title: 'Role',
        icon: 'pi pi-shield',
        href: '/roles',
        permission: ['role.view'],
      },
      // {
      //   title: 'Permission',
      //   icon: 'pi pi-lock',
      //   href: '/permissions',
      //   permission: ['permission.view'],
      // },
    ].filter((item) => {
      if (!item.permission)
        return true
      return item.permission.some((p: string) => permissions.includes(p))
    })
  })

  return { menu }
}
