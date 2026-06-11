<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const navLinks = [
  { label: 'Dashboard',   path: '/admin/dashboard' },
  { label: 'Products',    path: '/admin/products' },
  { label: 'Categories',  path: '/admin/categories' },
  { label: 'Brands',      path: '/admin/brands' },
  { label: 'Orders',      path: '/admin/orders' },
  { label: 'Customers',   path: '/admin/customers' },
  { label: 'Coupons',     path: '/admin/coupons' },
  { label: 'Reviews',     path: '/admin/reviews' },
]

const pageTitle = computed(() => {
  const matched = [...route.matched].reverse().find(r => r.meta?.title)
  if (matched?.meta?.title) return matched.meta.title as string
  const current = navLinks.find(l => route.path.startsWith(l.path))
  return current?.label ?? 'Admin'
})

const userName = computed(() => authStore.user?.full_name ?? authStore.user?.email ?? 'Admin')
const userInitial = computed(() => userName.value.charAt(0).toUpperCase())

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="flex h-screen bg-gray-100 overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 flex flex-col flex-shrink-0">
      <!-- Logo -->
      <div class="h-16 flex items-center px-6 border-b border-gray-700">
        <span class="text-white text-lg font-bold tracking-wide">SoleStyle Admin</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-1 px-3">
          <li v-for="link in navLinks" :key="link.path">
            <router-link
              :to="link.path"
              class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150"
              :class="
                route.path.startsWith(link.path)
                  ? 'bg-indigo-600 text-white'
                  : 'text-gray-400 hover:bg-gray-800 hover:text-white'
              "
            >
              {{ link.label }}
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- Sidebar Logout -->
      <div class="px-3 py-4 border-t border-gray-700">
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors duration-150"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top bar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
        <!-- Breadcrumb / Page title -->
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <span class="text-gray-400">Admin</span>
          <span class="text-gray-300">/</span>
          <span class="font-medium text-gray-800">{{ pageTitle }}</span>
        </div>

        <!-- User info + logout -->
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-semibold select-none">
              {{ userInitial }}
            </div>
            <span class="text-sm font-medium text-gray-700">{{ userName }}</span>
          </div>
          <button
            @click="handleLogout"
            class="text-sm text-gray-500 hover:text-gray-800 transition-colors duration-150"
          >
            Logout
          </button>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>
