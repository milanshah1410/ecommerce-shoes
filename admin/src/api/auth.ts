import axios from 'axios'
import type { AuthResponse, User } from '@/types'

const publicClient = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

export const authApi = {
  login: (payload: { email: string; password: string }) =>
    publicClient.post<AuthResponse>('/login', payload),

  me: () => {
    const token = localStorage.getItem('admin_token')
    return publicClient.get<User>('/me', {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  logout: () => {
    const token = localStorage.getItem('admin_token')
    return publicClient.post('/logout', {}, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}
