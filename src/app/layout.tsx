import type { Metadata } from "next";
import "./globals.css";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { CartProvider } from "@/context/CartContext";

export const metadata: Metadata = {
  title: {
    default: "Interia – Materiály, Polotovary, Atypická výroba",
    template: "%s | Interia",
  },
  description:
    "Predaj materiálov, výroba polotovarov a kompletná atypická výroba na mieru. Rýchlosť, kvalita, spoľahlivosť.",
  keywords: [
    "oceľ",
    "plech",
    "polotovary",
    "atypická výroba",
    "laserové rezanie",
    "ohýbanie",
    "Bratislava",
  ],
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="sk" className="h-full antialiased">
      <body className="min-h-full flex flex-col bg-gray-50 text-gray-900 font-sans">
        <CartProvider>
          <Navbar />
          <main className="flex-1">{children}</main>
          <Footer />
        </CartProvider>
      </body>
    </html>
  );
}
