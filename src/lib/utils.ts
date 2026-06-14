import type { InvoiceItem, OrderItem } from "./types";

/** Format a number as EUR currency (Slovak locale). */
export function formatCurrency(value: number): string {
  return new Intl.NumberFormat("sk-SK", {
    style: "currency",
    currency: "EUR",
  }).format(value);
}

/** Format an ISO date string to Slovak short date (d. M. yyyy). */
export function formatDate(isoString: string): string {
  return new Intl.DateTimeFormat("sk-SK", {
    day: "numeric",
    month: "numeric",
    year: "numeric",
  }).format(new Date(isoString));
}

/** Calculate the total (without VAT) for an array of invoice items. */
export function invoiceSubtotal(items: InvoiceItem[]): number {
  return items.reduce((sum, i) => sum + i.quantity * i.unitPrice, 0);
}

/** Calculate the VAT amount for an array of invoice items. */
export function invoiceVat(items: InvoiceItem[]): number {
  return items.reduce(
    (sum, i) => sum + i.quantity * i.unitPrice * i.vatRate,
    0,
  );
}

/** Calculate the total (with VAT) for an array of invoice items. */
export function invoiceTotal(items: InvoiceItem[]): number {
  return invoiceSubtotal(items) + invoiceVat(items);
}

/** Calculate price including VAT for a single unit price. */
export function priceWithVat(price: number, vatRate: number): number {
  return price * (1 + vatRate);
}

/** Calculate order total without VAT. */
export function orderSubtotal(items: OrderItem[]): number {
  return items.reduce((sum, i) => sum + i.quantity * i.unitPrice, 0);
}

/** Calculate order total with VAT. */
export function orderTotal(items: OrderItem[]): number {
  return items.reduce(
    (sum, i) => sum + i.quantity * i.unitPrice * (1 + i.vatRate),
    0,
  );
}

/** Generate a sequential invoice number for the current year. */
export function generateInvoiceNumber(sequence: number): string {
  const year = new Date().getFullYear();
  return `FA-${year}-${String(sequence).padStart(3, "0")}`;
}

/** Generate a sequential order number for the current year. */
export function generateOrderNumber(sequence: number): string {
  const year = new Date().getFullYear();
  return `${year}-${String(sequence).padStart(3, "0")}`;
}

/** Slugify a string for URL usage. */
export function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[áäà]/g, "a")
    .replace(/[čç]/g, "c")
    .replace(/ď/g, "d")
    .replace(/[éěê]/g, "e")
    .replace(/[íî]/g, "i")
    .replace(/[ľĺ]/g, "l")
    .replace(/ň/g, "n")
    .replace(/[óöô]/g, "o")
    .replace(/ŕ/g, "r")
    .replace(/š/g, "s")
    .replace(/ť/g, "t")
    .replace(/[úüů]/g, "u")
    .replace(/ý/g, "y")
    .replace(/ž/g, "z")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

/** Clamp a number between min and max. */
export function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max);
}
