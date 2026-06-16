<script setup lang="ts" generic="T extends Record<string, unknown>">
export interface Column<T> {
  key: keyof T | string
  label: string
  class?: string
  sortable?: boolean
}

defineProps<{
  columns: Column<T>[]
  rows: T[]
  loading?: boolean
  emptyText?: string
}>()

const emit = defineEmits<{
  sort: [key: string]
}>()
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
      <thead class="bg-gray-50 dark:bg-gray-900/40">
        <tr>
          <th
            v-for="col in columns"
            :key="String(col.key)"
            :class="[
              'px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider text-xs',
              col.class,
              col.sortable ? 'cursor-pointer select-none hover:text-gray-900 dark:hover:text-white' : '',
            ]"
            @click="col.sortable ? emit('sort', String(col.key)) : undefined"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
        <template v-if="loading">
          <tr v-for="n in 5" :key="n">
            <td v-for="col in columns" :key="String(col.key)" class="px-4 py-3">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
            </td>
          </tr>
        </template>
        <template v-else-if="rows.length === 0">
          <tr>
            <td :colspan="columns.length" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
              {{ emptyText ?? 'No records found.' }}
            </td>
          </tr>
        </template>
        <template v-else>
          <tr
            v-for="(row, idx) in rows"
            :key="idx"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
          >
            <slot :row="row" :idx="idx" />
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>
