"use client";

import type { ProductCategory } from "@/lib/types";

const CATEGORIES: { value: ProductCategory | "all"; label: string; description: string }[] = [
  { value: "all", label: "Všetky produkty", description: "" },
  { value: "materialy", label: "Materiály", description: "Plechy, profily, nerez, …" },
  { value: "polotovary", label: "Polotovary", description: "Zvarané diely, rámy, …" },
  {
    value: "atypicka-vyroba",
    label: "Atypická výroba",
    description: "Laser, ohýbanie, projekty na mieru",
  },
];

interface CategoryFilterProps {
  selected: ProductCategory | "all";
  onChange: (value: ProductCategory | "all") => void;
}

export default function CategoryFilter({ selected, onChange }: CategoryFilterProps) {
  return (
    <div className="flex flex-wrap gap-2">
      {CATEGORIES.map(({ value, label }) => (
        <button
          key={value}
          type="button"
          onClick={() => onChange(value)}
          className={[
            "px-4 py-2 rounded-full text-sm font-medium border transition-colors",
            selected === value
              ? "bg-blue-700 text-white border-blue-700"
              : "bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-700",
          ].join(" ")}
        >
          {label}
        </button>
      ))}
    </div>
  );
}
