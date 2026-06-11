<template>
  <div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Page Heading -->
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Shopping Cart</h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ cartStore.items.length }} {{ cartStore.items.length === 1 ? 'item' : 'items' }} in your cart
        </p>
      </div>

      <!-- Loading skeleton -->
      <div v-if="cartStore.loading" class="flex items-center justify-center py-32">
        <LoadingSpinner size="lg" />
      </div>

      <!-- Empty State -->
      <div v-else-if="!cartStore.items.length" class="flex flex-col items-center justify-center py-32 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">Your cart is empty</h2>
        <p class="text-gray-500 mb-8">Looks like you haven't added anything yet.</p>
        <router-link :to="{ name: 'products' }" class="btn-primary px-8 py-3 rounded-lg font-semibold">
          Start Shopping
        </router-link>
      </div>

      <!-- Cart Layout -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Cart Items -->
        <div class="lg:col-span-2 space-y-4">

          <!-- Continue Shopping -->
          <router-link :to="{ name: 'products' }" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-indigo-800 font-medium transition-colors mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Continue Shopping
          </router-link>

          <!-- Items Table Header -->
          <div class="hidden sm:grid grid-cols-12 gap-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider pb-2 border-b border-gray-200">
            <div class="col-span-5">Product</div>
            <div class="col-span-2 text-center">Price</div>
            <div class="col-span-3 text-center">Quantity</div>
            <div class="col-span-2 text-right">Total</div>
          </div>

          <!-- Cart Item Rows -->
          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="card bg-white rounded-xl border border-gray-200 shadow-sm p-4"
          >
            <div class="grid grid-cols-12 gap-4 items-center">

              <!-- Thumbnail + Info -->
              <div class="col-span-12 sm:col-span-5 flex items-center gap-4">
                <div class="flex-none w-20 h-20 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                  <img
                    v-if="item.product?.thumbnail"
                    :src="item.product.thumbnail"
                    :alt="item.product.name"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
                <div class="min-w-0">
                  <p class="font-semibold text-gray-900 text-sm leading-snug truncate">
                    {{ item.product.name }}
                  </p>
                  <div class="mt-1 flex flex-wrap gap-2">
                    <span v-if="item.variant?.size" class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 font-medium">
                      Size: {{ item.variant.size }}
                    </span>
                    <span v-if="item.variant?.color" class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 font-medium">
                      Color: {{ item.variant.color }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Unit Price -->
              <div class="col-span-4 sm:col-span-2 flex sm:justify-center items-center">
                <span class="text-sm sm:hidden text-gray-500 mr-1">Price:</span>
                <span class="text-sm font-semibold text-gray-800">{{ formatCurrency(item.unit_price) }}</span>
              </div>

              <!-- Qty Controls -->
              <div class="col-span-5 sm:col-span-3 flex items-center justify-start sm:justify-center">
                <div class="inline-flex items-center border border-gray-300 rounded-lg overflow-hidden">
                  <button
                    @click="decrement(item)"
                    :disabled="updatingId === item.id"
                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <input
                    type="number"
                    :value="item.quantity"
                    @change="onQtyChange(item, $event)"
                    min="1"
                    max="99"
                    class="w-10 text-center text-sm font-semibold text-gray-900 border-x border-gray-300 focus:outline-none focus:ring-0 bg-white h-8"
                  />
                  <button
                    @click="increment(item)"
                    :disabled="updatingId === item.id"
                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Line Total + Remove -->
              <div class="col-span-3 sm:col-span-2 flex items-center justify-end gap-3">
                <span class="text-sm font-bold text-gray-900">{{ formatCurrency(item.line_total) }}</span>
                <button
                  @click="removeItem(item)"
                  class="text-gray-400 hover:text-red-500 transition-colors flex-none"
                  title="Remove item"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>

            </div>
          </div>
          <!-- End Items -->

        </div>

        <!-- Right: Order Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-24 card bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
            <h2 class="text-lg font-bold text-gray-900">Order Summary</h2>

            <!-- Coupon -->
            <div>
              <label class="label block mb-1.5">Coupon Code</label>
              <div class="flex gap-2">
                <input
                  v-model="couponCode"
                  type="text"
                  placeholder="Enter coupon"
                  class="input flex-1 uppercase"
                  :disabled="!!appliedCoupon || couponLoading"
                  @keyup.enter="applyCoupon"
                />
                <button
                  v-if="!appliedCoupon"
                  @click="applyCoupon"
                  :disabled="!couponCode.trim() || couponLoading"
                  class="btn-outline px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 whitespace-nowrap"
                >
                  <span v-if="couponLoading">...</span>
                  <span v-else>Apply</span>
                </button>
                <button
                  v-else
                  @click="removeCoupon"
                  class="px-3 py-2 rounded-lg text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition-colors whitespace-nowrap"
                >
                  Remove
                </button>
              </div>
              <!-- Applied Badge -->
              <div v-if="appliedCoupon" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 border border-green-200 text-green-700 rounded-full text-xs font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ appliedCoupon }} applied
              </div>
              <!-- Coupon Error -->
              <p v-if="couponError" class="mt-1.5 text-xs text-red-600">{{ couponError }}</p>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100"></div>

            <!-- Price Breakdown -->
            <div class="space-y-2.5 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(summary.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Shipping</span>
                <span class="font-medium text-gray-900">
                  {{ summary.shipping === 0 ? 'FREE' : formatCurrency(summary.shipping) }}
                </span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Tax</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(summary.tax) }}</span>
              </div>
              <div v-if="summary.discount" class="flex justify-between text-green-600">
                <span>Discount</span>
                <span class="font-medium">-{{ formatCurrency(summary.discount) }}</span>
              </div>
            </div>

            <!-- Grand Total -->
            <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
              <span class="text-base font-bold text-gray-900">Grand Total</span>
              <span class="text-xl font-extrabold text-brand-600">{{ formatCurrency(summary.total) }}</span>
            </div>

            <!-- Checkout Button -->
            <button
              @click="proceedToCheckout"
              class="btn-primary w-full py-3 font-semibold rounded-lg text-base"
            >
              Proceed to Checkout
            </button>

            <!-- Secure note -->
            <p class="text-xs text-gray-400 text-center flex items-center justify-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
              Secure checkout
            </p>
          </div>
        </div>
        <!-- End Summary -->

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import type { CartItem } from '@/types'
import Swal from 'sweetalert2'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const couponCode = ref('')
const appliedCoupon = ref('')
const couponError = ref('')
const couponLoading = ref(false)
const updatingId = ref<number | string | null>(null)

