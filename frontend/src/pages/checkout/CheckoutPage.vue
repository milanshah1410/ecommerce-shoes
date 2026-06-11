<template>
  <div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Page Heading -->
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Checkout</h1>
        <p class="mt-1 text-sm text-gray-500">Complete your purchase below</p>
      </div>

      <!-- Loading State -->
      <div v-if="pageLoading" class="flex items-center justify-center py-32">
        <LoadingSpinner size="lg" />
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Checkout Form -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Section A: Shipping Address -->
          <div class="card bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold">1</span>
              Shipping Address
            </h2>

            <!-- Toggle: saved vs new -->
            <div v-if="savedAddresses.length" class="flex gap-3 mb-5">
              <button
                @click="useNewAddress = false"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-semibold border transition-colors',
                  !useNewAddress
                    ? 'bg-brand-600 text-white border-brand-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-brand-600',
                ]"
              >
                Use Saved Address
              </button>
              <button
                @click="useNewAddress = true"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-semibold border transition-colors',
                  useNewAddress
                    ? 'bg-brand-600 text-white border-brand-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-brand-600',
                ]"
              >
                Enter New Address
              </button>
            </div>

            <!-- Saved Address Dropdown -->
            <div v-if="!useNewAddress && savedAddresses.length" class="mb-4">
              <label class="label block mb-1.5">Select Address</label>
              <select v-model="selectedAddressId" class="input w-full">
                <option value="" disabled>-- Select a saved address --</option>
                <option
                  v-for="addr in savedAddresses"
                  :key="addr.id"
                  :value="addr.id"
                >
                  {{ addr.full_name }} — {{ addr.address_line }}, {{ addr.city }}, {{ addr.state }} {{ addr.pincode }}
                </option>
              </select>
              <p v-if="errors.address" class="mt-1 text-xs text-red-600">{{ errors.address }}</p>
            </div>

            <!-- New Address Form -->
            <div v-if="useNewAddress || !savedAddresses.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2 sm:grid sm:grid-cols-2 sm:gap-4">
                <div>
                  <label class="label block mb-1.5">Full Name <span class="text-red-500">*</span></label>
                  <input v-model="form.name" type="text" placeholder="John Doe" class="input w-full" />
                  <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                </div>
                <div>
                  <label class="label block mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                  <input v-model="form.mobile" type="tel" placeholder="+91 98765 43210" class="input w-full" />
                  <p v-if="errors.mobile" class="mt-1 text-xs text-red-600">{{ errors.mobile }}</p>
                </div>
              </div>
              <div class="sm:col-span-2">
                <label class="label block mb-1.5">Address Line <span class="text-red-500">*</span></label>
                <input v-model="form.address_line" type="text" placeholder="House / Flat no., Street, Locality" class="input w-full" />
                <p v-if="errors.address_line" class="mt-1 text-xs text-red-600">{{ errors.address_line }}</p>
              </div>
              <div>
                <label class="label block mb-1.5">City <span class="text-red-500">*</span></label>
                <input v-model="form.city" type="text" placeholder="Mumbai" class="input w-full" />
                <p v-if="errors.city" class="mt-1 text-xs text-red-600">{{ errors.city }}</p>
              </div>
              <div>
                <label class="label block mb-1.5">State <span class="text-red-500">*</span></label>
                <input v-model="form.state" type="text" placeholder="Maharashtra" class="input w-full" />
                <p v-if="errors.state" class="mt-1 text-xs text-red-600">{{ errors.state }}</p>
              </div>
              <div>
                <label class="label block mb-1.5">Country <span class="text-red-500">*</span></label>
                <input v-model="form.country" type="text" placeholder="India" class="input w-full" />
                <p v-if="errors.country" class="mt-1 text-xs text-red-600">{{ errors.country }}</p>
              </div>
              <div>
                <label class="label block mb-1.5">Pincode <span class="text-red-500">*</span></label>
                <input v-model="form.pincode" type="text" placeholder="400001" maxlength="10" class="input w-full" />
                <p v-if="errors.pincode" class="mt-1 text-xs text-red-600">{{ errors.pincode }}</p>
              </div>
            </div>
          </div>

          <!-- Section B: Payment Method -->
          <div class="card bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold">2</span>
              Payment Method
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <!-- COD -->
              <label
                :class="[
                  'relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all',
                  paymentMethod === 'cod'
                    ? 'border-brand-600 bg-indigo-50 shadow-sm'
                    : 'border-gray-200 hover:border-gray-300 bg-white',
                ]"
              >
                <input v-model="paymentMethod" type="radio" value="cod" class="sr-only" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm font-semibold text-gray-800">Cash on Delivery</span>
                <span class="text-xs text-gray-500 text-center">Pay when delivered</span>
                <span v-if="paymentMethod === 'cod'" class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center rounded-full bg-brand-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>
              </label>

              <!-- Razorpay -->
              <label
                :class="[
                  'relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all',
                  paymentMethod === 'razorpay'
                    ? 'border-brand-600 bg-indigo-50 shadow-sm'
                    : 'border-gray-200 hover:border-gray-300 bg-white',
                ]"
              >
                <input v-model="paymentMethod" type="radio" value="razorpay" class="sr-only" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="text-sm font-semibold text-gray-800">Razorpay</span>
                <span class="text-xs text-gray-500 text-center">UPI / Cards / Netbanking</span>
                <span v-if="paymentMethod === 'razorpay'" class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center rounded-full bg-brand-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>
              </label>

              <!-- Stripe -->
              <label
                :class="[
                  'relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all',
                  paymentMethod === 'stripe'
                    ? 'border-brand-600 bg-indigo-50 shadow-sm'
                    : 'border-gray-200 hover:border-gray-300 bg-white',
                ]"
              >
                <input v-model="paymentMethod" type="radio" value="stripe" class="sr-only" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <span class="text-sm font-semibold text-gray-800">Stripe</span>
                <span class="text-xs text-gray-500 text-center">International cards</span>
                <span v-if="paymentMethod === 'stripe'" class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center rounded-full bg-brand-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
            </div>
            <p v-if="errors.paymentMethod" class="mt-2 text-xs text-red-600">{{ errors.paymentMethod }}</p>
          </div>

          <!-- Section C: Order Notes -->
          <div class="card bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold">3</span>
              Order Notes
              <span class="text-xs text-gray-400 font-normal">(optional)</span>
            </h2>
            <label class="label block mb-1.5">Special instructions for your order</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="e.g. Leave at the door, ring the bell twice..."
              class="input w-full resize-none"
            ></textarea>
          </div>

        </div>

        <!-- Right: Order Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-24 card bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Order Summary</h2>

            <!-- Item List -->
            <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
              <div
                v-for="item in cartStore.items"
                :key="item.id"
                class="flex items-center gap-3"
              >
                <div class="flex-none w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                  <img
                    v-if="item.product?.thumbnail"
                    :src="item.product.thumbnail"
                    :alt="item.product.name"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-lg">
                    👟
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ item.product.name }}</p>
                  <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                </div>
                <span class="text-sm font-semibold text-gray-900 flex-none">{{ formatCurrency(item.line_total) }}</span>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100"></div>

            <!-- Price Breakdown -->
            <div class="space-y-2 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(summary.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Shipping</span>
                <span class="font-medium text-gray-900">{{ summary.shipping === 0 ? 'FREE' : formatCurrency(summary.shipping) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Tax</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(summary.tax) }}</span>
              </div>
              <div v-if="summary.discount" class="flex justify-between text-green-600">
                <span>Discount</span>
                <span class="font-medium">-{{ formatCurrency(summary.discount) }}</span>
              </div>
            </div>

            <!-- Total -->
            <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
              <span class="text-base font-bold text-gray-900">Total</span>
              <span class="text-xl font-extrabold text-brand-600">{{ formatCurrency(summary.total) }}</span>
            </div>

            <!-- Global Error -->
            <div v-if="submitError" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
              {{ submitError }}
            </div>

            <!-- Place Order Button -->
            <button
              @click="placeOrder"
              :disabled="submitting"
              class="btn-primary w-full py-3 font-semibold rounded-lg text-base flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <LoadingSpinner v-if="submitting" size="sm" />
              <span>{{ submitting ? 'Placing Order…' : 'Place Order' }}</span>
            </button>

            <!-- Secure -->
            <p class="text-xs text-gray-400 text-center flex items-center justify-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
              Secure & encrypted checkout
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { orderApi } from '@/api/order'
import { profileApi } from '@/api/profile'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import type { Address } from '@/types'
import Swal from 'sweetalert2'

