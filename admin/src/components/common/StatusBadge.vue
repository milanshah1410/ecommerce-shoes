<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: string; type?: 'order' | 'payment' | 'generic' }>()

const config = computed<{ label: string; class: string }>(() => {
  const s = props.status?.toLowerCase()
  const map: Record<string, { label: string; class: string }> = {
    // Order statuses
    pending:    { label: 'Pending',    class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' },
    confirmed:  { label: 'Confirmed',  class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' },
    processing: { label: 'Processing', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' },
    packed:     { label: 'Packed',     class: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300' },
    shipped:    { label: 'Shipped',    class: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300' },
    delivered:  { label: 'Delivered',  class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    cancelled:  { label: 'Cancelled',  class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
    returned:   { label: 'Returned',   class: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' },
    refunded:   { label: 'Refunded',   class: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300' },
    // Generic
    active:     { label: 'Active',     class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    inactive:   { label: 'Inactive',   class: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' },
    blocked:    { label: 'Blocked',    class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' },
    draft:      { label: 'Draft',      class: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' },
    paid:       { label: 'Paid',       class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    failed:     { label: 'Failed',     class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
    approved:   { label: 'Approved',   class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    rejected:   { label: 'Rejected',   class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
  }
  return map[s] ?? { label: props.status, class: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }
})
</script>

<template>
  <span
    :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize', config.class]"
  >
    {{ config.label }}
  </span>
</template>
