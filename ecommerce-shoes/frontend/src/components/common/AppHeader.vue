<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const cart = useCartStore()
const wishlist = useWishlistStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const searchQuery = ref('')

const navLinks: { label: string; to: string; query?: Record<string, string> }[] = [
  { label: 'Home', to: '/' },
  { label: 'Products', to: '/products' },
  { label: 'Men', to: '/products', query: { gender: 'male' } },
  { label: 'Women', to: '/products', query: { gender: 'female' } },
  { label: 'Sale', to: '/products', query: { sale: '1' } },
]

function isActive(link: { to: string; query?: Record<string, string> }) {
  if (link.to === '/' && route.path === '/') return true
  if (link.to !== '/' && route.path.startsWith(link.to)) {
    if (link.query) {
      return Object.entries(link.query).every(([k, v]) => route.query[k] === v)
    }
    return !link.query
  }
  return false
}

function search() {
  if (!searchQuery.value.trim()) return
  router.push({ path: '/products', query: { search: searchQuery.value.trim() } })
  searchQuery.value = ''
  mobileMenuOpen.value = false
}

function openCart() {
  cart.drawerOpen = true
}

function navigateTo(path: string, query?: Record<string, string>) {
  router.push({ path, query })
  mobileMenuOpen.value = false
  userMenuOpen.value = false
}

async function logout() {
  userMenuOpen.value = false
  await auth.logout()
  router.push('/')
}

const userInitials = computed(() => {
  if (!auth.user) return '?'
  const first = auth.user.first_name?.[0] ?? ''
  const last = auth.user.last_name?.[0] ?? ''
  return (first + last).toUpperCase() || auth.user.email[0].toUpperCase()
})
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-gray-100 bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center gap-4">

        <!-- Logo -->
        <RouterLink to="/" class="flex shrink-0 items-center gap-1.5">
          <span class="text-2xl">👟</span>
          <span class="text-xl font-extrabold text-brand-600">SoleStyle</span>
        </RouterLink>

        <!-- Desktop Nav -->
        <nav class="hidden items-center gap-1 md:flex">
          <button
            v-for="link in navLinks"
            :key="link.label"
            class="rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="
              isActive(link)
                ? 'bg-brand-50 text-brand-600'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
            "
            @click="navigateTo(link.to, link.query)"
          >
            {{ link.label }}
          </button>
        </nav>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Search -->
        <form
          class="hidden items-center md:flex"
          @submit.prevent="search"
        >
          <div class="relative">
            <svg
              class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search shoes..."
              class="h-9 w-52 rounded-lg border border-gray-300 pl-9 pr-3 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 lg:w-64"
            />
          </div>
        </form>

        <!-- Wishlist (logged in only) -->
        <button
          v-if="auth.isLoggedIn"
          class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-brand-600"
          aria-label="Wishlist"
          @click="navigateTo('/profile/wishlist')"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            />
          </svg>
          <span
            v-if="wishlist.count > 0"
            class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
          >
            {{ wishlist.count > 9 ? '9+' : wishlist.count }}
          </span>
        </button>

        <!-- Cart -->
        <button
          class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-brand-600"
          aria-label="Cart"
          @click="openCart"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
            />
          </svg>
          <span
            v-if="cart.count > 0"
            class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-600 text-[10px] font-bold text-white"
          >
            {{ cart.count > 9 ? '9+' : cart.count }}
          </span>
        </button>

        <!-- User menu -->
        <div class="relative hidden md:block">
          <!-- Not logged in -->
          <div v-if="!auth.isLoggedIn" class="flex items-center gap-2">
            <RouterLink to="/login" class="btn-outline py-1.5 text-sm">Login</RouterLink>
            <RouterLink to="/register" class="btn-primary py-1.5 text-sm">Register</RouterLink>
          </div>

          <!-- Logged in -->
          <div v-else>
            <button
              class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white transition hover:bg-brand-700"
              @click="userMenuOpen = !userMenuOpen"
            >
              {{ userInitials }}
            </button>

            <!-- Dropdown -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div
                v-if="userMenuOpen"
                class="absolute right-0 top-11 z-50 w-48 rounded-xl border border-gray-100 bg-white py-1 shadow-lg"
              >
                <div class="border-b border-gray-100 px-4 py-2">
                  <p class="truncate text-sm font-semibold text-gray-800">{{ auth.user?.full_name }}</p>
                  <p class="truncate text-xs text-gray-400">{{ auth.user?.email }}</p>
                </div>
                <button
                  class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50"
                  @click="navigateTo('/profile')"
                >
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  My Profile
                </button>
                <button
                  class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50"
                  @click="navigateTo('/profile/orders')"
                >
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  My Orders
                </button>
                <button
                  class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50"
                  @click="navigateTo('/profile/wishlist')"
                >
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                  Wishlist
                </button>
                <div class="my-1 border-t border-gray-100" />
                <button
                  class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 transition hover:bg-red-50"
                  @click="logout"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </button>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Mobile hamburger -->
        <button
          class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 md:hidden"
          @click="mobileMenuOpen = !mobileMenuOpen"
          aria-label="Menu"
        >
          <svg v-if="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="mobileMenuOpen" class="border-t border-gray-100 bg-white px-4 pb-4 md:hidden">
        <!-- Mobile search -->
        <form class="mt-3" @submit.prevent="search">
          <div class="relative">
            <svg
              class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search shoes..."
              class="input pl-9"
            />
          </div>
        </form>

        <!-- Mobile nav links -->
        <nav class="mt-3 flex flex-col gap-1">
          <button
            v-for="link in navLinks"
            :key="link.label"
            class="rounded-lg px-3 py-2.5 text-left text-sm font-medium transition"
            :class="
              isActive(link)
                ? 'bg-brand-50 text-brand-600'
                : 'text-gray-700 hover:bg-gray-100'
            "
            @click="navigateTo(link.to, link.query)"
          >
            {{ link.label }}
          </button>
        </nav>

        <!-- Mobile auth -->
        <div class="mt-3 border-t border-gray-100 pt-3">
          <div v-if="!auth.isLoggedIn" class="flex gap-2">
            <RouterLink to="/login" class="btn-outline flex-1 text-center text-sm" @click="mobileMenuOpen = false">Login</RouterLink>
            <RouterLink to="/register" class="btn-primary flex-1 text-center text-sm" @click="mobileMenuOpen = false">Register</RouterLink>
          </div>
          <div v-else class="space-y-1">
            <div class="mb-2 px-3 py-1">
              <p class="text-sm font-semibold text-gray-800">{{ auth.user?.full_name }}</p>
              <p class="text-xs text-gray-400">{{ auth.user?.email }}</p>
            </div>
            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="navigateTo('/profile')">My Profile</button>
            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="navigateTo('/profile/orders')">My Orders</button>
            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="navigateTo('/profile/wishlist')">Wishlist</button>
            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50" @click="logout">Logout</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Click outside overlay for user menu -->
    <div
      v-if="userMenuOpen"
      class="fixed inset-0 z-40"
      @click="userMenuOpen = false"
    />
  </header>
</template>
