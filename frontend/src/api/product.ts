import client from './client'
import type { Product, Review, Paginated } from '@/types'

export interface ProductFilters {
  category?: string
  brand?: string
  gender?: string
  min_price?: number
  max_price?: number
  rating?: number
  on_sale?: boolean
  size?: string
  color?: string
  search?: string
  sort?: string
  page?: number
  per_page?: number
  featured?: boolean
  trending?: boolean
  new?: boolean
  best_seller?: boolean
}

export const productApi = {
  list: (params: ProductFilters = {}) => client.get<Paginated<Product>>('/products', { params }),
  show: (slug: string) =>
    client.get<{ data: Product; related: Product[]; reviews: Review[] }>(`/products/${slug}`),
  addReview: (productId: number, data: { rating: number; title?: string; review?: string }) =>
    client.post(`/products/${productId}/reviews`, data),
}
