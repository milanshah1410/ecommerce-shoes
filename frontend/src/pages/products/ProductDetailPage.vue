<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import ProductCard from '@/components/product/ProductCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { productApi } from '@/api/product'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useAuthStore } from '@/stores/auth'
import type { Product, ProductVariant, ProductImage, Review } from '@/types'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
const authStore = useAuthStore()

const loading = ref(true)
const addingToCart = ref(false)
const product = ref<Product | null>(null)
const relatedProducts = ref<Product[]>([])
const reviews = ref<Review[]>([])

const activeImage = ref<ProductImage | null>(null)
const zoomOpen = ref(false)
const selectedSize = ref<string | null>(null)
const selectedVariant = ref<ProductVariant | null>(null)
const quantity = ref(1)
const activeTab = ref<'description' | 'specifications' | 'reviews'>('description')

// Review form
const reviewForm = reactive({
  rating: 0,
  title: '',
  body: '',
  submitting: false,
})

import { reactive } from 'vue'

const starRating = ref(0)
const starHover = ref(0)

const productBadge = computed(() => {
  if (!product.value) return null
  if ((product.value as any).is_new) return { text: 'NEW', class: 'bg-blue-500 text-white' }
  if ((product.value as any).discount_percent) return { text: `SALE ${(product.value as any).discount_percent}% OFF`, class: 'bg-red-500 text-white' }
  if ((product.value as any).is_trending) return { text: 'TRENDING', class: 'bg-amber-500 text-white' }
  return null
})

const availableColors = computed<ProductVariant[]>(() => {
  if (!product.value) return []
  const variants: ProductVariant[] = (product.value as any).variants ?? []
  const seen = new Set<string>()
  return variants.filter((v) => {
    if (seen.has(v.color ?? '')) return false
    seen.add(v.color ?? '')
    return true
  })
})

const availableSizes = computed<ProductVariant[]>(() => {
  if (!product.value) return []
  const variants: ProductVariant[] = (product.value as any).variants ?? []
  const seen = new Set<string>()
  return variants.filter((v) => {
    if (seen.has(v.size ?? '')) return false
    seen.add(v.size ?? '')
    return true
  })
})

function isSizeInStock(size: string): boolean {
  if (!product.value) return false
  const variants: ProductVariant[] = (product.value as any).variants ?? []
  return variants.some(
    (v) =>
      v.size === size &&
      (!selectedVariant.value || v.color === selectedVariant.value.color) &&
      (v.stock ?? 0) > 0
  )
}

function selectColor(variant: ProductVariant) {
  const previousSize = selectedSize.value
  selectedVariant.value = variant
  // Bug #10: only reset size if it's unavailable for the new color
  if (previousSize) {
    const variants: ProductVariant[] = (product.value as any)?.variants ?? []
    const stillAvailable = variants.some(
      v => v.size === previousSize && v.color === variant.color && (v.stock ?? 0) > 0
    )
    if (!stillAvailable) selectedSize.value = null
  }
}

async function selectSize(size: string) {
  if (!isSizeInStock(size)) {
    // Bug #17: show feedback instead of silently ignoring click
    await Swal.fire({
      toast: true, position: 'top-end', icon: 'warning',
      title: `Size ${size} is out of stock`, timer: 2500, showConfirmButton: false,
    })
    return
  }
  selectedSize.value = size
}

function incrementQty() {
  if (quantity.value < 10) quantity.value++
}

function decrementQty() {
  if (quantity.value > 1) quantity.value--
}

function getVariantForSelection(): ProductVariant | null {
  if (!product.value) return null
  const variants: ProductVariant[] = (product.value as any).variants ?? []
  return variants.find(
    (v) => v.size === selectedSize.value && v.color === (selectedVariant.value?.color ?? v.color)
  ) ?? null
}

