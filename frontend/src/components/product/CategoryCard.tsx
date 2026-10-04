import Link from "next/link";
import { BrandIcon, type IconName } from "@/components/ui/Icon";
import { Card } from "@/components/ui/Card";
import type { Category } from "@/types/product";

// Kategori dari API tidak punya field ikon → pilih ikon dari kata kunci nama, fallback generik
export function categoryIcon(name: string): IconName {
  const n = name.toLowerCase();
  if (n.includes("diagnos")) return "Stethoscope";
  if (n.includes("bedah") || n.includes("surg")) return "Scissors";
  if (n.includes("monitor")) return "Activity";
  if (n.includes("ortop") || n.includes("mobil") || n.includes("kursi")) return "Accessibility";
  if (n.includes("lab")) return "FlaskConical";
  if (n.includes("napas") || n.includes("respir") || n.includes("oksigen")) return "Wind";
  if (n.includes("terapi") || n.includes("rehab")) return "HeartPulse";
  return "Cross";
}

export function CategoryCard({ category }: { category: Category }) {
  return (
    <Link
      href={`/kategori/${category.slug}`}
      className="group block focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-[1rem]"
    >
      <Card className="h-full flex flex-col items-center text-center gap-3 transition-shadow group-hover:shadow-md group-hover:border-primary/40">
        <BrandIcon name={categoryIcon(category.name)} brandColor="primary" containerSize="lg" size="lg" />
        <h3 className="font-semibold text-on-surface">{category.name}</h3>
        {category.description && (
          <p className="text-xs text-on-surface-variant line-clamp-2">{category.description}</p>
        )}
      </Card>
    </Link>
  );
}
