import client from './client'
import type { User, Order, PaginatedResponse, ListParams } from '@/types'

export const customersApi = {
  list: (params?: ListParams) =>
    client.get<PaginatedResponse<User>>('/customers', { params }),

  show: (id: number) =>
    client.get<User>(`/customers/${id}`),

  orders: (id: number, params?: ListParams) =>
    client.get<PaginatedResponse<Order>>(`/customers/${id}/orders`, { params }),

  toggleStatus: (id: number) =>
    client.post(`/customers/${id}/toggle-status`),
}
