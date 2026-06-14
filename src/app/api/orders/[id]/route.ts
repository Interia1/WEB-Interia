import { NextRequest, NextResponse } from "next/server";
import { SAMPLE_ORDERS } from "@/lib/data";
import type { Order } from "@/lib/types";

const runtimeOrders: Order[] = [];

function findOrder(id: string): Order | undefined {
  return (
    SAMPLE_ORDERS.find((o) => o.id === id) ??
    runtimeOrders.find((o) => o.id === id)
  );
}

interface Params {
  params: Promise<{ id: string }>;
}

export async function GET(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const order = findOrder(id);
  if (!order) {
    return NextResponse.json({ error: "Objednávka neexistuje" }, { status: 404 });
  }
  return NextResponse.json({ data: order });
}

export async function PUT(req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const order = findOrder(id);
  if (!order) {
    return NextResponse.json({ error: "Objednávka neexistuje" }, { status: 404 });
  }

  const body = await req.json();
  const updated: Order = {
    ...order,
    ...body,
    id,
    updatedAt: new Date().toISOString(),
  };

  // In a real app: await db.orders.update(id, updated);

  return NextResponse.json({ data: updated });
}

export async function DELETE(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const order = findOrder(id);
  if (!order) {
    return NextResponse.json({ error: "Objednávka neexistuje" }, { status: 404 });
  }

  // In a real app: await db.orders.delete(id);

  return NextResponse.json({ data: { id } });
}
