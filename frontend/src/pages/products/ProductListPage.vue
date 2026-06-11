<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductCard from '@/components/product/ProductCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { productApi } from '@/api/product'
import type { Product, Paginated } from '@/types'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const products = ref<Product[]>([])
const pagination = ref<{ current: number; last: number }>({ current: 1, last: 1 })
const totalCount = ref(0)
const sidebarOpen = ref(false)

const filters = reactive({
  search: (route.query.search as string) || '',
  gender: (route.query.gender as string) || 'all',
  priceMin: route.query.price_min ? Number(route.query.price_min) : undefined as number | undefined,
  priceMax: route.query.price_max ? Number(route.query.price_max) : undefined as number | undefined,
  rating: (route.query.rating as string) || 'any',
  onSale: route.query.on_sale === '1',
  sort: (route.query.sort as string) || 'newest',
  page: route.query.page ? Number(route.query.page) : 1,
})

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
const searchInput = ref(filters.search)

watch(searchInput, (val) => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    filters.search = val
    filters.page = 1
  }, 400)
})

const sortOptions = [
  { value: 'newest', label: 'Newest' },
  { value: 'price_asc', label: 'Price Low→High' },
  { value: 'price_desc', label: 'Price High→Low' },
  { value: 'best_selling', label: 'Best Selling' },
  { value: 'popular', label: 'Popular' },
]

const genderOptions = [
  { value: 'all', label: 'All' },
  { value: 'men', label: 'Men' },
  { value: 'women', label: 'Women' },
  { value: 'unisex', label: 'Unisex' },
]

const ratingOptions = [
  { value: '4', label: '4+ Stars' },
  { value: '3', label: '3+ Stars' },
  { value: '2', label: '2+ Stars' },
  { value: 'any', label: 'Any' },
]

const activeChips = computed(() => {
  const chips: { key: string; label: string }[] = []
  if (filters.search) chips.push({ key: 'search', label: `Search: "${filters.search}"` })
  if (filters.gender !== 'all') chips.push({ key: 'gender', label: `Gender: ${filters.gender}` })
  if (filters.priceMin !== undefined) chips.push({ key: 'priceMin', label: `Min: $${filters.priceMin}` })
  if (filters.priceMax !== undefined) chips.push({ key: 'priceMax', label: `Max: $${filters.priceMax}` })
  if (filters.rating !== 'any') chips.push({ key: 'rating', label: `Rating: ${filters.rating}+` })
  if (filters.onSale) chips.push({ key: 'onSale', label: 'On Sale' })
  return chips
})

function removeChip(key: string) {
  if (key === 'search') { filters.search = ''; searchInput.value = '' }
  else if (key === 'gender') filters.gender = 'all'
  else if (key === 'priceMin') filters.priceMin = undefined
  else if (key === 'priceMax') filters.priceMax = undefined
  else if (key === 'rating') filters.rating = 'any'
  else if (key === 'onSale') filters.onSale = false
  filters.page = 1
}

function clearFilters() {
  filters.search = ''
  searchInput.value = ''
  filters.gender = 'all'
  filters.priceMin = undefined
  filters.priceMax = undefined
  filters.rating = 'any'
  filters.onSale = false
  filters.page = 1
}

function applyFilters() {
  filters.page = 1
  syncUrl()
  fetchProducts()
  sidebarOpen.value = false
}

function syncUrl() {
  const query: Record<string, string> = {}
  if (filters.search) query.search = filters.search
  if (filters.gender !== 'all') query.gender = filters.gender
  if (filters.priceMin !== undefined) query.price_min = String(filters.priceMin)
  if (filters.priceMax !== undefined) query.price_max = String(filters.priceMax)
  if (filters.rating !== 'any') query.rating = filters.rating
  if (filters.onSale) query.on_sale = '1'
  if (filters.sort !== 'newest') query.sort = filters.sort
  if (filters.page > 1) query.page = String(filters.page)
  router.replace({ query })
}

async function fetchProducts() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {
      sort: filters.sort,
      page: filters.page,
    }
    if (filters.search) params.search = filters.search
    if (filters.gender !== 'all') params.gender = filters.gender
    if (filters.priceMin !== undefined) params.price_min = filters.priceMin
    if (filters.priceMax !== undefined) params.price_max = filters.priceMax
    if (filters.rating !== 'any') params.rating = filters.rating
    if (filters.onSale) params.on_sale = 1

    const { data: res } = await productApi.list(params)
    products.value = res.data
    totalCount.value = res.meta?.total ?? res.data.length
    pagination.value = {
      current: res.meta?.current_page ?? filters.page,
      last: res.meta?.last_page ?? 1,
    }
  } finally {
    loading.value = false
  }
}

watch(
  () => filters.sort,
  () => {
    filters.page = 1
    syncUrl()
    fetchProducts()
  }
)

