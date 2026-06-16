import client from './client'

export interface SalesReport {
  period: string
  orders: number
  revenue: number
  avg_order_value: number
}

export interface ProductReport {
  id: number
  name: string
  sold_count: number
  revenue: number
  stock: number
}

export interface CustomerReport {
  id: number
  full_name: string
  email: string
  orders_count: number
  total_spent: number
}

export const reportsApi = {
  sales: (params: { from: string; to: string; group_by?: 'day' | 'week' | 'month' }) =>
    client.get<SalesReport[]>('/reports/sales', { params }),

  topProducts: (params?: { limit?: number; from?: string; to?: string }) =>
    client.get<ProductReport[]>('/reports/products/top', { params }),

  lowStock: (params?: { threshold?: number }) =>
    client.get<ProductReport[]>('/reports/products/low-stock', { params }),

  topCustomers: (params?: { limit?: number }) =>
    client.get<CustomerReport[]>('/reports/customers/top', { params }),

  inventoryValue: () =>
    client.get<{ total_value: number; by_category: Array<{ name: string; value: number }> }>('/reports/inventory/value'),
}
