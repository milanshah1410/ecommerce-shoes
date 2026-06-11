import client from './client'
import type { CartItem, CartSummary } from '@/types'

export interface CartResponse { items: CartItem[]; summary: CartSummary }

export const cartApi = {
  get: () => client.get<CartResponse>('/cart'),
  add: (data: { product_id: number; variant_id?: number; quantity?: number }) =>
    client.post<CartResponse>('/cart', data),
  update: (cartId: number, quantity: number) =>
    client.put<CartResponse>(`/cart/${cartId}`, { quantity }),
  remove: (cartId: number) => client.delete<CartResponse>(`/cart/${cartId}`),
  applyCoupon: (code: string) => client.post<CartResponse>('/cart/apply-coupon', { code }),
}
