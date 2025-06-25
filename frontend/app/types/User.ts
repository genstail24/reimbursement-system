// types/User.ts
export interface User {
  id: number
  name: string
  email: string
  roles: string[]
  created_at: string
  updated_at: string
}

export interface CreateUserPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  roles: string[]
}

export interface UpdateUserPayload {
  id: number
  name?: string
  email?: string
}

export interface SyncRolesPayload {
  roles: string[]
}
