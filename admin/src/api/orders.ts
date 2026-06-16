import client from './client'
import type { Order, PaginatedResponse, ListParams, OrderStatus } from '@/types'

export const ordersApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<Order>>('/orders', { params }),

  show: (id: number) =>
    client.get<Order>(`/orders/${id}`),

  updateStatus: (id: number, status: OrderStatus, note?: string) =>
    client.post(`/orders/${id}/status`, { status, note }),

  cancel: (id: number, reason?: string) =>
    client.post(`/orders/${id}/cancel`, { reason }),

  refund: (id: number, amount: number, reason?: string) =>
    client.post(`/orders/${id}/refund`, { amount, reason }),
}
