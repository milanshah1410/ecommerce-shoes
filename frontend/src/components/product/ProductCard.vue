<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import type { Product } from '@/types'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'

const props = defineProps<{ product: Product }>()

const router = useRouter()
const cart = useCartStore()
const wishlist = useWishlistStore()

const addingToCart = ref(false)

function goToProduct() {
  router.push(`/products/${props.product.slug}`)
}

async function addToCart(e: Event) {
  e.stopPropagation()
  if (addingToCart.value) return
  addingToCart.value = true
  try {
    await cart.add(props.product.id)
  } finally {
    addingToCart.value = false
  }
}

async function toggleWishlist(e: Event) {
  e.stopPropagation()
  await wishlist.toggle(props.product)
}
</script>

<template>
  <div
    class="card group flex cursor-pointer flex-col overflow-hidden transition hover:shadow-md"
    @click="goToProduct"
  >
    <!-- Image -->
    <div class="relative overflow-hidden bg-gray-100">
      <img
        v-if="product.thumbnail"
        :src="product.thumbnail"
        :alt="product.name"
        class="h-56 w-full object-cover transition-transform duration-300 group-hover:scale-105"
      />
      <div
        v-else
        class="flex h-56 w-full items-center justify-center bg-gray-100"
      >
        <svg
          class="h-16 w-16 text-gray-300"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1"
            d="M3 10h1l1-4h14l1 4h1a1 1 0 010 2H3a1 1 0 010-2zm2 6h14M7 16v2m10-2v2"
          />
        </svg>
      </div>

      <!-- Discount badge -->
      <span
        v-if="product.discount_percent > 0"
        class="absolute left-2 top-2 rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white"
      >
        -{{ product.discount_percent }}%
      </span>

      <!-- Wishlist button -->
      <button
        class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow transition hover:scale-110"
        :class="wishlist.isWishlisted(product.id) ? 'text-red-500' : 'text-gray-400 hover:text-red-400'"
        aria-label="Toggle wishlist"
        @click="toggleWishlist"
      >
        <svg
          class="h-4 w-4"
          :fill="wishlist.isWishlisted(product.id) ? 'currentColor' : 'none'"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
          />
        </svg>
      </button>
    </div>

    <!-- Info -->
    <div class="flex flex-1 flex-col gap-1 p-3">
      <!-- Brand -->
      <span v-if="product.brand" class="text-xs font-medium uppercase tracking-wide text-gray-400">
        {{ product.brand.name }}
      </span>

      <!-- Name -->
      <h3 class="truncate text-sm font-semibold text-gray-800">{{ product.name }}</h3>

      <!-- Rating -->
      <div class="flex items-center gap-1">
        <div class="flex">
          <svg
            v-for="star in 5"
            :key="star"
            class="h-3.5 w-3.5"
            :class="star <= Math.round(product.rating) ? 'text-amber-400' : 'text-gray-200'"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
            />
          </svg>
        </div>
        <span class="text-xs text-gray-400">({{ product.reviews_count }})</span>
      </div>

      <!-- Price -->
      <div class="mt-1 flex items-baseline gap-2">
        <span class="text-base font-bold text-brand-600">
          ₹{{ product.effective_price.toLocaleString() }}
        </span>
        <span
          v-if="product.sale_price && product.price !== product.effective_price"
          class="text-xs text-gray-400 line-through"
        >
          ₹{{ product.price.toLocaleString() }}
        </span>
      </div>

      <!-- Add to cart -->
      <button
        class="btn-primary mt-2 w-full text-xs"
        :disabled="addingToCart"
        @click="addToCart"
      >
        <svg
          v-if="addingToCart"
          class="h-4 w-4 animate-spin"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
          />
        </svg>
        <svg
          v-else
          class="h-4 w-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
          />
        </svg>
        {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
      </button>
    </div>
  </div>
</template>
