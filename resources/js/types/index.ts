export * from './auth';
export * from './navigation';
export * from './ui';

export interface TaskStatus {
  id: number
  title: string
  description: string | null
  background_colour: string
  text_colour: string
  meta: Record<string, unknown> | null
  created_by: number | null
  updated_by: number | null
  deleted_by: number | null
  restored_by: number | null
  restored_at: string | null
  created_at: string
  updated_at: string
  deleted_at: string | null
  creator?: { name: string }
  updater?: { name: string }
  deleter?: { name: string }
  restorer?: { name: string }
}