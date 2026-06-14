"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import type { Product } from "@/lib/types";
import { formatCurrency, priceWithVat } from "@/lib/utils";
import { useCart } from "@/context/CartContext";
import Badge from "@/components/ui/Badge";
import Button from "@/components/ui/Button";

const CATEGORY_LABELS: Record<Product["category"], string> = {
  materialy: "Materiály",
  polotovary: "Polotovary",
  "atypicka-vyroba": "Atypická výroba",
};

interface ProductCardProps {
  product: Product;
}

export default function ProductCard({ product }: ProductCardProps) {
  const { addItem, getItem } = useCart();
  const [added, setAdded] = useState(false);
  const inCart = getItem(product.id);
  const isQuoteOnly = product.price === 0;

  function handleAddToCart() {
    addItem(product, product.minOrderQuantity);
    setAdded(true);
    setTimeout(() => setAdded(false), 1500);
  }

  return (
    <div className="group flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
      {/* Image */}
      <Link href={"/eshop/product/" + product.id} className="block relative aspect-[4/3] bg-gray-100 overflow-hidden">
        {product.images[0] ? (
          <Image
            src={product.images[0]}
            alt={product.name}
            fill
            className="object-cover group-hover:scale-105 transition-transform duration-300"
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
          />
        ) : (
          <div className="absolute inset-0 flex items-center justify-center text-gray-300">
            <svg className="w-16 h-16" fill="none" stroke="currentColor" strokeWidth={1} viewBox="0 0 24 24" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3H8L6 7h12l-2-4z" />
            </svg>
          </div>
        )}
        <div className="absolute top-2 left-2">
          <Badge variant="blue">{CATEGORY_LABELS[product.category]}</Badge>
        </div>
        {product.stock === 0 && (
          <div className="absolute inset-0 bg-black/40 flex items-center justify-center">
            <span className="text-white font-semibold text-sm bg-black/60 px-3 py-1 rounded-full">
              Vypredané
            </span>
          </div>
        )}
      </Link>

      {/* Content */}
      <div className="flex flex-col flex-1 p-4 gap-3">
        <div className="flex-1">
          <Link href={"/eshop/product/" + product.id}>
            <h3 className="font-semibold text-gray-900 text-sm leading-snug hover:text-blue-700 transition-colors line-clamp-2">
              {product.name}
            </h3>
          </Link>
          <p className="mt-1 text-xs text-gray-500 line-clamp-2">
            {product.description}
          </p>
          <p className="mt-1 text-xs text-gray-400">SKU: {product.sku}</p>
        </div>

        {/* Price */}
        <div>
          {isQuoteOnly ? (
            <p className="text-sm font-semibold text-blue-700">
              Cena na dopyt
            </p>
          ) : (
            <div>
              <p className="text-base font-bold text-gray-900">
                {formatCurrency(priceWithVat(product.price, product.vatRate))}{" "}
                <span className="text-xs font-normal text-gray-500">
                  / {product.unit} s DPH
                </span>
              </p>
              <p className="text-xs text-gray-400">
                {formatCurrency(product.price)} bez DPH
              </p>
            </div>
          )}
        </div>

        {/* Action */}
        {isQuoteOnly ? (
          <Link
            href="/contact"
            className="block text-center py-2 px-4 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium transition-colors"
          >
            Dopytať cenu
          </Link>
        ) : (
          <Button
            variant={inCart ? "secondary" : "primary"}
            size="sm"
            onClick={handleAddToCart}
            disabled={product.stock === 0}
            className="w-full"
          >
            {added ? "Pridané ✓" : inCart ? "Pridať ďalšie" : "Do košíka"}
          </Button>
        )}
      </div>
    </div>
  );
}
