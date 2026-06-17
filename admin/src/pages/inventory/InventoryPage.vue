<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { inventoryApi } from '@/api/inventory'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import type { ProductVariant, StockMovement, PaginatedResponse, ListParams } from '@/types'
import { ArrowDownToLine, ArrowUpFromLine, SlidersHorizontal, Loader2, RefreshCw } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const tab = ref<'low_stock' | 'movements'>('low_stock')
const lowStockRes = ref<PaginatedResponse<ProductVariant> | null>(null)
const movementsRes = ref<PaginatedResponse<StockMovement> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 20 })

const stockModal = ref<{ type: 'in' | 'out' | 'adjust'; variant: ProductVariant } | null>(null)
const stockForm = reactive({ quantity: 1, new_stock: 0, note: '' })
const saving = ref(false)

async function loadLowStock() {
  loading.value = true
  try { const { data } = await inventoryApi.lowStock(params); lowStockRes.value = data }
  finally { loading.value = false }
}

async function loadMovements() {
  loading.value = true
  try { const { data } = await inventoryApi.movements(params); movementsRes.value = data }
  finally { loading.value = false }
}

watch([tab, params], () => {
  if (tab.value === 'low_stock') loadLowStock()
  else loadMovements()
}, { immediate: true })

function openStock(type: 'in' | 'out' | 'adjust', v: ProductVariant) {
  stockForm.quantity = 1
  stockForm.new_stock = v.stock
  stockForm.note = ''
  stockModal.value = { type, variant: v }
}

