<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import client from '@/api/client'
import PageHeader from '@/components/common/PageHeader.vue'
import { Loader2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const auth = useAuthStore()
const saving = ref(false)

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  mobile: '',
})

function populateForm() {
  if (!auth.user) return
  Object.assign(form, {
    first_name: auth.user.first_name ?? '',
    last_name: auth.user.last_name ?? '',
    email: auth.user.email ?? '',
    mobile: (auth.user as any).mobile ?? '',
  })
}

// Bug #5: user may not be loaded yet when this page mounts
onMounted(async () => {
  if (!auth.user) await auth.fetchUser()
  populateForm()
})

watch(() => auth.user, populateForm)

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const savingPass = ref(false)

async function saveProfile() {
  saving.value = true
  try {
    await client.put('/profile', form)
    Swal.fire({ icon: 'success', title: 'Profile updated!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
    await auth.fetchUser()
  } catch { Swal.fire({ icon: 'error', title: 'Failed to update.' }) }
  finally { saving.value = false }
}

async function changePassword() {
  savingPass.value = true
  try {
    await client.put('/profile/password', passwordForm)
    Object.assign(passwordForm, { current_password: '', password: '', password_confirmation: '' })
    Swal.fire({ icon: 'success', title: 'Password changed!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    Swal.fire({ icon: 'error', title: err.response?.data?.message ?? 'Failed to change password.' })
  } finally { savingPass.value = false }
}
</script>

<template>
  <div>
    <PageHeader title="Profile" subtitle="Manage your account settings" />
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Profile info -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h2>
        <form class="space-y-4" @submit.prevent="saveProfile">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
              <input v-model="form.first_name" type="text" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
              <input v-model="form.last_name" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
            <input v-model="form.mobile" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <button type="submit" :disabled="saving" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" /> {{ saving ? 'Saving…' : 'Save Changes' }}
          </button>
        </form>
      </div>

      <!-- Change password -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
        <form class="space-y-4" @submit.prevent="changePassword">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
            <input v-model="passwordForm.current_password" type="password" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
            <input v-model="passwordForm.password" type="password" required minlength="8" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
            <input v-model="passwordForm.password_confirmation" type="password" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <button type="submit" :disabled="savingPass" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
            <Loader2 v-if="savingPass" class="w-4 h-4 animate-spin" /> {{ savingPass ? 'Updating…' : 'Change Password' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