function onPageChange(page: number) {
  filters.page = page
  syncUrl()
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar toggle -->
    <div class="lg:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200">
      <button
        type="button"
        class="flex items-center gap-2 text-sm font-medium text-gray-700"
        @click="sidebarOpen = !sidebarOpen"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M7 8h10M11 12h4" />
        </svg>
        Filters
        <span v-if="activeChips.length" class="bg-brand-600 text-white text-xs rounded-full px-1.5 py-0.5">
          {{ activeChips.length }}
        </span>
      </button>
      <span class="text-sm text-gray-500">{{ totalCount }} results</span>
    </div>

    <!-- Mobile sidebar overlay -->
    <Transition name="fade">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex gap-6">
        <!-- Sidebar -->
        <aside
          :class="[
            'fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl overflow-y-auto transition-transform duration-300 lg:relative lg:inset-auto lg:z-auto lg:w-64 lg:shadow-none lg:translate-x-0 lg:block lg:flex-shrink-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
          ]"
        >
          <div class="p-5 flex items-center justify-between border-b border-gray-200 lg:hidden">
            <span class="font-semibold text-gray-900">Filters</span>
            <button type="button" @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-5 space-y-6">
            <!-- Search -->
            <div>
              <label class="label">Search</label>
              <input
                v-model="searchInput"
                type="text"
                placeholder="Search shoes..."
                class="input w-full"
              />
            </div>

            <!-- Gender -->
            <div>
              <label class="label">Gender</label>
              <div class="space-y-2 mt-2">
                <label
                  v-for="opt in genderOptions"
                  :key="opt.value"
                  class="flex items-center gap-2 cursor-pointer text-sm text-gray-700"
                >
                  <input
                    type="radio"
                    name="gender"
                    :value="opt.value"
                    v-model="filters.gender"
                    class="text-brand-600 focus:ring-brand-600"
                  />
                  {{ opt.label }}
                </label>
              </div>
            </div>

            <!-- Price Range -->
            <div>
              <label class="label">Price Range</label>
              <div class="flex items-center gap-2 mt-2">
                <input
                  v-model.number="filters.priceMin"
                  type="number"
                  placeholder="Min"
                  min="0"
                  class="input w-full"
                />
                <span class="text-gray-400 text-sm">–</span>
                <input
                  v-model.number="filters.priceMax"
                  type="number"
                  placeholder="Max"
                  min="0"
                  class="input w-full"
                />
              </div>
            </div>

            <!-- Rating -->
            <div>
              <label class="label">Minimum Rating</label>
              <div class="space-y-2 mt-2">
                <label
                  v-for="opt in ratingOptions"
                  :key="opt.value"
                  class="flex items-center gap-2 cursor-pointer text-sm text-gray-700"
                >
                  <input
                    type="radio"
                    name="rating"
                    :value="opt.value"
                    v-model="filters.rating"
                    class="text-brand-600 focus:ring-brand-600"
                  />
                  {{ opt.label }}
                </label>
              </div>
            </div>

            <!-- On Sale -->
            <div>
              <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input
                  type="checkbox"
                  v-model="filters.onSale"
                  class="text-brand-600 focus:ring-brand-600 rounded"
                />
                On Sale Only
              </label>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2 pt-2">
              <button type="button" class="btn-primary w-full" @click="applyFilters">
                Apply Filters
              </button>
              <button
                type="button"
                class="text-sm text-brand-600 hover:underline text-center"
                @click="clearFilters"
              >
                Clear all filters
              </button>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0">
          <!-- Sort bar -->
          <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <p class="text-sm text-gray-600 hidden lg:block">
              Showing <span class="font-semibold text-gray-900">{{ totalCount }}</span> results
            </p>
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600 whitespace-nowrap">Sort by:</label>
              <select v-model="filters.sort" class="input text-sm py-1.5">
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Active filter chips -->
          <div v-if="activeChips.length" class="flex flex-wrap gap-2 mb-4">
            <span
              v-for="chip in activeChips"
              :key="chip.key"
              class="inline-flex items-center gap-1 bg-brand-600/10 text-brand-600 text-xs font-medium px-2.5 py-1 rounded-full"
            >
              {{ chip.label }}
              <button
                type="button"
                @click="removeChip(chip.key)"
                class="hover:text-brand-800 ml-0.5"
                aria-label="Remove filter"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </span>
            <button
              type="button"
              class="text-xs text-gray-500 hover:text-gray-700 underline"
              @click="clearFilters"
            >
              Clear all
            </button>
          </div>

          <!-- Grid with loading overlay -->
          <div class="relative">
            <!-- Loading overlay -->
            <Transition name="fade">
              <div
                v-if="loading"
                class="absolute inset-0 z-10 bg-white/70 flex items-center justify-center rounded-lg"
              >
                <LoadingSpinner />
              </div>
            </Transition>

            <!-- Empty state -->
            <div
              v-if="!loading && products.length === 0"
              class="flex flex-col items-center justify-center py-20 text-center"
            >
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 class="text-lg font-semibold text-gray-700 mb-1">No products found</h3>
              <p class="text-sm text-gray-500 mb-4">Try adjusting your filters or search terms.</p>
              <button type="button" class="btn-outline" @click="clearFilters">
                Clear filters
              </button>
            </div>

            <!-- Product grid -->
            <div
              v-else
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
            >
              <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
              />
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.last > 1" class="mt-8 flex justify-center">
            <Pagination
              :current="pagination.current"
              :last="pagination.last"
              @change="onPageChange"
            />
          </div>
        </main>
      </div>
    </div>
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
