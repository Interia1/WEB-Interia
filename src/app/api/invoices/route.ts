import { NextRequest, NextResponse } from "next/server";
import type { ApiResponse, Invoice } from "@/lib/types";
import { SAMPLE_INVOICES } from "@/lib/data";
import { syncInvoiceToAll } from "@/lib/integrations";

// In-memory store for runtime-created invoices
const runtimeInvoices: Invoice[] = [];

function allInvoices(): Invoice[] {
  return [...SAMPLE_INVOICES, ...runtimeInvoices];
}

export async function GET(req: NextRequest): Promise<NextResponse> {
  const { searchParams } = req.nextUrl;
  const status = searchParams.get("status");
  const page = Math.max(1, Number(searchParams.get("page") ?? "1"));
  const pageSize = Math.min(100, Math.max(1, Number(searchParams.get("pageSize") ?? "20")));

  let invoices = allInvoices();
  if (status) invoices = invoices.filter((i) => i.status === status);

  const total = invoices.length;
  const paginated = invoices.slice((page - 1) * pageSize, page * pageSize);

  const response: ApiResponse<Invoice[]> = {
    data: paginated,
    total,
    page,
    pageSize,
  };

  return NextResponse.json(response);
}

export async function POST(req: NextRequest): Promise<NextResponse> {
  const body = await req.json();

  const newInvoice: Invoice = {
    id: "inv-" + Date.now(),
    invoiceNumber: body.invoiceNumber,
    status: body.status ?? "draft",
    orderId: body.orderId,
    supplier: body.supplier,
    customer: body.customer,
    items: body.items ?? [],
    issueDate: body.issueDate,
    dueDate: body.dueDate,
    paymentMethod: body.paymentMethod ?? "transfer",
    variableSymbol: body.variableSymbol,
    note: body.note,
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  };

  runtimeInvoices.push(newInvoice);

  // Sync to external integrations (non-blocking)
  syncInvoiceToAll(newInvoice).catch((err) =>
    console.error("[API/invoices] integration sync error:", err),
  );

  return NextResponse.json({ data: newInvoice }, { status: 201 });
}
