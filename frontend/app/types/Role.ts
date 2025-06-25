export interface Role {
  id: number
  name: string
  guard_name: string
  permissions: string[]
  created_at: string
  updated_at: string
}

export interface CreateRolePayload {
  name: string
  guard_name: string
}

export interface UpdateRolePayload {
  id: number
  name?: string
  guard_name?: string
}

export interface SyncPermissionsPayload {
  permissions: string[]
}
