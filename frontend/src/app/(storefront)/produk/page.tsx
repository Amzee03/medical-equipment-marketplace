"use client";

import { Suspense, useCallback, useEffect, useMemo, useState } from "react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Icon } from "@/components/ui/Icon";
import { Input } from "@/components/ui/Input";
import { ProductCard } from "@/components/product/ProductCard";
import { Pagination } from "@/components/common/Pagination";
import { EmptyState, ErrorState, ProductGridSkeleton } from "@/components/common/States";
import { useCategories } from "@/hooks/useCategories";
import { useProducts } from "@/hooks/useProducts";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import type { ProductFilters, ProductSort } from "@/types/product";

const SORT_OPTIONS: { value: ProductSort; label: string }[] = [
  { value: "newest", label: "Terbaru" },
  { value: "name_asc", label: "Nama A–Z" },
  { value: "name_desc", label: "Nama Z–A" },
  { value: "price_asc", label: "Harga terendah" },
  { value: "price_desc", label: "Harga tertinggi" },
];

function toNum(v: string | null): number | undefined {
  if (v === null || v === "") return undefined;
  const n = Number(v);
  return Number.isNaN(n) || n < 0 ? undefined : n;
}

function ProductListing() {
  const router = useRouter();
  const pathname = usePathname();
  const params = useSearchParams();
  const categories = useCategories();

  // Sumber kebenaran filter = URL (bisa di-share / back-forward)
  const filters: ProductFilters = useMemo(() => {
    const sort = params.get("sort") as ProductSort | null;
    return {
      q: params.get("q") || undefined,
      category_id: toNum(params.get("category_id")),
      min_price: toNum(params.get("min_price")),
      max_price: toNum(params.get("max_price")),
      purchase_available: params.get("purchase_available") === "true" || undefined,
      rental_available: params.get("rental_available") === "true" || undefined,
      sort: SORT_OPTIONS.some((o) => o.value === sort) ? (sort as ProductSort) : "newest",
      page: toNum(params.get("page")) || 1,
    };
  }, [params]);

  const updateParams = useCallback(
    (patch: Record<string, string | undefined>, resetPage = true) => {
      const next = new URLSearchParams(params.toString());
      Object.entries(patch).forEach(([k, v]) => {
        if (v === undefined || v === "") next.delete(k);
        else next.set(k, v);
      });
      if (resetPage) next.delete("page");
      const qs = next.toString();
      router.replace(qs ? `${pathname}?${qs}` : pathname, { scroll: false });
    },
    [params, pathname, router]
  );

  // Search (debounced → query param `q`)
  const [search, setSearch] = useState(filters.q ?? "");
  const debouncedSearch = useDebouncedValue(search, 400);
  useEffect(() => {
    if ((debouncedSearch || undefined) !== filters.q) {
      updateParams({ q: debouncedSearch.trim() || undefined });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [debouncedSearch]);

  // Rentang harga (diterapkan lewat tombol)
  const [minPrice, setMinPrice] = useState(filters.min_price?.toString() ?? "");
  const [maxPrice, setMaxPrice] = useState(filters.max_price?.toString() ?? "");

  const products = useProducts(filters);

  const hasActiveFilter =
    !!filters.q ||
    filters.category_id !== undefined ||
    filters.min_price !== undefined ||
    filters.max_price !== undefined ||
    !!filters.purchase_available ||
    !!filters.rental_available;

  const resetAll = () => {
    setSearch("");
    setMinPrice("");
    setMaxPrice("");
    router.replace(pathname, { scroll: false });
  };

  const applyPrice = () => updateParams({ min_price: minPrice || undefined, max_price: maxPrice || undefined });

  const [filtersOpen, setFiltersOpen] = useState(false);

  const filterPanel = (
    <Card className="space-y-6 p-5">
      <h2 className="text-lg font-semibold text-on-surface">Filter</h2>

      <fieldset className="space-y-2">
        <legend className="text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Kategori</legend>
        <label className="flex items-center gap-2 text-sm cursor-pointer">
          <input
            type="radio"
            name="category"
            className="accent-secondary"
            checked={filters.category_id === undefined}
            onChange={() => updateParams({ category_id: undefined })}
          />
          Semua kategori
        </label>
        {categories.data?.map((c) => (
          <label key={c.id} className="flex items-center gap-2 text-sm cursor-pointer">
            <input
              type="radio"
              name="category"
              className="accent-secondary"
              checked={filters.category_id === c.id}
              onChange={() => updateParams({ category_id: String(c.id) })}
            />
            {c.name}
          </label>
        ))}
        {categories.isError && <p className="text-xs text-error">Gagal memuat kategori.</p>}
      </fieldset>

      <fieldset className="space-y-2 border-t border-outline-variant/50 pt-4">
        <legend className="text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Tipe</legend>
        <label className="flex items-center gap-2 text-sm cursor-pointer">
          <input
            type="checkbox"
            className="accent-secondary"
            checked={!!filters.purchase_available}
            onChange={(e) => updateParams({ purchase_available: e.target.checked ? "true" : undefined })}
          />
          Bisa dibeli
        </label>
        <label className="flex items-center gap-2 text-sm cursor-pointer">
          <input
            type="checkbox"
            className="accent-secondary"
            checked={!!filters.rental_available}
            onChange={(e) => updateParams({ rental_available: e.target.checked ? "true" : undefined })}
          />
          Bisa disewa
        </label>
      </fieldset>

      <fieldset className="space-y-2 border-t border-outline-variant/50 pt-4">
        <legend className="text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Rentang Harga (Rp)</legend>
        <div className="flex items-center gap-2">
          <Input
            type="number"
            min={0}
            inputMode="numeric"
            placeholder="Min"
            aria-label="Harga minimum"
            value={minPrice}
            onChange={(e) => setMinPrice(e.target.value)}
          />
          <span className="text-on-surface-variant">–</span>
          <Input
            type="number"
            min={0}
            inputMode="numeric"
            placeholder="Max"
            aria-label="Harga maksimum"
            value={maxPrice}
            onChange={(e) => setMaxPrice(e.target.value)}
          />
        </div>
        <Button variant="secondary" className="w-full" onClick={applyPrice}>
          Terapkan
        </Button>
      </fieldset>

      {hasActiveFilter && (
        <Button variant="outline" className="w-full" onClick={resetAll}>
          Reset filter
        </Button>
      )}
    </Card>
  );

  const meta = products.data?.meta;
  const from = meta && meta.total > 0 ? (meta.current_page - 1) * meta.per_page + 1 : 0;
  const to = meta ? Math.min(meta.current_page * meta.per_page, meta.total) : 0;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-primary-container mb-6">Produk</h1>

      <div className="mb-6 max-w-xl">
        <Input
          type="search"
          leftIcon="Search"
          placeholder="Cari nama, merek, atau fungsi alat..."
          aria-label="Cari produk"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
      </div>

      <div className="grid gap-8 lg:grid-cols-[260px_1fr]">
        <aside>
          <Button
            variant="outline"
            className="w-full lg:hidden mb-4"
            onClick={() => setFiltersOpen((o) => !o)}
            aria-expanded={filtersOpen}
          >
            <Icon name="SlidersHorizontal" size="sm" variant="inherit" className="mr-2" />
            {filtersOpen ? "Sembunyikan filter" : "Tampilkan filter"}
          </Button>
          <div className={filtersOpen ? "block" : "hidden lg:block"}>{filterPanel}</div>
        </aside>

        <section aria-live="polite">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p className="text-sm text-on-surface-variant">
              {meta ? (
                meta.total > 0 ? (
                  <>
                    Menampilkan <strong>{from}–{to}</strong> dari <strong>{meta.total}</strong> produk
                  </>
                ) : (
                  "0 produk"
                )
              ) : (
                "Memuat..."
              )}
            </p>
            <label className="flex items-center gap-2 text-sm text-on-surface-variant">
              Urutkan:
              <select
                className="rounded-[8px] border border-gray-300 bg-white px-3 py-2 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={filters.sort}
                onChange={(e) => updateParams({ sort: e.target.value })}
              >
                {SORT_OPTIONS.map((o) => (
                  <option key={o.value} value={o.value}>
                    {o.label}
                  </option>
                ))}
              </select>
            </label>
          </div>

          {products.isPending ? (
            <ProductGridSkeleton count={9} className="xl:grid-cols-3" />
          ) : products.isError ? (
            <ErrorState onRetry={() => products.refetch()} />
          ) : products.data.data.length === 0 ? (
            <EmptyState
              icon="SearchX"
              title="Produk tidak ditemukan"
              description={
                hasActiveFilter
                  ? "Tidak ada produk yang cocok dengan pencarian atau filter Anda. Coba ubah kata kunci atau reset filter."
                  : "Belum ada produk yang tersedia."
              }
              action={hasActiveFilter ? <Button variant="outline" onClick={resetAll}>Reset filter</Button> : undefined}
            />
          ) : (
            <div className={products.isPlaceholderData ? "opacity-60 transition-opacity" : "transition-opacity"}>
              <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                {products.data.data.map((p) => (
                  <ProductCard key={p.id} product={p} />
                ))}
              </div>
              <div className="mt-10">
                <Pagination
                  currentPage={products.data.meta.current_page}
                  lastPage={products.data.meta.last_page}
                  onPageChange={(page) => {
                    updateParams({ page: page > 1 ? String(page) : undefined }, false);
                    window.scrollTo({ top: 0, behavior: "smooth" });
                  }}
                />
              </div>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}

export default function ProductsPage() {
  // useSearchParams wajib dibungkus Suspense agar build static berhasil
  return (
    <Suspense fallback={<div className="max-w-7xl mx-auto px-4 py-8"><ProductGridSkeleton count={9} className="xl:grid-cols-3" /></div>}>
      <ProductListing />
    </Suspense>
  );
}
