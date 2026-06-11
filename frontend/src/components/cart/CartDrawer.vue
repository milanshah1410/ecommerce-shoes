<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const cart = useCartStore()

function close() {
  cart.drawerOpen = false
}

function goToCart() {
  cart.drawerOpen = false
  router.push('/cart')
}

function goToCheckout() {
  cart.drawerOpen = false
  router.push('/checkout')
}

async function updateQty(cartId: number, qty: number) {
  if (qty < 1) return
  await cart.update(cartId, qty)
}

async function removeItem(cartId: number) {
  await cart.remove(cartId)
}

function fmt(n: number) {
  return `₹${n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="cart.drawerOpen"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
        @click="close"
      />
    </Transition>

    <!-- Drawer panel -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="cart.drawerOpen"
        class="fixed right-0 top-0 z-50 flex h-full w-full max-w-md flex-col bg-white shadow-2xl"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h2 class="text-base font-semibold text-gray-900">
              Shopping Cart
              <span v-if="cart.count > 0" class="ml-1 text-sm font-normal text-gray-400">({{ cart.count }})</span>
            </h2>
          </div>
          <button
            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
            aria-label="Close cart"
            @click="close"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Items -->
        <div class="flex-1 overflow-y-auto px-5 py-4">
          <!-- Empty state -->
          <div
            v-if="cart.items.length === 0"
            class="flex flex-col items-center justify-center gap-4 py-20 text-center"
          >
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
              <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="font-semibold text-gray-700">Your cart is empty</p>
              <p class="mt-1 text-sm text-gray-400">Add some shoes to get started!</p>
            </div>
            <button class="btn-primary" @click="close">Continue Shopping</button>
          </div>

          <!-- Cart items list -->
          <ul v-else class="space-y-4">
            <li
              v-for="item in cart.items"
              :key="item.id"
              class="flex gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3"
            >
              <!-- Thumbnail -->
              <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img
                  v-if="item.product.thumbnail"
                  :src="item.product.thumbnail"
                  :alt="item.product.name"
                  class="h-full w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center"
                >
                  <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 10h1l1-4h14l1 4h1a1 1 0 010 2H3a1 1 0 010-2z" />
                  </svg>
                </div>
              </div>

              <!-- Info -->
              <div class="flex flex-1 flex-col gap-1 overflow-hidden">
                <p class="truncate text-sm font-semibold text-gray-800">{{ item.product.name }}</p>

                <!-- Variant -->
                <div v-if="item.variant" class="flex flex-wrap gap-1.5">
                  <span class="rounded-full bg-white border border-gray-200 px-2 py-0.5 text-xs text-gray-500">
                    Size: {{ item.variant.size }}
                  </span>
                  <span class="flex items-center gap-1 rounded-full bg-white border border-gray-200 px-2 py-0.5 text-xs text-gray-500">
                    <span
                      v-if="item.variant.color_hex"
                      class="inline-block h-3 w-3 rounded-full border border-gray-300"
                      :style="{ backgroundColor: item.variant.color_hex }"
                    />
                    {{ item.variant.color }}
                  </span>
                </div>

                <!-- Price + Qty -->
                <div class="mt-auto flex items-center justify-between gap-2">
                  <span class="text-sm font-bold text-brand-600">{{ fmt(item.line_total) }}</span>

                  <!-- Qty controls -->
                  <div class="flex items-center gap-1">
                    <button
                      class="flex h-6 w-6 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 transition hover:bg-gray-100 disabled:opacity-50"
                      :disabled="item.quantity <= 1"
                      @click="updateQty(item.id, item.quantity - 1)"
                    >
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                      </svg>
                    </button>
                    <span class="w-6 text-center text-sm font-medium text-gray-700">{{ item.quantity }}</span>
                    <button
                      class="flex h-6 w-6 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 transition hover:bg-gray-100"
                      @click="updateQty(item.id, item.quantity + 1)"
                    >
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>

                    <!-- Remove -->
                    <button
                      class="ml-1 flex h-6 w-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                      aria-label="Remove item"
                      @click="removeItem(item.id)"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Summary + Actions (only shown when cart has items) -->
        <div
          v-if="cart.items.length > 0"
          class="border-t border-gray-100 bg-white px-5 py-4"
        >
          <!-- Order summary -->
          <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal</span>
              <span>{{ fmt(cart.summary.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Shipping</span>
              <span :class="cart.summary.shipping === 0 ? 'text-green-600 font-medium' : ''">
                {{ cart.summary.shipping === 0 ? 'Free' : fmt(cart.summary.shipping) }}
              </span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Tax</span>
              <span>{{ fmt(cart.summary.tax) }}</span>
            </div>
            <div
              v-if="cart.summary.discount > 0"
              class="flex justify-between text-green-600"
            >
              <span>Discount</span>
              <span>-{{ fmt(cart.summary.discount) }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-bold text-gray-900">
              <span>Total</span>
              <span class="text-brand-600">{{ fmt(cart.summary.total) }}</span>
            </div>
          </div>

          <!-- Buttons -->
          <div class="mt-4 flex flex-col gap-2">
            <button class="btn-primary w-full" @click="goToCheckout">
              Checkout
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <button class="btn-outline w-full" @click="goToCart">
              View Cart
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
