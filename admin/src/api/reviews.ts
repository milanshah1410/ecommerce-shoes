import client from './client'
import type { Review, PaginatedResponse, ListParams } from '@/types'

export const reviewsApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Review>>('/reviews', { params }),

  approve: (id: number) =>
    client.post(`/reviews/${id}/approve`),

  reject: (id: number) =>
    client.post(`/reviews/${id}/reject`),

  delete: (id: number) =>
    client.delete(`/reviews/${id}`),
}
