<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ordersApi } from '@/api/orders'
import StatusBadge from '@/components/common/StatusBadge.vue'
import type { Order, OrderStatus } from '@/types'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()
const order = ref<Order | null>(null)
const loading = ref(true)
const updating = ref(false)

const ORDER_STATUSES: OrderStatus[] = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned', 'refunded']

async function load() {
  loading.value = true
  try { const { data } = await ordersApi.show(Number(route.params.id)); order.value = data }
  finally { loading.value = false }
}

onMounted(load)

async function updateStatus(status: OrderStatus) {
  if (!order.value) return
  updating.value = true
  try {
    await ordersApi.updateStatus(order.value.id, status)
    order.value.status = status
    Swal.fire({ icon: 'success', title: `Status updated to ${status}`, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch { Swal.fire({ icon: 'error', title: 'Failed to update status.' }) }
  finally { updating.value = false }
}
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" @click="router.push({ name: 'orders' })">
        <ArrowLeft class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Detail</h1>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
    </div>

    <div v-else-if="order" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Main -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Order items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Items</h2>
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="item in order.items" :key="item.id" class="py-3 flex items-center gap-3">
              <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                <img v-if="item.product?.images?.[0]?.image" :src="item.product.images[0].image" class="w-full h-full object-cover" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ item.product_name }}</p>
                <p class="text-xs text-gray-400">{{ item.size }} / {{ item.color }} · SKU: {{ item.sku }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">₹{{ Number(item.total_price).toLocaleString() }}</p>
                <p class="text-xs text-gray-400">{{ item.quantity }} × ₹{{ Number(item.unit_price).toLocaleString() }}</p>
              </div>
            </div>
          </div>
          <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 space-y-1.5">
            <div class="flex justify-between text-sm text-gray-500"><span>Subtotal</span><span>₹{{ Number(order.subtotal).toLocaleString() }}</span></div>
            <div class="flex justify-between text-sm text-gray-500"><span>Tax</span><span>₹{{ Number(order.tax).toLocaleString() }}</span></div>
            <div class="flex justify-between text-sm text-gray-500"><span>Shipping</span><span>₹{{ Number(order.shipping_charge).toLocaleString() }}</span></div>
            <div v-if="Number(order.discount) > 0" class="flex justify-between text-sm text-green-600"><span>Discount</span><span>−₹{{ Number(order.discount).toLocaleString() }}</span></div>
            <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-1 border-t border-gray-100 dark:border-gray-700">
              <span>Total</span><span>₹{{ Number(order.total_amount).toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <!-- Shipping address -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Shipping Address</h2>
          <address class="text-sm text-gray-600 dark:text-gray-300 not-italic space-y-0.5">
            <p class="font-medium">{{ order.ship_full_name }}</p>
            <p>{{ order.ship_mobile }}</p>
            <p>{{ order.ship_address_line }}</p>
            <p>{{ order.ship_city }}, {{ order.ship_state }} {{ order.ship_pincode }}</p>
            <p>{{ order.ship_country }}</p>
          </address>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Order summary -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Info</h2>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-gray-500">Order #</dt>
              <dd class="font-mono font-medium text-gray-800 dark:text-gray-200">{{ order.order_number }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Status</dt>
              <dd><StatusBadge :status="order.status" /></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Payment</dt>
              <dd><StatusBadge :status="order.payment_status" /></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Method</dt>
              <dd class="capitalize text-gray-700 dark:text-gray-300">{{ order.payment_method }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Date</dt>
              <dd class="text-gray-700 dark:text-gray-300">{{ new Date(order.created_at).toLocaleString() }}</dd>
            </div>
          </dl>
        </div>

        <!-- Update status -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Update Status</h2>
          <div class="space-y-1.5">
            <button
              v-for="s in ORDER_STATUSES"
              :key="s"
              :disabled="updating || s === order.status"
              :class="[
                'w-full text-left px-3 py-2 rounded-lg text-sm capitalize transition-colors',
                s === order.status
                  ? 'bg-indigo-600 text-white cursor-default'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-40',
              ]"
              @click="updateStatus(s)"
            >
              {{ updating && s !== order.status ? '…' : s }}
            </button>
          </div>
        </div>

        <!-- Customer -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Customer</h2>
          <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ order.user?.full_name }}</p>
          <p class="text-xs text-gray-400">{{ order.user?.email }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
