import Link from "next/link";
import type { Invoice } from "@/lib/types";
import { formatCurrency, formatDate, invoiceTotal } from "@/lib/utils";
import Badge from "@/components/ui/Badge";

type BadgeVariant = "green" | "blue" | "yellow" | "red" | "gray";

const STATUS_CONFIG: Record<
  Invoice["status"],
  { label: string; variant: BadgeVariant }
> = {
  draft: { label: "Koncept", variant: "gray" },
  issued: { label: "Vystavená", variant: "blue" },
  sent: { label: "Odoslaná", variant: "yellow" },
  paid: { label: "Zaplatená", variant: "green" },
  overdue: { label: "Po splatnosti", variant: "red" },
  cancelled: { label: "Zrušená", variant: "gray" },
};

interface InvoiceCardProps {
  invoice: Invoice;
}

export default function InvoiceCard({ invoice }: InvoiceCardProps) {
  const { label, variant } = STATUS_CONFIG[invoice.status];
  const total = invoiceTotal(invoice.items);

  return (
    <Link
      href={"/invoices/" + invoice.id}
      className="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-blue-200 transition-all"
    >
      <div className="flex items-start justify-between gap-4">
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 flex-wrap">
            <span className="font-semibold text-gray-900">
              {invoice.invoiceNumber}
            </span>
            <Badge variant={variant}>{label}</Badge>
          </div>
          <p className="mt-1 text-sm text-gray-600 truncate">
            {invoice.customer.name}
          </p>
          <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
            <span>Vystavená: {formatDate(invoice.issueDate)}</span>
            <span>Splatnosť: {formatDate(invoice.dueDate)}</span>
            {invoice.orderId && (
              <span>Objednávka: {invoice.orderId}</span>
            )}
          </div>
        </div>
        <div className="text-right shrink-0">
          <p className="font-bold text-gray-900 text-lg">
            {formatCurrency(total)}
          </p>
          <p className="text-xs text-gray-400">s DPH</p>
        </div>
      </div>
    </Link>
  );
}
