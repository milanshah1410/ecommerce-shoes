<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { productsApi } from '@/api/products'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import type { Product, PaginatedResponse, ListParams } from '@/types'
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const router = useRouter()
const response = ref<PaginatedResponse<Product> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15, search: '', status: '' })
const deleteTarget = ref<Product | null>(null)
const deleting = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await productsApi.list(params)
    response.value = data
  } finally {
    loading.value = false
  }
}

watch(params, load, { immediate: true })

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await productsApi.delete(deleteTarget.value.id)
    deleteTarget.value = null
    load()
    Swal.fire({ icon: 'success', title: 'Deleted!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Products" subtitle="Manage your product catalogue">
      <button
        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors"
        @click="router.push({ name: 'products.create' })"
      >
        <Plus class="w-4 h-4" /> Add Product
      </button>
    </PageHeader>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <div class="relative flex-1 min-w-[200px] max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="params.search"
          type="text"
          placeholder="Search products…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @input="params.page = 1"
        />
      </div>
      <select
        v-model="params.status"
        class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none"
        @change="params.page = 1"
      >
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="draft">Draft</option>
      </select>
    </div>

    <DataTable
      :columns="[
        { key: 'name', label: 'Product' },
        { key: 'sku', label: 'SKU' },
        { key: 'price', label: 'Price' },
        { key: 'sold_count', label: 'Sold' },
        { key: 'status', label: 'Status' },
        { key: 'actions', label: '' },
      ]"
      :rows="response?.data ?? []"
      :loading="loading"
      empty-text="No products found."
    >
      <template #default="{ row: p }">
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
              <img
                v-if="(p as Product).images?.length"
                :src="(p as Product).images![0].image"
                :alt="(p as Product).name"
                class="w-full h-full object-cover"
              />
            </div>
            <div>
              <p class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ (p as Product).name }}</p>
              <p class="text-xs text-gray-400">{{ (p as Product).brand?.name }}</p>
            </div>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ (p as Product).sku }}</td>
        <td class="px-4 py-3">
          <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">₹{{ Number((p as Product).price).toLocaleString() }}</p>
          <p v-if="(p as Product).sale_price" class="text-xs text-green-600">Sale: ₹{{ Number((p as Product).sale_price).toLocaleString() }}</p>
        </td>
        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ (p as Product).sold_count }}</td>
        <td class="px-4 py-3"><StatusBadge :status="(p as Product).status" /></td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button
              class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600"
              @click="router.push({ name: 'products.edit', params: { id: (p as Product).id } })"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-red-600"
              @click="deleteTarget = p as Product"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />

    <ConfirmModal
      v-if="deleteTarget"
      title="Delete Product"
      :message="`Delete '${deleteTarget.name}'? This cannot be undone.`"
      confirm-label="Delete"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>
