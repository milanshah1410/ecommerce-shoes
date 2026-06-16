import client from './client'
import type { StockMovement, ProductVariant, PaginatedResponse, ListParams } from '@/types'

export const inventoryApi = {
  lowStock: (params?: ListParams) =>
    client.get<PaginatedResponse<ProductVariant>>('/inventory/low-stock', { params }),

  movements: (params?: ListParams) =>
    client.get<PaginatedResponse<StockMovement>>('/inventory/movements', { params }),

  stockIn: (payload: { variant_id: number; quantity: number; note?: string }) =>
    client.post<StockMovement>('/inventory/stock-in', payload),

  stockOut: (payload: { variant_id: number; quantity: number; note?: string }) =>
    client.post<StockMovement>('/inventory/stock-out', payload),

  adjust: (payload: { variant_id: number; new_stock: number; note?: string }) =>
    client.post<StockMovement>('/inventory/adjust', payload),
}
