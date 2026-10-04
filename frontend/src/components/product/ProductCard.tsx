import Link from "next/link";
import { Card } from "@/components/ui/Card";
import { Badge } from "@/components/ui/Badge";
import { Icon } from "@/components/ui/Icon";
import { formatRupiah } from "@/lib/format";
import type { Product } from "@/types/product";

export function ProductCard({ product }: { product: Product }) {
  const images = product.images ?? [];
  const image = images.find((i) => i.is_primary) ?? images[0];

  const salePrice = product.purchase_available ? formatRupiah(product.sale_price) : null;
  const rentPrice = product.rental_available ? formatRupiah(product.rental_price_daily) : null;

  const canBuy = product.purchase_available && (product.stock_purchase ?? 0) > 0;
  const inStock = canBuy || product.rental_available;

  return (
    <Link
      href={`/produk/${product.slug}`}
      className="group block h-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-[1rem]"
    >
      <Card className="p-0 overflow-hidden h-full flex flex-col transition-shadow group-hover:shadow-md">
        <div className="relative aspect-[4/3] bg-surface-container-low flex items-center justify-center">
          {image ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={image.url}
              alt={product.name}
              loading="lazy"
              className="h-full w-full object-contain p-4 transition-transform group-hover:scale-105"
            />
          ) : (
            <div className="flex flex-col items-center gap-1 text-on-surface-variant/50">
              <Icon name="ImageOff" size="xl" variant="inherit" />
              <span className="text-xs">Belum ada foto</span>
            </div>
          )}
          <div className="absolute top-3 left-3">
            <Badge variant={inStock ? "success" : "danger"}>{inStock ? "Tersedia" : "Stok Habis"}</Badge>
          </div>
        </div>

        <div className="flex flex-1 flex-col gap-1 p-4">
          {product.category && (
            <span className="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">
              {product.category.name}
            </span>
          )}
          <h3 className="font-semibold text-on-surface line-clamp-2 min-h-[3rem]">{product.name}</h3>

          <div className="mt-2 space-y-1 text-sm">
            {salePrice && (
              <div className="flex items-baseline justify-between">
                <span className="text-on-surface-variant">Beli</span>
                <span className="font-bold text-primary">{salePrice}</span>
              </div>
            )}
            {rentPrice && (
              <div className="flex items-baseline justify-between">
                <span className="text-on-surface-variant">Sewa mulai dari</span>
                <span className="font-bold text-secondary">
                  {rentPrice}
                  <span className="text-xs font-normal text-on-surface-variant">/hari</span>
                </span>
              </div>
            )}
            {!salePrice && !rentPrice && (
              <span className="text-on-surface-variant">Harga belum tersedia</span>
            )}
          </div>

          <span className="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary group-hover:underline">
            Lihat Detail <Icon name="ArrowRight" size="sm" variant="inherit" />
          </span>
        </div>
      </Card>
    </Link>
  );
}
