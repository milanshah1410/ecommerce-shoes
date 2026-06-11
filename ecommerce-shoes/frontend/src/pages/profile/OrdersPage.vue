<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { orderApi } from '@/api/order'
import type { Order } from '@/types'
import Pagination from '@/components/ui/Pagination.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()

const filterTabs = [
  { key: 'all', label: 'All' },
  { key: 'pending', label: 'Pending' },
  { key: 'processing', label: 'Processing' },
  { key: 'shipped', label: 'Shipped' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'cancelled', label: 'Cancelled' },
] as const

type FilterKey = typeof filterTabs[number]['key']

const activeFilter = ref<FilterKey>('all')
const currentPage = ref(1)
const orders = ref<Order[]>([])
const total = ref(0)
const lastPage = ref(1)
const loading = ref(false)

function statusBadgeClass(status: string): string {
  const map: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-indigo-100 text-indigo-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    returned: 'bg-orange-100 text-orange-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-700'
}

async function fetchOrders() {
  loading.value = true
  try {
    const params: any = { page: currentPage.value }
    if (activeFilter.value !== 'all') params.status = activeFilter.value
    const { data: res } = await orderApi.list(params)
    orders.value = res.data
    total.value = res.meta?.total ?? res.data.length
    lastPage.value = res.meta?.last_page ?? 1
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function setFilter(key: FilterKey) {
  activeFilter.value = key
  currentPage.value = 1
}

function setPage(page: number) {
  currentPage.value = page
}

watch([activeFilter, currentPage], fetchOrders)

onMounted(fetchOrders)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

    <!-- Filter Tabs -->
    <div class="flex gap-2 flex-wrap mb-6">
      <button
        v-for="tab in filterTabs"
        :key="tab.key"
        @click="setFilter(tab.key)"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium transition-colors border',
          activeFilter === tab.key
            ? 'bg-brand-600 text-white border-brand-600'
            : 'bg-white text-gray-600 border-gray-300 hover:border-brand-600 hover:text-brand-600',
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <LoadingSpinner />
    </div>

    <!-- Orders List -->
    <div v-else-if="orders.length > 0" class="space-y-4">
      <div
        v-for="order in orders"
        :key="order.id"
        class="card p-5 cursor-pointer hover:shadow-md transition-shadow"
        @click="router.push({ name: 'order-detail', params: { id: order.id } })"
      >
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
              <span class="font-semibold text-gray-800">#{{ order.order_number }}</span>
              <span :class="['text-xs px-2 py-1 rounded-full font-medium capitalize', statusBadgeClass(order.status)]">
                {{ order.status }}
              </span>
            </div>
            <p class="text-sm text-gray-500">
              {{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
            </p>
            <p class="text-sm text-gray-600 mt-1">
              {{ order.items_count ?? order.items?.length ?? 0 }} item(s)
            </p>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-gray-900">${{ order.total_amount }}</p>
            <span class="text-xs text-gray-400 uppercase">{{ order.payment_method }}</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6">
        <Pagination :current="currentPage" :last="lastPage" @change="setPage" />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <p class="text-xl font-semibold text-gray-500 mb-2">No orders yet</p>
      <p class="text-gray-400 mb-6">Looks like you haven't placed any orders.</p>
      <button class="btn-primary" @click="router.push({ name: 'products' })">Start Shopping</button>
    </div>
  </div>
</template>
