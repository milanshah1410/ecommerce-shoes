<script setup lang="ts">
defineProps<{
  title?: string
  message?: string
  confirmLabel?: string
  loading?: boolean
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="emit('cancel')">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ title ?? 'Confirm Action' }}
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        {{ message ?? 'Are you sure you want to proceed? This action cannot be undone.' }}
      </p>
      <div class="flex gap-3 justify-end">
        <button
          type="button"
          class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          @click="emit('cancel')"
        >
          Cancel
        </button>
        <button
          type="button"
          :disabled="loading"
          class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 transition-colors"
          @click="emit('confirm')"
        >
          {{ loading ? 'Processing…' : (confirmLabel ?? 'Confirm') }}
        </button>
      </div>
    </div>
  </div>
</template>
