// types/ActivityLog.ts
export interface ActivityLog {
  id: number
  log_name: string
  description: string
  subject_type: string
  event: string | null
  subject_id: number | null
  causer_type: string | null
  causer_id: number | null
  properties: Record<string, any>
  batch_uuid: string | null
  created_at: string
  updated_at: string
}
