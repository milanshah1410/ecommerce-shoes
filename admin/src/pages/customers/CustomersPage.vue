<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { customersApi } from '@/api/customers'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import type { User, PaginatedResponse, ListParams } from '@/types'
import { Search, Eye, ToggleRight, ToggleLeft } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const router = useRouter()
const response = ref<PaginatedResponse<User> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15, search: '' })

async function load() {
  loading.value = true
  try { const { data } = await customersApi.list(params); response.value = data }
  finally { loading.value = false }
}

watch(params, load, { immediate: true })

async function toggleStatus(u: User) {
  try {
    await customersApi.toggleStatus(u.id)
    load()
    Swal.fire({ icon: 'success', title: `Customer ${u.status === 'active' ? 'blocked' : 'activated'}`, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch { Swal.fire({ icon: 'error', title: 'Failed to update status.' }) }
}
</script>

<template>
  <div>
    <PageHeader title="Customers" subtitle="Manage registered customers" />

    <div class="flex items-center gap-3 mb-4">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="params.search" type="text" placeholder="Search by name or email…" class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" @input="params.page = 1" />
      </div>
    </div>

    <DataTable
      :columns="[{ key: 'name', label: 'Customer' }, { key: 'email', label: 'Email' }, { key: 'mobile', label: 'Mobile' }, { key: 'status', label: 'Status' }, { key: 'joined', label: 'Joined' }, { key: 'actions', label: '' }]"
      :rows="response?.data ?? []"
      :loading="loading"
    >
      <template #default="{ row: u }">
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-xs font-semibold">
              {{ (u as User).full_name?.charAt(0)?.toUpperCase() }}
            </div>
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ (u as User).full_name }}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ (u as User).email }}</td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ (u as User).mobile ?? '—' }}</td>
        <td class="px-4 py-3"><StatusBadge :status="(u as User).status" /></td>
        <td class="px-4 py-3 text-xs text-gray-400">{{ new Date((u as User).created_at).toLocaleDateString() }}</td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" title="View" @click="router.push({ name: 'customers.show', params: { id: (u as User).id } })">
              <Eye class="w-4 h-4" />
            </button>
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-yellow-600" :title="(u as User).status === 'active' ? 'Block' : 'Activate'" @click="toggleStatus(u as User)">
              <component :is="(u as User).status === 'active' ? ToggleRight : ToggleLeft" class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />
  </div>
</template>
