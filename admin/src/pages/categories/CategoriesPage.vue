<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { categoriesApi } from '@/api/categories'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import type { Category, PaginatedResponse, ListParams } from '@/types'
import { Plus, Pencil, Trash2, Loader2, ImagePlus } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const response = ref<PaginatedResponse<Category> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15 })
const showForm = ref(false)
const editItem = ref<Category | null>(null)
const deleteTarget = ref<Category | null>(null)
const saving = ref(false)
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const form = reactive({ name: '', status: 'active' as 'active' | 'inactive' })

async function load() {
  loading.value = true
  try { const { data } = await categoriesApi.list(params); response.value = data }
  finally { loading.value = false }
}

watch(params, load, { immediate: true })

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', status: 'active' })
  imageFile.value = null; imagePreview.value = null
  showForm.value = true
}

function openEdit(c: Category) {
  editItem.value = c
  Object.assign(form, { name: c.name, status: c.status })
  imagePreview.value = c.image ?? null; imageFile.value = null
  showForm.value = true
}

function handleImage(e: Event) {
  const f = (e.target as HTMLInputElement).files?.[0]
  if (!f) return
  imageFile.value = f
  imagePreview.value = URL.createObjectURL(f)
}

async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    fd.append('name', form.name)
    fd.append('status', form.status)
    if (imageFile.value) fd.append('image', imageFile.value)
    if (editItem.value) { await categoriesApi.update(editItem.value.id, fd) }
    else { await categoriesApi.create(fd) }
    showForm.value = false; load()
    Swal.fire({ icon: 'success', title: 'Saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch { Swal.fire({ icon: 'error', title: 'Error saving category.' }) }
  finally { saving.value = false }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try { await categoriesApi.delete(deleteTarget.value.id); deleteTarget.value = null; load() }
  finally { saving.value = false }
}
</script>

<template>
  <div>
    <PageHeader title="Categories" subtitle="Manage product categories">
      <button class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700" @click="openCreate">
        <Plus class="w-4 h-4" /> Add Category
      </button>
    </PageHeader>

    <DataTable
      :columns="[{ key: 'name', label: 'Name' }, { key: 'products_count', label: 'Products' }, { key: 'is_active', label: 'Active' }, { key: 'actions', label: '' }]"
      :rows="response?.data ?? []"
      :loading="loading"
    >
      <template #default="{ row: c }">
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            <img v-if="(c as Category).image" :src="(c as Category).image!" class="w-8 h-8 rounded-lg object-cover" />
            <div v-else class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700" />
            <span class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ (c as Category).name }}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ (c as Category).products_count ?? '—' }}</td>
        <td class="px-4 py-3">
          <span :class="['text-xs px-2 py-1 rounded-full font-medium', (c as Category).status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-500']">
            {{ (c as Category).status === 'active' ? 'Active' : 'Inactive' }}
          </span>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" @click="openEdit(c as Category)"><Pencil class="w-4 h-4" /></button>
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-red-600" @click="deleteTarget = c as Category"><Trash2 class="w-4 h-4" /></button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ editItem ? 'Edit Category' : 'Add Category' }}</h3>
        <form class="space-y-4" @submit.prevent="save">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
            <input v-model="form.name" type="text" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image</label>
            <label class="flex items-center gap-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 cursor-pointer hover:border-indigo-400">
              <img v-if="imagePreview" :src="imagePreview" class="w-12 h-12 rounded object-cover" />
              <div v-else class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center"><ImagePlus class="w-5 h-5 text-gray-400" /></div>
              <span class="text-sm text-gray-500">Upload image</span>
              <input type="file" accept="image/*" class="hidden" @change="handleImage" />
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" @click="showForm = false">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
              {{ saving ? 'Saving…' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmModal v-if="deleteTarget" title="Delete Category" :message="`Delete '${deleteTarget.name}'?`" :loading="saving" @confirm="confirmDelete" @cancel="deleteTarget = null" />
  </div>
</template>
