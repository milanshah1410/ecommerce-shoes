import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cartApi } from '@/api/cart'
import type { CartItem, CartSummary } from '@/types'

const defaultSummary: CartSummary = { subtotal: 0, tax: 0, shipping: 0, discount: 0, total: 0, coupon: null }

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])
  const summary = ref<CartSummary>({ ...defaultSummary })
  const loading = ref(false)
  const drawerOpen = ref(false)

  const count = computed(() => items.value.reduce((n, i) => n + i.quantity, 0))

  async function fetch() {
    loading.value = true
    try {
      const { data } = await cartApi.get()
      items.value = data.items
      summary.value = data.summary
    } finally {
      loading.value = false
    }
  }

  async function add(productId: number, variantId?: number, quantity = 1) {
    const { data } = await cartApi.add({ product_id: productId, variant_id: variantId, quantity })
    items.value = data.items
    summary.value = data.summary
    drawerOpen.value = true
  }

  async function update(cartId: number, quantity: number) {
    const { data } = await cartApi.update(cartId, quantity)
    items.value = data.items
    summary.value = data.summary
  }

  async function remove(cartId: number) {
    const { data } = await cartApi.remove(cartId)
    items.value = data.items
    summary.value = data.summary
  }

  async function applyCoupon(code: string) {
    const { data } = await cartApi.applyCoupon(code)
    items.value = data.items
    summary.value = data.summary
  }

  function clear() {
    items.value = []
    summary.value = { ...defaultSummary }
  }

  return { items, summary, loading, drawerOpen, count, fetch, add, update, remove, applyCoupon, clear }
})
