import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('admin_token'))

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() =>
    user.value ? ['super_admin', 'admin', 'manager'].includes(user.value.role) : false,
  )
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  async function login(email: string, password: string) {
    const { data } = await authApi.login({ email, password })
    if (!['super_admin', 'admin', 'manager', 'inventory_manager', 'sales_manager', 'customer_support'].includes(data.user.role)) {
      throw new Error('You do not have admin access.')
    }
    token.value = data.token
    user.value = data.user
    localStorage.setItem('admin_token', data.token)
  }

  async function logout() {
    try { await authApi.logout() } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('admin_token')
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await authApi.me()
      user.value = data
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('admin_token')
    }
  }

  function can(permission: string): boolean {
    if (!user.value) return false
    if (user.value.role === 'super_admin') return true
    // Role-based permission map
    const rolePermissions: Record<string, string[]> = {
      admin: ['users', 'products', 'categories', 'brands', 'inventory', 'orders', 'customers', 'coupons', 'reviews', 'reports', 'settings'],
      manager: ['products', 'categories', 'brands', 'inventory', 'orders', 'customers', 'reviews', 'reports'],
      inventory_manager: ['inventory', 'products'],
      sales_manager: ['orders', 'reports', 'customers', 'coupons'],
      customer_support: ['orders', 'customers', 'reviews'],
    }
    const modules = rolePermissions[user.value.role] ?? []
    return modules.some((mod) => permission.startsWith(mod))
  }

  return { user, token, isLoggedIn, isAdmin, isSuperAdmin, login, logout, fetchUser, can }
})
