import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { wishlistApi } from '@/api/wishlist'
import type { Product } from '@/types'

export const useWishlistStore = defineStore('wishlist', () => {
  const items = ref<Product[]>([])

  const ids = computed(() => new Set(items.value.map((p) => p.id)))
  const count = computed(() => items.value.length)

  function isWishlisted(id: number) {
    return ids.value.has(id)
  }

  async function fetch() {
    const { data } = await wishlistApi.get()
    items.value = data.data
  }

  async function toggle(product: Product) {
    if (isWishlisted(product.id)) {
      await wishlistApi.remove(product.id)
      items.value = items.value.filter((p) => p.id !== product.id)
    } else {
      await wishlistApi.add(product.id)
      items.value.push(product)
    }
  }

  async function remove(productId: number) {
    await wishlistApi.remove(productId)
    items.value = items.value.filter((p) => p.id !== productId)
  }

  async function moveToCart(productId: number) {
    await wishlistApi.moveToCart(productId)
    items.value = items.value.filter((p) => p.id !== productId)
  }

  return { items, count, isWishlisted, fetch, toggle, remove, moveToCart }
})
