<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ordersApi } from '@/api/orders'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import type { Order, PaginatedResponse, ListParams } from '@/types'
import { Search, Eye } from 'lucide-vue-next'

const router = useRouter()
const response = ref<PaginatedResponse<Order> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15, search: '', status: '' })

async function load() {
  loading.value = true
  try { const { data } = await ordersApi.list(params); response.value = data }
  finally { loading.value = false }
}

watch(params, load, { immediate: true })

const ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned', 'refunded']
</script>

<template>
  <div>
    <PageHeader title="Orders" subtitle="View and manage customer orders" />

    <div class="flex flex-wrap items-center gap-3 mb-4">
      <div class="relative flex-1 min-w-[200px] max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="params.search" type="text" placeholder="Search order # or customer…" class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" @input="params.page = 1" />
      </div>
      <select v-model="params.status" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none" @change="params.page = 1">
        <option value="">All Status</option>
        <option v-for="s in ORDER_STATUSES" :key="s" :value="s" class="capitalize">{{ s }}</option>
      </select>
    </div>

    <DataTable
      :columns="[{ key: 'order_number', label: 'Order #' }, { key: 'customer', label: 'Customer' }, { key: 'total', label: 'Total' }, { key: 'payment', label: 'Payment' }, { key: 'status', label: 'Status' }, { key: 'date', label: 'Date' }, { key: 'actions', label: '' }]"
      :rows="response?.data ?? []"
      :loading="loading"
    >
      <template #default="{ row: o }">
        <td class="px-4 py-3 text-sm font-mono font-medium text-indigo-600 dark:text-indigo-400">#{{ (o as Order).order_number }}</td>
        <td class="px-4 py-3">
          <p class="text-sm text-gray-800 dark:text-gray-200">{{ (o as Order).user?.full_name }}</p>
          <p class="text-xs text-gray-400">{{ (o as Order).user?.email }}</p>
        </td>
        <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">₹{{ Number((o as Order).total_amount).toLocaleString() }}</td>
        <td class="px-4 py-3"><StatusBadge :status="(o as Order).payment_status" /></td>
        <td class="px-4 py-3"><StatusBadge :status="(o as Order).status" /></td>
        <td class="px-4 py-3 text-xs text-gray-400">{{ new Date((o as Order).created_at).toLocaleDateString() }}</td>
        <td class="px-4 py-3">
          <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" @click="router.push({ name: 'orders.show', params: { id: (o as Order).id } })">
            <Eye class="w-4 h-4" />
          </button>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />
  </div>
</template>
