import client from './client'
import type { Brand, PaginatedResponse, ListParams } from '@/types'

export const brandsApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Brand>>('/brands', { params }),

  all: () =>
    client.get<Brand[]>('/brands/all'),

  show: (id: number) =>
    client.get<Brand>(`/brands/${id}`),

  create: (payload: FormData) =>
    client.post<Brand>('/brands', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  update: (id: number, payload: FormData) =>
    client.post<Brand>(`/brands/${id}`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' },
    }),

  delete: (id: number) =>
    client.delete(`/brands/${id}`),
}
