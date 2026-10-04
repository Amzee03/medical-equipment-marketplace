const idr = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  maximumFractionDigits: 0,
});

// API mengembalikan harga sebagai string decimal ("45000000.00") atau null
export function formatRupiah(value: string | number | null | undefined): string | null {
  if (value === null || value === undefined || value === "") return null;
  const n = typeof value === "number" ? value : Number(value);
  if (Number.isNaN(n)) return null;
  return idr.format(n);
}
