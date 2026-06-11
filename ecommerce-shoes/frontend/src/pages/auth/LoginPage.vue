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
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h2>
        <p class="text-sm text-gray-500 mb-8">Sign in to your account to continue</p>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
          <span class="mt-0.5">&#9888;</span>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSubmit" novalidate>
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
            />
          </div>

          <!-- Password -->
          <div class="mb-5">
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="label">Password</label>
              <router-link to="/forgot-password" class="text-xs text-brand-600 hover:text-indigo-800 font-medium transition-colors">
                Forgot password?
              </router-link>
            </div>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              placeholder="••••••••"
              class="input w-full"
            />
          </div>

          <!-- Remember Me -->
          <div class="flex items-center gap-2 mb-7">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-brand-600 border-gray-300 rounded focus:ring-brand-600"
            />
            <label for="remember" class="text-sm text-gray-600 cursor-pointer select-none">Remember me</label>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="submitting"
            class="btn-primary w-full flex items-center justify-center gap-2 py-2.5 font-semibold rounded-lg disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="submitting" size="sm" />
            <span>{{ submitting ? 'Signing in…' : 'Sign in' }}</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-7">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-3 bg-white text-gray-400">or</span>
          </div>
        </div>

        <!-- Register Link -->
        <p class="text-center text-sm text-gray-600">
          Don't have an account?
          <router-link :to="{ name: 'register' }" class="font-semibold text-brand-600 hover:text-indigo-800 transition-colors ml-1">
            Create account
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const submitting = ref(false)
const errorMessage = ref('')

async function handleSubmit() {
  errorMessage.value = ''
  submitting.value = true
  try {
    await authStore.login({ email: form.email, password: form.password })
    const redirect = route.query.redirect as string | undefined
    router.push(redirect ? { path: redirect } : { name: 'home' })
  } catch (err: any) {
    const msg =
      err?.response?.data?.message ||
      err?.message ||
      'Invalid credentials. Please try again.'
    errorMessage.value = msg
  } finally {
    submitting.value = false
  }
}
</script>
