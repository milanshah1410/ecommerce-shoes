<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { reportsApi, type SalesReport, type ProductReport, type CustomerReport } from '@/api/reports'
import PageHeader from '@/components/common/PageHeader.vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ApexOptions } from 'apexcharts'
import { Download } from 'lucide-vue-next'

const tab = ref<'sales' | 'products' | 'customers' | 'inventory'>('sales')

const salesFilter = reactive({ from: '', to: '', group_by: 'day' as 'day' | 'week' | 'month' })
const salesData = ref<SalesReport[]>([])
const topProducts = ref<ProductReport[]>([])
const topCustomers = ref<CustomerReport[]>([])
const loading = ref(false)

// Default date range: last 30 days
function initDates() {
  const now = new Date()
  salesFilter.to = now.toISOString().slice(0, 10)
  const from = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
  salesFilter.from = from.toISOString().slice(0, 10)
}

onMounted(() => {
  initDates()
  loadSales()
  loadProducts()
  loadCustomers()
})

async function loadSales() {
  loading.value = true
  try { const { data } = await reportsApi.sales(salesFilter); salesData.value = data }
  catch {} finally { loading.value = false }
}

async function loadProducts() {
  try { const { data } = await reportsApi.topProducts({ limit: 10 }); topProducts.value = data } catch {}
}

async function loadCustomers() {
  try { const { data } = await reportsApi.topCustomers({ limit: 10 }); topCustomers.value = data } catch {}
}

const salesChartOptions = computed<ApexOptions>(() => ({
  chart: { type: 'bar' as const, toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#6366f1'],
  xaxis: { categories: salesData.value.map((d) => d.period), labels: { style: { colors: '#9ca3af', fontSize: '11px' }, rotate: -45 } },
  yaxis: { labels: { formatter: (v: number) => `₹${(v / 1000).toFixed(0)}k`, style: { colors: '#9ca3af', fontSize: '11px' } } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#e5e7eb', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
  tooltip: { y: { formatter: (v: number) => `₹${v.toLocaleString()}` } },
  plotOptions: { bar: { borderRadius: 4 } },
}))

const salesChartSeries = computed(() => [{ name: 'Revenue', data: salesData.value.map((d) => d.revenue) }])
</script>

<template>
  <div>
    <PageHeader title="Reports" subtitle="Analytics and business intelligence">
      <button class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
        <Download class="w-4 h-4" /> Export
      </button>
    </PageHeader>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
      <button v-for="t in ['sales', 'products', 'customers']" :key="t" :class="['px-4 py-1.5 rounded-md text-sm font-medium capitalize transition-colors', tab === t ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']" @click="tab = t as typeof tab">{{ t }}</button>
    </div>

    <!-- Sales tab -->
    <div v-if="tab === 'sales'" class="space-y-6">
      <!-- Filters -->
      <div class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">From</label>
          <input v-model="salesFilter.from" type="date" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">To</label>
          <input v-model="salesFilter.to" type="date" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Group By</label>
          <select v-model="salesFilter.group_by" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none">
            <option value="day">Day</option>
            <option value="week">Week</option>
            <option value="month">Month</option>
          </select>
        </div>
        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700" @click="loadSales">Apply</button>
      </div>

      <!-- Summary cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ salesData.reduce((s, d) => s + d.revenue, 0).toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 mb-1">Total Orders</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ salesData.reduce((s, d) => s + d.orders, 0).toLocaleString() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 mb-1">Avg Order Value</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            ₹{{ salesData.length ? (salesData.reduce((s, d) => s + d.revenue, 0) / salesData.reduce((s, d) => s + d.orders, 0) || 0).toFixed(0) : 0 }}
          </p>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Revenue Trend</h2>
        <VueApexCharts v-if="salesData.length" type="bar" height="300" :options="salesChartOptions" :series="salesChartSeries" />
        <div v-else class="h-64 flex items-center justify-center text-gray-400 text-sm">
          {{ loading ? 'Loading…' : 'No data for this period.' }}
        </div>
      </div>

      <!-- Data table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/40">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Period</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Orders</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Revenue</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Avg Order</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <tr v-for="row in salesData" :key="row.period" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ row.period }}</td>
              <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ row.orders }}</td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">₹{{ row.revenue.toLocaleString() }}</td>
              <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">₹{{ row.avg_order_value.toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Products tab -->
    <div v-if="tab === 'products'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/40">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sold</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Revenue</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
          <tr v-for="(p, i) in topProducts" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
            <td class="px-4 py-3 text-gray-400">{{ i + 1 }}</td>
            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ p.name }}</td>
            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ p.sold_count }}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">₹{{ p.revenue?.toLocaleString() ?? '—' }}</td>
            <td class="px-4 py-3 text-right" :class="p.stock < 5 ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-300'">{{ p.stock }}</td>
          </tr>
          <tr v-if="topProducts.length === 0">
            <td colspan="5" class="px-4 py-12 text-center text-gray-400">No product data.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Customers tab -->
    <div v-if="tab === 'customers'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/40">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Orders</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Spent</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
          <tr v-for="(c, i) in topCustomers" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
            <td class="px-4 py-3 text-gray-400">{{ i + 1 }}</td>
            <td class="px-4 py-3">
              <p class="font-medium text-gray-800 dark:text-gray-200">{{ c.full_name }}</p>
              <p class="text-xs text-gray-400">{{ c.email }}</p>
            </td>
            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ c.orders_count }}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">₹{{ c.total_spent?.toLocaleString() }}</td>
          </tr>
          <tr v-if="topCustomers.length === 0">
            <td colspan="4" class="px-4 py-12 text-center text-gray-400">No customer data.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
