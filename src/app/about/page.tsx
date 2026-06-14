import type { Metadata } from "next";
import Link from "next/link";
import { COMPANY_INFO } from "@/lib/data";

export const metadata: Metadata = {
  title: "O nás",
  description:
    "Spoznajte Interia s.r.o. – slovenského výrobcu a predajcu kovových materiálov a polotovarov.",
};

const STATS = [
  { label: "Rokov skúseností", value: "10+" },
  { label: "Spokojných zákazníkov", value: "500+" },
  { label: "Produktov v katalógu", value: "200+" },
  { label: "Realizovaných projektov", value: "2 000+" },
];

const VALUES = [
  {
    title: "Kvalita",
    desc: "Používame certifikované materiály a dodržiavame výrobné normy STN/EN ISO.",
  },
  {
    title: "Rýchlosť",
    desc: "Štandardné objednávky vybavujeme do 5 pracovných dní, urgentné do 48 hodín.",
  },
  {
    title: "Flexibilita",
    desc: "Každý projekt je iný – prispôsobíme sa Vašim výkresovým podkladom a termínom.",
  },
  {
    title: "Férové ceny",
    desc: "Transparentná cenová politika, množstevné zľavy a dlhodobé partnerstvá.",
  },
];

export default function AboutPage() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Hero */}
      <div className="max-w-3xl mb-12">
        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          O spoločnosti {COMPANY_INFO.name}
        </h1>
        <p className="text-lg text-gray-500 leading-relaxed">
          Sme slovenská spoločnosť so sídlom v Bratislave, ktorá sa zameriava na
          predaj kovových materiálov, výrobu polotovarov a realizáciu atypických
          zákaziek pre priemyselných zákazníkov aj jednotlivcov.
        </p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-16">
        {STATS.map(({ label, value }) => (
          <div
            key={label}
            className="bg-blue-700 text-white rounded-2xl p-6 text-center"
          >
            <p className="text-3xl font-bold">{value}</p>
            <p className="text-blue-200 text-sm mt-1">{label}</p>
          </div>
        ))}
      </div>

      {/* Story */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16 items-center">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Náš príbeh</h2>
          <div className="prose prose-gray max-w-none text-gray-600 space-y-4 text-sm leading-relaxed">
            <p>
              Spoločnosť Interia bola založená s jasným cieľom: poskytnúť
              zákazníkom komplexné riešenie od nákupu materiálu až po finálny
              výrobok. Začínali sme ako malá zváračská dielňa a postupne sme
              rozšírili naše kapacity o laserové rezanie, CNC ohýbanie a
              povrchové úpravy.
            </p>
            <p>
              Dnes sme plnohodnotným partnerom pre strojárske, stavebné a
              inžinierske firmy na Slovensku aj v zahraničí. Investujeme
              pravidelne do moderných technológií a vzdelávania nášho tímu.
            </p>
            <p>
              Digitalizácia je pre nás kľúčová – preto sme spustili tento
              e-shop a integrovali náš systém s účtovnými programami, aby sme
              zákazníkom ušetrili čas a papierovanie.
            </p>
          </div>
        </div>
        <div className="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl aspect-square flex items-center justify-center">
          <svg className="w-32 h-32 text-blue-400" fill="none" stroke="currentColor" strokeWidth={0.75} viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
      </div>

      {/* Values */}
      <div className="mb-16">
        <h2 className="text-2xl font-bold text-gray-900 mb-8 text-center">
          Naše hodnoty
        </h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {VALUES.map(({ title, desc }) => (
            <div
              key={title}
              className="bg-white rounded-xl border border-gray-200 p-6 shadow-sm"
            >
              <h3 className="font-semibold text-blue-700 mb-2">{title}</h3>
              <p className="text-sm text-gray-500 leading-relaxed">{desc}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Company details */}
      <div className="bg-gray-50 rounded-2xl border border-gray-200 p-8">
        <h2 className="text-xl font-bold text-gray-900 mb-6">
          Firemné informácie
        </h2>
        <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-3 text-sm">
          {[
            { label: "Obchodné meno", value: COMPANY_INFO.name },
            { label: "IČO", value: COMPANY_INFO.ico },
            { label: "DIČ", value: COMPANY_INFO.dic },
            { label: "IČ DPH", value: COMPANY_INFO.icdph },
            {
              label: "Sídlo",
              value:
                COMPANY_INFO.address.street +
                ", " +
                COMPANY_INFO.address.zip +
                " " +
                COMPANY_INFO.address.city,
            },
            { label: "E-mail", value: COMPANY_INFO.email },
            { label: "Telefón", value: COMPANY_INFO.phone },
            { label: "Banka", value: COMPANY_INFO.bank },
            { label: "IBAN", value: COMPANY_INFO.iban },
          ].map(({ label, value }) => (
            <div key={label} className="flex justify-between border-b border-gray-100 pb-2">
              <dt className="text-gray-500">{label}</dt>
              <dd className="font-medium text-gray-800 text-right">{value}</dd>
            </div>
          ))}
        </dl>
      </div>

      <div className="mt-10 text-center">
        <Link
          href="/contact"
          className="inline-block px-8 py-4 bg-blue-700 text-white font-semibold rounded-xl hover:bg-blue-800 transition-colors shadow"
        >
          Kontaktujte nás
        </Link>
      </div>
    </div>
  );
}
