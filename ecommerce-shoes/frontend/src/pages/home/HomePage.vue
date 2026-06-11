<template>
  <div class="min-h-screen bg-white">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <LoadingSpinner size="lg" />
    </div>

    <template v-else>
      <!-- Hero Banner -->
      <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 to-indigo-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
          <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                Step Into Your<br />
                <span class="text-indigo-200">Best Style</span>
              </h1>
              <p class="mt-6 text-lg sm:text-xl text-indigo-100 max-w-md leading-relaxed">
                Discover the latest footwear trends. From casual to luxury — find the perfect pair that defines you.
              </p>
              <div class="mt-10 flex flex-wrap gap-4">
                <button @click="router.push({ name: 'products' })" class="btn-primary px-8 py-3 text-base font-semibold rounded-lg shadow-lg">
                  Shop Now
                </button>
                <button @click="router.push({ name: 'products', query: { gender: 'men' } })" class="btn-outline border-white text-white hover:bg-white hover:text-indigo-700 px-8 py-3 text-base font-semibold rounded-lg transition-colors">
                  Explore Collections
                </button>
              </div>
            </div>
            <div class="flex items-center justify-center">
              <span class="text-[180px] lg:text-[220px] leading-none select-none drop-shadow-2xl">👟</span>
            </div>
          </div>
        </div>
        <!-- Decorative blob -->
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-indigo-500 opacity-20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-brand-600 opacity-30 rounded-full blur-2xl pointer-events-none"></div>
      </section>

      <!-- Trending Shoes -->
      <section v-if="data.trending?.length" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="Trending Shoes" link-label="View All" @view-all="router.push({ name: 'products', query: { sort: 'trending' } })" />
          <div class="mt-8 flex gap-6 overflow-x-auto pb-4 scrollbar-thin scrollbar-thumb-indigo-200 scrollbar-track-transparent snap-x snap-mandatory">
            <div
              v-for="product in data.trending.slice(0, 8)"
              :key="product.id"
              class="flex-none w-60 snap-start"
            >
              <ProductCard :product="product" />
            </div>
          </div>
        </div>
      </section>

      <!-- New Arrivals -->
      <section v-if="data.new_arrivals?.length" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="New Arrivals" link-label="View All" @view-all="router.push({ name: 'products', query: { sort: 'newest' } })" />
          <div class="mt-8 flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory">
            <div
              v-for="product in data.new_arrivals.slice(0, 8)"
              :key="product.id"
              class="flex-none w-60 snap-start"
            >
              <ProductCard :product="product" />
            </div>
          </div>
        </div>
      </section>

      <!-- Best Sellers -->
      <section v-if="data.best_sellers?.length" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="Best Sellers" link-label="View All" @view-all="router.push({ name: 'products', query: { sort: 'best_sellers' } })" />
          <div class="mt-8 flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory">
            <div
              v-for="product in data.best_sellers.slice(0, 8)"
              :key="product.id"
              class="flex-none w-60 snap-start"
            >
              <ProductCard :product="product" />
            </div>
          </div>
        </div>
      </section>

      <!-- Featured Brands -->
      <section v-if="data.brands?.length" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="Featured Brands" />
          <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div
              v-for="brand in data.brands"
              :key="brand.id"
              class="card flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-brand-600 hover:shadow-md transition-all cursor-pointer min-h-[96px]"
            >
              <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" class="h-12 w-auto object-contain mb-2" />
              <span class="text-sm font-semibold text-gray-700 text-center">{{ brand.name }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Men's Collection -->
      <section v-if="data.mens?.length" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="Men's Collection" link-label="View All" @view-all="router.push({ name: 'products', query: { gender: 'men' } })" />
          <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <ProductCard
              v-for="product in data.mens.slice(0, 4)"
              :key="product.id"
              :product="product"
            />
          </div>
        </div>
      </section>

      <!-- Women's Collection -->
      <section v-if="data.womens?.length" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionHeader title="Women's Collection" link-label="View All" @view-all="router.push({ name: 'products', query: { gender: 'women' } })" />
          <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <ProductCard
              v-for="product in data.womens.slice(0, 4)"
              :key="product.id"
              :product="product"
            />
          </div>
        </div>
      </section>

      <!-- Limited Offers (On Sale) -->
      <section v-if="data.on_sale?.length" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <h2 class="text-2xl font-bold text-gray-900">Limited Offers</h2>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white uppercase tracking-wider shadow">
                SALE
              </span>
            </div>
            <button
              @click="router.push({ name: 'products', query: { on_sale: '1' } })"
              class="text-sm font-medium text-brand-600 hover:text-indigo-800 transition-colors"
            >
              View All &rarr;
            </button>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <ProductCard
              v-for="product in data.on_sale.slice(0, 4)"
              :key="product.id"
              :product="product"
            />
          </div>
        </div>
      </section>

      <!-- Newsletter -->
      <section class="py-20 bg-gradient-to-r from-brand-600 to-indigo-700 text-white">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 class="text-3xl font-bold mb-3">Stay in the Loop</h2>
          <p class="text-indigo-200 mb-8 text-lg">
            Get the latest drops, exclusive deals, and style tips delivered to your inbox.
          </p>
          <form @submit.prevent="handleNewsletterSubmit" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input
              v-model="newsletterEmail"
              type="email"
              required
              placeholder="Enter your email address"
              class="input flex-1 bg-white/10 border-white/30 text-white placeholder-indigo-300 focus:ring-white focus:border-white"
            />
            <button type="submit" class="btn-dark px-6 py-2.5 font-semibold rounded-lg whitespace-nowrap">
              Subscribe
            </button>
          </form>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, defineComponent, h } from 'vue'
import { useRouter } from 'vue-router'
import { catalogApi } from '@/api/catalog'
import ProductCard from '@/components/product/ProductCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const loading = ref(true)

const data = reactive<{
  trending: any[]
  new_arrivals: any[]
  best_sellers: any[]
  featured: any[]
  mens: any[]
  womens: any[]
  on_sale: any[]
  brands: any[]
}>({
  trending: [],
  new_arrivals: [],
  best_sellers: [],
  featured: [],
  mens: [],
  womens: [],
  on_sale: [],
  brands: [],
})

const newsletterEmail = ref('')

// Inline section header component
const SectionHeader = defineComponent({
  props: {
    title: { type: String, required: true },
    linkLabel: { type: String, default: '' },
  },
  emits: ['view-all'],
  setup(props, { emit }) {
    return () =>
      h('div', { class: 'flex items-center justify-between' }, [
        h('h2', { class: 'text-2xl font-bold text-gray-900' }, props.title),
        props.linkLabel
          ? h(
              'button',
              {
                onClick: () => emit('view-all'),
                class: 'text-sm font-medium text-brand-600 hover:text-indigo-800 transition-colors',
              },
              props.linkLabel + ' →'
            )
          : null,
      ])
  },
})

onMounted(async () => {
  try {
    const { data: result } = await catalogApi.home()
    data.trending = result.trending ?? []
    data.new_arrivals = result.new_arrivals ?? []
    data.best_sellers = result.best_sellers ?? []
    data.featured = result.featured ?? []
    data.mens = result.mens ?? []
    data.womens = result.womens ?? []
    data.on_sale = result.on_sale ?? []
    data.brands = result.brands ?? []
  } catch (e) {
    console.error('Failed to load home data', e)
  } finally {
    loading.value = false
  }
})

function handleNewsletterSubmit() {
  newsletterEmail.value = ''
  alert('You have been subscribed! Keep an eye on your inbox for the latest updates.')
}
</script>
