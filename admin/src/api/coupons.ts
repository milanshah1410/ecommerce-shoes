import client from './client'
import type { Coupon, PaginatedResponse, ListParams } from '@/types'

export const couponsApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Coupon>>('/coupons', { params }),

  show: (id: number) =>
    client.get<Coupon>(`/coupons/${id}`),

  create: (payload: Partial<Coupon>) =>
    client.post<Coupon>('/coupons', payload),

  update: (id: number, payload: Partial<Coupon>) =>
    client.put<Coupon>(`/coupons/${id}`, payload),

  delete: (id: number) =>
    client.delete(`/coupons/${id}`),
}
