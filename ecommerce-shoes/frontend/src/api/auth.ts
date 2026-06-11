import client from './client'
import type { User } from '@/types'

export interface LoginPayload { email: string; password: string }
export interface RegisterPayload { first_name: string; last_name?: string; email: string; mobile?: string; password: string; password_confirmation: string }
export interface AuthResponse { user: User; token: string }

export const authApi = {
  login: (data: LoginPayload) => client.post<AuthResponse>('/login', data),
  register: (data: RegisterPayload) => client.post<AuthResponse>('/register', data),
  logout: () => client.post('/logout'),
  me: () => client.get<User>('/me'),
  forgotPassword: (email: string) => client.post('/forgot-password', { email }),
  resetPassword: (data: { email: string; token: string; password: string; password_confirmation: string }) =>
    client.post('/reset-password', data),
}
