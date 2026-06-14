import { notFound } from "next/navigation";
import Link from "next/link";
import type { Metadata } from "next";
import { SAMPLE_INVOICES } from "@/lib/data";
import PrintButton from "./PrintButton";
import {
  formatCurrency,
  formatDate,
  invoiceSubtotal,
  invoiceVat,
  invoiceTotal,
} from "@/lib/utils";
import Badge from "@/components/ui/Badge";

type BadgeVariant = "green" | "blue" | "yellow" | "red" | "gray";

const STATUS_CONFIG: Record<
  string,
  { label: string; variant: BadgeVariant }
> = {
  draft: { label: "Koncept", variant: "gray" },
  issued: { label: "Vystavená", variant: "blue" },
  sent: { label: "Odoslaná", variant: "yellow" },
  paid: { label: "Zaplatená", variant: "green" },
  overdue: { label: "Po splatnosti", variant: "red" },
  cancelled: { label: "Zrušená", variant: "gray" },
};

const PAYMENT_LABELS: Record<string, string> = {
  transfer: "Bankový prevod",
  cash: "Hotovosť",
  card: "Platobná karta",
};

interface Props {
  params: Promise<{ id: string }>;
}

export async function generateStaticParams() {
  return SAMPLE_INVOICES.map((inv) => ({ id: inv.id }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params;
  const invoice = SAMPLE_INVOICES.find((inv) => inv.id === id);
  if (!invoice) return {};
  return { title: "Faktúra " + invoice.invoiceNumber };
}

export default async function InvoiceDetailPage({ params }: Props) {
  const { id } = await params;
  const invoice = SAMPLE_INVOICES.find((inv) => inv.id === id);
  if (!invoice) notFound();

  const { label, variant } = STATUS_CONFIG[invoice.status] ?? STATUS_CONFIG.draft;
  const subtotal = invoiceSubtotal(invoice.items);
  const vat = invoiceVat(invoice.items);
  const total = invoiceTotal(invoice.items);

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex items-start justify-between gap-4 mb-8">
        <div>
          <Link href="/invoices" className="text-sm text-blue-700 hover:underline">
            ← Späť na faktúry
          </Link>
          <h1 className="text-3xl font-bold text-gray-900 mt-2">
            Faktúra {invoice.invoiceNumber}
          </h1>
        </div>
        <div className="flex items-center gap-3">
          <Badge variant={variant} className="text-sm px-3 py-1">
            {label}
          </Badge>
          <PrintButton />
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 space-y-8 print:shadow-none print:border-none">
        {/* Parties */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
          <div>
            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
              Dodávateľ
            </p>
            <p className="font-bold text-gray-900">{invoice.supplier.name}</p>
            <p className="text-sm text-gray-600">{invoice.supplier.address.street}</p>
            <p className="text-sm text-gray-600">
              {invoice.supplier.address.zip} {invoice.supplier.address.city}
            </p>
            <p className="text-sm text-gray-500 mt-2">IČO: {invoice.supplier.ico}</p>
            <p className="text-sm text-gray-500">DIČ: {invoice.supplier.dic}</p>
            <p className="text-sm text-gray-500">IČ DPH: {invoice.supplier.icdph}</p>
            <p className="text-sm text-gray-500 mt-1">IBAN: {invoice.supplier.iban}</p>
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
              Odberateľ
            </p>
            <p className="font-bold text-gray-900">{invoice.customer.name}</p>
            {invoice.customer.address && (
              <>
                <p className="text-sm text-gray-600">{invoice.customer.address.street}</p>
                <p className="text-sm text-gray-600">
                  {invoice.customer.address.zip} {invoice.customer.address.city}
                </p>
              </>
            )}
            {invoice.customer.ico && (
              <p className="text-sm text-gray-500 mt-2">IČO: {invoice.customer.ico}</p>
            )}
            {invoice.customer.dic && (
              <p className="text-sm text-gray-500">DIČ: {invoice.customer.dic}</p>
            )}
            <p className="text-sm text-gray-500 mt-1">{invoice.customer.email}</p>
          </div>
        </div>

        {/* Invoice meta */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50 rounded-xl p-4">
          {[
            { label: "Číslo faktúry", value: invoice.invoiceNumber },
            { label: "Dátum vystavenia", value: formatDate(invoice.issueDate) },
            { label: "Dátum splatnosti", value: formatDate(invoice.dueDate) },
            { label: "Platba", value: PAYMENT_LABELS[invoice.paymentMethod] ?? invoice.paymentMethod },
          ].map(({ label: l, value }) => (
            <div key={l}>
              <p className="text-xs text-gray-400">{l}</p>
              <p className="text-sm font-medium text-gray-800 mt-0.5">{value}</p>
            </div>
          ))}
          <div>
            <p className="text-xs text-gray-400">Variabilný symbol</p>
            <p className="text-sm font-medium text-gray-800 mt-0.5">{invoice.variableSymbol}</p>
          </div>
        </div>

        {/* Line items */}
        <div className="overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200">
                <th className="pb-2 text-left font-medium">Popis</th>
                <th className="pb-2 text-right font-medium w-20">Množ.</th>
                <th className="pb-2 text-right font-medium w-12">MJ</th>
                <th className="pb-2 text-right font-medium w-28">Cena / MJ</th>
                <th className="pb-2 text-right font-medium w-20">DPH</th>
                <th className="pb-2 text-right font-medium w-28">Spolu</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {invoice.items.map((item, idx) => (
                <tr key={idx} className="py-2">
                  <td className="py-2 pr-4">{item.description}</td>
                  <td className="py-2 text-right">{item.quantity}</td>
                  <td className="py-2 text-right text-gray-500">{item.unit}</td>
                  <td className="py-2 text-right">{formatCurrency(item.unitPrice)}</td>
                  <td className="py-2 text-right text-gray-500">
                    {(item.vatRate * 100).toFixed(0)} %
                  </td>
                  <td className="py-2 text-right font-medium">
                    {formatCurrency(
                      item.quantity * item.unitPrice * (1 + item.vatRate),
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Totals */}
        <dl className="flex flex-col items-end gap-1.5 text-sm border-t border-gray-100 pt-4">
          <div className="flex gap-8">
            <dt className="text-gray-500">Základ DPH:</dt>
            <dd className="w-28 text-right font-medium">{formatCurrency(subtotal)}</dd>
          </div>
          <div className="flex gap-8">
            <dt className="text-gray-500">DPH:</dt>
            <dd className="w-28 text-right font-medium">{formatCurrency(vat)}</dd>
          </div>
          <div className="flex gap-8 text-base font-bold border-t border-gray-200 pt-2 mt-2">
            <dt>Celkom na úhradu:</dt>
            <dd className="w-28 text-right">{formatCurrency(total)}</dd>
          </div>
        </dl>

        {/* Note */}
        {invoice.note && (
          <div className="border-t border-gray-100 pt-4">
            <p className="text-xs text-gray-400 mb-1">Poznámka</p>
            <p className="text-sm text-gray-600">{invoice.note}</p>
          </div>
        )}
      </div>
    </div>
  );
}
