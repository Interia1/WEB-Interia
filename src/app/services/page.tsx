import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Služby",
  description:
    "Kompletná atypická výroba, laserové rezanie, CNC ohýbanie a zváračské práce na mieru.",
};

const SERVICES = [
  {
    id: "laser",
    icon: "⚡",
    title: "Laserové rezanie",
    description:
      "Presné rezanie plechov laserom podľa DXF/DWG dokumentácie zákazníka. Tolerancie ±0,1 mm. Materiály: oceľ, nerez, hliník, pozink.",
    specs: [
      "Max. hrúbka ocele: 20 mm",
      "Max. hrúbka nerezu: 12 mm",
      "Max. hrúbka hliníka: 15 mm",
      "Pracovná plocha: 3000 × 1500 mm",
      "Tolerancia: ±0,1 mm",
    ],
    productId: "aty-002",
  },
  {
    id: "ohybanie",
    icon: "🔧",
    title: "CNC ohýbanie",
    description:
      "Ohýbanie plechov na CNC ohraňovacom lise podľa výkresovej dokumentácie. Max. dĺžka ohybu 3 000 mm, sila lisu 220 t.",
    specs: [
      "Max. dĺžka ohybu: 3 000 mm",
      "Sila lisu: 220 t",
      "Tolerancia uhla: ±0,5°",
      "Materiály: oceľ, nerez, hliník",
    ],
    productId: "aty-003",
  },
  {
    id: "zváranie",
    icon: "🔥",
    title: "Zváračské práce",
    description:
      "Zváračské práce MIG/MAG, TIG a elektrickým oblúkom. Certifikovaní zvárači. Výroba konštrukcií, rámov, konzol a ďalších dielov.",
    specs: [
      "Metódy: MIG/MAG, TIG, elektrický oblúk",
      "Materiály: oceľ, nerez, hliník",
      "Certifikácia: STN EN ISO 3834",
    ],
    productId: "aty-001",
  },
  {
    id: "povrchova-uprava",
    icon: "🎨",
    title: "Povrchová úprava",
    description:
      "Základný a vrchný náter, pozinkovanie, pieskovanie. Zabezpečujeme kompletnú povrchovú úpravu vyrobených dielov.",
    specs: [
      "Základný náter",
      "Vrchný náter (RAL paleta)",
      "Žárové pozinkovanie (kooperácia)",
      "Pieskovanie",
    ],
    productId: "aty-001",
  },
  {
    id: "vyroba-polotovarov",
    icon: "🏭",
    title: "Sériová výroba polotovarov",
    description:
      "Výroba oceľových polotovarov vo väčších sériách podľa výkresov zákazníka – konzoly, rámy, stupačky, plechy na mieru.",
    specs: [
      "Série od 10 ks",
      "Výrobné tolerancie podľa ISO 2768",
      "Kompletná dokumentácia",
    ],
    productId: "pol-001",
  },
  {
    id: "konzultacie",
    icon: "💬",
    title: "Technické poradenstvo",
    description:
      "Pomôžeme s výberom správneho materiálu, optimalizáciou konštrukcie a znížením výrobných nákladov ešte pred spustením výroby.",
    specs: [
      "Výber materiálu",
      "Optimalizácia výkresov pre výrobu",
      "Nacenenie a harmonogram",
    ],
    productId: null,
  },
];

export default function ServicesPage() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="max-w-2xl mb-12">
        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          Naše služby
        </h1>
        <p className="text-lg text-gray-500 leading-relaxed">
          Od rezania a ohýbania až po kompletnú výrobu oceľových konštrukcií na
          mieru. Realizujeme projekty pre priemysel, stavebníctvo aj
          individuálnych zákazníkov.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {SERVICES.map((service) => (
          <div
            key={service.id}
            className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col"
          >
            <div className="p-6 flex-1">
              <div className="text-4xl mb-4">{service.icon}</div>
              <h2 className="text-xl font-semibold text-gray-900 mb-2">
                {service.title}
              </h2>
              <p className="text-sm text-gray-500 leading-relaxed mb-4">
                {service.description}
              </p>
              <ul className="space-y-1.5">
                {service.specs.map((spec) => (
                  <li key={spec} className="flex items-start gap-2 text-sm text-gray-600">
                    <svg className="w-4 h-4 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24" aria-hidden="true">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {spec}
                  </li>
                ))}
              </ul>
            </div>
            <div className="px-6 pb-6">
              {service.productId ? (
                <Link
                  href={"/eshop/product/" + service.productId}
                  className="block text-center py-2 px-4 rounded-lg border border-blue-700 text-blue-700 text-sm font-medium hover:bg-blue-50 transition-colors"
                >
                  Pozrieť v e-shope
                </Link>
              ) : (
                <Link
                  href="/contact"
                  className="block text-center py-2 px-4 rounded-lg border border-blue-700 text-blue-700 text-sm font-medium hover:bg-blue-50 transition-colors"
                >
                  Kontaktovať nás
                </Link>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* CTA */}
      <div className="mt-16 bg-blue-700 rounded-2xl p-8 sm:p-12 text-center text-white">
        <h2 className="text-2xl sm:text-3xl font-bold mb-3">
          Máte projekt, ktorý potrebuje riešenie?
        </h2>
        <p className="text-blue-100 mb-6 text-lg">
          Zašlite nám výkres alebo dopyt. Nacenenie do 24 hodín.
        </p>
        <Link
          href="/contact"
          className="inline-block px-8 py-4 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors shadow"
        >
          Odoslať dopyt
        </Link>
      </div>
    </div>
  );
}
