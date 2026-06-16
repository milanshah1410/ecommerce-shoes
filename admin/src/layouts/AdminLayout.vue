<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import {
  LayoutDashboard, Users, ShieldCheck, Package, Tags, Award,
  Warehouse, ShoppingCart, Ticket, UserCircle, Star, BarChart3,
  Settings, LogOut, Menu, X, Bell, Search, ChevronDown,
  Moon, Sun, ChevronRight, Box, Truck, FileText
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const settingsStore = useSettingsStore()

const sidebarOpen = ref(true)
const userMenuOpen = ref(false)
const searchQuery = ref('')
const mobileOpen = ref(false)

onMounted(() => {
  settingsStore.initDarkMode()
})

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

interface NavItem {
  label: string
  name?: string
  icon: unknown
  children?: { label: string; name: string }[]
}

interface NavGroup {
  title: string
  items: NavItem[]
}

const navGroups: NavGroup[] = [
  {
    title: 'Overview',
    items: [
      { label: 'Dashboard', name: 'dashboard', icon: LayoutDashboard },
    ],
  },
  {
    title: 'User Management',
    items: [
      { label: 'Users', name: 'users', icon: Users },
      { label: 'Roles', name: 'roles', icon: ShieldCheck },
    ],
  },
  {
    title: 'Catalog',
    items: [
      { label: 'Products', name: 'products', icon: Package },
      { label: 'Categories', name: 'categories', icon: Tags },
      { label: 'Brands', name: 'brands', icon: Award },
    ],
  },
  {
    title: 'Inventory',
    items: [
      { label: 'Stock Management', name: 'inventory', icon: Warehouse },
    ],
  },
  {
    title: 'Sales',
    items: [
      { label: 'Orders', name: 'orders', icon: ShoppingCart },
    ],
  },
  {
    title: 'Marketing',
    items: [
      { label: 'Coupons', name: 'coupons', icon: Ticket },
    ],
  },
  {
    title: 'Customers',
    items: [
      { label: 'Customer List', name: 'customers', icon: UserCircle },
      { label: 'Reviews', name: 'reviews', icon: Star },
    ],
  },
  {
    title: 'Analytics',
    items: [
      { label: 'Reports', name: 'reports', icon: BarChart3 },
    ],
  },
  {
    title: 'System',
    items: [
      { label: 'Settings', name: 'settings', icon: Settings },
    ],
  },
]

function isActive(name: string): boolean {
  return route.name === name || String(route.name).startsWith(name + '.')
}
</script>

<template>
  <div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">
    <!-- Mobile overlay -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-20 bg-black/50 lg:hidden"
      @click="mobileOpen = false"
    />

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-30 flex flex-col bg-slate-900 transition-all duration-300',
        sidebarOpen ? 'w-64' : 'w-16',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- Logo -->
      <div class="flex h-16 items-center justify-between px-4 border-b border-slate-700/50">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
            <Box class="w-5 h-5 text-white" />
          </div>
          <span
            v-if="sidebarOpen"
            class="font-bold text-white text-lg whitespace-nowrap"
          >SoleStore</span>
        </div>
        <button
          class="hidden lg:flex p-1 rounded text-slate-400 hover:text-white transition-colors"
          @click="sidebarOpen = !sidebarOpen"
        >
          <ChevronRight :class="['w-4 h-4 transition-transform', sidebarOpen ? 'rotate-180' : '']" />
        </button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2 scrollbar-thin scrollbar-thumb-slate-700">
        <template v-for="group in navGroups" :key="group.title">
          <div v-if="sidebarOpen" class="px-2 pt-4 pb-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ group.title }}</span>
          </div>
          <div v-else class="pt-3" />

          <RouterLink
            v-for="item in group.items"
            :key="item.name"
            :to="{ name: item.name }"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all',
              isActive(item.name!)
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white',
            ]"
            :title="!sidebarOpen ? item.label : undefined"
          >
            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.label }}</span>
          </RouterLink>
        </template>
      </nav>

      <!-- User info -->
      <div class="border-t border-slate-700/50 p-4">
        <div v-if="sidebarOpen" class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
            {{ auth.user?.full_name?.charAt(0)?.toUpperCase() ?? 'A' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ auth.user?.full_name }}</p>
            <p class="text-xs text-slate-400 capitalize truncate">{{ auth.user?.role?.replace('_', ' ') }}</p>
          </div>
          <button
            class="p-1 text-slate-400 hover:text-white transition-colors"
            title="Logout"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
        <button
          v-else
          class="w-full flex justify-center p-1 text-slate-400 hover:text-white transition-colors"
          title="Logout"
          @click="handleLogout"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </aside>

    <!-- Main area -->
    <div
      :class="[
        'flex flex-col flex-1 min-w-0 transition-all duration-300',
        sidebarOpen ? 'lg:ml-64' : 'lg:ml-16',
      ]"
    >
      <!-- Header -->
      <header class="sticky top-0 z-10 flex h-16 items-center gap-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 lg:px-6">
        <!-- Mobile menu button -->
        <button
          class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
          @click="mobileOpen = !mobileOpen"
        >
          <component :is="mobileOpen ? X : Menu" class="w-5 h-5" />
        </button>

        <!-- Search -->
        <div class="flex-1 max-w-md hidden sm:block">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-200"
            />
          </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
          <!-- Dark mode toggle -->
          <button
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400"
            @click="settingsStore.toggleDarkMode()"
          >
            <component :is="settingsStore.darkMode ? Sun : Moon" class="w-5 h-5" />
          </button>

          <!-- Notifications -->
          <button class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400">
            <Bell class="w-5 h-5" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full" />
          </button>

          <!-- Profile dropdown -->
          <div class="relative">
            <button
              class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
              @click="userMenuOpen = !userMenuOpen"
            >
              <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold">
                {{ auth.user?.full_name?.charAt(0)?.toUpperCase() ?? 'A' }}
              </div>
              <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ auth.user?.full_name }}
              </span>
              <ChevronDown class="w-4 h-4 text-gray-400" />
            </button>
            <div
              v-if="userMenuOpen"
              v-click-outside="() => (userMenuOpen = false)"
              class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
            >
              <RouterLink
                :to="{ name: 'profile' }"
                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="userMenuOpen = false"
              >
                <UserCircle class="w-4 h-4" />
                Profile
              </RouterLink>
              <RouterLink
                :to="{ name: 'settings' }"
                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="userMenuOpen = false"
              >
                <Settings class="w-4 h-4" />
                Settings
              </RouterLink>
              <hr class="my-1 border-gray-200 dark:border-gray-700" />
              <button
                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                @click="handleLogout"
              >
                <LogOut class="w-4 h-4" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-4 lg:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
