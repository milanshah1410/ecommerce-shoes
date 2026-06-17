import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // Public layout
    {
      path: '/',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        { path: '', name: 'home', component: () => import('@/pages/home/HomePage.vue') },
        { path: 'products', name: 'products', component: () => import('@/pages/products/ProductListPage.vue') },
        { path: 'products/:slug', name: 'product', component: () => import('@/pages/products/ProductDetailPage.vue') },
        { path: 'cart', name: 'cart', component: () => import('@/pages/cart/CartPage.vue') },
        {
          path: 'checkout',
          name: 'checkout',
          component: () => import('@/pages/checkout/CheckoutPage.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'checkout/success/:orderId',
          name: 'order-success',
          component: () => import('@/pages/checkout/OrderSuccessPage.vue'),
          meta: { requiresAuth: true },
        },
        // Profile (auth required)
        {
          path: 'profile',
          meta: { requiresAuth: true },
          children: [
            { path: '', name: 'profile', component: () => import('@/pages/profile/ProfilePage.vue') },
            { path: 'orders', name: 'orders', component: () => import('@/pages/profile/OrdersPage.vue') },
            { path: 'orders/:id', name: 'order-detail', component: () => import('@/pages/profile/OrderDetailPage.vue') },
            { path: 'wishlist', name: 'wishlist', component: () => import('@/pages/profile/WishlistPage.vue') },
          ],
        },
      ],
    },
    // Auth pages (no layout wrapper needed)
    { path: '/login', name: 'login', component: () => import('@/pages/auth/LoginPage.vue'), meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: () => import('@/pages/auth/RegisterPage.vue'), meta: { guestOnly: true } },
    { path: '/forgot-password', name: 'forgot-password', component: () => import('@/pages/auth/ForgotPasswordPage.vue'), meta: { guestOnly: true } },

    { path: '/privacy', name: 'privacy', component: () => import('@/pages/legal/PrivacyPage.vue') },
    { path: '/terms', name: 'terms', component: () => import('@/pages/legal/TermsPage.vue') },

    // Catch-all
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isLoggedIn) {
    return { name: 'home' }
  }
})

export default router
