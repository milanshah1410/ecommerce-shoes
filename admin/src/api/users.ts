import client from './client'
import type { User, PaginatedResponse, ListParams } from '@/types'

export const usersApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<User>>('/users', { params }),

  show: (id: number) =>
    client.get<User>(`/users/${id}`),

  create: (payload: Partial<User> & { password: string }) =>
    client.post<User>('/users', payload),

  update: (id: number, payload: Partial<User> & { password?: string }) =>
    client.put<User>(`/users/${id}`, payload),

  delete: (id: number) =>
    client.delete(`/users/${id}`),

  toggleStatus: (id: number) =>
    client.post(`/users/${id}/toggle-status`),
}
