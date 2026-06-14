"use client";

import { useState } from "react";
import Link from "next/link";
import { useCart } from "@/context/CartContext";
import { formatCurrency, priceWithVat } from "@/lib/utils";
import Button from "@/components/ui/Button";

interface CheckoutForm {
  name: string;
  email: string;
  phone: string;
  street: string;
  city: string;
  zip: string;
  isCompany: boolean;
  ico: string;
  dic: string;
  note: string;
  paymentMethod: "transfer" | "cash";
}

const INITIAL: CheckoutForm = {
  name: "",
  email: "",
  phone: "",
  street: "",
  city: "",
  zip: "",
  isCompany: false,
  ico: "",
  dic: "",
  note: "",
  paymentMethod: "transfer",
};

export default function CheckoutPage() {
  const { cart, clearCart } = useCart();
  const [form, setForm] = useState<CheckoutForm>(INITIAL);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [orderNumber, setOrderNumber] = useState("");

  const total = cart.items.reduce(
    (sum, i) => sum + priceWithVat(i.product.price, i.product.vatRate) * i.quantity,
    0,
  );

  function update(field: keyof CheckoutForm, value: string | boolean) {
    setForm((f) => ({ ...f, [field]: value }));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await fetch("/api/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          customer: {
            name: form.name,
            email: form.email,
            phone: form.phone,
            ico: form.ico,
            dic: form.dic,
            isCompany: form.isCompany,
            address: {
              street: form.street,
              city: form.city,
              zip: form.zip,
              country: "Slovensko",
            },
          },
          items: cart.items.map((i) => ({
            productId: i.product.id,
            productName: i.product.name,
            sku: i.product.sku,
            quantity: i.quantity,
            unitPrice: i.product.price,
            vatRate: i.product.vatRate,
            unit: i.product.unit,
          })),
          paymentMethod: form.paymentMethod,
          note: form.note,
        }),
      });
      const data = (await res.json()) as { data: { orderNumber: string } };
      setOrderNumber(data.data.orderNumber);
      clearCart();
      setSubmitted(true);
    } finally {
      setLoading(false);
    }
  }

  if (submitted) {
    return (
      <div className="max-w-lg mx-auto px-4 py-20 text-center">
        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Objednávka prijatá!</h1>
        <p className="text-gray-500 mb-1">Číslo objednávky: <strong>{orderNumber}</strong></p>
        <p className="text-gray-500 mb-6">Potvrdenie sme odoslali na Váš e-mail. Ďakujeme!</p>
        <Link href="/eshop" className="inline-block px-6 py-3 bg-blue-700 text-white font-semibold rounded-xl hover:bg-blue-800 transition-colors">
          Späť do e-shopu
        </Link>
      </div>
    );
  }

  if (cart.items.length === 0) {
    return (
      <div className="max-w-lg mx-auto px-4 py-20 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Košík je prázdny</h1>
        <Link href="/eshop" className="inline-block mt-4 px-6 py-3 bg-blue-700 text-white font-semibold rounded-xl hover:bg-blue-800 transition-colors">
          Prejsť do e-shopu
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Objednávka</h1>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Form */}
        <form onSubmit={handleSubmit} className="lg:col-span-2 space-y-6">
          {/* Customer info */}
          <fieldset className="bg-white rounded-xl border border-gray-200 p-6">
            <legend className="text-base font-semibold text-gray-900 px-1">Kontaktné údaje</legend>
            <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="name">
                  Meno / Názov firmy *
                </label>
                <input required id="name" type="text" value={form.name} onChange={(e) => update("name", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="email">E-mail *</label>
                <input required id="email" type="email" value={form.email} onChange={(e) => update("email", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="phone">Telefón</label>
                <input id="phone" type="tel" value={form.phone} onChange={(e) => update("phone", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
            <div className="mt-4">
              <label className="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" checked={form.isCompany} onChange={(e) => update("isCompany", e.target.checked)}
                  className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                Objednávam na firmu (zadať IČO / DIČ)
              </label>
            </div>
            {form.isCompany && (
              <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="ico">IČO</label>
                  <input id="ico" type="text" value={form.ico} onChange={(e) => update("ico", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="dic">DIČ / IČ DPH</label>
                  <input id="dic" type="text" value={form.dic} onChange={(e) => update("dic", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            )}
          </fieldset>

          {/* Shipping address */}
          <fieldset className="bg-white rounded-xl border border-gray-200 p-6">
            <legend className="text-base font-semibold text-gray-900 px-1">Doručovacia adresa</legend>
            <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="street">Ulica a číslo *</label>
                <input required id="street" type="text" value={form.street} onChange={(e) => update("street", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="city">Mesto *</label>
                <input required id="city" type="text" value={form.city} onChange={(e) => update("city", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="zip">PSČ *</label>
                <input required id="zip" type="text" value={form.zip} onChange={(e) => update("zip", e.target.value)}
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
          </fieldset>

          {/* Payment */}
          <fieldset className="bg-white rounded-xl border border-gray-200 p-6">
            <legend className="text-base font-semibold text-gray-900 px-1">Spôsob úhrady</legend>
            <div className="mt-4 space-y-3">
              {([
                { value: "transfer", label: "Bankový prevod" },
                { value: "cash", label: "Hotovosť pri prevzatí" },
              ] as const).map(({ value, label }) => (
                <label key={value} className="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:border-blue-400 transition-colors">
                  <input
                    type="radio"
                    name="payment"
                    value={value}
                    checked={form.paymentMethod === value}
                    onChange={() => update("paymentMethod", value)}
                    className="text-blue-600 focus:ring-blue-500"
                  />
                  <span className="text-sm font-medium text-gray-700">{label}</span>
                </label>
              ))}
            </div>
          </fieldset>

          {/* Note */}
          <div className="bg-white rounded-xl border border-gray-200 p-6">
            <label className="block text-sm font-semibold text-gray-900 mb-2" htmlFor="note">
              Poznámka k objednávke
            </label>
            <textarea id="note" rows={3} value={form.note} onChange={(e) => update("note", e.target.value)}
              placeholder="Špeciálne požiadavky, termín dodania…"
              className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
          </div>

          <Button type="submit" loading={loading} size="lg" className="w-full">
            Odoslať objednávku
          </Button>
        </form>

        {/* Order summary */}
        <div className="bg-white rounded-xl border border-gray-200 p-6 h-fit sticky top-20">
          <h2 className="font-semibold text-gray-900 text-lg mb-4">Vaša objednávka</h2>
          <ul className="space-y-2 text-sm text-gray-600 mb-4">
            {cart.items.map(({ product, quantity }) => (
              <li key={product.id} className="flex justify-between gap-2">
                <span className="line-clamp-1">{product.name} ×{quantity}</span>
                <span className="shrink-0 font-medium text-gray-900">
                  {formatCurrency(priceWithVat(product.price, product.vatRate) * quantity)}
                </span>
              </li>
            ))}
          </ul>
          <div className="border-t border-gray-200 pt-3 flex justify-between font-bold">
            <span>Celkom s DPH</span>
            <span>{formatCurrency(total)}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