async function submitStock() {
  if (!stockModal.value) return
  saving.value = true
  try {
    const { type, variant } = stockModal.value
    if (type === 'in') await inventoryApi.stockIn({ variant_id: variant.id, quantity: stockForm.quantity, note: stockForm.note })
    else if (type === 'out') await inventoryApi.stockOut({ variant_id: variant.id, quantity: stockForm.quantity, note: stockForm.note })
    else await inventoryApi.adjust({ variant_id: variant.id, new_stock: stockForm.new_stock, note: stockForm.note })
    stockModal.value = null
    loadLowStock()
    loadMovements()
    Swal.fire({ icon: 'success', title: 'Stock updated!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    Swal.fire({ icon: 'error', title: err.response?.data?.message ?? 'Error updating stock.' })
  } finally { saving.value = false }
}

const typeLabel = { in: 'Stock In', out: 'Stock Out', adjust: 'Adjust Stock' }
const typeColor: Record<string, string> = {
  stock_in: 'text-green-600 dark:text-green-400',
  stock_out: 'text-red-600 dark:text-red-400',
  adjustment: 'text-yellow-600 dark:text-yellow-400',
}
</script>

<template>
  <div>
    <PageHeader title="Inventory" subtitle="Track and manage stock levels">
      <button class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700" @click="tab === 'low_stock' ? loadLowStock() : loadMovements()">
        <RefreshCw class="w-4 h-4" /> Refresh
      </button>
    </PageHeader>

    <!-- Tabs -->
    <div class="flex gap-1 mb-4 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
      <button :class="['px-4 py-1.5 rounded-md text-sm font-medium transition-colors', tab === 'low_stock' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700']" @click="tab = 'low_stock'; params.page = 1">Low Stock</button>
      <button :class="['px-4 py-1.5 rounded-md text-sm font-medium transition-colors', tab === 'movements' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700']" @click="tab = 'movements'; params.page = 1">Movement Log</button>
    </div>

    <!-- Low Stock Tab -->
    <template v-if="tab === 'low_stock'">
      <DataTable
        :columns="[{ key: 'product', label: 'Product' }, { key: 'variant', label: 'Variant' }, { key: 'stock', label: 'Stock' }, { key: 'actions', label: 'Actions' }]"
        :rows="lowStockRes?.data ?? []"
        :loading="loading"
        empty-text="No low stock variants."
      >
        <template #default="{ row: v }">
          <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
            {{ (v as ProductVariant & { product?: { name: string } }).product?.name ?? `Variant #${(v as ProductVariant).id}` }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ (v as ProductVariant).size }} / {{ (v as ProductVariant).color }}
          </td>
          <td class="px-4 py-3">
            <span :class="['text-sm font-bold', (v as ProductVariant).stock === 0 ? 'text-red-600' : (v as ProductVariant).stock < 3 ? 'text-orange-500' : 'text-yellow-600']">
              {{ (v as ProductVariant).stock }}
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-1">
              <button class="p-1.5 rounded hover:bg-green-50 dark:hover:bg-green-900/20 text-green-600" title="Stock In" @click="openStock('in', v as ProductVariant)"><ArrowDownToLine class="w-4 h-4" /></button>
              <button class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600" title="Stock Out" @click="openStock('out', v as ProductVariant)"><ArrowUpFromLine class="w-4 h-4" /></button>
              <button class="p-1.5 rounded hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-yellow-600" title="Adjust" @click="openStock('adjust', v as ProductVariant)"><SlidersHorizontal class="w-4 h-4" /></button>
            </div>
          </td>
        </template>
      </DataTable>
      <Pagination v-if="lowStockRes?.meta" :meta="lowStockRes.meta" @update:page="params.page = $event" />
    </template>

    <!-- Movements Tab -->
    <template v-else>
      <DataTable
        :columns="[{ key: 'type', label: 'Type' }, { key: 'variant', label: 'Variant' }, { key: 'quantity', label: 'Qty' }, { key: 'stock', label: 'Before → After' }, { key: 'note', label: 'Note' }, { key: 'date', label: 'Date' }]"
        :rows="movementsRes?.data ?? []"
        :loading="loading"
        empty-text="No movements recorded."
      >
        <template #default="{ row: m }">
          <td class="px-4 py-3 text-sm font-medium" :class="typeColor[(m as StockMovement).type]">
            {{ (m as StockMovement).type.replace('_', ' ').toUpperCase() }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
            {{ (m as StockMovement).variant?.product?.name ?? `#${(m as StockMovement).product_variant_id}` }}
            <span class="text-gray-400 text-xs"> · {{ (m as StockMovement).variant?.size }}/{{ (m as StockMovement).variant?.color }}</span>
          </td>
          <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ (m as StockMovement).quantity }}</td>
          <td class="px-4 py-3 text-sm text-gray-500">{{ (m as StockMovement).previous_stock }} → {{ (m as StockMovement).new_stock }}</td>
          <td class="px-4 py-3 text-xs text-gray-400 max-w-[160px] truncate">{{ (m as StockMovement).note ?? '—' }}</td>
          <td class="px-4 py-3 text-xs text-gray-400">{{ new Date((m as StockMovement).created_at).toLocaleString() }}</td>
        </template>
      </DataTable>
      <Pagination v-if="movementsRes?.meta" :meta="movementsRes.meta" @update:page="params.page = $event" />
    </template>

    <!-- Stock Modal -->
    <div v-if="stockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ typeLabel[stockModal.type] }}</h3>
        <p class="text-sm text-gray-500 mb-4">
          {{ stockModal.variant.size }} / {{ stockModal.variant.color }} — current stock: <strong>{{ stockModal.variant.stock }}</strong>
        </p>
        <form class="space-y-4" @submit.prevent="submitStock">
          <div v-if="stockModal.type !== 'adjust'">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
            <input v-model.number="stockForm.quantity" type="number" min="1" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div v-else>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Stock</label>
            <input v-model.number="stockForm.new_stock" type="number" min="0" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note (optional)</label>
            <input v-model="stockForm.note" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="flex gap-3 justify-end">
            <button type="button" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300" @click="stockModal = null">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" /> {{ saving ? 'Saving…' : 'Confirm' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
