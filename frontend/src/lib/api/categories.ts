import { apiClient } from "@/lib/api-client";
import type { Category } from "@/types/product";

// GET /api/categories — flat array di `data`, tanpa meta/links
export async function fetchCategories(): Promise<Category[]> {
  const res = await apiClient.get<{ data: Category[] }>("/api/categories");
  return res.data.data;
}
