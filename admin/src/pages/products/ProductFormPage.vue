<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { productsApi } from '@/api/products'
import { categoriesApi } from '@/api/categories'
import { brandsApi } from '@/api/brands'
import PageHeader from '@/components/common/PageHeader.vue'
import type { Category, Brand, Product } from '@/types'
import { Loader2, Plus, X, ImagePlus } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const router = useRouter()
const route = useRoute()
const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)

const categories = ref<Category[]>([])
const brands = ref<Brand[]>([])
const imageFiles = ref<File[]>([])
const imagePreviews = ref<string[]>([])
const existingImages = ref<Product['images']>([])

const form = reactive({
  name: '', slug: '', sku: '', gender: '',
  category_id: '', brand_id: '',
  short_description: '', description: '',
  price: '', sale_price: '', weight: '',
  video_url: '', status: 'active',
  is_featured: false, is_trending: false,
  is_new_arrival: false, is_best_seller: false,
})

const variants = ref<Array<{ size: string; color: string; stock: number; sku: string }>>([])
const SIZES = ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10']
const COLORS = ['Black', 'White', 'Blue', 'Red', 'Green']

function addVariant() {
  variants.value.push({ size: 'UK 7', color: 'Black', stock: 0, sku: '' })
}
function removeVariant(i: number) {
  variants.value.splice(i, 1)
}

function handleImageSelect(e: Event) {
  const files = (e.target as HTMLInputElement).files
  if (!files) return
  for (const file of files) {
    imageFiles.value.push(file)
    imagePreviews.value.push(URL.createObjectURL(file))
  }
}

function removePreview(i: number) {
  imageFiles.value.splice(i, 1)
  imagePreviews.value.splice(i, 1)
}

function autoSlug() {
  form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
}

