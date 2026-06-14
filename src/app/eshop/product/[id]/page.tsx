import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import type { Metadata } from "next";
import { PRODUCTS } from "@/lib/data";
import { formatCurrency, priceWithVat } from "@/lib/utils";
import Badge from "@/components/ui/Badge";
import AddToCartButton from "./AddToCartButton";

interface Props {
  params: Promise<{ id: string }>;
}

export async function generateStaticParams() {
  return PRODUCTS.map((p) => ({ id: p.id }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params;
  const product = PRODUCTS.find((p) => p.id === id);
  if (!product) return {};
  return { title: product.name };
}

const CATEGORY_LABELS: Record<string, string> = {
  materialy: "Materiály",
  polotovary: "Polotovary",
  "atypicka-vyroba": "Atypická výroba",
};

export default async function ProductDetailPage({ params }: Props) {
  const { id } = await params;
  const product = PRODUCTS.find((p) => p.id === id);
  if (!product) notFound();

  const isQuoteOnly = product.price === 0;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Breadcrumbs */}
      <nav className="text-sm text-gray-500 mb-6 flex items-center gap-1.5">
        <Link href="/eshop" className="hover:text-blue-700 transition-colors">E-shop</Link>
        <span>/</span>
        <Link href={"/eshop?category=" + product.category} className="hover:text-blue-700 transition-colors">
          {CATEGORY_LABELS[product.category]}
        </Link>
        <span>/</span>
        <span className="text-gray-700 font-medium truncate">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {/* Image */}
        <div className="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden">
          {product.images[0] ? (
            <Image
              src={product.images[0]}
              alt={product.name}
              fill
              className="object-cover"
              priority
              sizes="(max-width: 1024px) 100vw, 50vw"
            />
          ) : (
            <div className="absolute inset-0 flex items-center justify-center text-gray-300">
              <svg className="w-24 h-24" fill="none" stroke="currentColor" strokeWidth={0.75} viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3H8L6 7h12l-2-4z" />
              </svg>
            </div>
          )}
        </div>

        {/* Details */}
        <div className="flex flex-col gap-5">
          <div>
            <Badge variant="blue" className="mb-2">
              {CATEGORY_LABELS[product.category]}
            </Badge>
            <h1 className="text-3xl font-bold text-gray-900">{product.name}</h1>
            <p className="text-sm text-gray-400 mt-1">SKU: {product.sku}</p>
          </div>

          <p className="text-gray-600 leading-relaxed">{product.description}</p>

          {/* Attributes */}
          {product.attributes.length > 0 && (
            <div>
              <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                Parametre
              </h2>
              <dl className="grid grid-cols-2 gap-x-4 gap-y-2">
                {product.attributes.map((attr) => (
                  <div key={attr.name} className="flex justify-between text-sm border-b border-gray-100 pb-1">
                    <dt className="text-gray-500">{attr.name}</dt>
                    <dd className="font-medium text-gray-800">
                      {attr.value}
                      {attr.unit ? " " + attr.unit : ""}
                    </dd>
                  </div>
                ))}
              </dl>
            </div>
          )}

          {/* Price */}
          <div className="bg-gray-50 rounded-xl p-5 flex flex-col gap-3">
            {isQuoteOnly ? (
              <p className="text-xl font-bold text-blue-700">Cena na dopyt</p>
            ) : (
              <>
                <div>
                  <p className="text-3xl font-bold text-gray-900">
                    {formatCurrency(priceWithVat(product.price, product.vatRate))}
                    <span className="text-sm font-normal text-gray-500 ml-1">
                      / {product.unit} s DPH
                    </span>
                  </p>
                  <p className="text-sm text-gray-400 mt-0.5">
                    {formatCurrency(product.price)} bez DPH
                  </p>
                </div>
                <p className="text-xs text-gray-500">
                  Min. objednávka: {product.minOrderQuantity} {product.unit} ·{" "}
                  {product.stock > 0 ? (
                    <span className="text-green-600 font-medium">
                      Skladom ({product.stock} {product.unit})
                    </span>
                  ) : (
                    <span className="text-red-600 font-medium">Vypredané</span>
                  )}
                </p>
              </>
            )}

            {isQuoteOnly ? (
              <Link
                href="/contact"
                className="block text-center py-3 px-6 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold transition-colors"
              >
                Odoslať dopyt
              </Link>
            ) : (
              <AddToCartButton product={product} />
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
