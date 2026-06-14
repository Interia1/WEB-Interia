import { NextRequest, NextResponse } from "next/server";
import { PRODUCTS } from "@/lib/data";

interface Params {
  params: Promise<{ id: string }>;
}

export async function GET(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const product = PRODUCTS.find((p) => p.id === id);
  if (!product) {
    return NextResponse.json({ error: "Produkt neexistuje" }, { status: 404 });
  }
  return NextResponse.json({ data: product });
}

export async function PUT(req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const existing = PRODUCTS.find((p) => p.id === id);
  if (!existing) {
    return NextResponse.json({ error: "Produkt neexistuje" }, { status: 404 });
  }

  const body = await req.json();
  const updated = {
    ...existing,
    ...body,
    id,
    updatedAt: new Date().toISOString(),
  };

  // In a real app: persist to database
  // await db.products.update(id, updated);

  return NextResponse.json({ data: updated });
}

export async function DELETE(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const existing = PRODUCTS.find((p) => p.id === id);
  if (!existing) {
    return NextResponse.json({ error: "Produkt neexistuje" }, { status: 404 });
  }

  // In a real app: await db.products.delete(id);

  return NextResponse.json({ data: { id } });
}
