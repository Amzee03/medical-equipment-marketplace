"use client";

import { use, useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/Button";
import { Icon } from "@/components/ui/Icon";
import { ProductCard } from "@/components/product/ProductCard";
import { Pagination } from "@/components/common/Pagination";
import { EmptyState, ErrorState, ProductGridSkeleton } from "@/components/common/States";
import { useCategories } from "@/hooks/useCategories";
import { useProducts } from "@/hooks/useProducts";

export default function CategoryPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  const [page, setPage] = useState(1);

  // Slug → category_id via daftar kategori (GET /api/categories), tanpa endpoint baru
  const categories = useCategories();
  const category = categories.data?.find((c) => c.slug === slug);

  const products = useProducts({ category_id: category?.id, page }, { enabled: !!category });

  if (categories.isPending) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <ProductGridSkeleton count={8} />
      </div>
    );
  }

  if (categories.isError) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-8">
        <ErrorState onRetry={() => categories.refetch()} />
      </div>
    );
  }

  if (!category) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-8">
        <EmptyState
          icon="FolderX"
          title="Kategori tidak ditemukan"
          description="Kategori yang Anda cari tidak ada atau sudah tidak aktif."
          action={
            <Link href="/produk">
              <Button variant="outline">Lihat semua produk</Button>
            </Link>
          }
        />
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <nav aria-label="Breadcrumb" className="mb-4 flex items-center gap-1 text-sm text-on-surface-variant">
        <Link href="/" className="hover:underline">Beranda</Link>
        <Icon name="ChevronRight" size="sm" variant="inherit" />
        <span className="text-on-surface">{category.name}</span>
      </nav>

      <h1 className="text-3xl font-bold text-primary-container">{category.name}</h1>
      {category.description && <p className="mt-2 max-w-2xl text-on-surface-variant">{category.description}</p>}

      <div className="mt-8">
        {products.isPending ? (
          <ProductGridSkeleton count={8} />
        ) : products.isError ? (
          <ErrorState onRetry={() => products.refetch()} />
        ) : products.data.data.length === 0 ? (
          <EmptyState
            icon="PackageOpen"
            title="Belum ada produk di kategori ini"
            description="Coba lihat kategori lain atau jelajahi semua produk."
            action={
              <Link href="/produk">
                <Button variant="outline">Lihat semua produk</Button>
              </Link>
            }
          />
        ) : (
          <div className={products.isPlaceholderData ? "opacity-60 transition-opacity" : "transition-opacity"}>
            <p className="mb-4 text-sm text-on-surface-variant">{products.data.meta.total} produk</p>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {products.data.data.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
            <div className="mt-10">
              <Pagination
                currentPage={products.data.meta.current_page}
                lastPage={products.data.meta.last_page}
                onPageChange={(p) => {
                  setPage(p);
                  window.scrollTo({ top: 0, behavior: "smooth" });
                }}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
