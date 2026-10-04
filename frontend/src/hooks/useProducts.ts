import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { fetchProductBySlug, fetchProducts } from "@/lib/api/products";
import type { ProductFilters } from "@/types/product";

export const productKeys = {
  all: ["products"] as const,
  list: (filters: ProductFilters) => ["products", "list", filters] as const,
  detail: (slug: string) => ["products", "detail", slug] as const,
};

// filters masuk ke query key → otomatis refetch saat filter berubah
export function useProducts(filters: ProductFilters = {}, options: { enabled?: boolean } = {}) {
  return useQuery({
    queryKey: productKeys.list(filters),
    queryFn: () => fetchProducts(filters),
    placeholderData: keepPreviousData,
    enabled: options.enabled ?? true,
  });
}

export function useProduct(slug: string) {
  return useQuery({
    queryKey: productKeys.detail(slug),
    queryFn: () => fetchProductBySlug(slug),
    enabled: !!slug,
  });
}
