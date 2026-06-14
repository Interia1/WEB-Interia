import { NextRequest, NextResponse } from "next/server";
import type { ApiResponse, Order } from "@/lib/types";
import { SAMPLE_ORDERS } from "@/lib/data";
import { generateOrderNumber } from "@/lib/utils";
import { syncOrderToAll } from "@/lib/integrations";

// In-memory store for new orders created at runtime (replaces a real DB)
const runtimeOrders: Order[] = [];

function allOrders(): Order[] {
  return [...SAMPLE_ORDERS, ...runtimeOrders];
}

export async function GET(req: NextRequest): Promise<NextResponse> {
  const { searchParams } = req.nextUrl;
  const status = searchParams.get("status");
  const page = Math.max(1, Number(searchParams.get("page") ?? "1"));
  const pageSize = Math.min(100, Math.max(1, Number(searchParams.get("pageSize") ?? "20")));

  let orders = allOrders();
  if (status) orders = orders.filter((o) => o.status === status);

  const total = orders.length;
  const paginated = orders.slice((page - 1) * pageSize, page * pageSize);

  const response: ApiResponse<Order[]> = {
    data: paginated,
    total,
    page,
    pageSize,
  };

  return NextResponse.json(response);
}

export async function POST(req: NextRequest): Promise<NextResponse> {
  const body = await req.json();
  const sequence = allOrders().length + 1;

  const newOrder: Order = {
    id: "ord-" + Date.now(),
    orderNumber: generateOrderNumber(sequence),
    status: "nova",
    customer: body.customer,
    items: body.items,
    shippingAddress: body.customer?.address ?? body.shippingAddress,
    billingAddress: body.customer?.address ?? body.billingAddress,
    note: body.note,
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  };

  runtimeOrders.push(newOrder);

  // Sync to external integrations (non-blocking)
  syncOrderToAll(newOrder).catch((err) =>
    console.error("[API/orders] integration sync error:", err),
  );

  return NextResponse.json({ data: newOrder }, { status: 201 });
}
