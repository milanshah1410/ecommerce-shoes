<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useWishlistStore } from '@/stores/wishlist'
import ProductCard from '@/components/product/ProductCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const wishlistStore = useWishlistStore()

const loading = ref(false)
const movingAll = ref(false)

const items = computed(() => wishlistStore.items)
const count = computed(() => items.value.length)

async function fetchWishlist() {
  loading.value = true
  try {
    await wishlistStore.fetch()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function moveAllToCart() {
  if (!items.value.length) return
  movingAll.value = true
  try {
    for (const item of [...items.value]) {
      await wishlistStore.moveToCart(item.id)
    }
  } catch (e) {
    console.error(e)
  } finally {
    movingAll.value = false
  }
}

onMounted(fetchWishlist)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">My Wishlist</h1>
        <p class="text-sm text-gray-500 mt-1">{{ count }} {{ count === 1 ? 'item' : 'items' }}</p>
      </div>
      <button
        v-if="count > 0"
        class="btn-dark flex items-center gap-2"
        :disabled="movingAll"
        @click="moveAllToCart"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span v-if="movingAll">Moving...</span>
        <span v-else>Move All to Cart</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <LoadingSpinner />
    </div>

    <!-- Grid -->
    <div
      v-else-if="count > 0"
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6"
    >
      <ProductCard
        v-for="item in items"
        :key="item.id"
        :product="item"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-24">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
      <h2 class="text-xl font-semibold text-gray-500 mb-2">No items in your wishlist</h2>
      <p class="text-gray-400 mb-6">Save items you love and come back to them later.</p>
      <button class="btn-primary" @click="router.push({ name: 'products' })">
        Explore Products
      </button>
    </div>

  </div>
</template>
