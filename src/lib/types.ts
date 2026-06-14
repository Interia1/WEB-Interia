// ─── Product & Catalog ───────────────────────────────────────────────────────

export type ProductCategory =
  | "materialy"        // raw materials
  | "polotovary"       // semi-finished products
  | "atypicka-vyroba"; // custom / atypical manufacturing

export interface ProductAttribute {
  name: string;
  value: string;
  unit?: string;
}

export interface Product {
  id: string;
  sku: string;
  name: string;
  description: string;
  category: ProductCategory;
  price: number;         // EUR, without VAT
  vatRate: number;       // e.g. 0.20 for 20 %
  unit: string;          // "ks", "m", "m²", "kg", …
  stock: number;
  minOrderQuantity: number;
  images: string[];
  attributes: ProductAttribute[];
  tags: string[];
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

// ─── Cart ────────────────────────────────────────────────────────────────────

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface Cart {
  items: CartItem[];
}

// ─── Order ───────────────────────────────────────────────────────────────────

export type OrderStatus =
  | "nova"        // new
  | "potvrdena"   // confirmed
  | "v-vyrobe"    // in production
  | "odoslana"    // shipped
  | "dorucena"    // delivered
  | "zrusena";    // cancelled

export interface OrderItem {
  productId: string;
  productName: string;
  sku: string;
  quantity: number;
  unitPrice: number;
  vatRate: number;
  unit: string;
}

export interface Address {
  street: string;
  city: string;
  zip: string;
  country: string;
}

export interface Order {
  id: string;
  orderNumber: string;
  status: OrderStatus;
  customer: Customer;
  items: OrderItem[];
  shippingAddress: Address;
  billingAddress: Address;
  note?: string;
  createdAt: string;
  updatedAt: string;
  externalRef?: string;  // reference in external ERP/system
}

// ─── Customer ────────────────────────────────────────────────────────────────

export interface Customer {
  id: string;
  name: string;
  email: string;
  phone?: string;
  ico?: string;   // company registration number (IČO)
  dic?: string;   // tax ID (DIČ)
  icdph?: string; // VAT number (IČ DPH)
  address: Address;
  isCompany: boolean;
}

// ─── Invoice ─────────────────────────────────────────────────────────────────

export type InvoiceStatus = "draft" | "issued" | "sent" | "paid" | "overdue" | "cancelled";

export interface InvoiceItem {
  description: string;
  quantity: number;
  unit: string;
  unitPrice: number;
  vatRate: number;
}

export interface Invoice {
  id: string;
  invoiceNumber: string;
  status: InvoiceStatus;
  orderId?: string;
  supplier: BusinessInfo;
  customer: Customer;
  items: InvoiceItem[];
  issueDate: string;
  dueDate: string;
  paymentMethod: "transfer" | "cash" | "card";
  note?: string;
  variableSymbol: string;
  createdAt: string;
  updatedAt: string;
  externalRef?: string;
}

export interface BusinessInfo {
  name: string;
  ico: string;
  dic: string;
  icdph: string;
  address: Address;
  bank: string;
  iban: string;
  swift: string;
  email: string;
  phone: string;
  web: string;
}

// ─── Integration / External Programs ─────────────────────────────────────────

export type IntegrationProvider =
  | "pohoda"     // POHODA accounting software (common in SK/CZ)
  | "superfaktura"
  | "custom";

export interface IntegrationConfig {
  provider: IntegrationProvider;
  apiUrl: string;
  apiKey: string;
  enabled: boolean;
}

export interface WebhookPayload {
  event: string;
  data: Record<string, unknown>;
  timestamp: string;
  source: string;
}

// ─── API Responses ───────────────────────────────────────────────────────────

export interface ApiResponse<T> {
  data: T;
  total?: number;
  page?: number;
  pageSize?: number;
}

export interface ApiError {
  error: string;
  code?: string;
  details?: unknown;
}
