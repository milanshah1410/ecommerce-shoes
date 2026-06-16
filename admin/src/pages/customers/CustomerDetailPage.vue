<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { customersApi } from '@/api/customers'
import StatusBadge from '@/components/common/StatusBadge.vue'
import DataTable from '@/components/common/DataTable.vue'
import type { User, Order, PaginatedResponse } from '@/types'
import { ArrowLeft } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const customer = ref<User | null>(null)
const orders = ref<PaginatedResponse<Order> | null>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const [cRes, oRes] = await Promise.all([
      customersApi.show(Number(route.params.id)),
      customersApi.orders(Number(route.params.id), { per_page: 10 }),
    ])
    customer.value = cRes.data
    orders.value = oRes.data
  } finally { loading.value = false }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" @click="router.push({ name: 'customers' })">
        <ArrowLeft class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Profile</h1>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
    </div>

    <div v-else-if="customer" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Profile card -->
      <div class="xl:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-2xl font-bold mx-auto mb-3">
            {{ customer.full_name?.charAt(0)?.toUpperCase() }}
          </div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ customer.full_name }}</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ customer.email }}</p>
          <div class="mt-3"><StatusBadge :status="customer.status" /></div>
          <dl class="mt-4 space-y-2 text-sm text-left">
            <div class="flex justify-between border-t border-gray-100 dark:border-gray-700 pt-2">
              <dt class="text-gray-500">Mobile</dt>
              <dd class="text-gray-800 dark:text-gray-200">{{ customer.mobile ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Joined</dt>
              <dd class="text-gray-800 dark:text-gray-200">{{ new Date(customer.created_at).toLocaleDateString() }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Order history -->
      <div class="xl:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order History</h2>
          <DataTable
            :columns="[{ key: 'order_number', label: 'Order #' }, { key: 'total', label: 'Total' }, { key: 'status', label: 'Status' }, { key: 'date', label: 'Date' }]"
            :rows="orders?.data ?? []"
            empty-text="No orders yet."
          >
            <template #default="{ row: o }">
              <td class="px-4 py-3 text-sm font-mono text-indigo-600 dark:text-indigo-400">#{{ (o as Order).order_number }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">₹{{ Number((o as Order).total_amount).toLocaleString() }}</td>
              <td class="px-4 py-3"><StatusBadge :status="(o as Order).status" /></td>
              <td class="px-4 py-3 text-xs text-gray-400">{{ new Date((o as Order).created_at).toLocaleDateString() }}</td>
            </template>
          </DataTable>
        </div>
      </div>
    </div>
  </div>
</template>
