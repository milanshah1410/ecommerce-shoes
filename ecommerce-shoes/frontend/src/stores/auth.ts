import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi, type LoginPayload, type RegisterPayload } from '@/api/auth'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.is_admin ?? false)

  async function login(payload: LoginPayload) {
    const { data } = await authApi.login(payload)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)
  }

  async function register(payload: RegisterPayload) {
    const { data } = await authApi.register(payload)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)
  }

  async function logout() {
    try { await authApi.logout() } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await authApi.me()
      user.value = data
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
    }
  }

  return { user, token, isLoggedIn, isAdmin, login, register, logout, fetchUser }
})
