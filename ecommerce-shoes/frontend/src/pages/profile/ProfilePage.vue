<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { profileApi } from '@/api/profile'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()

const activeTab = ref<'dashboard' | 'personal' | 'password' | 'addresses'>('dashboard')

const dashboardData = ref<any>(null)
const dashboardLoading = ref(false)

const personalForm = reactive({
  first_name: '',
  last_name: '',
  mobile: '',
  email: '',
})
const personalLoading = ref(false)

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: '',
})
const passwordLoading = ref(false)

const addresses = ref<any[]>([])
const addressesLoading = ref(false)
const showAddressForm = ref(false)
const editingAddress = ref<any>(null)

const addressForm = reactive({
  full_name: '',
  mobile: '',
  address_line: '',
  city: '',
  state: '',
  country: '',
  pincode: '',
  type: 'home',
  is_default: false,
})
const addressFormLoading = ref(false)

const tabs = [
  { key: 'dashboard', label: 'Dashboard', icon: 'grid' },
  { key: 'personal', label: 'Personal Info', icon: 'user' },
  { key: 'password', label: 'Change Password', icon: 'lock' },
  { key: 'addresses', label: 'Manage Addresses', icon: 'map-pin' },
] as const

async function loadDashboard() {
  dashboardLoading.value = true
  try {
    const res = await profileApi.dashboard()
    dashboardData.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    dashboardLoading.value = false
  }
}

async function loadAddresses() {
  addressesLoading.value = true
  try {
    const { data: addrRes } = await profileApi.addresses()
    addresses.value = addrRes.data
  } catch (e) {
    console.error(e)
  } finally {
    addressesLoading.value = false
  }
}

async function savePersonal() {
  personalLoading.value = true
  try {
    await profileApi.update({
      first_name: personalForm.first_name,
      last_name: personalForm.last_name,
      mobile: personalForm.mobile,
    })
    alert('Profile updated successfully!')
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Failed to update profile.')
  } finally {
    personalLoading.value = false
  }
}

async function savePassword() {
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    alert('New passwords do not match.')
    return
  }
  passwordLoading.value = true
  try {
    await profileApi.changePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.new_password,
      password_confirmation: passwordForm.confirm_password,
    })
    alert('Password changed successfully!')
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.confirm_password = ''
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Failed to change password.')
  } finally {
    passwordLoading.value = false
  }
}

function openAddressForm(addr?: any) {
  if (addr) {
    editingAddress.value = addr
    Object.assign(addressForm, {
      full_name: addr.full_name,
      mobile: addr.mobile,
      address_line: addr.address_line,
      city: addr.city,
      state: addr.state,
      country: addr.country,
      pincode: addr.pincode,
      type: addr.type,
      is_default: addr.is_default,
    })
  } else {
    editingAddress.value = null
    Object.assign(addressForm, {
      full_name: '',
      mobile: '',
      address_line: '',
      city: '',
      state: '',
      country: '',
      pincode: '',
      type: 'home',
      is_default: false,
    })
  }
  showAddressForm.value = true
}

async function saveAddress() {
  addressFormLoading.value = true
  try {
    if (editingAddress.value) {
      await profileApi.updateAddress(editingAddress.value.id, { ...addressForm })
    } else {
      await profileApi.addAddress({ ...addressForm })
    }
    alert(editingAddress.value ? 'Address updated!' : 'Address added!')
    showAddressForm.value = false
    await loadAddresses()
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Failed to save address.')
  } finally {
    addressFormLoading.value = false
  }
}

async function deleteAddress(id: number) {
  if (!confirm('Delete this address?')) return
  try {
    await profileApi.deleteAddress(id)
    await loadAddresses()
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Failed to delete address.')
  }
}

function selectTab(tab: typeof activeTab.value) {
  activeTab.value = tab
  if (tab === 'addresses' && addresses.value.length === 0) {
    loadAddresses()
  }
}

