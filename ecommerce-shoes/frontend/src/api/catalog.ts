import client from './client'
import type { Category, Brand } from '@/types'

export const catalogApi = {
  categories: () => client.get<{ data: Category[] }>('/categories'),
  brands: () => client.get<{ data: Brand[] }>('/brands'),
  filters: () => client.get('/filters'),
  home: () => client.get('/home'),
}