const summary = computed(() => {
  const s = cartStore.summary
  return {
    subtotal: s?.subtotal ?? 0,
    shipping: s?.shipping ?? 0,
    tax: s?.tax ?? 0,
    discount: s?.discount ?? 0,
    total: s?.total ?? 0,
  }
})

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(value)
}

async function increment(item: CartItem) {
  updatingId.value = item.id
  try {
    await cartStore.update(item.id, item.quantity + 1)
  } finally {
    updatingId.value = null
  }
}

async function decrement(item: CartItem) {
  if (item.quantity <= 1) {
    removeItem(item)
    return
  }
  updatingId.value = item.id
  try {
    await cartStore.update(item.id, item.quantity - 1)
  } finally {
    updatingId.value = null
  }
}

async function onQtyChange(item: CartItem, event: Event) {
  const val = parseInt((event.target as HTMLInputElement).value, 10)
  if (!val || val < 1) return
  updatingId.value = item.id
  try {
    await cartStore.update(item.id, val)
  } finally {
    updatingId.value = null
  }
}

async function removeItem(item: CartItem) {
  const result = await Swal.fire({
    title: 'Remove item?',
    text: `Remove "${item.product.name}" from your cart?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, remove',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc2626',
  })
  if (result.isConfirmed) {
    await cartStore.remove(item.id)
  }
}

async function applyCoupon() {
  if (!couponCode.value.trim()) return
  couponError.value = ''
  couponLoading.value = true
  try {
    await cartStore.applyCoupon(couponCode.value.trim().toUpperCase())
    appliedCoupon.value = couponCode.value.trim().toUpperCase()
  } catch (err: any) {
    couponError.value = err?.response?.data?.message ?? err?.message ?? 'Invalid coupon code.'
    couponCode.value = ''
  } finally {
    couponLoading.value = false
  }
}

function removeCoupon() {
  appliedCoupon.value = ''
  couponCode.value = ''
  couponError.value = ''
  cartStore.fetch()
}

function proceedToCheckout() {
  if (authStore.isLoggedIn) {
    router.push({ name: 'checkout' })
  } else {
    router.push({ name: 'login', query: { redirect: '/checkout' } })
  }
}

onMounted(() => {
  cartStore.fetch()
})
</script>
