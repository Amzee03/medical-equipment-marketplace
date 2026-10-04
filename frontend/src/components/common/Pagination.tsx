import { Icon } from "@/components/ui/Icon";
import { cn } from "@/lib/utils";

function pageList(current: number, last: number): (number | "…")[] {
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);
  const pages = new Set([1, last, current - 1, current, current + 1]);
  const sorted = [...pages].filter((p) => p >= 1 && p <= last).sort((a, b) => a - b);
  const out: (number | "…")[] = [];
  sorted.forEach((p, i) => {
    if (i > 0 && p - sorted[i - 1] > 1) out.push("…");
    out.push(p);
  });
  return out;
}

const btn =
  "h-9 min-w-9 px-2 inline-flex items-center justify-center rounded border text-sm font-medium transition-colors disabled:opacity-40 disabled:pointer-events-none";

// Berdasarkan meta.current_page / meta.last_page (api-contract: tidak ada `links`)
export function Pagination({
  currentPage,
  lastPage,
  onPageChange,
}: {
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => void;
}) {
  if (lastPage <= 1) return null;

  return (
    <nav aria-label="Pagination" className="flex items-center justify-center gap-2">
      <button
        type="button"
        aria-label="Halaman sebelumnya"
        className={cn(btn, "border-outline-variant bg-white text-on-surface hover:bg-surface-container-low")}
        disabled={currentPage <= 1}
        onClick={() => onPageChange(currentPage - 1)}
      >
        <Icon name="ChevronLeft" size="sm" variant="inherit" />
      </button>

      {pageList(currentPage, lastPage).map((p, i) =>
        p === "…" ? (
          <span key={`gap-${i}`} className="px-1 text-on-surface-variant">
            …
          </span>
        ) : (
          <button
            key={p}
            type="button"
            aria-current={p === currentPage ? "page" : undefined}
            className={cn(
              btn,
              p === currentPage
                ? "border-secondary bg-secondary text-white"
                : "border-outline-variant bg-white text-on-surface hover:bg-surface-container-low"
            )}
            onClick={() => onPageChange(p)}
          >
            {p}
          </button>
        )
      )}

      <button
        type="button"
        aria-label="Halaman berikutnya"
        className={cn(btn, "border-outline-variant bg-white text-on-surface hover:bg-surface-container-low")}
        disabled={currentPage >= lastPage}
        onClick={() => onPageChange(currentPage + 1)}
      >
        <Icon name="ChevronRight" size="sm" variant="inherit" />
      </button>
    </nav>
  );
}
