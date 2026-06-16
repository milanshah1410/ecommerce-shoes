<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import KpiCard from '@/components/cards/KpiCard.vue'
import RevenueChart from '@/components/charts/RevenueChart.vue'
import OrderStatusChart from '@/components/charts/OrderStatusChart.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import { DollarSign, ShoppingBag, Users, Package, Clock, CheckCircle, TrendingDown, RotateCcw } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'

const store = useDashboardStore()
onMounted(() => store.fetchStats())

const fmt = (n: number) => n?.toLocaleString('en-IN') ?? '0'
const fmtCurrency = (n: number) => `₹${n?.toLocaleString('en-IN', { minimumFractionDigits: 0 }) ?? '0'}`
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Welcome back! Here's what's happening today.</p>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <KpiCard
        title="Total Revenue"
        :value="fmtCurrency(store.stats?.total_revenue ?? 0)"
        subtitle="All-time paid orders"
        color="indigo"
        :loading="store.loading"
      >
        <template #icon>
          <DollarSign class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Total Orders"
        :value="fmt(store.stats?.total_orders ?? 0)"
        subtitle="All time"
        color="blue"
        :loading="store.loading"
      >
        <template #icon>
          <ShoppingBag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Customers"
        :value="fmt(store.stats?.total_customers ?? 0)"
        subtitle="Registered users"
        color="green"
        :loading="store.loading"
      >
        <template #icon>
          <Users class="w-5 h-5 text-green-600 dark:text-green-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Products"
        :value="fmt(store.stats?.total_products ?? 0)"
        subtitle="In catalogue"
        color="purple"
        :loading="store.loading"
      >
        <template #icon>
          <Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Pending Orders"
        :value="fmt(store.stats?.pending_orders ?? 0)"
        subtitle="Awaiting action"
        color="yellow"
        :loading="store.loading"
      >
        <template #icon>
          <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Delivered"
        :value="fmt(store.stats?.delivered_orders ?? 0)"
        subtitle="Successfully delivered"
        color="green"
        :loading="store.loading"
      >
        <template #icon>
          <CheckCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Low Stock"
        :value="fmt(store.stats?.low_stock_count ?? (store.stats?.low_stock?.length ?? 0))"
        subtitle="Variants below 5 units"
        color="red"
        :loading="store.loading"
      >
        <template #icon>
          <TrendingDown class="w-5 h-5 text-red-600 dark:text-red-400" />
        </template>
      </KpiCard>

      <KpiCard
        title="Today's Revenue"
        :value="fmtCurrency(store.stats?.today_revenue ?? 0)"
        subtitle="Paid orders today"
        color="indigo"
        :loading="store.loading"
      >
        <template #icon>
          <RotateCcw class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
        </template>
      </KpiCard>
    </div>

    <!-- Charts row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Revenue chart -->
      <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Monthly Revenue</h2>
        <RevenueChart
          v-if="store.stats?.monthly_revenue?.length"
          :data="store.stats.monthly_revenue"
        />
        <div v-else-if="store.loading" class="h-64 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-gray-400 text-sm">No revenue data.</div>
      </div>

      <!-- Order status donut -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Status</h2>
        <OrderStatusChart
          v-if="store.stats?.order_statuses && Object.keys(store.stats.order_statuses).length"
          :statuses="store.stats.order_statuses"
        />
        <div v-else-if="store.loading" class="h-56 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
        </div>
        <div v-else class="h-56 flex items-center justify-center text-gray-400 text-sm">No data.</div>
      </div>
    </div>

    <!-- Bottom row: Recent Orders + Top Products -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent orders -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Orders</h2>
          <RouterLink :to="{ name: 'orders' }" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
            View all →
          </RouterLink>
        </div>
        <div class="space-y-3">
          <div
            v-for="order in (store.stats?.recent_orders ?? []).slice(0, 8)"
            :key="order.id"
            class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0"
          >
            <div>
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200">#{{ order.order_number }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.customer ?? 'Guest' }}</p>
            </div>
            <div class="flex items-center gap-3">
              <StatusBadge :status="order.status" />
              <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">₹{{ order.total_amount?.toLocaleString() }}</span>
            </div>
          </div>
          <div v-if="!store.stats?.recent_orders?.length && !store.loading" class="text-center py-6 text-gray-400 text-sm">
            No orders yet.
          </div>
        </div>
      </div>

      <!-- Top products -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Top Products</h2>
          <RouterLink :to="{ name: 'products' }" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
            View all →
          </RouterLink>
        </div>
        <div class="space-y-3">
          <div
            v-for="(product, i) in store.stats?.top_products ?? []"
            :key="product.id"
            class="flex items-center gap-3"
          >
            <span class="w-6 text-sm font-semibold text-gray-400">{{ i + 1 }}</span>
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
              <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ product.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.sold_count }} sold</p>
            </div>
          </div>
          <div v-if="!store.stats?.top_products?.length && !store.loading" class="text-center py-6 text-gray-400 text-sm">
            No products data.
          </div>
        </div>
      </div>
    </div>

    <!-- Low stock alert -->
    <div v-if="store.stats?.low_stock?.length" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-xl p-5">
      <h2 class="text-base font-semibold text-red-800 dark:text-red-300 mb-3">⚠ Low Stock Alert</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
        <div
          v-for="v in store.stats.low_stock"
          :key="v.id"
          class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-red-100 dark:border-red-800/30"
        >
          <div>
            <p class="text-xs font-medium text-gray-700 dark:text-gray-200">
              Variant #{{ v.id }} — {{ v.size }} / {{ v.color }}
            </p>
          </div>
          <span class="text-xs font-bold text-red-600 dark:text-red-400">{{ v.stock }} left</span>
        </div>
      </div>
    </div>
  </div>
</template>