const router = useRouter()
const cartStore = useCartStore()

const pageLoading = ref(true)
const submitting = ref(false)
const submitError = ref('')
const savedAddresses = ref<Address[]>([])
const useNewAddress = ref(false)
const selectedAddressId = ref<number | string>('')
const paymentMethod = ref<'cod' | 'razorpay' | 'stripe'>('cod')

const form = reactive({
  name: '',
  mobile: '',
  address_line: '',
  city: '',
  state: '',
  country: 'India',
  pincode: '',
  notes: '',
})

const errors = reactive<Record<string, string>>({})

const summary = computed(() => {
  const s = cartStore.summary
  return {
    subtotal: s?.subtotal ?? 0,
    shipping: s?.shipping ?? 0,
    tax: s?.tax ?? 0,
    discount: s?.discount ?? 0,
    total: s?.total ?? 0,
  }
})

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(value)
}

function clearErrors() {
  Object.keys(errors).forEach(k => delete errors[k])
  submitError.value = ''
}

function validate(): boolean {
  clearErrors()
  let valid = true

  if (!useNewAddress.value && savedAddresses.value.length) {
    if (!selectedAddressId.value) {
      errors.address = 'Please select a saved address.'
      valid = false
    }
  } else {
    if (!form.name.trim()) { errors.name = 'Full name is required.'; valid = false }
    if (!form.mobile.trim()) { errors.mobile = 'Mobile number is required.'; valid = false }
    if (!form.address_line.trim()) { errors.address_line = 'Address is required.'; valid = false }
    if (!form.city.trim()) { errors.city = 'City is required.'; valid = false }
    if (!form.state.trim()) { errors.state = 'State is required.'; valid = false }
    if (!form.country.trim()) { errors.country = 'Country is required.'; valid = false }
    if (!form.pincode.trim()) { errors.pincode = 'Pincode is required.'; valid = false }
  }

  if (!paymentMethod.value) {
    errors.paymentMethod = 'Please select a payment method.'
    valid = false
  }

  return valid
}

