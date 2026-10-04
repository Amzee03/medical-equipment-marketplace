import { useQuery } from "@tanstack/react-query";
import { fetchCategories } from "@/lib/api/categories";

export const categoryKeys = {
  all: ["categories"] as const,
};

// Kategori jarang berubah → cache 60 detik
export function useCategories() {
  return useQuery({
    queryKey: categoryKeys.all,
    queryFn: fetchCategories,
    staleTime: 60_000,
  });
}
