<script setup lang="ts">
import PageHeader from '@/components/common/PageHeader.vue'
import { ShieldCheck, Users, Warehouse, ShoppingCart, BarChart3, Headphones, UserCircle } from 'lucide-vue-next'

const roles = [
  {
    name: 'Super Admin',
    slug: 'super_admin',
    icon: ShieldCheck,
    color: 'indigo',
    description: 'Full system access including user management, settings and all modules.',
    permissions: ['All Permissions'],
  },
  {
    name: 'Admin',
    slug: 'admin',
    icon: ShieldCheck,
    color: 'blue',
    description: 'Operational access to all modules except system configuration.',
    permissions: ['users', 'products', 'orders', 'inventory', 'customers', 'coupons', 'reports', 'settings'],
  },
  {
    name: 'Manager',
    slug: 'manager',
    icon: Users,
    color: 'purple',
    description: 'Manage products, inventory, orders and generate reports.',
    permissions: ['products', 'categories', 'brands', 'inventory', 'orders', 'customers', 'reviews', 'reports'],
  },
  {
    name: 'Inventory Manager',
    slug: 'inventory_manager',
    icon: Warehouse,
    color: 'yellow',
    description: 'Manage stock levels, adjustments and inventory logs.',
    permissions: ['inventory', 'products (view only)'],
  },
  {
    name: 'Sales Manager',
    slug: 'sales_manager',
    icon: ShoppingCart,
    color: 'green',
    description: 'Access orders, sales reports, customers and coupons.',
    permissions: ['orders', 'reports', 'customers', 'coupons'],
  },
  {
    name: 'Customer Support',
    slug: 'customer_support',
    icon: Headphones,
    color: 'cyan',
    description: 'Manage customer queries, orders and review moderation.',
    permissions: ['orders', 'customers', 'reviews'],
  },
  {
    name: 'Customer',
    slug: 'customer',
    icon: UserCircle,
    color: 'gray',
    description: 'Frontend access only. Cannot log into admin panel.',
    permissions: ['storefront only'],
  },
]

const colorMap: Record<string, string> = {
  indigo: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
  blue: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
  purple: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
  yellow: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
  green: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
  cyan: 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300',
  gray: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
}
</script>

<template>
  <div>
    <PageHeader title="Roles & Permissions" subtitle="Role-based access control configuration" />

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="role in roles"
        :key="role.slug"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5"
      >
        <div class="flex items-center gap-3 mb-3">
          <div :class="['p-2 rounded-lg', colorMap[role.color]]">
            <component :is="role.icon" class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ role.name }}</h3>
            <code class="text-xs text-gray-400">{{ role.slug }}</code>
          </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ role.description }}</p>
        <div class="flex flex-wrap gap-1.5">
          <span
            v-for="perm in role.permissions"
            :key="perm"
            class="text-xs bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded"
          >
            {{ perm }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
