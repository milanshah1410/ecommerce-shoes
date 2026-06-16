<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { reviewsApi } from '@/api/reviews'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import type { Review, PaginatedResponse, ListParams } from '@/types'
import { CheckCircle, XCircle, Trash2, Star } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const response = ref<PaginatedResponse<Review> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15, status: '' })
const deleteTarget = ref<Review | null>(null)
const saving = ref(false)

async function load() {
  loading.value = true
  try { const { data } = await reviewsApi.list(params); response.value = data }
  finally { loading.value = false }
}

watch(params, load, { immediate: true })

async function approve(r: Review) {
  await reviewsApi.approve(r.id); load()
  Swal.fire({ icon: 'success', title: 'Approved', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 })
}

async function reject(r: Review) {
  await reviewsApi.reject(r.id); load()
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try { await reviewsApi.delete(deleteTarget.value.id); deleteTarget.value = null; load() }
  finally { saving.value = false }
}
</script>

<template>
  <div>
    <PageHeader title="Reviews" subtitle="Moderate customer reviews">
      <select v-model="params.status" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none" @change="params.page = 1">
        <option value="">All Reviews</option>
        <option value="approved">Approved</option>
        <option value="pending">Pending</option>
        <option value="rejected">Rejected</option>
      </select>
    </PageHeader>

    <DataTable
      :columns="[{ key: 'product', label: 'Product' }, { key: 'customer', label: 'Customer' }, { key: 'rating', label: 'Rating' }, { key: 'review', label: 'Review' }, { key: 'status', label: 'Status' }, { key: 'actions', label: '' }]"
      :rows="response?.data ?? []"
      :loading="loading"
    >
      <template #default="{ row: r }">
        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200 max-w-[150px] truncate">{{ (r as Review).product?.name ?? `#${(r as Review).product_id}` }}</td>
        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ (r as Review).user?.full_name }}</td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1">
            <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ (r as Review).rating }}/5</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-[200px] truncate">
          <span class="font-medium">{{ (r as Review).title }}</span>
          <span v-if="(r as Review).review" class="ml-1 text-gray-400"> — {{ (r as Review).review }}</span>
        </td>
        <td class="px-4 py-3">
          <span :class="['text-xs px-2 py-1 rounded-full font-medium', (r as Review).status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : (r as Review).status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300']">
            {{ (r as Review).status }}
          </span>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button v-if="(r as Review).status !== 'approved'" class="p-1.5 rounded hover:bg-green-50 dark:hover:bg-green-900/20 text-green-600" title="Approve" @click="approve(r as Review)"><CheckCircle class="w-4 h-4" /></button>
            <button v-if="(r as Review).status === 'approved'" class="p-1.5 rounded hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-yellow-600" title="Reject" @click="reject(r as Review)"><XCircle class="w-4 h-4" /></button>
            <button class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600" title="Delete" @click="deleteTarget = r as Review"><Trash2 class="w-4 h-4" /></button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />

    <ConfirmModal v-if="deleteTarget" title="Delete Review" message="Permanently delete this review?" :loading="saving" @confirm="confirmDelete" @cancel="deleteTarget = null" />
  </div>
</template>
