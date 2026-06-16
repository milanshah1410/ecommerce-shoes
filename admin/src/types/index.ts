// ─── User & Auth ────────────────────────────────────────────────────────────

export type UserRole = 'super_admin' | 'admin' | 'manager' | 'inventory_manager' | 'sales_manager' | 'customer_support' | 'customer'

export interface User {
  id: number
  first_name: string
  last_name: string
  name: string | null
  full_name: string
  email: string
  mobile: string | null
  role: UserRole
  status: 'active' | 'blocked'
  email_verified_at: string | null
  created_at: string
  updated_at: string
  is_admin?: boolean
}

export interface AuthResponse {
  token: string
  user: User
}

// ─── Pagination ──────────────────────────────────────────────────────────────

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links?: Record<string, string | null>
}

// ─── Category & Brand ────────────────────────────────────────────────────────

export interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  image: string | null
  status: 'active' | 'inactive'
  products_count?: number
  created_at: string
}

export interface Brand {
  id: number
  name: string
  slug: string
  logo: string | null
  description: string | null
  status: 'active' | 'inactive'
  products_count?: number
  created_at: string
}

// ─── Product ─────────────────────────────────────────────────────────────────

export type ProductStatus = 'active' | 'inactive' | 'draft'

export interface ProductVariant {
  id: number
  product_id: number
  size: string
  color: string
  sku: string | null
  stock: number
  price_modifier: number
  created_at: string
}

export interface ProductImage {
  id: number
  product_id: number
  image: string
  is_primary: boolean
  sort_order: number
}

export interface Product {
  id: number
  category_id: number
  brand_id: number
  name: string
  slug: string
  sku: string
  gender: string | null
  short_description: string | null
  description: string | null
  price: string
  sale_price: string | null
  weight: string | null
  video_url: string | null
  is_featured: boolean
  is_trending: boolean
  is_new_arrival: boolean
  is_best_seller: boolean
  sold_count: number
  rating: string
  reviews_count: number
  status: ProductStatus
  effective_price: number
  discount_percent: number
  in_stock: boolean
  category?: Category
  brand?: Brand
  variants?: ProductVariant[]
  images?: ProductImage[]
  created_at: string
  updated_at: string
}

// ─── Inventory ───────────────────────────────────────────────────────────────

export type StockMovementType = 'stock_in' | 'stock_out' | 'adjustment'

export interface StockMovement {
  id: number
  product_variant_id: number
  type: StockMovementType
  quantity: number
  previous_stock: number
  new_stock: number
  note: string | null
  created_by: number
  created_at: string
  variant?: ProductVariant & { product?: Pick<Product, 'id' | 'name'> }
  creator?: Pick<User, 'id' | 'full_name'>
}

// ─── Order ───────────────────────────────────────────────────────────────────

export type OrderStatus =
  | 'pending' | 'confirmed' | 'processing' | 'packed'
  | 'shipped' | 'delivered' | 'cancelled' | 'returned' | 'refunded'

export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded'

export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  variant_id: number | null
  product_name: string
  size: string | null
  color: string | null
  sku: string | null
  quantity: number
  unit_price: string
  total_price: string
  product?: Pick<Product, 'id' | 'name' | 'images'>
}

export interface Order {
  id: number
  user_id: number
  order_number: string
  status: OrderStatus
  payment_method: string
  payment_status: PaymentStatus
  payment_reference: string | null
  subtotal: string
  tax: string
  shipping_charge: string
  discount: string
  total_amount: string
  coupon_code: string | null
  ship_full_name: string
  ship_mobile: string
  ship_address_line: string
  ship_city: string
  ship_state: string
  ship_country: string
  ship_pincode: string
  notes: string | null
  created_at: string
  updated_at: string
  user?: Pick<User, 'id' | 'full_name' | 'email'>
  items?: OrderItem[]
}

// ─── Coupon ──────────────────────────────────────────────────────────────────

export type CouponType = 'percentage' | 'fixed'

export interface Coupon {
  id: number
  code: string
  type: CouponType
  discount_value: string
  min_order_amount: string | null
  max_discount: string | null
  start_date: string | null
  end_date: string | null
  usage_limit: number | null
  used_count: number
  status: 'active' | 'inactive'
  created_at: string
}

// ─── Review ──────────────────────────────────────────────────────────────────

export interface Review {
  id: number
  user_id: number
  product_id: number
  rating: number
  title: string | null
  review: string | null
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  user?: Pick<User, 'id' | 'full_name'>
  product?: Pick<Product, 'id' | 'name'>
}

// ─── Dashboard ───────────────────────────────────────────────────────────────

export interface DashboardStats {
  total_orders: number
  total_revenue: number
  today_revenue: number
  total_customers: number
  total_products: number
  pending_orders: number
  delivered_orders: number
  low_stock_count: number
  recent_orders: Array<{
    id: number
    order_number: string
    status: OrderStatus
    total_amount: number
    created_at: string
    customer: string | null
  }>
  top_products: Array<{
    id: number
    name: string
    sold_count: number
    thumbnail: string | null
  }>
  low_stock: Array<{
    id: number
    product_id: number
    size: string
    color: string
    stock: number
  }>
  order_statuses: Record<string, number>
  monthly_revenue: Array<{ month: string; revenue: number }>
}

// ─── Settings ────────────────────────────────────────────────────────────────

export interface AppSettings {
  store_name: string
  store_email: string
  store_phone: string
  store_address: string
  currency: string
  currency_symbol: string
  tax_rate: number
  shipping_charge: number
  free_shipping_threshold: number
  logo: string | null
  razorpay_key: string | null
  stripe_key: string | null
  smtp_host: string | null
  smtp_port: number | null
  smtp_user: string | null
  smtp_from_name: string | null
}

// ─── Filters / Query Params ──────────────────────────────────────────────────

export interface ListParams {
  page?: number
  per_page?: number
  search?: string
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
  [key: string]: unknown
}