function buildShippingAddress() {
  if (!useNewAddress.value && savedAddresses.value.length) {
    return savedAddresses.value.find((a: Address) => a.id === selectedAddressId.value) ?? null
  }
  return {
    name: form.name,
    mobile: form.mobile,
    address_line: form.address_line,
    city: form.city,
    state: form.state,
    country: form.country,
    pincode: form.pincode,
  }
}

async function placeOrder() {
  if (!validate()) return

  submitting.value = true
  submitError.value = ''

  try {
    const payload = {
      shipping_address: buildShippingAddress(),
      payment_method: paymentMethod.value,
      notes: form.notes.trim() || undefined,
    }

    const { data: orderRes } = await orderApi.create(payload as any)

    if (paymentMethod.value !== 'cod') {
      await Swal.fire({
        icon: 'info',
        title: 'Coming Soon!',
        text: 'Payment integration coming soon! Order placed as COD.',
        confirmButtonColor: '#4f46e5',
      })
    }

    await cartStore.clear()
    router.push({ name: 'order-success', params: { orderId: orderRes.data.id } })
  } catch (err: any) {
    const msg = err?.response?.data?.message ?? err?.message ?? 'Failed to place order. Please try again.'
    submitError.value = msg
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    await cartStore.fetch()

    if (!cartStore.items.length) {
      router.push({ name: 'cart' })
      return
    }

    try {
      const { data: addrRes } = await profileApi.addresses()
      savedAddresses.value = addrRes.data ?? []
      if (!savedAddresses.value.length) {
        useNewAddress.value = true
      }
    } catch {
      useNewAddress.value = true
    }
  } finally {
    pageLoading.value = false
  }
})
</script>
