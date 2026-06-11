<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <span class="text-4xl">👟</span>
        <h1 class="mt-2 text-2xl font-extrabold text-brand-600 tracking-tight">SoleStyle</h1>
      </div>

      <!-- Card -->
      <div class="card bg-white rounded-2xl shadow-lg border border-gray-100 px-8 py-10">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Create your account</h2>
        <p class="text-sm text-gray-500 mb-8">Join SoleStyle and start shopping today</p>

        <!-- General Error -->
        <div v-if="generalError" class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
          <span class="mt-0.5">&#9888;</span>
          <span>{{ generalError }}</span>
        </div>

        <form @submit.prevent="handleSubmit" novalidate>
          <!-- First Name + Last Name -->
          <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
              <label for="first_name" class="label block mb-1.5">First Name</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                autocomplete="given-name"
                required
                placeholder="Jane"
                class="input w-full"
                :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.first_name }"
              />
              <p v-if="fieldErrors.first_name" class="mt-1 text-xs text-red-600">{{ fieldErrors.first_name }}</p>
            </div>
            <div>
              <label for="last_name" class="label block mb-1.5">Last Name</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                autocomplete="family-name"
                required
                placeholder="Doe"
                class="input w-full"
                :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.last_name }"
              />
              <p v-if="fieldErrors.last_name" class="mt-1 text-xs text-red-600">{{ fieldErrors.last_name }}</p>
            </div>
          </div>

          <!-- Email -->
          <div class="mb-5">
            <label for="email" class="label block mb-1.5">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              placeholder="you@example.com"
              class="input w-full"
              :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.email }"
            />
            <p v-if="fieldErrors.email" class="mt-1 text-xs text-red-600">{{ fieldErrors.email }}</p>
          </div>

          <!-- Mobile (optional) -->
          <div class="mb-5">
            <label for="mobile" class="label block mb-1.5">
              Mobile
              <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
            </label>
            <input
              id="mobile"
              v-model="form.mobile"
              type="tel"
              autocomplete="tel"
              placeholder="+1 555 000 0000"
              class="input w-full"
              :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.mobile }"
            />
            <p v-if="fieldErrors.mobile" class="mt-1 text-xs text-red-600">{{ fieldErrors.mobile }}</p>
          </div>

          <!-- Password -->
          <div class="mb-5">
            <label for="password" class="label block mb-1.5">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              required
              placeholder="At least 8 characters"
              class="input w-full"
              :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.password }"
            />
            <p v-if="fieldErrors.password" class="mt-1 text-xs text-red-600">{{ fieldErrors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="mb-7">
            <label for="confirm_password" class="label block mb-1.5">Confirm Password</label>
            <input
              id="confirm_password"
              v-model="form.confirm_password"
              type="password"
              autocomplete="new-password"
              required
              placeholder="Repeat your password"
              class="input w-full"
              :class="{ 'border-red-500 focus:ring-red-500': fieldErrors.confirm_password || passwordMismatch }"
            />
            <p v-if="passwordMismatch" class="mt-1 text-xs text-red-600">Passwords do not match.</p>
            <p v-else-if="fieldErrors.confirm_password" class="mt-1 text-xs text-red-600">{{ fieldErrors.confirm_password }}</p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="submitting"
            class="btn-primary w-full flex items-center justify-center gap-2 py-2.5 font-semibold rounded-lg disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="submitting" size="sm" />
            <span>{{ submitting ? 'Creating account…' : 'Create account' }}</span>
          </button>
        </form>

        <!-- Login Link -->
        <p class="text-center text-sm text-gray-600 mt-6">
          Already have an account?
          <router-link :to="{ name: 'login' }" class="font-semibold text-brand-600 hover:text-indigo-800 transition-colors ml-1">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  mobile: '',
  password: '',
  confirm_password: '',
})

const submitting = ref(false)
const generalError = ref('')
const fieldErrors = reactive<Record<string, string>>({})

const passwordMismatch = computed(
  () => form.confirm_password.length > 0 && form.password !== form.confirm_password
)

function clearErrors() {
  generalError.value = ''
  Object.keys(fieldErrors).forEach((k) => delete fieldErrors[k])
}

async function handleSubmit() {
  if (passwordMismatch.value) return
  clearErrors()
  submitting.value = true
  try {
    await authStore.register({
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      mobile: form.mobile || undefined,
      password: form.password,
      password_confirmation: form.confirm_password,
    })
    router.push({ name: 'home' })
  } catch (err: any) {
    const errors = err?.response?.data?.errors
    if (errors && typeof errors === 'object') {
      Object.entries(errors).forEach(([field, messages]) => {
        fieldErrors[field] = Array.isArray(messages) ? (messages as string[])[0] : String(messages)
      })
    }
    const msg =
      err?.response?.data?.message ||
      err?.message ||
      'Registration failed. Please check the form and try again.'
    generalError.value = msg
  } finally {
    submitting.value = false
  }
}
</script>
