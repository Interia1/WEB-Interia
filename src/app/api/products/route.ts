import { NextRequest, NextResponse } from "next/server";
import type { ApiResponse, Product } from "@/lib/types";
import { PRODUCTS } from "@/lib/data";

export async function GET(req: NextRequest): Promise<NextResponse> {
  const { searchParams } = req.nextUrl;
  const category = searchParams.get("category");
  const search = searchParams.get("q");
  const page = Math.max(1, Number(searchParams.get("page") ?? "1"));
  const pageSize = Math.min(100, Math.max(1, Number(searchParams.get("pageSize") ?? "20")));

  let products = PRODUCTS.filter((p) => p.isActive);

  if (category) {
    products = products.filter((p) => p.category === category);
  }
  if (search) {
    const q = search.toLowerCase();
    products = products.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        p.tags.some((t) => t.toLowerCase().includes(q)),
    );
  }

  const total = products.length;
  const paginated = products.slice((page - 1) * pageSize, page * pageSize);

  const response: ApiResponse<Product[]> = {
    data: paginated,
    total,
    page,
    pageSize,
  };

  return NextResponse.json(response);
}

export async function POST(req: NextRequest): Promise<NextResponse> {
  const body = (await req.json()) as Omit<Product, "id" | "createdAt" | "updatedAt">;

  const newProduct: Product = {
    ...body,
    id: "prod-" + Date.now(),
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  };

  // In a real app: persist to database
  // await db.products.insert(newProduct);

  return NextResponse.json({ data: newProduct }, { status: 201 });
}
