import client from './client'
import type { Product, PaginatedResponse, ListParams } from '@/types'

export const productsApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Product>>('/products', { params }),

  show: (id: number) =>
    client.get<Product>(`/products/${id}`),

  create: (payload: FormData) =>
    client.post<Product>('/products', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  update: (id: number, payload: FormData) =>
    client.post<Product>(`/products/${id}`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' },
    }),

  delete: (id: number) =>
    client.delete(`/products/${id}`),

  deleteImage: (productId: number, imageId: number) =>
    client.delete(`/products/${productId}/images/${imageId}`),
}
