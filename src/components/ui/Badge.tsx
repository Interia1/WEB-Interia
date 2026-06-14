import type { ReactNode } from "react";

type Variant =
  | "default"
  | "blue"
  | "green"
  | "yellow"
  | "red"
  | "gray"
  | "indigo";

const VARIANT_CLASSES: Record<Variant, string> = {
  default: "bg-gray-100 text-gray-800",
  blue: "bg-blue-100 text-blue-800",
  green: "bg-green-100 text-green-800",
  yellow: "bg-yellow-100 text-yellow-800",
  red: "bg-red-100 text-red-800",
  gray: "bg-gray-200 text-gray-600",
  indigo: "bg-indigo-100 text-indigo-800",
};

interface BadgeProps {
  children: ReactNode;
  variant?: Variant;
  className?: string;
}

export default function Badge({
  children,
  variant = "default",
  className = "",
}: BadgeProps) {
  return (
    <span
      className={[
        "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium",
        VARIANT_CLASSES[variant],
        className,
      ].join(" ")}
    >
      {children}
    </span>
  );
}
