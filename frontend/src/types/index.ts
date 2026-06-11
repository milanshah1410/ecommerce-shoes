export interface User {
  id: number
  first_name: string
  last_name: string | null
  full_name: string
  email: string
  mobile: string | null
  role: string
  status: string
  is_admin: boolean
}

export interface Brand {
  id: number
  name: string
  slug: string
  logo: string | null
  description: string | null
  products_count?: number
}

export interface Category {
  id: number
  name: string
  slug: string
  parent_id: number | null
  image: string | null
  children?: Category[]
  products_count?: number
}

export interface ProductImage {
  id: number
  image: string
  is_primary: boolean
  sort_order: number
}

export interface ProductVariant {
  id: number
  size: string
  color: string
  color_hex: string | null
  price_adjustment: number
  stock: number
  in_stock: boolean
}

export interface Product {
  id: number
  name: string
  slug: string
  sku: string
  gender: string
  short_description: string | null
  description?: string | null
  price: number
  sale_price: number | null
  effective_price: number
  discount_percent: number
  rating: number
  reviews_count: number
  is_featured: boolean
  is_trending: boolean
  is_new_arrival: boolean
  is_best_seller: boolean
  in_stock?: boolean
  thumbnail: string | null
  brand?: Brand
  category?: Category
  images?: ProductImage[]
  variants?: ProductVariant[]
}

export interface Review {
  id: number
  rating: number
  title: string | null
  review: string | null
  status: string
  user_name?: string
  created_at: string
}

export interface CartItem {
  id: number
  quantity: number
  unit_price: number
  line_total: number
  product: Product
  variant: ProductVariant | null
}

export interface CartSummary {
  subtotal: number
  tax: number
  shipping: number
  discount: number
  total: number
  coupon: string | null
}

export interface Address {
  id: number
  full_name: string
  mobile: string
  address_line: string
  city: string
  state: string
  country: string
  pincode: string
  type: string
  is_default: boolean
}

export interface OrderItem {
  id: number
  product_id: number | null
  product_name: string
  size: string | null
  color: string | null
  price: number
  quantity: number
  subtotal: number
  thumbnail?: string | null
}

export interface Order {
  id: number
  order_number: string
  status: string
  payment_method: string
  payment_status: string
  subtotal: number
  tax: number
  shipping_charge: number
  discount: number
  total_amount: number
  coupon_code: string | null
  can_be_cancelled: boolean
  shipping: {
    full_name: string
    mobile: string
    address_line: string
    city: string
    state: string
    country: string
    pincode: string
  }
  items?: OrderItem[]
  items_count?: number
  created_at: string
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links?: Record<string, string | null>
}
