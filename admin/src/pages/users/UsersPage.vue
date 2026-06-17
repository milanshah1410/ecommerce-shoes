<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { usersApi } from '@/api/users'
import PageHeader from '@/components/common/PageHeader.vue'
import DataTable from '@/components/common/DataTable.vue'
import Pagination from '@/components/common/Pagination.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import type { User, PaginatedResponse, ListParams } from '@/types'
import { UserPlus, Search, Pencil, Trash2, ToggleLeft, ToggleRight, Loader2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const response = ref<PaginatedResponse<User> | null>(null)
const loading = ref(false)
const params = reactive<ListParams>({ page: 1, per_page: 15, search: '' })
const showForm = ref(false)
const editUser = ref<User | null>(null)
const deleteTarget = ref<User | null>(null)
const saving = ref(false)

const form = reactive({
  first_name: '', last_name: '', email: '', mobile: '', role: 'customer' as User['role'], password: '',
})

async function load() {
  loading.value = true
  try {
    const { data } = await usersApi.list(params)
    response.value = data
  } finally {
    loading.value = false
  }
}

watch(params, load, { immediate: true })

function openCreate() {
  editUser.value = null
  Object.assign(form, { first_name: '', last_name: '', email: '', mobile: '', role: 'customer', password: '' })
  showForm.value = true
}

function openEdit(u: User) {
  editUser.value = u
  Object.assign(form, { first_name: u.first_name, last_name: u.last_name, email: u.email, mobile: u.mobile ?? '', role: u.role, password: '' })
  showForm.value = true
}

async function save() {
  saving.value = true
  try {
    if (editUser.value) {
      await usersApi.update(editUser.value.id, form)
    } else {
      await usersApi.create(form)
    }
    showForm.value = false
    load()
    Swal.fire({ icon: 'success', title: 'Saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    Swal.fire({ icon: 'error', title: err.response?.data?.message ?? 'Error saving user.' })
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await usersApi.delete(deleteTarget.value.id)
    deleteTarget.value = null
    load()
  } finally {
    saving.value = false
  }
}

async function toggleStatus(u: User) {
  const isBlocking = u.status === 'active'
  const { isConfirmed } = await Swal.fire({
    title: isBlocking ? `Block ${u.full_name}?` : `Unblock ${u.full_name}?`,
    text: isBlocking ? 'They will not be able to log in.' : 'Their access will be restored.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: isBlocking ? 'Block' : 'Unblock',
    confirmButtonColor: isBlocking ? '#dc2626' : '#16a34a',
  })
  if (!isConfirmed) return
  await usersApi.toggleStatus(u.id)
  load()
}

const roles: User['role'][] = ['super_admin', 'admin', 'manager', 'inventory_manager', 'sales_manager', 'customer_support', 'customer']
</script>

<template>
  <div>
    <PageHeader title="Users" subtitle="Manage admin and customer accounts">
      <button
        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors"
        @click="openCreate"
      >
        <UserPlus class="w-4 h-4" /> Add User
      </button>
    </PageHeader>

    <!-- Filters -->
    <div class="flex items-center gap-3 mb-4">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="params.search"
          type="text"
          placeholder="Search by name or email…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @input="params.page = 1"
        />
      </div>
      <select
        v-model="params.role"
        class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        @change="params.page = 1"
      >
        <option value="">All Roles</option>
        <option v-for="r in roles" :key="r" :value="r">{{ r.replace(/_/g, ' ') }}</option>
      </select>
    </div>

    <DataTable
      :columns="[
        { key: 'full_name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Role' },
        { key: 'status', label: 'Status' },
        { key: 'created_at', label: 'Joined' },
        { key: 'actions', label: '' },
      ]"
      :rows="response?.data ?? []"
      :loading="loading"
      empty-text="No users found."
    >
      <template #default="{ row: u }">
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-xs font-semibold">
              {{ (u as User).full_name?.charAt(0)?.toUpperCase() }}
            </div>
            <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">{{ (u as User).full_name }}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ (u as User).email }}</td>
        <td class="px-4 py-3">
          <span class="text-xs capitalize bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded-full">
            {{ (u as User).role?.replace(/_/g, ' ') }}
          </span>
        </td>
        <td class="px-4 py-3"><StatusBadge :status="(u as User).status" /></td>
        <td class="px-4 py-3 text-xs text-gray-400">{{ new Date((u as User).created_at).toLocaleDateString() }}</td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1 justify-end">
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" @click="toggleStatus(u as User)">
              <component :is="(u as User).status === 'active' ? ToggleRight : ToggleLeft" class="w-4 h-4" />
            </button>
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600" @click="openEdit(u as User)">
              <Pencil class="w-4 h-4" />
            </button>
            <button class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-red-600" @click="deleteTarget = u as User">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>
    </DataTable>

    <Pagination v-if="response?.meta" :meta="response.meta" @update:page="params.page = $event" />

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          {{ editUser ? 'Edit User' : 'Create User' }}
        </h3>
        <form class="space-y-4" autocomplete="off" @submit.prevent="save">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
              <input v-model="form.first_name" type="text" required class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
              <input v-model="form.last_name" type="text" class="input-field" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input v-model="form.email" type="email" required autocomplete="new-password" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
            <input v-model="form.mobile" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
            <select v-model="form.role" class="input-field">
              <option v-for="r in roles" :key="r" :value="r">{{ r.replace(/_/g, ' ') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Password <span v-if="editUser" class="text-gray-400 font-normal">(leave blank to keep)</span>
            </label>
            <input v-model="form.password" type="password" :required="!editUser" class="input-field" />
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" class="btn-secondary" @click="showForm = false">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
              {{ saving ? 'Saving…' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm delete -->
    <ConfirmModal
      v-if="deleteTarget"
      title="Delete User"
      :message="`Delete ${deleteTarget.full_name}? This cannot be undone.`"
      confirm-label="Delete"
      :loading="saving"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </div>
</template>

<style scoped>
@reference "tailwindcss";

.input-field {
  @apply w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500;
}
.btn-primary {
  @apply px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 transition-colors;
}
.btn-secondary {
  @apply px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors;
}
</style>
