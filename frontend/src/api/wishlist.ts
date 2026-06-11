import client from './client'
import type { Product } from '@/types'

export const wishlistApi = {
  get: () => client.get<{ data: Product[] }>('/wishlist'),
  add: (productId: number) => client.post('/wishlist', { product_id: productId }),
  remove: (productId: number) => client.delete(`/wishlist/${productId}`),
  moveToCart: (productId: number) => client.post(`/wishlist/${productId}/move-to-cart`),
}
