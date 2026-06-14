"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { COMPANY_INFO } from "@/lib/data";
import { generateInvoiceNumber } from "@/lib/utils";
import Button from "@/components/ui/Button";

interface LineItem {
  description: string;
  quantity: number;
  unit: string;
  unitPrice: number;
  vatRate: number;
}

const EMPTY_LINE: LineItem = {
  description: "",
  quantity: 1,
  unit: "ks",
  unitPrice: 0,
  vatRate: 0.2,
};

export default function NewInvoicePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  // Customer
  const [customerName, setCustomerName] = useState("");
  const [customerEmail, setCustomerEmail] = useState("");
  const [customerIco, setCustomerIco] = useState("");
  const [customerDic, setCustomerDic] = useState("");
  const [customerStreet, setCustomerStreet] = useState("");
  const [customerCity, setCustomerCity] = useState("");
  const [customerZip, setCustomerZip] = useState("");

  // Invoice meta – use lazy initialisers so dates are computed only once
  const [issueDate, setIssueDate] = useState<string>(
    () => new Date().toISOString().split("T")[0],
  );
  const [dueDate, setDueDate] = useState<string>(() => {
    const d = new Date();
    d.setDate(d.getDate() + 14);
    return d.toISOString().split("T")[0];
  });
  const [paymentMethod, setPaymentMethod] = useState<"transfer" | "cash">("transfer");
  const [note, setNote] = useState("");

  // Items
  const [items, setItems] = useState<LineItem[]>([{ ...EMPTY_LINE }]);

  function updateItem(idx: number, field: keyof LineItem, value: string | number) {
    setItems((prev) =>
      prev.map((item, i) => (i === idx ? { ...item, [field]: value } : item)),
    );
  }

  function addItem() {
    setItems((prev) => [...prev, { ...EMPTY_LINE }]);
  }

  function removeItem(idx: number) {
    setItems((prev) => prev.filter((_, i) => i !== idx));
  }

  const subtotal = items.reduce((s, i) => s + i.quantity * i.unitPrice, 0);
  const vat = items.reduce(
    (s, i) => s + i.quantity * i.unitPrice * i.vatRate,
    0,
  );
  const total = subtotal + vat;

  const fmt = (n: number) =>
    new Intl.NumberFormat("sk-SK", { style: "currency", currency: "EUR" }).format(n);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    try {
      const payload = {
        invoiceNumber: generateInvoiceNumber(Math.floor(Math.random() * 900) + 100),
        status: "draft",
        supplier: COMPANY_INFO,
        customer: {
          id: "",
          name: customerName,
          email: customerEmail,
          ico: customerIco,
          dic: customerDic,
          isCompany: Boolean(customerIco),
          address: {
            street: customerStreet,
            city: customerCity,
            zip: customerZip,
            country: "Slovensko",
          },
        },
        items,
        issueDate,
        dueDate,
        paymentMethod,
        variableSymbol: Date.now().toString().slice(-6),
        note,
      };

      const res = await fetch("/api/invoices", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = (await res.json()) as { data: { id: string } };
      router.push("/invoices/" + data.data.id);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Nová faktúra</h1>

      <form onSubmit={handleSubmit} className="space-y-8">
        {/* Supplier */}
        <div className="bg-blue-50 rounded-xl border border-blue-200 p-6">
          <h2 className="font-semibold text-blue-800 mb-3">Dodávateľ</h2>
          <p className="text-sm text-blue-700">
            <strong>{COMPANY_INFO.name}</strong> · IČO: {COMPANY_INFO.ico} · IČ DPH: {COMPANY_INFO.icdph}
          </p>
          <p className="text-sm text-blue-700 mt-0.5">
            {COMPANY_INFO.address.street}, {COMPANY_INFO.address.zip} {COMPANY_INFO.address.city}
          </p>
          <p className="text-sm text-blue-700 mt-0.5">IBAN: {COMPANY_INFO.iban}</p>
        </div>

        {/* Customer */}
        <fieldset className="bg-white rounded-xl border border-gray-200 p-6">
          <legend className="text-base font-semibold text-gray-900 px-1">Odberateľ</legend>
          <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="sm:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Meno / Firma *</label>
              <input required type="text" value={customerName} onChange={(e) => setCustomerName(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
              <input required type="email" value={customerEmail} onChange={(e) => setCustomerEmail(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">IČO</label>
              <input type="text" value={customerIco} onChange={(e) => setCustomerIco(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">DIČ / IČ DPH</label>
              <input type="text" value={customerDic} onChange={(e) => setCustomerDic(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div className="sm:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Ulica a číslo</label>
              <input type="text" value={customerStreet} onChange={(e) => setCustomerStreet(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Mesto</label>
              <input type="text" value={customerCity} onChange={(e) => setCustomerCity(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">PSČ</label>
              <input type="text" value={customerZip} onChange={(e) => setCustomerZip(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
        </fieldset>

        {/* Invoice meta */}
        <fieldset className="bg-white rounded-xl border border-gray-200 p-6">
          <legend className="text-base font-semibold text-gray-900 px-1">Dátumy a platba</legend>
          <div className="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Dátum vystavenia</label>
              <input type="date" value={issueDate} onChange={(e) => setIssueDate(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Dátum splatnosti</label>
              <input type="date" value={dueDate} onChange={(e) => setDueDate(e.target.value)}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Spôsob úhrady</label>
              <select value={paymentMethod} onChange={(e) => setPaymentMethod(e.target.value as "transfer" | "cash")}
                className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="transfer">Bankový prevod</option>
                <option value="cash">Hotovosť</option>
              </select>
            </div>
          </div>
        </fieldset>

        {/* Line items */}
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Položky</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead>
                <tr className="text-left text-xs text-gray-500 uppercase tracking-wide border-b border-gray-100">
                  <th className="pb-2 pr-3 font-medium">Popis</th>
                  <th className="pb-2 pr-3 font-medium w-20">Množstvo</th>
                  <th className="pb-2 pr-3 font-medium w-16">MJ</th>
                  <th className="pb-2 pr-3 font-medium w-28">Cena / MJ (€)</th>
                  <th className="pb-2 pr-3 font-medium w-20">DPH (%)</th>
                  <th className="pb-2 font-medium w-28 text-right">Spolu</th>
                  <th className="pb-2 w-8" />
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {items.map((item, idx) => (
                  <tr key={idx}>
                    <td className="py-2 pr-3">
                      <input value={item.description} onChange={(e) => updateItem(idx, "description", e.target.value)}
                        placeholder="Popis položky"
                        className="w-full px-2 py-1 rounded border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </td>
                    <td className="py-2 pr-3">
                      <input type="number" min={0} value={item.quantity} onChange={(e) => updateItem(idx, "quantity", Number(e.target.value))}
                        className="w-full px-2 py-1 rounded border border-gray-200 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </td>
                    <td className="py-2 pr-3">
                      <input value={item.unit} onChange={(e) => updateItem(idx, "unit", e.target.value)}
                        className="w-full px-2 py-1 rounded border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </td>
                    <td className="py-2 pr-3">
                      <input type="number" min={0} step="0.01" value={item.unitPrice} onChange={(e) => updateItem(idx, "unitPrice", Number(e.target.value))}
                        className="w-full px-2 py-1 rounded border border-gray-200 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </td>
                    <td className="py-2 pr-3">
                      <select value={item.vatRate} onChange={(e) => updateItem(idx, "vatRate", Number(e.target.value))}
                        className="w-full px-2 py-1 rounded border border-gray-200 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value={0.2}>20 %</option>
                        <option value={0.1}>10 %</option>
                        <option value={0}>0 %</option>
                      </select>
                    </td>
                    <td className="py-2 text-right font-medium text-gray-800">
                      {fmt(item.quantity * item.unitPrice * (1 + item.vatRate))}
                    </td>
                    <td className="py-2 pl-2">
                      {items.length > 1 && (
                        <button type="button" onClick={() => removeItem(idx)} className="text-gray-300 hover:text-red-500 transition-colors" aria-label="Odstraniť">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <button type="button" onClick={addItem}
            className="mt-3 text-sm text-blue-700 hover:underline flex items-center gap-1">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Pridať položku
          </button>

          {/* Totals */}
          <dl className="mt-6 border-t border-gray-100 pt-4 space-y-1 text-sm text-right">
            <div className="flex justify-end gap-4">
              <dt className="text-gray-500">Základ DPH:</dt>
              <dd className="w-28 font-medium">{fmt(subtotal)}</dd>
            </div>
            <div className="flex justify-end gap-4">
              <dt className="text-gray-500">DPH:</dt>
              <dd className="w-28 font-medium">{fmt(vat)}</dd>
            </div>
            <div className="flex justify-end gap-4 text-base font-bold border-t border-gray-200 pt-2 mt-2">
              <dt>Celkom:</dt>
              <dd className="w-28">{fmt(total)}</dd>
            </div>
          </dl>
        </div>

        {/* Note */}
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <label className="block text-sm font-semibold text-gray-900 mb-2" htmlFor="inv-note">Poznámka</label>
          <textarea id="inv-note" rows={2} value={note} onChange={(e) => setNote(e.target.value)}
            placeholder="Napr. Ďakujeme za Vašu objednávku."
            className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
        </div>

        <Button type="submit" loading={loading} size="lg" className="w-full">
          Uložiť faktúru
        </Button>
      </form>
    </div>
  );
}
