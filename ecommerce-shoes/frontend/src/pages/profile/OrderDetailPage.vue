<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { orderApi } from '@/api/order'
import type { Order } from '@/types'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()

const order = ref<Order | null>(null)
const loading = ref(false)
const cancelling = ref(false)

const trackingSteps = [
  { key: 'pending', label: 'Ordered' },
  { key: 'confirmed', label: 'Confirmed' },
  { key: 'processing', label: 'Processing' },
  { key: 'shipped', label: 'Shipped' },
  { key: 'delivered', label: 'Delivered' },
]

const statusOrder: Record<string, number> = {
  pending: 0,
  confirmed: 1,
  processing: 2,
  shipped: 3,
  delivered: 4,
  cancelled: -1,
  returned: -1,
}

const currentStepIndex = computed(() => {
  if (!order.value) return -1
  return statusOrder[order.value.status] ?? -1
})

function isStepDone(index: number): boolean {
  return currentStepIndex.value >= index
}

function statusBadgeClass(status: string): string {
  const map: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-50 text-blue-600',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-indigo-100 text-indigo-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    returned: 'bg-orange-100 text-orange-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-700'
}

function paymentBadgeClass(method: string): string {
  const map: Record<string, string> = {
    cod: 'bg-gray-100 text-gray-700',
    card: 'bg-purple-100 text-purple-700',
    upi: 'bg-teal-100 text-teal-700',
    netbanking: 'bg-blue-100 text-blue-700',
    wallet: 'bg-orange-100 text-orange-700',
  }
  return map[method?.toLowerCase()] ?? 'bg-gray-100 text-gray-700'
}

async function fetchOrder() {
  loading.value = true
  try {
    const { data: res } = await orderApi.show(Number(route.params.id))
    order.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function cancelOrder() {
  if (!order.value) return
  const confirmed = window.confirm('Are you sure you want to cancel this order? This action cannot be undone.')
  if (!confirmed) return
  cancelling.value = true
  try {
    await orderApi.cancel(order.value.id)
    await fetchOrder()
    alert('Order cancelled successfully.')
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Failed to cancel the order.')
  } finally {
    cancelling.value = false
  }
}

onMounted(fetchOrder)
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">

    <!-- Back -->
    <button
      class="flex items-center gap-2 text-sm text-gray-500 hover:text-brand-600 mb-6 transition-colors"
      @click="router.push({ name: 'orders' })"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back to Orders
    </button>

    <div v-if="loading" class="flex justify-center py-20">
      <LoadingSpinner />
    </div>

    <div v-else-if="order" class="space-y-6">

      <!-- Order Header -->
      <div class="card p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl font-bold text-gray-900">Order #{{ order.order_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">
              Placed on {{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <span :class="['px-3 py-1 rounded-full text-sm font-medium capitalize', statusBadgeClass(order.status)]">
              {{ order.status }}
            </span>
            <span :class="['px-3 py-1 rounded-full text-sm font-medium uppercase', paymentBadgeClass(order.payment_method)]">
              {{ order.payment_method }}
            </span>
          </div>
        </div>
      </div>

      <!-- Tracking Timeline -->
      <div class="card p-6" v-if="order.status !== 'cancelled' && order.status !== 'returned'">
        <h2 class="text-base font-semibold text-gray-800 mb-6">Order Tracking</h2>
        <div class="flex items-start">
          <div
            v-for="(step, index) in trackingSteps"
            :key="step.key"
            class="flex-1 flex flex-col items-center relative"
          >
            <!-- Connector line -->
            <div
              v-if="index < trackingSteps.length - 1"
              :class="[
                'absolute top-4 left-1/2 w-full h-0.5 transition-colors',
                isStepDone(index + 1) ? 'bg-brand-600' : 'bg-gray-200',
              ]"
            />
            <!-- Circle -->
            <div
              :class="[
                'relative z-10 w-8 h-8 rounded-full border-2 flex items-center justify-center transition-colors',
                isStepDone(index)
                  ? 'bg-brand-600 border-brand-600 text-white'
                  : 'bg-white border-gray-300 text-gray-400',
              ]"
            >
              <svg v-if="isStepDone(index)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
              <span v-else class="text-xs font-bold">{{ index + 1 }}</span>
            </div>
            <p :class="['text-xs mt-2 text-center font-medium', isStepDone(index) ? 'text-brand-600' : 'text-gray-400']">
              {{ step.label }}
            </p>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="card p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Order Items</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b text-left text-gray-500">
                <th class="pb-3 font-medium">Product</th>
                <th class="pb-3 font-medium text-center">Qty</th>
                <th class="pb-3 font-medium text-right">Price</th>
                <th class="pb-3 font-medium text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in order.items"
                :key="item.id"
                class="border-b last:border-0"
              >
                <td class="py-4">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="item.thumbnail"
                      :src="item.thumbnail"
                      :alt="item.product_name"
                      class="w-14 h-14 object-cover rounded-lg border"
                    />
                    <div
                      v-else
                      class="w-14 h-14 rounded-lg border bg-gray-100 flex items-center justify-center text-gray-400"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-800">{{ item.product_name }}</p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        <span v-if="item.size">Size: {{ item.size }}</span>
                        <span v-if="item.size && item.color"> · </span>
                        <span v-if="item.color">Color: {{ item.color }}</span>
                      </p>
                    </div>
                  </div>
                </td>
                <td class="py-4 text-center text-gray-700">{{ item.quantity }}</td>
                <td class="py-4 text-right text-gray-700">${{ item.price }}</td>
                <td class="py-4 text-right font-medium text-gray-800">${{ (Number(item.price) * Number(item.quantity)).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Shipping Address -->
        <div class="card p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">Shipping Address</h2>
          <div v-if="order.shipping" class="text-sm text-gray-600 space-y-1">
            <p class="font-semibold text-gray-800">{{ order.shipping.full_name }}</p>
            <p>{{ order.shipping.address_line }}</p>
            <p>{{ order.shipping.city }}, {{ order.shipping.state }}</p>
            <p>{{ order.shipping.country }} - {{ order.shipping.pincode }}</p>
            <p class="mt-2">{{ order.shipping.mobile }}</p>
          </div>
          <p v-else class="text-sm text-gray-400">No address on record.</p>
        </div>

        <!-- Price Breakdown -->
        <div class="card p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">Price Breakdown</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal</span>
              <span>${{ order.subtotal }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Shipping</span>
              <span>{{ Number(order.shipping_charge) === 0 ? 'Free' : `$${order.shipping_charge}` }}</span>
            </div>
            <div v-if="order.tax" class="flex justify-between text-gray-600">
              <span>Tax</span>
              <span>${{ order.tax }}</span>
            </div>
            <div v-if="order.discount && Number(order.discount) > 0" class="flex justify-between text-green-600">
              <span>Discount</span>
              <span>-${{ order.discount }}</span>
            </div>
            <div class="border-t pt-2 flex justify-between font-bold text-gray-900 text-base">
              <span>Total</span>
              <span>${{ order.total_amount }}</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Cancel Order -->
      <div v-if="order.can_be_cancelled" class="flex justify-end">
        <button
          class="px-6 py-2.5 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 font-medium text-sm transition-colors"
          :disabled="cancelling"
          @click="cancelOrder"
        >
          <span v-if="cancelling">Cancelling...</span>
          <span v-else>Cancel Order</span>
        </button>
      </div>

    </div>

    <div v-else class="text-center py-20">
      <p class="text-gray-500">Order not found.</p>
    </div>

  </div>
</template>
