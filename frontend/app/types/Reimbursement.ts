export interface Reimbursement {
  id: number
  title: string
  description: string
  amount: number
  category_id: number
  attachment: string | null
  attachment_url: string | null
  approval_reason: string | null
  reviewed_by: any | null
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  updated_at: string
}

export interface SubmissionReimbursementPayload {
  title: string
  description: string
  amount: number
  category_id: number
  attachment: File
}

export interface ApprovalReimbursementPayload {
  approval_reason?: string | null
  status: 'approved' | 'rejected'
}
