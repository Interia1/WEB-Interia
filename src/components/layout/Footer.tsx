import Link from "next/link";
import { COMPANY_INFO } from "@/lib/data";

const FOOTER_LINKS = [
  { label: "E-shop", href: "/eshop" },
  { label: "Materiály", href: "/eshop?category=materialy" },
  { label: "Polotovary", href: "/eshop?category=polotovary" },
  { label: "Atypická výroba", href: "/eshop?category=atypicka-vyroba" },
  { label: "Služby", href: "/services" },
  { label: "O nás", href: "/about" },
  { label: "Kontakt", href: "/contact" },
];

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="bg-gray-900 text-gray-300 mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
          {/* Brand */}
          <div>
            <p className="text-white font-bold text-lg mb-2">{COMPANY_INFO.name}</p>
            <p className="text-sm text-gray-400 leading-relaxed">
              Predaj materiálov, výroba polotovarov a kompletná atypická výroba
              pre priemyselné a stavebné projekty.
            </p>
          </div>

          {/* Links */}
          <div>
            <p className="text-white font-semibold text-sm uppercase tracking-wider mb-3">
              Navigácia
            </p>
            <ul className="space-y-2">
              {FOOTER_LINKS.map(({ label, href }) => (
                <li key={href}>
                  <Link
                    href={href}
                    className="text-sm hover:text-white transition-colors"
                  >
                    {label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <p className="text-white font-semibold text-sm uppercase tracking-wider mb-3">
              Kontakt
            </p>
            <address className="not-italic space-y-1 text-sm text-gray-400">
              <p>{COMPANY_INFO.address.street}</p>
              <p>
                {COMPANY_INFO.address.zip} {COMPANY_INFO.address.city}
              </p>
              <p className="mt-2">
                <a href={"mailto:" + COMPANY_INFO.email} className="hover:text-white transition-colors">
                  {COMPANY_INFO.email}
                </a>
              </p>
              <p>
                <a href={"tel:" + COMPANY_INFO.phone.replace(/\s/g, "")} className="hover:text-white transition-colors">
                  {COMPANY_INFO.phone}
                </a>
              </p>
              <p className="mt-2 text-xs text-gray-500">
                IČO: {COMPANY_INFO.ico} | DIČ: {COMPANY_INFO.dic}
              </p>
            </address>
          </div>
        </div>

        <div className="mt-10 pt-6 border-t border-gray-800 text-center text-xs text-gray-500">
          © {year} {COMPANY_INFO.name}. Všetky práva vyhradené.
        </div>
      </div>
    </footer>
  );
}
