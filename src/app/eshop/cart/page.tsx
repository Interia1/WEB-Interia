"use client";

import Image from "next/image";
import Link from "next/link";
import { useCart } from "@/context/CartContext";
import { formatCurrency, priceWithVat } from "@/lib/utils";

export default function CartPage() {
  const { cart, removeItem, updateQuantity } = useCart();

  const subtotal = cart.items.reduce(
    (sum, i) => sum + i.product.price * i.quantity,
    0,
  );
  const vat = cart.items.reduce(
    (sum, i) =>
      sum + i.product.price * i.quantity * i.product.vatRate,
    0,
  );
  const total = subtotal + vat;

  if (cart.items.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <svg className="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" strokeWidth={1} viewBox="0 0 24 24" aria-hidden="true">
          <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L5 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z" />
        </svg>
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Košík je prázdny</h1>
        <p className="text-gray-500 mb-6">Pridajte produkty z e-shopu.</p>
        <Link href="/eshop" className="inline-block px-6 py-3 bg-blue-700 text-white font-semibold rounded-xl hover:bg-blue-800 transition-colors">
          Prejsť do e-shopu
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Košík</h1>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Items */}
        <div className="lg:col-span-2 space-y-4">
          {cart.items.map(({ product, quantity }) => (
            <div key={product.id} className="bg-white rounded-xl border border-gray-200 p-4 flex gap-4">
              <div className="relative w-20 h-20 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                {product.images[0] ? (
                  <Image src={product.images[0]} alt={product.name} fill className="object-cover" sizes="80px" />
                ) : (
                  <div className="absolute inset-0 flex items-center justify-center text-gray-300">
                    <svg className="w-8 h-8" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24" aria-hidden="true">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3H8L6 7h12l-2-4z" />
                    </svg>
                  </div>
                )}
              </div>
              <div className="flex-1 min-w-0">
                <Link href={"/eshop/product/" + product.id} className="font-medium text-gray-900 hover:text-blue-700 text-sm line-clamp-2 transition-colors">
                  {product.name}
                </Link>
                <p className="text-xs text-gray-400 mt-0.5">{product.sku}</p>
                <p className="text-sm text-gray-600 mt-1">
                  {formatCurrency(priceWithVat(product.price, product.vatRate))} / {product.unit}
                </p>
              </div>
              <div className="flex flex-col items-end gap-2 shrink-0">
                <button
                  onClick={() => removeItem(product.id)}
                  className="text-gray-400 hover:text-red-500 transition-colors"
                  aria-label="Odstraniť položku"
                >
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <div className="flex items-center border border-gray-300 rounded-lg overflow-hidden text-sm">
                  <button
                    onClick={() => updateQuantity(product.id, quantity - 1)}
                    className="px-2 py-1 hover:bg-gray-100 transition-colors"
                    aria-label="Znížiť"
                  >
                    −
                  </button>
                  <span className="px-3 py-1 border-x border-gray-300 font-medium min-w-[2.5rem] text-center">
                    {quantity}
                  </span>
                  <button
                    onClick={() => updateQuantity(product.id, quantity + 1)}
                    className="px-2 py-1 hover:bg-gray-100 transition-colors"
                    aria-label="Zvýšiť"
                  >
                    +
                  </button>
                </div>
                <p className="font-semibold text-gray-900 text-sm">
                  {formatCurrency(priceWithVat(product.price, product.vatRate) * quantity)}
                </p>
              </div>
            </div>
          ))}
        </div>

        {/* Summary */}
        <div className="bg-white rounded-xl border border-gray-200 p-6 h-fit sticky top-20">
          <h2 className="font-semibold text-gray-900 text-lg mb-4">Zhrnutie</h2>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-gray-500">Medziúčet bez DPH</dt>
              <dd>{formatCurrency(subtotal)}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-gray-500">DPH</dt>
              <dd>{formatCurrency(vat)}</dd>
            </div>
            <div className="flex justify-between font-bold text-base border-t border-gray-200 pt-2 mt-2">
              <dt>Celkom s DPH</dt>
              <dd>{formatCurrency(total)}</dd>
            </div>
          </dl>
          <Link href="/eshop/checkout" className="mt-6 block text-center py-3 px-6 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-semibold transition-colors">
            Pokračovať v objednávke
          </Link>
          <Link href="/eshop" className="mt-2 block text-center text-sm text-blue-700 hover:underline">
            ← Pokračovať v nákupe
          </Link>
        </div>
      </div>
    </div>
  );
}
