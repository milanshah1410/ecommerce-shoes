<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { couponsApi } from '@/api/coupons'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import type { Coupon, PaginatedResponse, ListParams } from '@/types'
import { Plus, Pencil, Trash2, Loader2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const response = ref<PaginatedResponse<Coupon> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15 })
const showForm = ref(false)
const editItem = ref<Coupon | null>(null)
const deleteTarget = ref<Coupon | null>(null)
const saving = ref(false)

const form = reactive<Partial<Coupon>>({
  code: '', type: 'percentage', discount_value: '10',
  min_order_amount: '', max_discount: '',
  start_date: '', end_date: '', usage_limit: undefined,
  status: 'active',
})

async function load() {
  loading.value = true
  try { const { data } = await couponsApi.list(params); response.value = data }
  finally { loading.value = false }
}

watch(params, load, { immediate: true })

function openCreate() {
  editItem.value = null
  Object.assign(form, { code: '', type: 'percentage', discount_value: '10', min_order_amount: '', max_discount: '', start_date: '', end_date: '', usage_limit: undefined, status: 'active' })
  showForm.value = true
}

function openEdit(c: Coupon) {
  editItem.value = c
  Object.assign(form, { ...c, start_date: c.start_date?.slice(0, 10) ?? '', end_date: c.end_date?.slice(0, 10) ?? '' })
  showForm.value = true
}

async function save() {
  saving.value = true
  try {
    if (editItem.value) await couponsApi.update(editItem.value.id, form)
    else await couponsApi.create(form)
    showForm.value = false; load()
    Swal.fire({ icon: 'success', title: 'Saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    Swal.fire({ icon: 'error', title: err.response?.data?.message ?? 'Error.' })
  } finally { saving.value = false }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try { await couponsApi.delete(deleteTarget.value.id); deleteTarget.value = null; load() }
  finally { saving.value = false }
}
</script>

<template>
  <div>
    <PageHeader title="Coupons" subtitle="Manage discount coupons">
      <button class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700" @click="openCreate">
        <Plus class="w-4 h-4" /> Add Coupon
      </button>
    </PageHeader>

    <DataTable
      :columns="[{ key: 'code', label: 'Code' }, { key: 'type', label: 'Type' }, { key: 'value', label: 'Discount' }, { key: 'used', label: 'Used' }, { key: 'valid', label: 'Validity' }, { key: 'status', label: 'Status' }, { key: 'actions', label: '' }]"
      :rows="response?.data ?? []"
      :loading="loading"
    >
      <template #default="{ row: c }">
        <td class="px-4 py-3 font-mono font-bold text-sm text-indigo-700 dark:text-indigo-400">{{ (c as Coupon).code }}</td>
        <td class="px-4 py-3 text-sm capitalize text-gray-600 dark:text-gray-300">{{ (c as Coupon).type }}</td>
        <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">
          {{ (c as Coupon).type === 'percentage' ? `${(c as Coupon).discount_value}%` : `₹${(c as Coupon).discount_value}` }}
        </td>
        <td class="px-4 py-3 text-sm text-gray-500">{{ (c as Coupon).used_count }} / {{ (c as Coupon).usage_limit ?? '∞' }}</td>
        <td class="px-4 py-3 text-xs text-gray-400">
          <span v-if="(c as Coupon).end_date">Until {{ new Date((c as Coupon).end_date!).toLocaleDateString() }}</span>
          <span v-else>No expiry</span>
        </td>
        <td class="px-4 py-3"><StatusBadge :status="(c as Coupon).status" /></td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" @click="openEdit(c as Coupon)"><Pencil class="w-4 h-4" /></button>
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-red-600" @click="deleteTarget = c as Coupon"><Trash2 class="w-4 h-4" /></button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ editItem ? 'Edit Coupon' : 'Create Coupon' }}</h3>
        <form class="space-y-4" @submit.prevent="save">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
              <input v-model="form.code" type="text" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono uppercase" style="text-transform: uppercase" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
              <select v-model="form.type" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount (₹)</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Value</label>
              <input v-model="form.discount_value" type="number" step="0.01" min="0" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Discount (₹)</label>
              <input v-model="form.max_discount" type="number" step="0.01" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Order Amount (₹)</label>
            <input v-model="form.min_order_amount" type="number" step="0.01" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
              <input v-model="form.start_date" type="date" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
              <input v-model="form.end_date" type="date" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usage Limit</label>
              <input v-model.number="form.usage_limit" type="number" min="1" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Unlimited" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300" @click="showForm = false">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" /> {{ saving ? 'Saving…' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmModal v-if="deleteTarget" title="Delete Coupon" :message="`Delete coupon '${deleteTarget.code}'?`" :loading="saving" @confirm="confirmDelete" @cancel="deleteTarget = null" />
  </div>
</template>
