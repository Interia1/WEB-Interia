"use client";

import { useMemo, useState } from "react";
import type { ProductCategory } from "@/lib/types";
import { PRODUCTS } from "@/lib/data";
import ProductCard from "@/components/eshop/ProductCard";
import CategoryFilter from "@/components/eshop/CategoryFilter";

export default function EshopPage() {
  const [category, setCategory] = useState<ProductCategory | "all">("all");
  const [search, setSearch] = useState("");

  const filtered = useMemo(() => {
    let list = PRODUCTS.filter((p) => p.isActive);
    if (category !== "all") list = list.filter((p) => p.category === category);
    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter(
        (p) =>
          p.name.toLowerCase().includes(q) ||
          p.sku.toLowerCase().includes(q) ||
          p.tags.some((t) => t.toLowerCase().includes(q)),
      );
    }
    return list;
  }, [category, search]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 className="text-3xl font-bold text-gray-900 mb-2">E-shop</h1>
      <p className="text-gray-500 mb-8">
        Materiály, polotovary a atypická výroba na mieru
      </p>

      {/* Filters */}
      <div className="flex flex-col sm:flex-row gap-4 mb-8">
        <CategoryFilter selected={category} onChange={setCategory} />
        <div className="relative sm:ml-auto">
          <input
            type="search"
            placeholder="Hľadať produkt…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64"
          />
          <svg
            className="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none"
            stroke="currentColor"
            strokeWidth={2}
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      {/* Results count */}
      <p className="text-sm text-gray-500 mb-4">
        Zobrazené: {filtered.length} z {PRODUCTS.filter((p) => p.isActive).length} produktov
      </p>

      {/* Grid */}
      {filtered.length === 0 ? (
        <div className="text-center py-20 text-gray-400">
          <svg className="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" strokeWidth={1} viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <p className="text-lg font-medium">Žiadne produkty nenájdené</p>
          <p className="text-sm mt-1">Skúste upraviť filtre alebo vyhľadávanie.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {filtered.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
}
