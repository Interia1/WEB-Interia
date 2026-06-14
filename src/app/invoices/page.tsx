import type { Metadata } from "next";
import Link from "next/link";
import { SAMPLE_INVOICES } from "@/lib/data";
import InvoiceCard from "@/components/invoices/InvoiceCard";

export const metadata: Metadata = {
  title: "Faktúry",
  description: "Správa faktúr a daňových dokladov",
};

export default function InvoicesPage() {
  const invoices = SAMPLE_INVOICES;

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Faktúry</h1>
          <p className="text-gray-500 mt-1">Správa vystavených faktúr</p>
        </div>
        <Link
          href="/invoices/new"
          className="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors text-sm"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Nová faktúra
        </Link>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        {[
          {
            label: "Celkom",
            value: invoices.length,
            color: "bg-gray-100 text-gray-700",
          },
          {
            label: "Vystavené",
            value: invoices.filter((i) => i.status === "issued" || i.status === "sent").length,
            color: "bg-blue-100 text-blue-700",
          },
          {
            label: "Zaplatené",
            value: invoices.filter((i) => i.status === "paid").length,
            color: "bg-green-100 text-green-700",
          },
          {
            label: "Po splatnosti",
            value: invoices.filter((i) => i.status === "overdue").length,
            color: "bg-red-100 text-red-700",
          },
        ].map(({ label, value, color }) => (
          <div key={label} className={"rounded-xl p-4 text-center " + color}>
            <p className="text-2xl font-bold">{value}</p>
            <p className="text-sm mt-0.5">{label}</p>
          </div>
        ))}
      </div>

      {/* List */}
      {invoices.length === 0 ? (
        <div className="text-center py-16 text-gray-400">
          <p className="text-lg font-medium">Žiadne faktúry</p>
          <p className="text-sm mt-1">Vytvorte prvú faktúru kliknutím na tlačidlo vyššie.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {invoices.map((invoice) => (
            <InvoiceCard key={invoice.id} invoice={invoice} />
          ))}
        </div>
      )}
    </div>
  );
}
