"use client";

import { useState } from "react";
import type { Product } from "@/lib/types";
import { useCart } from "@/context/CartContext";
import { clamp } from "@/lib/utils";
import Button from "@/components/ui/Button";

export default function AddToCartButton({ product }: { product: Product }) {
  const { addItem } = useCart();
  const [qty, setQty] = useState(product.minOrderQuantity);
  const [added, setAdded] = useState(false);

  function handleAdd() {
    addItem(product, qty);
    setAdded(true);
    setTimeout(() => setAdded(false), 1500);
  }

  return (
    <div className="flex items-center gap-3">
      <div className="flex items-center border border-gray-300 rounded-lg overflow-hidden">
        <button
          type="button"
          onClick={() =>
            setQty((q) => clamp(q - 1, product.minOrderQuantity, product.stock))
          }
          className="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none"
          aria-label="Znížiť množstvo"
        >
          −
        </button>
        <input
          type="number"
          min={product.minOrderQuantity}
          max={product.stock}
          value={qty}
          onChange={(e) =>
            setQty(clamp(Number(e.target.value), product.minOrderQuantity, product.stock))
          }
          className="w-14 text-center text-sm font-medium border-x border-gray-300 py-2 focus:outline-none"
          aria-label="Množstvo"
        />
        <button
          type="button"
          onClick={() =>
            setQty((q) => clamp(q + 1, product.minOrderQuantity, product.stock))
          }
          className="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none"
          aria-label="Zvýšiť množstvo"
        >
          +
        </button>
      </div>
      <Button
        onClick={handleAdd}
        disabled={product.stock === 0}
        className="flex-1"
      >
        {added ? "Pridané ✓" : "Do košíka"}
      </Button>
    </div>
  );
}
