import { NextRequest, NextResponse } from "next/server";
import { SAMPLE_INVOICES } from "@/lib/data";
import type { Invoice } from "@/lib/types";

const runtimeInvoices: Invoice[] = [];

function findInvoice(id: string): Invoice | undefined {
  return (
    SAMPLE_INVOICES.find((i) => i.id === id) ??
    runtimeInvoices.find((i) => i.id === id)
  );
}

interface Params {
  params: Promise<{ id: string }>;
}

export async function GET(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const invoice = findInvoice(id);
  if (!invoice) {
    return NextResponse.json({ error: "Faktúra neexistuje" }, { status: 404 });
  }
  return NextResponse.json({ data: invoice });
}

export async function PUT(req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const invoice = findInvoice(id);
  if (!invoice) {
    return NextResponse.json({ error: "Faktúra neexistuje" }, { status: 404 });
  }

  const body = await req.json();
  const updated: Invoice = {
    ...invoice,
    ...body,
    id,
    updatedAt: new Date().toISOString(),
  };

  // In a real app: await db.invoices.update(id, updated);

  return NextResponse.json({ data: updated });
}

export async function DELETE(_req: NextRequest, { params }: Params): Promise<NextResponse> {
  const { id } = await params;
  const invoice = findInvoice(id);
  if (!invoice) {
    return NextResponse.json({ error: "Faktúra neexistuje" }, { status: 404 });
  }

  // In a real app: await db.invoices.delete(id);

  return NextResponse.json({ data: { id } });
}
