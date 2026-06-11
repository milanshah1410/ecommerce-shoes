<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-32">
        <LoadingSpinner size="lg" />
        <p class="mt-4 text-sm text-gray-500">Loading your order…</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-32">
        <p class="text-red-600 font-medium">{{ error }}</p>
        <router-link :to="{ name: 'products' }" class="mt-6 inline-block btn-primary px-8 py-3 rounded-lg font-semibold">
          Continue Shopping
        </router-link>
      </div>

      <!-- Success Content -->
      <div v-else-if="order" class="space-y-8">

        <!-- Success Header Card -->
        <div class="card bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
          <!-- Green Checkmark Circle -->
          <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
            Order Placed Successfully!
          </h1>
          <p class="text-gray-500 text-base mb-4">
            Thank you for your purchase. We'll send you a confirmation email shortly.
          </p>

          <!-- Order Number -->
          <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-indigo-50 border border-indigo-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
            </svg>
            <span class="text-brand-600 font-bold text-base tracking-wide">Order #{{ order.order_number ?? order.id }}</span>
          </div>

          <!-- Order Meta -->
          <div class="mt-5 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500">
            <span v-if="order.created_at" class="flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              {{ formatDate(order.created_at) }}
            </span>
            <span v-if="order.payment_method" class="flex items-center gap-1.5 capitalize">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
              </svg>
              {{ order.payment_method.replace('_', ' ') }}
            </span>
            <span
              v-if="order.status"
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider',
                statusClass(order.status),
              ]"
            >
              {{ order.status }}
            </span>
          </div>
        </div>

        <!-- Order Items Summary -->
        <div class="card bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Items Ordered</h2>
          </div>
          <div class="divide-y divide-gray-100">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex items-center gap-4 px-6 py-4"
            >
              <div class="flex-none w-14 h-14 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                <img
                  v-if="item.thumbnail"
                  :src="item.thumbnail"
                  :alt="item.product_name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-xl">👟</div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ item.product_name }}</p>
                <div class="flex flex-wrap gap-2 mt-0.5">
                  <span v-if="item.size" class="text-xs text-gray-500">Size: {{ item.size }}</span>
                  <span v-if="item.color" class="text-xs text-gray-500">Color: {{ item.color }}</span>
                </div>
              </div>
              <div class="flex-none text-right">
                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(item.subtotal) }}</p>
                <p class="text-xs text-gray-400">{{ formatCurrency(item.price) }} × {{ item.quantity }}</p>
              </div>
            </div>
          </div>

          <!-- Totals Footer -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Subtotal</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(order.subtotal ?? 0) }}</span>
            </div>
            <div v-if="order.shipping_charge !== undefined" class="flex justify-between text-sm text-gray-600">
              <span>Shipping</span>
              <span class="font-medium text-gray-900">{{ order.shipping_charge === 0 ? 'FREE' : formatCurrency(order.shipping_charge ?? 0) }}</span>
            </div>
            <div v-if="order.tax" class="flex justify-between text-sm text-gray-600">
              <span>Tax</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(order.tax) }}</span>
            </div>
            <div v-if="order.discount" class="flex justify-between text-sm text-green-600">
              <span>Discount</span>
              <span class="font-medium">-{{ formatCurrency(order.discount) }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200">
              <span class="text-base font-bold text-gray-900">Total</span>
              <span class="text-xl font-extrabold text-brand-600">{{ formatCurrency(order.total_amount ?? 0) }}</span>
            </div>
          </div>
        </div>

        <!-- Shipping Address -->
        <div v-if="order.shipping" class="card bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
          <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
            Shipping Address
          </h2>
          <div class="text-sm text-gray-700 space-y-1">
            <p class="font-semibold text-gray-900">{{ order.shipping.full_name }}</p>
            <p v-if="order.shipping.mobile" class="text-gray-500">{{ order.shipping.mobile }}</p>
            <p>{{ order.shipping.address_line }}</p>
            <p>
              {{ order.shipping.city }}<span v-if="order.shipping.state">, {{ order.shipping.state }}</span>
              <span v-if="order.shipping.pincode"> — {{ order.shipping.pincode }}</span>
            </p>
            <p v-if="order.shipping.country">{{ order.shipping.country }}</p>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <router-link
            :to="`/profile/orders/${order.id}`"
            class="btn-primary px-8 py-3 rounded-lg font-semibold text-center"
          >
            Track Order
          </router-link>
          <router-link
            :to="{ name: 'products' }"
            class="btn-outline px-8 py-3 rounded-lg font-semibold text-center"
          >
            Continue Shopping
          </router-link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { orderApi } from '@/api/order'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import type { Order } from '@/types'

const route = useRoute()

const loading = ref(true)
const error = ref('')
const order = ref<Order | null>(null)

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(value)
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function statusClass(status: string): string {
  const map: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    processing: 'bg-indigo-100 text-indigo-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
  }
  return map[status.toLowerCase()] ?? 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  const orderId = route.params.orderId as string
  if (!orderId) {
    error.value = 'Order ID not found.'
    loading.value = false
    return
  }
  try {
    const { data: res } = await orderApi.show(Number(orderId))
    order.value = res.data
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? err?.message ?? 'Failed to load order details.'
  } finally {
    loading.value = false
  }
})
</script>
