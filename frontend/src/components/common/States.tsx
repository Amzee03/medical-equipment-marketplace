import React from "react";
import { Card } from "@/components/ui/Card";
import { Button } from "@/components/ui/Button";
import { Icon, type IconName } from "@/components/ui/Icon";
import { cn } from "@/lib/utils";

export function ProductCardSkeleton() {
  return (
    <Card className="p-0 overflow-hidden animate-pulse" aria-hidden="true">
      <div className="aspect-[4/3] bg-surface-container" />
      <div className="p-4 space-y-3">
        <div className="h-3 w-1/3 rounded bg-surface-container" />
        <div className="h-4 w-4/5 rounded bg-surface-container" />
        <div className="h-4 w-3/5 rounded bg-surface-container" />
        <div className="h-4 w-full rounded bg-surface-container" />
      </div>
    </Card>
  );
}

export function ProductGridSkeleton({ count = 8, className }: { count?: number; className?: string }) {
  return (
    <div
      className={cn("grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6", className)}
      role="status"
      aria-label="Memuat produk"
    >
      {Array.from({ length: count }).map((_, i) => (
        <ProductCardSkeleton key={i} />
      ))}
    </div>
  );
}

export function CategoryGridSkeleton({ count = 4 }: { count?: number }) {
  return (
    <div className="grid grid-cols-2 lg:grid-cols-4 gap-6" role="status" aria-label="Memuat kategori">
      {Array.from({ length: count }).map((_, i) => (
        <Card key={i} className="animate-pulse flex flex-col items-center gap-3">
          <div className="h-12 w-12 rounded-full bg-surface-container" />
          <div className="h-4 w-24 rounded bg-surface-container" />
        </Card>
      ))}
    </div>
  );
}

export function EmptyState({
  icon = "SearchX",
  title,
  description,
  action,
}: {
  icon?: IconName;
  title: string;
  description?: string;
  action?: React.ReactNode;
}) {
  return (
    <div className="flex flex-col items-center justify-center text-center py-16 px-4 gap-3">
      <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
        <Icon name={icon} size="xl" variant="inherit" />
      </div>
      <h3 className="text-lg font-semibold text-on-surface">{title}</h3>
      {description && <p className="max-w-md text-sm text-on-surface-variant">{description}</p>}
      {action && <div className="mt-2">{action}</div>}
    </div>
  );
}

export function ErrorState({
  message = "Terjadi kesalahan saat memuat data. Periksa koneksi Anda lalu coba lagi.",
  onRetry,
}: {
  message?: string;
  onRetry?: () => void;
}) {
  return (
    <div role="alert" className="flex flex-col items-center justify-center text-center py-16 px-4 gap-3">
      <div className="flex h-16 w-16 items-center justify-center rounded-full bg-error/10 text-error">
        <Icon name="TriangleAlert" size="xl" variant="inherit" />
      </div>
      <h3 className="text-lg font-semibold text-on-surface">Gagal memuat data</h3>
      <p className="max-w-md text-sm text-on-surface-variant">{message}</p>
      {onRetry && (
        <Button variant="outline" onClick={onRetry} className="mt-2">
          <Icon name="RefreshCw" size="sm" variant="inherit" className="mr-2" />
          Coba lagi
        </Button>
      )}
    </div>
  );
}
