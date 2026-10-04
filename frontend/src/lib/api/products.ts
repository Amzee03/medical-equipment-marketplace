import { apiClient } from "@/lib/api-client";
import type { PaginatedProducts, Product, ProductFilters } from "@/types/product";

// GET /api/products — query params sesuai api-contract.md.
// Boolean filter hanya dikirim saat `true` (kontrak: "Jika true, filter ...").
export async function fetchProducts(filters: ProductFilters = {}): Promise<PaginatedProducts> {
  const params: Record<string, string | number | boolean> = {};

  if (filters.category_id !== undefined) params.category_id = filters.category_id;
  if (filters.q) params.q = filters.q;
  if (filters.min_price !== undefined) params.min_price = filters.min_price;
  if (filters.max_price !== undefined) params.max_price = filters.max_price;
  if (filters.purchase_available) params.purchase_available = true;
  if (filters.rental_available) params.rental_available = true;
  if (filters.sort) params.sort = filters.sort;
  if (filters.page && filters.page > 1) params.page = filters.page;

  const res = await apiClient.get<PaginatedProducts>("/api/products", { params });
  return res.data;
}

// GET /api/products/{slug} — response dibungkus di `data`
export async function fetchProductBySlug(slug: string): Promise<Product> {
  const res = await apiClient.get<{ data: Product }>(`/api/products/${encodeURIComponent(slug)}`);
  return res.data.data;
}