async function load() {
  loading.value = true
  try {
    const [catRes, brandRes] = await Promise.all([
      categoriesApi.all(),
      brandsApi.all(),
    ])
    categories.value = catRes.data
    brands.value = brandRes.data

    if (isEdit.value) {
      const { data } = await productsApi.show(Number(route.params.id))
      Object.assign(form, {
        name: data.name, slug: data.slug, sku: data.sku,
        gender: data.gender ?? '', category_id: String(data.category_id),
        brand_id: String(data.brand_id),
        short_description: data.short_description ?? '',
        description: data.description ?? '',
        price: data.price, sale_price: data.sale_price ?? '',
        weight: data.weight ?? '', video_url: data.video_url ?? '',
        status: data.status, is_featured: data.is_featured,
        is_trending: data.is_trending, is_new_arrival: data.is_new_arrival,
        is_best_seller: data.is_best_seller,
      })
      existingImages.value = data.images ?? []
      variants.value = (data.variants ?? []).map((v) => ({
        size: v.size, color: v.color, stock: v.stock, sku: v.sku ?? '',
      }))
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    Object.entries(form).forEach(([k, v]) => fd.append(k, String(v)))
    variants.value.forEach((v, i) => {
      fd.append(`variants[${i}][size]`, v.size)
      fd.append(`variants[${i}][color]`, v.color)
      fd.append(`variants[${i}][stock]`, String(v.stock))
      fd.append(`variants[${i}][sku]`, v.sku)
    })
    imageFiles.value.forEach((f) => fd.append('images[]', f))

    if (isEdit.value) {
      await productsApi.update(Number(route.params.id), fd)
    } else {
      await productsApi.create(fd)
    }
    Swal.fire({ icon: 'success', title: 'Product saved!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
    router.push({ name: 'products' })
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    Swal.fire({ icon: 'error', title: err.response?.data?.message ?? 'Error saving product.' })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader
      :title="isEdit ? 'Edit Product' : 'Create Product'"
      subtitle="Fill in the product details below"
    >
      <button class="btn-secondary" @click="router.push({ name: 'products' })">Cancel</button>
      <button :disabled="saving" class="btn-primary flex items-center gap-2" @click="save">
        <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
        {{ saving ? 'Saving…' : 'Save Product' }}
      </button>
    </PageHeader>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-500 border-t-transparent" />
    </div>

    <div v-else class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Main info -->
      <div class="xl:col-span-2 space-y-6">
        <div class="card">
          <h3 class="section-title">Basic Information</h3>
          <div class="space-y-4">
            <div>
              <label class="label">Product Name</label>
              <input v-model="form.name" type="text" class="input-field" required @blur="!form.slug && autoSlug()" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Slug</label>
                <input v-model="form.slug" type="text" class="input-field" />
              </div>
              <div>
                <label class="label">SKU</label>
                <input v-model="form.sku" type="text" class="input-field font-mono" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Category</label>
                <select v-model="form.category_id" class="input-field">
                  <option value="">Select Category</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div>
                <label class="label">Brand</label>
                <select v-model="form.brand_id" class="input-field">
                  <option value="">Select Brand</option>
                  <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label">Gender</label>
              <select v-model="form.gender" class="input-field">
                <option value="">Unisex</option>
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="kids">Kids</option>
              </select>
            </div>
            <div>
              <label class="label">Short Description</label>
              <textarea v-model="form.short_description" rows="2" class="input-field" />
            </div>
            <div>
              <label class="label">Description</label>
              <textarea v-model="form.description" rows="5" class="input-field" />
            </div>
          </div>
        </div>

        <!-- Pricing -->
        <div class="card">
          <h3 class="section-title">Pricing</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="label">Price (₹) *</label>
              <input v-model="form.price" type="number" step="0.01" class="input-field" required />
            </div>
            <div>
              <label class="label">Sale Price (₹)</label>
              <input v-model="form.sale_price" type="number" step="0.01" class="input-field" />
            </div>
            <div>
              <label class="label">Weight (kg)</label>
              <input v-model="form.weight" type="number" step="0.01" class="input-field" />
            </div>
          </div>
        </div>

        <!-- Variants -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="section-title mb-0">Variants (Size / Color / Stock)</h3>
            <button type="button" class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium" @click="addVariant">
              <Plus class="w-4 h-4" /> Add Variant
            </button>
          </div>
          <div class="space-y-2">
            <div
              v-for="(v, i) in variants"
              :key="i"
              class="flex items-center gap-2"
            >
              <select v-model="v.size" class="input-field text-sm">
                <option v-for="s in SIZES" :key="s" :value="s">{{ s }}</option>
              </select>
              <select v-model="v.color" class="input-field text-sm">
                <option v-for="c in COLORS" :key="c" :value="c">{{ c }}</option>
              </select>
              <input v-model.number="v.stock" type="number" min="0" class="input-field text-sm w-24" placeholder="Stock" />
              <input v-model="v.sku" type="text" class="input-field text-sm font-mono" placeholder="SKU" />
              <button type="button" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded" @click="removeVariant(i)">
                <X class="w-4 h-4" />
              </button>
            </div>
            <p v-if="variants.length === 0" class="text-sm text-gray-400 py-2">No variants. Click "Add Variant" to begin.</p>
          </div>
        </div>

        <!-- Images -->
        <div class="card">
          <h3 class="section-title">Product Gallery</h3>

          <!-- Existing images -->
          <div v-if="existingImages?.length" class="flex flex-wrap gap-2 mb-4">
            <div v-for="img in existingImages" :key="img.id" class="relative group">
              <img :src="img.image" class="w-20 h-20 rounded-lg object-cover border border-gray-200 dark:border-gray-700" />
              <span v-if="img.is_primary" class="absolute top-1 left-1 text-xs bg-indigo-500 text-white px-1 rounded">Primary</span>
            </div>
          </div>

          <!-- New images -->
          <div v-if="imagePreviews.length" class="flex flex-wrap gap-2 mb-4">
            <div v-for="(src, i) in imagePreviews" :key="i" class="relative group">
              <img :src="src" class="w-20 h-20 rounded-lg object-cover border border-gray-200 dark:border-gray-700" />
              <button
                type="button"
                class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                @click="removePreview(i)"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>

          <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 cursor-pointer hover:border-indigo-400 transition-colors">
            <ImagePlus class="w-8 h-8 text-gray-400" />
            <span class="text-sm text-gray-500">Click to upload images</span>
            <input type="file" multiple accept="image/*" class="hidden" @change="handleImageSelect" />
          </label>

          <div>
            <label class="label mt-4">Video URL</label>
            <input v-model="form.video_url" type="url" class="input-field" placeholder="https://youtube.com/..." />
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status -->
        <div class="card">
          <h3 class="section-title">Status</h3>
          <select v-model="form.status" class="input-field">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="draft">Draft</option>
          </select>
        </div>

        <!-- Tags -->
        <div class="card">
          <h3 class="section-title">Product Tags</h3>
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              Featured
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input v-model="form.is_trending" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              Trending
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input v-model="form.is_new_arrival" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              New Arrival
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input v-model="form.is_best_seller" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              Best Seller
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";

.card { @apply bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5; }
.section-title { @apply text-base font-semibold text-gray-900 dark:text-white mb-4; }
.label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5; }
.input-field { @apply w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500; }
.btn-primary { @apply px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 transition-colors; }
.btn-secondary { @apply px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors; }
</style>
