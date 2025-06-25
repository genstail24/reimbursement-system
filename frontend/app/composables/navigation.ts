export function useNavigationMenu() {
  const separator = h('hr')

  const menu = computed(() => {
    return [
      {
        href: '/',
        title: 'Home',
        icon: 'pi pi-fw pi-home',
      },
      {
        component: markRaw(separator),
      },
      { title: 'Reimbursement', icon: 'pi pi-wallet', href: '/reimbursements' },
      { title: 'Category', icon: 'pi pi-tags', href: '/categories' },
      { title: 'Activity Log', icon: 'pi pi-history', href: '/activity-logs' },
      { title: 'User', icon: 'pi pi-user', href: '/users' },
      { title: 'Role', icon: 'pi pi-shield', href: '/roles' },
    ]
  })

  return { menu }
}
