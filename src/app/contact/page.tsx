"use client";

import { useState } from "react";
import { COMPANY_INFO } from "@/lib/data";
import Button from "@/components/ui/Button";

interface FormState {
  name: string;
  email: string;
  phone: string;
  company: string;
  subject: string;
  message: string;
}

const INITIAL: FormState = {
  name: "",
  email: "",
  phone: "",
  company: "",
  subject: "",
  message: "",
};

export default function ContactPage() {
  const [form, setForm] = useState<FormState>(INITIAL);
  const [sent, setSent] = useState(false);
  const [loading, setLoading] = useState(false);

  function update(field: keyof FormState, value: string) {
    setForm((f) => ({ ...f, [field]: value }));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    // Simulate sending – replace with real API call / email service
    await new Promise((res) => setTimeout(res, 800));
    setLoading(false);
    setSent(true);
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="max-w-2xl mb-10">
        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          Kontakt
        </h1>
        <p className="text-lg text-gray-500">
          Máte otázku alebo chcete dopytovať cenu? Napíšte nám – odpovieme do
          24 hodín v pracovné dni.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {/* Form */}
        <div>
          {sent ? (
            <div className="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
              <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h2 className="text-xl font-semibold text-gray-900 mb-2">Správa odoslaná!</h2>
              <p className="text-gray-500 text-sm">
                Ďakujeme, čoskoro sa Vám ozveme.
              </p>
              <button
                onClick={() => { setSent(false); setForm(INITIAL); }}
                className="mt-4 text-blue-700 text-sm hover:underline"
              >
                Odoslať ďalšiu správu
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-name">
                    Meno a priezvisko *
                  </label>
                  <input required id="c-name" type="text" value={form.name} onChange={(e) => update("name", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-company">
                    Firma
                  </label>
                  <input id="c-company" type="text" value={form.company} onChange={(e) => update("company", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-email">
                    E-mail *
                  </label>
                  <input required id="c-email" type="email" value={form.email} onChange={(e) => update("email", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-phone">
                    Telefón
                  </label>
                  <input id="c-phone" type="tel" value={form.phone} onChange={(e) => update("phone", e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-subject">
                  Predmet *
                </label>
                <input required id="c-subject" type="text" value={form.subject} onChange={(e) => update("subject", e.target.value)}
                  placeholder="Napr. Dopyt na laserové rezanie"
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="c-message">
                  Správa *
                </label>
                <textarea required id="c-message" rows={5} value={form.message} onChange={(e) => update("message", e.target.value)}
                  placeholder="Opíšte Vašu požiadavku, ideálne aj s rozmermi a množstvami…"
                  className="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
              </div>
              <Button type="submit" loading={loading} size="lg" className="w-full">
                Odoslať správu
              </Button>
            </form>
          )}
        </div>

        {/* Info */}
        <div className="space-y-6">
          <div className="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 className="font-semibold text-gray-900 mb-4">Kontaktné údaje</h2>
            <address className="not-italic space-y-3 text-sm text-gray-600">
              <div className="flex items-start gap-3">
                <svg className="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                  <p className="font-medium text-gray-800">{COMPANY_INFO.name}</p>
                  <p>{COMPANY_INFO.address.street}</p>
                  <p>{COMPANY_INFO.address.zip} {COMPANY_INFO.address.city}</p>
                  <p>{COMPANY_INFO.address.country}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <svg className="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a href={"mailto:" + COMPANY_INFO.email} className="hover:text-blue-700 transition-colors">
                  {COMPANY_INFO.email}
                </a>
              </div>
              <div className="flex items-center gap-3">
                <svg className="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <a href={"tel:" + COMPANY_INFO.phone.replace(/\s/g, "")} className="hover:text-blue-700 transition-colors">
                  {COMPANY_INFO.phone}
                </a>
              </div>
            </address>
          </div>

          <div className="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 className="font-semibold text-gray-900 mb-3">Otváracie hodiny</h2>
            <dl className="space-y-1 text-sm text-gray-600">
              {[
                { day: "Pondelok – Piatok", hours: "07:00 – 16:00" },
                { day: "Sobota", hours: "08:00 – 12:00" },
                { day: "Nedeľa", hours: "Zatvorené" },
              ].map(({ day, hours }) => (
                <div key={day} className="flex justify-between">
                  <dt className="text-gray-500">{day}</dt>
                  <dd className="font-medium">{hours}</dd>
                </div>
              ))}
            </dl>
          </div>
        </div>
      </div>
    </div>
  );
}
