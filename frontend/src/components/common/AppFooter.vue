<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

function go(path: string, query?: Record<string, string>) {
  router.push({ path, query })
}
</script>

<template>
  <footer class="bg-gray-900 text-gray-300">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">

        <!-- Brand -->
        <div class="lg:col-span-1">
          <div class="flex items-center gap-2">
            <span class="text-2xl">👟</span>
            <span class="text-xl font-extrabold text-white">SoleStyle</span>
          </div>
          <p class="mt-3 text-sm leading-relaxed text-gray-400">
            Step into style. Discover premium footwear for every occasion, every pace, every you.
          </p>

          <!-- Social icons -->
          <div class="mt-5 flex items-center gap-3">
            <!-- Facebook -->
            <a
              href="#"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-gray-400 transition hover:bg-brand-600 hover:text-white"
              aria-label="Facebook"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
              </svg>
            </a>
            <!-- Instagram -->
            <a
              href="#"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-gray-400 transition hover:bg-brand-600 hover:text-white"
              aria-label="Instagram"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke-width="2" />
                <circle cx="12" cy="12" r="4" stroke-width="2" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
              </svg>
            </a>
            <!-- Twitter/X -->
            <a
              href="#"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-gray-400 transition hover:bg-brand-600 hover:text-white"
              aria-label="Twitter"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
            </a>
            <!-- YouTube -->
            <a
              href="#"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-gray-400 transition hover:bg-brand-600 hover:text-white"
              aria-label="YouTube"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-white">
            Quick Links
          </h3>
          <ul class="space-y-2.5">
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/')"
              >
                Home
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/products')"
              >
                All Products
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/products', { gender: 'male' })"
              >
                Men
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/products', { gender: 'female' })"
              >
                Women
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/products', { sale: '1' })"
              >
                Sale
              </button>
            </li>
          </ul>
        </div>

        <!-- Account -->
        <div>
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-white">
            Account
          </h3>
          <ul class="space-y-2.5">
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/profile')"
              >
                My Profile
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/profile/orders')"
              >
                My Orders
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/profile/wishlist')"
              >
                Wishlist
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/cart')"
              >
                Cart
              </button>
            </li>
            <li>
              <button
                class="text-sm text-gray-400 transition hover:text-white"
                @click="go('/login')"
              >
                Login / Register
              </button>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-white">
            Contact
          </h3>
          <ul class="space-y-3">
            <li class="flex items-start gap-2.5">
              <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span class="text-sm text-gray-400">support@solestyle.com</span>
            </li>
            <li class="flex items-start gap-2.5">
              <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span class="text-sm text-gray-400">+91 98765 43210</span>
            </li>
            <li class="flex items-start gap-2.5">
              <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span class="text-sm text-gray-400">
                123 Fashion Street,<br />Mumbai, Maharashtra 400001
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-gray-800">
      <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-gray-500 sm:flex-row sm:px-6 lg:px-8">
        <span>© 2026 SoleStyle. All rights reserved.</span>
        <div class="flex items-center gap-4">
          <button class="transition hover:text-gray-300" @click="go('/privacy')">Privacy Policy</button>
          <button class="transition hover:text-gray-300" @click="go('/terms')">Terms of Service</button>
        </div>
      </div>
    </div>
  </footer>
</template>