onMounted(async () => {
  await loadDashboard()
  if (authStore.user) {
    personalForm.first_name = authStore.user.first_name ?? ''
    personalForm.last_name = authStore.user.last_name ?? ''
    personalForm.mobile = (authStore.user as any).mobile ?? ''
    personalForm.email = authStore.user.email ?? ''
  }
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Account</h1>

    <div class="flex flex-col md:flex-row gap-6">
      <!-- Sidebar -->
      <aside class="w-full md:w-64 shrink-0">
        <div class="card p-2">
          <nav class="space-y-1">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="selectTab(tab.key)"
              :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors text-left',
                activeTab === tab.key
                  ? 'bg-brand-600 text-white'
                  : 'text-gray-600 hover:bg-gray-100',
              ]"
            >
              <!-- grid icon -->
              <svg v-if="tab.icon === 'grid'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              <!-- user icon -->
              <svg v-if="tab.icon === 'user'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <!-- lock icon -->
              <svg v-if="tab.icon === 'lock'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <!-- map-pin icon -->
              <svg v-if="tab.icon === 'map-pin'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ tab.label }}
            </button>
          </nav>
        </div>
      </aside>

      <!-- Main content -->
      <main class="flex-1">

        <!-- Dashboard Tab -->
        <div v-if="activeTab === 'dashboard'">
          <div v-if="dashboardLoading" class="flex justify-center py-12">
            <LoadingSpinner />
          </div>
          <div v-else-if="dashboardData">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
              <div class="card p-6 text-center">
                <p class="text-3xl font-bold text-brand-600">{{ dashboardData.total_orders ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Orders</p>
              </div>
              <div class="card p-6 text-center">
                <p class="text-3xl font-bold text-brand-600">{{ dashboardData.wishlist_count ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Items in Wishlist</p>
              </div>
              <div class="card p-6 text-center">
                <p class="text-3xl font-bold text-brand-600">{{ dashboardData.coupons_used ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Coupons Used</p>
              </div>
            </div>

            <!-- Recent Orders -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h2>
              <div v-if="dashboardData.recent_orders && dashboardData.recent_orders.length > 0" class="space-y-3">
                <div
                  v-for="order in dashboardData.recent_orders.slice(0, 3)"
                  :key="order.id"
                  class="flex items-center justify-between py-3 border-b last:border-0"
                >
                  <div>
                    <p class="font-medium text-gray-800">#{{ order.order_number }}</p>
                    <p class="text-xs text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                  </div>
                  <div class="text-right">
                    <span
                      :class="[
                        'inline-block px-2 py-1 rounded-full text-xs font-medium',
                        order.status === 'delivered' ? 'bg-green-100 text-green-700' :
                        order.status === 'shipped' ? 'bg-indigo-100 text-indigo-700' :
                        order.status === 'processing' ? 'bg-blue-100 text-blue-700' :
                        order.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                        order.status === 'cancelled' ? 'bg-red-100 text-red-700' :
                        'bg-gray-100 text-gray-700'
                      ]"
                    >
                      {{ order.status }}
                    </span>
                    <p class="text-sm font-semibold text-gray-800 mt-1">${{ order.total }}</p>
                  </div>
                </div>
              </div>
              <p v-else class="text-gray-500 text-sm">No recent orders.</p>
            </div>
          </div>
        </div>

        <!-- Personal Info Tab -->
        <div v-if="activeTab === 'personal'">
          <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Personal Information</h2>
            <form @submit.prevent="savePersonal" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="label">First Name</label>
                  <input v-model="personalForm.first_name" class="input" type="text" placeholder="First name" required />
                </div>
                <div>
                  <label class="label">Last Name</label>
                  <input v-model="personalForm.last_name" class="input" type="text" placeholder="Last name" required />
                </div>
              </div>
              <div>
                <label class="label">Mobile</label>
                <input v-model="personalForm.mobile" class="input" type="tel" placeholder="Mobile number" />
              </div>
              <div>
                <label class="label">Email</label>
                <input v-model="personalForm.email" class="input bg-gray-50 cursor-not-allowed" type="email" readonly />
                <p class="text-xs text-gray-400 mt-1">Email cannot be changed.</p>
              </div>
              <div class="pt-2">
                <button type="submit" class="btn-primary" :disabled="personalLoading">
                  <span v-if="personalLoading">Saving...</span>
                  <span v-else>Save Changes</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password Tab -->
        <div v-if="activeTab === 'password'">
          <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Change Password</h2>
            <form @submit.prevent="savePassword" class="space-y-4">
              <div>
                <label class="label">Current Password</label>
                <input v-model="passwordForm.current_password" class="input" type="password" placeholder="Enter current password" required />
              </div>
              <div>
                <label class="label">New Password</label>
                <input v-model="passwordForm.new_password" class="input" type="password" placeholder="Enter new password" required />
              </div>
              <div>
                <label class="label">Confirm New Password</label>
                <input v-model="passwordForm.confirm_password" class="input" type="password" placeholder="Confirm new password" required />
              </div>
              <div class="pt-2">
                <button type="submit" class="btn-primary" :disabled="passwordLoading">
                  <span v-if="passwordLoading">Updating...</span>
                  <span v-else>Update Password</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Manage Addresses Tab -->
        <div v-if="activeTab === 'addresses'">
          <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-800">Saved Addresses</h2>
              <button class="btn-primary text-sm" @click="openAddressForm()">
                + Add New Address
              </button>
            </div>

            <div v-if="addressesLoading" class="flex justify-center py-8">
              <LoadingSpinner />
            </div>

            <!-- Address List -->
            <div v-else-if="addresses.length > 0 && !showAddressForm" class="space-y-4">
              <div
                v-for="addr in addresses"
                :key="addr.id"
                class="border rounded-lg p-4 relative"
              >
                <div class="flex items-start justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-semibold text-gray-800">{{ addr.full_name }}</span>
                      <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize">{{ addr.type }}</span>
                      <span v-if="addr.is_default" class="text-xs px-2 py-0.5 rounded-full bg-brand-600 text-white">Default</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ addr.address_line }}</p>
                    <p class="text-sm text-gray-600">{{ addr.city }}, {{ addr.state }}, {{ addr.country }} - {{ addr.pincode }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ addr.mobile }}</p>
                  </div>
                  <div class="flex gap-2 shrink-0">
                    <button @click="openAddressForm(addr)" class="btn-outline text-xs px-3 py-1.5">Edit</button>
                    <button @click="deleteAddress(addr.id)" class="text-xs px-3 py-1.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                  </div>
                </div>
              </div>
            </div>

            <p v-else-if="!showAddressForm" class="text-gray-500 text-sm text-center py-6">No addresses saved yet.</p>

            <!-- Address Form -->
            <div v-if="showAddressForm" class="border rounded-lg p-4 mt-4">
              <h3 class="text-md font-semibold text-gray-800 mb-4">{{ editingAddress ? 'Edit Address' : 'New Address' }}</h3>
              <form @submit.prevent="saveAddress" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Full Name</label>
                    <input v-model="addressForm.full_name" class="input" type="text" required />
                  </div>
                  <div>
                    <label class="label">Mobile</label>
                    <input v-model="addressForm.mobile" class="input" type="tel" required />
                  </div>
                </div>
                <div>
                  <label class="label">Address Line</label>
                  <input v-model="addressForm.address_line" class="input" type="text" required />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">City</label>
                    <input v-model="addressForm.city" class="input" type="text" required />
                  </div>
                  <div>
                    <label class="label">State</label>
                    <input v-model="addressForm.state" class="input" type="text" required />
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Country</label>
                    <input v-model="addressForm.country" class="input" type="text" required />
                  </div>
                  <div>
                    <label class="label">Pincode</label>
                    <input v-model="addressForm.pincode" class="input" type="text" required />
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Type</label>
                    <select v-model="addressForm.type" class="input">
                      <option value="home">Home</option>
                      <option value="work">Work</option>
                    </select>
                  </div>
                  <div class="flex items-center gap-2 mt-6">
                    <input v-model="addressForm.is_default" id="is_default" type="checkbox" class="w-4 h-4 accent-brand-600" />
                    <label for="is_default" class="text-sm text-gray-700">Set as default</label>
                  </div>
                </div>
                <div class="flex gap-3 pt-2">
                  <button type="submit" class="btn-primary" :disabled="addressFormLoading">
                    <span v-if="addressFormLoading">Saving...</span>
                    <span v-else>{{ editingAddress ? 'Update Address' : 'Save Address' }}</span>
                  </button>
                  <button type="button" class="btn-outline" @click="showAddressForm = false">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>
</template>