async function addToCart() {
  if (!authStore.token) {
    await Swal.fire({
      icon: 'info',
      title: 'Login required',
      text: 'Please log in to add items to your cart.',
      confirmButtonText: 'Log In',
      confirmButtonColor: 'var(--color-brand-600, #7c3aed)',
    })
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (!selectedSize.value && availableSizes.value.length > 0) {
    await Swal.fire({
      icon: 'warning',
      title: 'Select a size',
      text: 'Please select a size before adding to cart.',
      confirmButtonColor: 'var(--color-brand-600, #7c3aed)',
    })
    return
  }
  if (!selectedVariant.value && availableColors.value.length > 0) {
    await Swal.fire({
      icon: 'warning',
      title: 'Select a color',
      text: 'Please select a color before adding to cart.',
      confirmButtonColor: 'var(--color-brand-600, #7c3aed)',
    })
    return
  }
  if (!product.value) return
  const variant = getVariantForSelection()
  addingToCart.value = true
  try {
    await cartStore.add(product.value.id, variant?.id, quantity.value)
    await Swal.fire({
      icon: 'success',
      title: 'Added to cart!',
      timer: 1500,
      showConfirmButton: false,
    })
  } finally {
    addingToCart.value = false
  }
}

async function toggleWishlist() {
  if (!authStore.token) {
    await Swal.fire({
      icon: 'info',
      title: 'Login required',
      text: 'Please log in to save items to your wishlist.',
      confirmButtonText: 'Log In',
      confirmButtonColor: 'var(--color-brand-600, #7c3aed)',
    })
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (!product.value) return
  wishlistStore.toggle(product.value)
}

const isWishlisted = computed(() => {
  if (!product.value) return false
  return wishlistStore.isWishlisted(product.value.id)
})

function setZoom(open: boolean) {
  zoomOpen.value = open
}

async function fetchProduct() {
  loading.value = true
  selectedSize.value = null
  selectedVariant.value = null
  quantity.value = 1
  activeTab.value = 'description'
  try {
    const slug = route.params.slug as string
    const { data: res } = await productApi.show(slug)
    product.value = res.data
    relatedProducts.value = res.related ?? []
    reviews.value = res.reviews ?? []
    const images: ProductImage[] = res.data.images ?? []
    activeImage.value = images[0] ?? null
  } finally {
    loading.value = false
  }
}

async function submitReview() {
  if (!authStore.token) {
    await Swal.fire({
      icon: 'info',
      title: 'Login required',
      text: 'Please log in to submit a review.',
      confirmButtonText: 'Log In',
      confirmButtonColor: 'var(--color-brand-600, #7c3aed)',
    })
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (reviewForm.rating === 0) {
    await Swal.fire({ icon: 'warning', title: 'Rate the product', text: 'Please select a star rating.' })
    return
  }
  reviewForm.submitting = true
  try {
    await (productApi as any).addReview(product.value!.id, {
      rating: reviewForm.rating,
      title: reviewForm.title,
      body: reviewForm.body,
    })
    reviewForm.rating = 0
    reviewForm.title = ''
    reviewForm.body = ''
    await fetchProduct()
    await Swal.fire({ icon: 'success', title: 'Review submitted!', timer: 1500, showConfirmButton: false })
  } finally {
    reviewForm.submitting = false
  }
}

watch(() => route.params.slug, () => {
  if (route.params.slug) fetchProduct()
})

onMounted(() => {
  fetchProduct()
})

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function filledStars(rating: number) {
  return Math.round(rating)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <LoadingSpinner />
    </div>

    <template v-else-if="product">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1.5">
          <router-link to="/" class="hover:text-brand-600">Home</router-link>
          <span>/</span>
          <router-link :to="{ name: 'products' }" class="hover:text-brand-600">Products</router-link>
          <span>/</span>
          <span class="text-gray-800 font-medium truncate">{{ product.name }}</span>
        </nav>

        <!-- Main product section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-12">
          <!-- LEFT: Image gallery -->
          <div class="space-y-4">
            <!-- Main image -->
            <div
              class="relative rounded-2xl overflow-hidden bg-white shadow-sm cursor-zoom-in aspect-square"
              @click="setZoom(true)"
            >
              <img
                v-if="activeImage"
                :src="activeImage.image"
                :alt="product?.name ?? ''"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                No image
              </div>

              <!-- Badge -->
              <span
                v-if="productBadge"
                :class="['absolute top-3 left-3 text-xs font-bold px-2.5 py-1 rounded-full', productBadge.class]"
              >
                {{ productBadge.text }}
              </span>

              <!-- Zoom icon -->
              <span class="absolute bottom-3 right-3 bg-white/80 rounded-full p-1.5 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0zm-2 0h.01M11 9v4m-2-2h4" />
                </svg>
              </span>
            </div>

            <!-- Thumbnails -->
            <div class="flex gap-2 overflow-x-auto pb-1">
              <button
                v-for="(img, idx) in (product as any).images ?? []"
                :key="idx"
                type="button"
                :class="[
                  'flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors',
                  activeImage?.image === img.image ? 'border-brand-600' : 'border-transparent hover:border-gray-300',
                ]"
                @click="activeImage = img"
              >
                <img :src="img.image" :alt="''" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- RIGHT: Product info -->
          <div class="space-y-5">
            <!-- Brand -->
            <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">
              {{ (product as any).brand?.name ?? '' }}
            </p>

            <!-- Name -->
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ product.name }}</h1>

            <!-- Rating + Stock -->
            <div class="flex items-center gap-3 flex-wrap">
              <div class="flex items-center gap-1">
                <template v-for="n in 5" :key="n">
                  <svg
                    :class="n <= filledStars((product as any).rating ?? 0) ? 'text-amber-400' : 'text-gray-200'"
                    class="w-4.5 h-4.5 fill-current"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </template>
                <span class="text-sm text-gray-500 ml-1">({{ reviews.length }} reviews)</span>
              </div>
              <span
                :class="(product as any).in_stock !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                class="text-xs font-medium px-2 py-0.5 rounded-full"
              >
                {{ (product as any).in_stock !== false ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3">
              <span class="text-3xl font-bold text-gray-900">
                ₹{{ ((product as any).sale_price ?? product.price)?.toLocaleString('en-IN') }}
              </span>
              <span
                v-if="(product as any).sale_price && (product as any).sale_price < product.price"
                class="text-lg text-gray-400 line-through"
              >
                ₹{{ product.price?.toLocaleString('en-IN') }}
              </span>
            </div>

            <!-- Short description -->
            <p v-if="(product as any).short_description" class="text-sm text-gray-600 leading-relaxed">
              {{ (product as any).short_description }}
            </p>

            <!-- Color selector -->
            <div v-if="availableColors.length">
              <label class="label mb-2">
                Color
                <span v-if="selectedVariant" class="font-normal text-gray-500">: {{ selectedVariant.color }}</span>
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="variant in availableColors"
                  :key="variant.id"
                  type="button"
                  :title="variant.color ?? ''"
                  :class="[
                    'w-8 h-8 rounded-full border-2 transition-transform hover:scale-110',
                    selectedVariant?.id === variant.id
                      ? 'border-brand-600 scale-110 ring-2 ring-brand-600 ring-offset-1'
                      : 'border-gray-300',
                  ]"
                  :style="{ backgroundColor: (variant as any).color_hex ?? variant.color ?? '#ccc' }"
                  @click="selectColor(variant)"
                />
              </div>
            </div>

            <!-- Size selector -->
            <div v-if="availableSizes.length">
              <label class="label mb-2">
                Size
                <span v-if="selectedSize" class="font-normal text-gray-500">: {{ selectedSize }}</span>
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="variant in availableSizes"
                  :key="variant.id"
                  type="button"
                  :disabled="!isSizeInStock(variant.size ?? '')"
                  :class="[
                    'min-w-[3rem] px-3 py-2 text-sm font-medium rounded-lg border transition-colors',
                    selectedSize === variant.size
                      ? 'bg-brand-600 text-white border-brand-600'
                      : isSizeInStock(variant.size ?? '')
                      ? 'bg-white text-gray-700 border-gray-300 hover:border-brand-600 hover:text-brand-600'
                      : 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed line-through',
                  ]"
                  @click="selectSize(variant.size ?? '')"
                >
                  {{ variant.size }}
                </button>
              </div>
            </div>

            <!-- Quantity -->
            <div>
              <label class="label mb-2">Quantity</label>
              <div class="inline-flex items-center border border-gray-300 rounded-lg overflow-hidden">
                <button
                  type="button"
                  class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40"
                  :disabled="quantity <= 1"
                  @click="decrementQty"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>
                <span class="px-4 py-2 text-sm font-semibold text-gray-900 min-w-[2.5rem] text-center">
                  {{ quantity }}
                </span>
                <button
                  type="button"
                  class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40"
                  :disabled="quantity >= 10"
                  @click="incrementQty"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
              <button
                type="button"
                class="btn-primary flex-1 flex items-center justify-center gap-2 disabled:opacity-60"
                :disabled="addingToCart || (product as any).in_stock === false"
                @click="addToCart"
              >
                <LoadingSpinner v-if="addingToCart" class="w-4 h-4" />
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7A1 1 0 007 17h11m-5 0a2 2 0 100 4 2 2 0 000-4zm-6 0a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
                {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
              </button>
              <button
                type="button"
                class="btn-outline flex items-center gap-2 px-4"
                @click="toggleWishlist"
              >
                <svg
                  class="w-5 h-5 transition-colors"
                  :class="isWishlisted ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-current'"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span class="hidden sm:inline">{{ isWishlisted ? 'Wishlisted' : 'Wishlist' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="card mb-10">
          <div class="flex border-b border-gray-200">
            <button
              v-for="tab in (['description', 'specifications', 'reviews'] as const)"
              :key="tab"
              type="button"
              :class="[
                'px-5 py-3 text-sm font-medium capitalize border-b-2 transition-colors -mb-px',
                activeTab === tab
                  ? 'border-brand-600 text-brand-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700',
              ]"
              @click="activeTab = tab"
            >
              {{ tab }}
              <span v-if="tab === 'reviews'" class="ml-1 text-xs text-gray-400">({{ reviews.length }})</span>
            </button>
          </div>

          <div class="p-6">
            <!-- Description -->
            <div v-if="activeTab === 'description'">
              <div
                class="prose prose-sm max-w-none text-gray-700"
                v-html="(product as any).description ?? product.name"
              />
            </div>

            <!-- Specifications -->
            <div v-else-if="activeTab === 'specifications'">
              <dl
                v-if="(product as any).specifications && Object.keys((product as any).specifications).length"
                class="divide-y divide-gray-100"
              >
                <div
                  v-for="(val, key) in (product as any).specifications"
                  :key="key"
                  class="py-3 grid grid-cols-2 gap-4"
                >
                  <dt class="text-sm font-medium text-gray-600 capitalize">{{ key }}</dt>
                  <dd class="text-sm text-gray-800">{{ val }}</dd>
                </div>
              </dl>
              <p v-else class="text-sm text-gray-500">No specifications available.</p>
            </div>

            <!-- Reviews -->
            <div v-else-if="activeTab === 'reviews'" class="space-y-6">
              <!-- Review list -->
              <div v-if="reviews.length" class="space-y-5">
                <div
                  v-for="review in reviews"
                  :key="review.id"
                  class="border border-gray-100 rounded-xl p-4 bg-gray-50"
                >
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ (review as any).user?.name ?? 'Anonymous' }}</p>
                      <p class="text-xs text-gray-400">{{ formatDate((review as any).created_at ?? '') }}</p>
                    </div>
                    <div class="flex items-center gap-0.5">
                      <template v-for="n in 5" :key="n">
                        <svg
                          :class="n <= filledStars(review.rating) ? 'text-amber-400' : 'text-gray-200'"
                          class="w-4 h-4 fill-current"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      </template>
                    </div>
                  </div>
                  <p v-if="(review as any).title" class="text-sm font-medium text-gray-800 mb-1">{{ (review as any).title }}</p>
                  <p class="text-sm text-gray-600">{{ review.review }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No reviews yet. Be the first to write one!</p>

              <!-- Write a review form -->
              <div class="border-t border-gray-200 pt-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Write a Review</h3>
                <form class="space-y-4" @submit.prevent="submitReview">
                  <!-- Star rating selector -->
                  <div>
                    <label class="label mb-1">Your Rating</label>
                    <div class="flex items-center gap-1">
                      <template v-for="n in 5" :key="n">
                        <button
                          type="button"
                          @mouseenter="starHover = n"
                          @mouseleave="starHover = 0"
                          @click="reviewForm.rating = n"
                        >
                          <svg
                            :class="n <= (starHover || reviewForm.rating) ? 'text-amber-400' : 'text-gray-200'"
                            class="w-7 h-7 fill-current transition-colors cursor-pointer hover:scale-110 transition-transform"
                            viewBox="0 0 20 20"
                          >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </button>
                      </template>
                    </div>
                  </div>
                  <div>
                    <label class="label">Review Title</label>
                    <input v-model="reviewForm.title" type="text" class="input w-full" placeholder="Summarise your experience" />
                  </div>
                  <div>
                    <label class="label">Your Review</label>
                    <textarea
                      v-model="reviewForm.body"
                      rows="4"
                      class="input w-full resize-none"
                      placeholder="Tell others what you think..."
                    />
                  </div>
                  <button
                    type="submit"
                    class="btn-primary flex items-center gap-2 disabled:opacity-60"
                    :disabled="reviewForm.submitting"
                  >
                    <LoadingSpinner v-if="reviewForm.submitting" class="w-4 h-4" />
                    {{ reviewForm.submitting ? 'Submitting...' : 'Submit Review' }}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Products -->
        <section v-if="relatedProducts.length">
          <h2 class="text-xl font-bold text-gray-900 mb-5">You May Also Like</h2>
          <div class="flex gap-5 overflow-x-auto pb-3 -mx-1 px-1 snap-x scroll-smooth">
            <div
              v-for="related in relatedProducts"
              :key="related.id"
              class="flex-shrink-0 w-56 snap-start"
            >
              <ProductCard :product="related" />
            </div>
          </div>
        </section>
      </div>
    </template>

    <!-- Not found -->
    <div v-else class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Product not found</h2>
      <p class="text-gray-500 mb-6">The product you're looking for doesn't exist or has been removed.</p>
      <router-link :to="{ name: 'products' }" class="btn-primary">Browse Products</router-link>
    </div>

    <!-- Fullscreen zoom overlay -->
    <Transition name="fade">
      <div
        v-if="zoomOpen && activeImage"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
        @click="setZoom(false)"
      >
        <button
          type="button"
          class="absolute top-4 right-4 text-white/80 hover:text-white"
          @click.stop="setZoom(false)"
        >
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <img
          :src="activeImage.image"
          :alt="''"
          class="max-h-full max-w-full object-contain rounded-lg"
          @click.stop
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
