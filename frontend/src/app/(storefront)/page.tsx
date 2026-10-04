"use client";

import Link from "next/link";
import { Button } from "@/components/ui/Button";
import { BrandIcon, type IconName } from "@/components/ui/Icon";
import { Icon } from "@/components/ui/Icon";
import { ProductCard } from "@/components/product/ProductCard";
import { CategoryCard } from "@/components/product/CategoryCard";
import { CategoryGridSkeleton, EmptyState, ErrorState, ProductGridSkeleton } from "@/components/common/States";
import { useCategories } from "@/hooks/useCategories";
import { useProducts } from "@/hooks/useProducts";

const WHY_US: { icon: IconName; title: string; text: string }[] = [
  {
    icon: "BadgeCheck",
    title: "Professional Service",
    text: "Tim kami siap membantu Anda menemukan peralatan yang tepat untuk kebutuhan spesifik Anda.",
  },
  {
    icon: "ShieldCheck",
    title: "Quality Equipment",
    text: "Peralatan medis berkualitas yang diperiksa dan dirawat sebelum sampai ke tangan Anda.",
  },
  {
    icon: "Truck",
    title: "Fast Delivery",
    text: "Pengiriman aman dan cepat dengan beberapa opsi layanan sesuai kebutuhan Anda.",
  },
];

export default function HomePage() {
  const categories = useCategories();
  const products = useProducts({});
  const rentals = useProducts({ rental_available: true });

  return (
    <div>
      {/* Hero */}
      <section className="bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 grid items-center gap-10 lg:grid-cols-2">
          <div className="space-y-6">
            <h1 className="text-4xl lg:text-5xl font-bold leading-tight text-primary-container">
              Temukan Alat Medis yang Anda Butuhkan
            </h1>
            <p className="text-on-surface-variant max-w-lg">
              Solusi lengkap untuk kebutuhan alat medis rumah sakit, klinik, dan perawatan di rumah. Beli atau sewa
              peralatan berkualitas dengan layanan terpercaya.
            </p>
            <div className="flex flex-wrap gap-3">
              <Link href="/produk">
                <Button variant="primary" size="lg">Lihat Produk</Button>
              </Link>
              <Link href="/produk?rental_available=true">
                <Button variant="outline" size="lg">Mulai Menyewa</Button>
              </Link>
            </div>
          </div>
          <div className="overflow-hidden rounded-[1rem] shadow-lg">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src="/hero-hospital.jpg"
              alt="Ruang rawat inap dengan tempat tidur dan monitor pasien"
              className="w-full h-full object-cover aspect-[4/3]"
            />
          </div>
        </div>
      </section>

      {/* Kategori */}
      <section id="kategori" className="scroll-mt-20 border-t border-outline-variant/40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
          <h2 className="text-2xl font-bold text-center text-on-surface mb-8">Kategori Peralatan</h2>
          {categories.isPending ? (
            <CategoryGridSkeleton />
          ) : categories.isError ? (
            <ErrorState onRetry={() => categories.refetch()} />
          ) : categories.data.length === 0 ? (
            <EmptyState icon="LayoutGrid" title="Belum ada kategori" description="Kategori peralatan akan tampil di sini." />
          ) : (
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
              {categories.data.map((c) => (
                <CategoryCard key={c.id} category={c} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Produk Tersedia */}
      <section className="bg-white border-t border-outline-variant/40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
          <div className="flex items-end justify-between mb-8">
            <div>
              <h2 className="text-2xl font-bold text-on-surface">Produk Tersedia</h2>
              <p className="text-sm text-on-surface-variant">Peralatan medis terbaru dengan opsi sewa &amp; beli</p>
            </div>
            <Link href="/produk" className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
              Lihat Semua <Icon name="ArrowRight" size="sm" variant="inherit" />
            </Link>
          </div>
          {products.isPending ? (
            <ProductGridSkeleton count={8} />
          ) : products.isError ? (
            <ErrorState onRetry={() => products.refetch()} />
          ) : products.data.data.length === 0 ? (
            <EmptyState icon="PackageOpen" title="Belum ada produk" description="Produk akan tampil di sini setelah ditambahkan." />
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {products.data.data.slice(0, 8).map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Produk tersedia untuk sewa */}
      {(rentals.isPending || rentals.isError || rentals.data.data.length > 0) && (
        <section className="border-t border-outline-variant/40">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div className="flex items-end justify-between mb-8">
              <div>
                <h2 className="text-2xl font-bold text-on-surface">Tersedia untuk Disewa</h2>
                <p className="text-sm text-on-surface-variant">Sewa harian, mingguan, atau bulanan</p>
              </div>
              <Link
                href="/produk?rental_available=true"
                className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
              >
                Lihat Semua <Icon name="ArrowRight" size="sm" variant="inherit" />
              </Link>
            </div>
            {rentals.isPending ? (
              <ProductGridSkeleton count={4} />
            ) : rentals.isError ? (
              <ErrorState onRetry={() => rentals.refetch()} />
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {rentals.data.data.slice(0, 4).map((p) => (
                  <ProductCard key={p.id} product={p} />
                ))}
              </div>
            )}
          </div>
        </section>
      )}

      {/* Mengapa memilih kami */}
      <section className="bg-white border-t border-outline-variant/40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
          <h2 className="text-2xl font-bold text-center text-on-surface mb-10">Mengapa Memilih Kami?</h2>
          <div className="grid gap-8 md:grid-cols-3">
            {WHY_US.map((item) => (
              <div key={item.title} className="flex flex-col items-center text-center gap-3">
                <BrandIcon name={item.icon} brandColor="primary" containerSize="lg" size="lg" />
                <h3 className="font-semibold text-on-surface">{item.title}</h3>
                <p className="text-sm text-on-surface-variant max-w-xs text-center">{item.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-primary-container text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="text-center md:text-left">
            <h2 className="text-2xl font-bold">Butuh alat medis sekarang?</h2>
            <p className="text-white/80 text-sm mt-1">
              Jelajahi katalog kami atau hubungi tim kami lewat WhatsApp untuk konsultasi kebutuhan Anda.
            </p>
          </div>
          <Link href="/produk">
            <Button variant="secondary" size="lg">Jelajahi Katalog</Button>
          </Link>
        </div>
      </section>
    </div>
  );
}
