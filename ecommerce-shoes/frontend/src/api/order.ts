import client from './client'
import type { Order, Paginated } from '@/types'

export interface CheckoutPayload {
  address_id?: number
  shipping: {
    full_name: string
    mobile: string
    address_line: string
    city: string
    state: string
    country: string
    pincode: string
  }
  payment_method: 'cod' | 'razorpay' | 'stripe'
  coupon_code?: string
}

export const orderApi = {
  list: (page = 1) => client.get<Paginated<Order>>('/orders', { params: { page } }),
  show: (id: number) => client.get<{ data: Order }>(`/orders/${id}`),
  create: (data: CheckoutPayload) => client.post<{ data: Order }>('/orders', data),
  cancel: (id: number) => client.post(`/orders/${id}/cancel`),
}
