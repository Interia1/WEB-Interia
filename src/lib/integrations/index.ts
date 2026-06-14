/**
 * External Integration Layer
 *
 * This module provides a unified interface for connecting Interia to
 * external programs such as POHODA (accounting / ERP), SuperFaktúra,
 * or any custom REST API.
 *
 * All providers implement the IntegrationAdapter interface so that
 * switching or adding providers requires only minimal changes.
 *
 * Configuration is read from environment variables (see .env.example).
 */

import type { Invoice, Order, Product, WebhookPayload } from "../types";

// ─── Adapter interface ────────────────────────────────────────────────────────

export interface IntegrationAdapter {
  /** Human-readable provider name */
  name: string;
  /** Whether the integration is configured and active */
  isEnabled(): boolean;
  /** Push a new or updated invoice to the external system */
  syncInvoice(invoice: Invoice): Promise<{ externalRef: string }>;
  /** Push a new or updated order to the external system */
  syncOrder(order: Order): Promise<{ externalRef: string }>;
  /** Push product catalogue changes to the external system */
  syncProducts(products: Product[]): Promise<void>;
  /** Handle an inbound webhook payload from the external system */
  handleWebhook(payload: WebhookPayload): Promise<void>;
}

// ─── POHODA adapter (stub) ────────────────────────────────────────────────────

export class PohodaAdapter implements IntegrationAdapter {
  name = "POHODA";
  private apiUrl: string;
  private apiKey: string;

  constructor() {
    this.apiUrl = process.env.POHODA_API_URL ?? "";
    this.apiKey = process.env.POHODA_API_KEY ?? "";
  }

  isEnabled(): boolean {
    return Boolean(this.apiUrl && this.apiKey);
  }

  private authHeader(): string {
    return "Bearer " + this.apiKey;
  }

  async syncInvoice(invoice: Invoice): Promise<{ externalRef: string }> {
    if (!this.isEnabled()) return { externalRef: "" };
    const response = await fetch(this.apiUrl + "/invoices", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: this.authHeader(),
      },
      body: JSON.stringify(invoice),
    });
    if (!response.ok) {
      throw new Error("POHODA syncInvoice failed: " + response.statusText);
    }
    const data = (await response.json()) as { id: string };
    return { externalRef: data.id };
  }

  async syncOrder(order: Order): Promise<{ externalRef: string }> {
    if (!this.isEnabled()) return { externalRef: "" };
    const response = await fetch(this.apiUrl + "/orders", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: this.authHeader(),
      },
      body: JSON.stringify(order),
    });
    if (!response.ok) {
      throw new Error("POHODA syncOrder failed: " + response.statusText);
    }
    const data = (await response.json()) as { id: string };
    return { externalRef: data.id };
  }

  async syncProducts(products: Product[]): Promise<void> {
    if (!this.isEnabled()) return;
    await fetch(this.apiUrl + "/products/batch", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: this.authHeader(),
      },
      body: JSON.stringify(products),
    });
  }

  async handleWebhook(payload: WebhookPayload): Promise<void> {
    console.log("[POHODA] Webhook received:", payload.event);
  }
}

// ─── SuperFaktúra adapter (stub) ──────────────────────────────────────────────

export class SuperFakturaAdapter implements IntegrationAdapter {
  name = "SuperFaktúra";
  private apiUrl: string;
  private apiKey: string;

  constructor() {
    this.apiUrl =
      process.env.SUPERFAKTURA_API_URL ?? "https://moja.superfaktura.sk";
    this.apiKey = process.env.SUPERFAKTURA_API_KEY ?? "";
  }

  isEnabled(): boolean {
    return Boolean(this.apiKey);
  }

  private authHeader(): string {
    return "SFAPI " + this.apiKey;
  }

  async syncInvoice(invoice: Invoice): Promise<{ externalRef: string }> {
    if (!this.isEnabled()) return { externalRef: "" };
    const response = await fetch(this.apiUrl + "/invoices/create", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: this.authHeader(),
      },
      body: JSON.stringify({ invoice }),
    });
    if (!response.ok) {
      throw new Error(
        "SuperFaktúra syncInvoice failed: " + response.statusText,
      );
    }
    const data = (await response.json()) as { Invoice: { id: string } };
    return { externalRef: String(data.Invoice.id) };
  }

  async syncOrder(_order: Order): Promise<{ externalRef: string }> {
    // SuperFaktúra is primarily an invoicing tool; orders are not directly supported
    return { externalRef: "" };
  }

  async syncProducts(_products: Product[]): Promise<void> {
    // SuperFaktúra does not have a dedicated product catalogue sync
  }

  async handleWebhook(payload: WebhookPayload): Promise<void> {
    console.log("[SuperFaktúra] Webhook received:", payload.event);
  }
}

// ─── Factory ──────────────────────────────────────────────────────────────────

/**
 * Returns all configured and enabled adapters.
 * Add new adapters here as the company adopts more external systems.
 */
export function getActiveAdapters(): IntegrationAdapter[] {
  const adapters: IntegrationAdapter[] = [
    new PohodaAdapter(),
    new SuperFakturaAdapter(),
  ];
  return adapters.filter((a) => a.isEnabled());
}

/**
 * Convenience helper: sync an invoice to all active integrations.
 * Returns a map of provider name → externalRef.
 */
export async function syncInvoiceToAll(
  invoice: Invoice,
): Promise<Record<string, string>> {
  const results: Record<string, string> = {};
  for (const adapter of getActiveAdapters()) {
    try {
      const { externalRef } = await adapter.syncInvoice(invoice);
      results[adapter.name] = externalRef;
    } catch (err) {
      console.error("[Integration] " + adapter.name + " syncInvoice error:", err);
    }
  }
  return results;
}

/**
 * Convenience helper: sync an order to all active integrations.
 */
export async function syncOrderToAll(
  order: Order,
): Promise<Record<string, string>> {
  const results: Record<string, string> = {};
  for (const adapter of getActiveAdapters()) {
    try {
      const { externalRef } = await adapter.syncOrder(order);
      results[adapter.name] = externalRef;
    } catch (err) {
      console.error("[Integration] " + adapter.name + " syncOrder error:", err);
    }
  }
  return results;
}
