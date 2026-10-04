// Shape mengikuti docs/api-contract.md (CategoryResource, ProductImageResource, ProductResource)

export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface ProductImage {
  id: number;
  product_id: number;
  url: string;
  path: string;
  is_primary: boolean;
  sort_order: number;
}

export type ProductCondition = "baru" | "bekas_baik" | "perlu_pemeriksaan";

export interface Product {
  id: number;
  category_id: number;
  category?: Category;
  name: string;
  slug: string;
  sku: string;
  description: string | null;
  function: string | null;
  brand: string | null;
  model: string | null;
  specifications: Record<string, unknown> | null;
  condition: ProductCondition;
  purchase_available: boolean;
  sale_price: string | null;
  stock_purchase: number | null;
  rental_available: boolean;
  rental_price_daily: string | null;
  rental_price_weekly: string | null;
  rental_price_monthly: string | null;
  min_rental_days: number | null;
  max_rental_days: number | null;
  shipping_owner_delivery: boolean;
  shipping_express: boolean;
  shipping_regular: boolean;
  shipping_pickup: boolean;
  status: string;
  available_units_count?: number;
  images?: ProductImage[];
  created_at: string;
  updated_at: string;
}

export type ProductSort = "name_asc" | "name_desc" | "price_asc" | "price_desc" | "newest";

export interface ProductFilters {
  category_id?: number;
  q?: string;
  min_price?: number;
  max_price?: number;
  purchase_available?: boolean;
  rental_available?: boolean;
  sort?: ProductSort;
  page?: number;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PaginatedProducts {
  data: Product[];
  meta: PaginationMeta;
}
