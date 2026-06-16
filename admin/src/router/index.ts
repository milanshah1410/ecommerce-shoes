import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory('/admin'),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // Auth
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { guestOnly: true },
    },

    // Admin shell
    {
      path: '/',
      component: () => import('@/layouts/AdminLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/dashboard' },

        // Dashboard
        { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') },

        // User Management
        { path: 'users', name: 'users', component: () => import('@/pages/users/UsersPage.vue') },
        { path: 'roles', name: 'roles', component: () => import('@/pages/roles/RolesPage.vue') },

        // Catalog
        { path: 'products', name: 'products', component: () => import('@/pages/products/ProductsPage.vue') },
        { path: 'products/create', name: 'products.create', component: () => import('@/pages/products/ProductFormPage.vue') },
        { path: 'products/:id/edit', name: 'products.edit', component: () => import('@/pages/products/ProductFormPage.vue') },
        { path: 'categories', name: 'categories', component: () => import('@/pages/categories/CategoriesPage.vue') },
        { path: 'brands', name: 'brands', component: () => import('@/pages/brands/BrandsPage.vue') },

        // Inventory
        { path: 'inventory', name: 'inventory', component: () => import('@/pages/inventory/InventoryPage.vue') },

        // Sales
        { path: 'orders', name: 'orders', component: () => import('@/pages/orders/OrdersPage.vue') },
        { path: 'orders/:id', name: 'orders.show', component: () => import('@/pages/orders/OrderDetailPage.vue') },

        // Marketing
        { path: 'coupons', name: 'coupons', component: () => import('@/pages/coupons/CouponsPage.vue') },

        // Customers
        { path: 'customers', name: 'customers', component: () => import('@/pages/customers/CustomersPage.vue') },
        { path: 'customers/:id', name: 'customers.show', component: () => import('@/pages/customers/CustomerDetailPage.vue') },
        { path: 'reviews', name: 'reviews', component: () => import('@/pages/reviews/ReviewsPage.vue') },

        // Reports
        { path: 'reports', name: 'reports', component: () => import('@/pages/reports/ReportsPage.vue') },

        // Settings
        { path: 'settings', name: 'settings', component: () => import('@/pages/settings/SettingsPage.vue') },

        // Profile
        { path: 'profile', name: 'profile', component: () => import('@/pages/profile/ProfilePage.vue') },
      ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
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
    return { name: 'dashboard' }
  }
})

export default router
