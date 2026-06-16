<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { settingsApi } from '@/api/settings'
import PageHeader from '@/components/common/PageHeader.vue'
import type { AppSettings } from '@/types'
import { Loader2, ImagePlus } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const loading = ref(true)
const saving = ref(false)
const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)
const tab = ref<'general' | 'payment' | 'shipping' | 'email'>('general')

const form = reactive<AppSettings>({
  store_name: '', store_email: '', store_phone: '', store_address: '',
  currency: 'INR', currency_symbol: '₹', tax_rate: 0,
  shipping_charge: 0, free_shipping_threshold: 0, logo: null,
  razorpay_key: '', stripe_key: '',
  smtp_host: '', smtp_port: 587, smtp_user: '', smtp_from_name: '',
})

onMounted(async () => {
  try {
    const { data } = await settingsApi.get()
    Object.assign(form, data)
    logoPreview.value = data.logo ?? null
  } catch {} finally { loading.value = false }
})

function handleLogo(e: Event) {
  const f = (e.target as HTMLInputElement).files?.[0]
  if (!f) return
  logoFile.value = f
  logoPreview.value = URL.createObjectURL(f)
}

async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    Object.entries(form).forEach(([k, v]) => { if (v !== null && v !== undefined) fd.append(k, String(v)) })
    if (logoFile.value) fd.append('logo', logoFile.value)
    await settingsApi.update(fd)
    Swal.fire({ icon: 'success', title: 'Settings saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch { Swal.fire({ icon: 'error', title: 'Failed to save settings.' }) }
  finally { saving.value = false }
}
</script>

<template>
  <div>
    <PageHeader title="Settings" subtitle="Configure your store">
      <button :disabled="saving" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-60" @click="save">
        <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
        {{ saving ? 'Saving…' : 'Save Settings' }}
      </button>
    </PageHeader>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
    </div>

    <div v-else class="grid grid-cols-1 xl:grid-cols-4 gap-6">
      <!-- Tab nav -->
      <div class="xl:col-span-1">
        <nav class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 space-y-1">
          <button v-for="t in [{ key: 'general', label: 'General' }, { key: 'payment', label: 'Payment' }, { key: 'shipping', label: 'Shipping' }, { key: 'email', label: 'Email / SMTP' }]" :key="t.key" :class="['w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors', tab === t.key ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700']" @click="tab = t.key as typeof tab">{{ t.label }}</button>
        </nav>
      </div>

      <!-- Tab content -->
      <div class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <!-- General -->
        <div v-if="tab === 'general'" class="space-y-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">General Settings</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Store Logo</label>
            <label class="flex items-center gap-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 cursor-pointer hover:border-indigo-400 w-fit">
              <img v-if="logoPreview" :src="logoPreview" class="h-12 object-contain" />
              <div v-else class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center"><ImagePlus class="w-5 h-5 text-gray-400" /></div>
              <span class="text-sm text-gray-500">Upload logo</span>
              <input type="file" accept="image/*" class="hidden" @change="handleLogo" />
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Store Name</label>
            <input v-model="form.store_name" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Store Email</label>
              <input v-model="form.store_email" type="email" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Store Phone</label>
              <input v-model="form.store_phone" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Store Address</label>
            <textarea v-model="form.store_address" rows="2" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Currency Code</label>
              <input v-model="form.currency" type="text" maxlength="3" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Symbol</label>
              <input v-model="form.currency_symbol" type="text" maxlength="5" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tax Rate (%)</label>
              <input v-model.number="form.tax_rate" type="number" step="0.1" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
        </div>

        <!-- Payment -->
        <div v-if="tab === 'payment'" class="space-y-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Payment Gateways</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Razorpay Key ID</label>
            <input v-model="form.razorpay_key" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Stripe Publishable Key</label>
            <input v-model="form.stripe_key" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" />
          </div>
        </div>

        <!-- Shipping -->
        <div v-if="tab === 'shipping'" class="space-y-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Shipping Settings</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Default Shipping Charge (₹)</label>
              <input v-model.number="form.shipping_charge" type="number" step="0.01" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Free Shipping Above (₹)</label>
              <input v-model.number="form.free_shipping_threshold" type="number" step="0.01" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
        </div>

        <!-- Email -->
        <div v-if="tab === 'email'" class="space-y-4">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">SMTP Configuration</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Host</label>
              <input v-model="form.smtp_host" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Port</label>
              <input v-model.number="form.smtp_port" type="number" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Username</label>
            <input v-model="form.smtp_user" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">From Name</label>
            <input v-model="form.smtp_from_name" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
