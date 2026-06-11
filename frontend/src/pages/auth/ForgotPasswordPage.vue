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
        <!-- Success State -->
        <template v-if="submitted">
          <div class="text-center py-4">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 mb-5">
              <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Check your email</h2>
            <p class="text-sm text-gray-500 mb-8 leading-relaxed">
              We've sent a password reset link to <strong class="text-gray-700">{{ submittedEmail }}</strong>. Please check your inbox (and spam folder).
            </p>
            <router-link
              :to="{ name: 'login' }"
              class="btn-primary inline-block px-6 py-2.5 font-semibold rounded-lg"
            >
              Back to Sign In
            </router-link>
          </div>
        </template>

        <!-- Form State -->
        <template v-else>
          <h2 class="text-2xl font-bold text-gray-900 mb-1">Forgot your password?</h2>
          <p class="text-sm text-gray-500 mb-8 leading-relaxed">
            Enter the email address linked to your account and we'll send you a reset link.
          </p>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            <span class="mt-0.5">&#9888;</span>
            <span>{{ errorMessage }}</span>
          </div>

          <form @submit.prevent="handleSubmit" novalidate>
            <div class="mb-7">
              <label for="email" class="label block mb-1.5">Email address</label>
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
                required
                placeholder="you@example.com"
                class="input w-full"
              />
            </div>

            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary w-full flex items-center justify-center gap-2 py-2.5 font-semibold rounded-lg disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <LoadingSpinner v-if="submitting" size="sm" />
              <span>{{ submitting ? 'Sending…' : 'Send reset link' }}</span>
            </button>
          </form>

          <p class="text-center text-sm text-gray-600 mt-6">
            Remembered your password?
            <router-link :to="{ name: 'login' }" class="font-semibold text-brand-600 hover:text-indigo-800 transition-colors ml-1">
              Sign in
            </router-link>
          </p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { authApi } from '@/api/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const email = ref('')
const submitting = ref(false)
const submitted = ref(false)
const submittedEmail = ref('')
const errorMessage = ref('')

async function handleSubmit() {
  errorMessage.value = ''
  submitting.value = true
  try {
    await authApi.forgotPassword(email.value)
    submittedEmail.value = email.value
    submitted.value = true
  } catch (err: any) {
    const msg =
      err?.response?.data?.message ||
      err?.message ||
      'Something went wrong. Please try again.'
    errorMessage.value = msg
  } finally {
    submitting.value = false
  }
}
</script>
