<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  subtitle?: string
  color?: 'indigo' | 'green' | 'yellow' | 'red' | 'blue' | 'purple'
  loading?: boolean
}>()
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex flex-col gap-3">
    <div class="flex items-start justify-between">
      <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ title }}</span>
      <div
        :class="[
          'rounded-lg p-2',
          color === 'green'  ? 'bg-green-100 dark:bg-green-900/30' :
          color === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/30' :
          color === 'red'    ? 'bg-red-100 dark:bg-red-900/30' :
          color === 'blue'   ? 'bg-blue-100 dark:bg-blue-900/30' :
          color === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30' :
                               'bg-indigo-100 dark:bg-indigo-900/30',
        ]"
      >
        <slot name="icon" />
      </div>
    </div>
    <div>
      <div v-if="loading" class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
      <p v-else class="text-2xl font-bold text-gray-900 dark:text-white">{{ value }}</p>
      <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ subtitle }}</p>
    </div>
  </div>
</template>
