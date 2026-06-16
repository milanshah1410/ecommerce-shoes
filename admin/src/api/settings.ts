import client from './client'
import type { AppSettings } from '@/types'

export const settingsApi = {
  get: () =>
    client.get<AppSettings>('/settings'),

  update: (payload: FormData) =>
    client.post<AppSettings>('/settings', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
}
