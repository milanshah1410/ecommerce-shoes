import client from './client'
import type { Address } from '@/types'

export const profileApi = {
  dashboard: () => client.get('/profile/dashboard'),
  update: (data: { first_name: string; last_name?: string; mobile?: string }) =>
    client.put('/profile', data),
  changePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
    client.put('/profile/password', data),

  // addresses
  addresses: () => client.get<{ data: Address[] }>('/addresses'),
  addAddress: (data: Omit<Address, 'id'>) => client.post('/addresses', data),
  updateAddress: (id: number, data: Partial<Address>) => client.put(`/addresses/${id}`, data),
  deleteAddress: (id: number) => client.delete(`/addresses/${id}`),
}
