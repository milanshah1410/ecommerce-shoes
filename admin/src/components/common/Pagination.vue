<script setup lang="ts">
import type { PaginationMeta } from '@/types'
import { computed } from 'vue'

const props = defineProps<{ meta: PaginationMeta }>()
const emit = defineEmits<{ 'update:page': [page: number] }>()

const pages = computed(() => {
  const total = props.meta.last_page
  const current = props.meta.current_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages: (number | '...')[] = [1]
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})
</script>

<template>
  <div class="flex items-center justify-between px-1 mt-4 text-sm">
    <span class="text-gray-500 dark:text-gray-400">
      Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }} results
    </span>
    <div class="flex items-center gap-1">
      <button
        :disabled="meta.current_page === 1"
        class="px-3 py-1.5 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        @click="emit('update:page', meta.current_page - 1)"
      >
        Prev
      </button>
      <template v-for="(p, i) in pages" :key="i">
        <span v-if="p === '...'" class="px-2 text-gray-400">…</span>
        <button
          v-else
          :class="[
            'px-3 py-1.5 rounded border text-sm transition-colors',
            p === meta.current_page
              ? 'bg-indigo-600 text-white border-indigo-600'
              : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
          ]"
          @click="emit('update:page', p as number)"
        >
          {{ p }}
        </button>
      </template>
      <button
        :disabled="meta.current_page === meta.last_page"
        class="px-3 py-1.5 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        @click="emit('update:page', meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
