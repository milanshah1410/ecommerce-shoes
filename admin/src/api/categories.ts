import client from './client'
import type { Category, PaginatedResponse, ListParams } from '@/types'

export const categoriesApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Category>>('/categories', { params }),

  all: () =>
    client.get<Category[]>('/categories/all'),

  show: (id: number) =>
    client.get<Category>(`/categories/${id}`),

  create: (payload: FormData) =>
    client.post<Category>('/categories', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  update: (id: number, payload: FormData) =>
    client.post<Category>(`/categories/${id}`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' },
    }),

  delete: (id: number) =>
    client.delete(`/categories/${id}`),
}
