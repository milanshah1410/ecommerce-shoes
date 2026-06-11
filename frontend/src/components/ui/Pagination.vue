<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  current: number
  last: number
}>()

const emit = defineEmits<{
  change: [page: number]
}>()

function go(page: number) {
  if (page < 1 || page > props.last || page === props.current) return
  emit('change', page)
}

const pages = computed<(number | '...')[]>(() => {
  const { current, last } = props
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)

  const result: (number | '...')[] = [1]

  if (current > 3) result.push('...')

  const start = Math.max(2, current - 1)
  const end = Math.min(last - 1, current + 1)

  for (let i = start; i <= end; i++) result.push(i)

  if (current < last - 2) result.push('...')
  result.push(last)

  return result
})
</script>

<template>
  <nav
    v-if="last > 1"
    class="flex items-center justify-center gap-1"
    aria-label="Pagination"
  >
    <!-- Previous -->
    <button
      class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
      :disabled="current === 1"
      aria-label="Previous page"
      @click="go(current - 1)"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <!-- Page numbers -->
    <template v-for="(page, i) in pages" :key="i">
      <span
        v-if="page === '...'"
        class="flex h-9 w-9 items-center justify-center text-sm text-gray-400"
      >
        &hellip;
      </span>
      <button
        v-else
        class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-medium transition"
        :class="
          page === current
            ? 'border-brand-600 bg-brand-600 text-white'
            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
        "
        :aria-current="page === current ? 'page' : undefined"
        @click="go(page)"
      >
        {{ page }}
      </button>
    </template>

    <!-- Next -->
    <button
      class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
      :disabled="current === last"
      aria-label="Next page"
      @click="go(current + 1)"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </nav>
</template>
