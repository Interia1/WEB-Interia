import { NextRequest, NextResponse } from "next/server";
import type { WebhookPayload } from "@/lib/types";
import { getActiveAdapters } from "@/lib/integrations";

/**
 * POST /api/integrations/webhook
 *
 * Receives inbound webhook events from external systems (POHODA,
 * SuperFaktúra, payment gateways, …).  Each registered adapter
 * handles the payload if it understands the event source.
 *
 * The endpoint validates a shared secret from the Authorization
 * header before processing.
 */
export async function POST(req: NextRequest): Promise<NextResponse> {
  // Validate webhook secret
  const webhookSecret = process.env.WEBHOOK_SECRET;
  if (webhookSecret) {
    const authHeader = req.headers.get("authorization") ?? "";
    const provided = authHeader.startsWith("Bearer ")
      ? authHeader.slice(7)
      : authHeader;
    if (provided !== webhookSecret) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }
  }

  let payload: WebhookPayload;
  try {
    payload = (await req.json()) as WebhookPayload;
  } catch {
    return NextResponse.json({ error: "Invalid JSON body" }, { status: 400 });
  }

  if (!payload.event || !payload.source) {
    return NextResponse.json(
      { error: "Missing required fields: event, source" },
      { status: 400 },
    );
  }

  const adapters = getActiveAdapters();
  const errors: string[] = [];

  await Promise.allSettled(
    adapters.map(async (adapter) => {
      try {
        await adapter.handleWebhook(payload);
      } catch (err) {
        const msg = err instanceof Error ? err.message : String(err);
        errors.push("[" + adapter.name + "] " + msg);
      }
    }),
  );

  if (errors.length > 0) {
    console.error("[Webhook] Handler errors:", errors);
  }

  return NextResponse.json({ received: true }, { status: 200 });
}
