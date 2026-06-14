import Link from "next/link";
import type { Metadata } from "next";
import { PRODUCTS } from "@/lib/data";
import ProductCard from "@/components/eshop/ProductCard";

export const metadata: Metadata = {
  title: "Interia – Materiály, Polotovary, Atypická výroba",
};

const FEATURES = [
  {
    icon: (
      <svg className="w-7 h-7" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3H8L6 7h12l-2-4z" />
      </svg>
    ),
    title: "Predaj materiálov",
    desc: "Oceľové plechy, profily, nerezové materiály a ďalší sortiment skladom s rýchlym dodaním.",
  },
  {
    icon: (
      <svg className="w-7 h-7" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M11 4a7 7 0 100 14A7 7 0 0011 4zm0 0V2m0 18v-2m8-8h2M2 12H0m15.66-6.34l1.42-1.42M4.93 19.07L3.51 17.65M19.07 19.07l-1.42-1.42M4.93 4.93L3.51 3.51" />
      </svg>
    ),
    title: "Výroba polotovarov",
    desc: "Sériová výroba zváraných dielov, konzol, rámov a ďalších polotovarov podľa vašich výkresov.",
  },
  {
    icon: (
      <svg className="w-7 h-7" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
      </svg>
    ),
    title: "Atypická výroba",
    desc: "Laserové rezanie, CNC ohýbanie a kompletná výroba oceľových konštrukcií na mieru.",
  },
  {
    icon: (
      <svg className="w-7 h-7" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
      </svg>
    ),
    title: "Integrácie & faktúrovanie",
    desc: "Prepojenie s POHODA a SuperFaktúra. Online správa objednávok a faktúr v jednom mieste.",
  },
];

export default function HomePage() {
  const featured = PRODUCTS.slice(0, 3);

  return (
    <>
      {/* Hero */}
      <section className="bg-gradient-to-br from-blue-700 to-blue-900 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
          <div className="max-w-2xl">
            <p className="text-blue-200 text-sm font-semibold uppercase tracking-widest mb-4">
              Materiály · Polotovary · Výroba na mieru
            </p>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight">
              Váš partner pre&nbsp;
              <span className="text-blue-200">kovospracujúci</span> priemysel
            </h1>
            <p className="mt-6 text-lg text-blue-100 leading-relaxed">
              Predávame materiály, vyrábame polotovary a realizujeme kompletné
              atypické projekty. Rýchle dodanie, konkurencieschopné ceny,
              priame prepojenie s účtovnými systémami.
            </p>
            <div className="mt-8 flex flex-wrap gap-4">
              <Link
                href="/eshop"
                className="px-6 py-3 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors shadow-lg"
              >
                Prejsť do e-shopu
              </Link>
              <Link
                href="/contact"
                className="px-6 py-3 bg-blue-800 text-white font-semibold rounded-xl border border-blue-600 hover:bg-blue-700 transition-colors"
              >
                Kontaktujte nás
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
          Čo ponúkame
        </h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {FEATURES.map(({ icon, title, desc }) => (
            <div
              key={title}
              className="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow"
            >
              <div className="w-12 h-12 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center mb-4">
                {icon}
              </div>
              <h3 className="font-semibold text-gray-900 mb-2">{title}</h3>
              <p className="text-sm text-gray-500 leading-relaxed">{desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Featured products */}
      <section className="bg-white border-t border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">
              Vybrané produkty
            </h2>
            <Link
              href="/eshop"
              className="text-blue-700 font-medium text-sm hover:underline"
            >
              Zobraziť všetky →
            </Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {featured.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-blue-50 border-t border-blue-100">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">
            Potrebujete niečo na mieru?
          </h2>
          <p className="text-gray-600 mb-8 text-lg">
            Pošlite nám výkres alebo popíšte požiadavku – radi vypracujeme
            cenovú ponuku do 24 hodín.
          </p>
          <Link
            href="/contact"
            className="inline-block px-8 py-4 bg-blue-700 text-white font-semibold rounded-xl hover:bg-blue-800 transition-colors shadow-lg"
          >
            Odoslať dopyt
          </Link>
        </div>
      </section>
    </>
  );
}
