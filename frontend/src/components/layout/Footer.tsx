import React from "react";
import Link from "next/link";
import { BrandIcon } from "../ui/Icon";

export const Footer = () => {
  return (
    <footer className="bg-primary-container text-white py-12 mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <h3 className="font-bold text-xl mb-4">SariGuntur Medical</h3>
            <p className="text-white/80 text-sm leading-relaxed max-w-sm">
              Penyedia layanan jual-beli dan penyewaan alat kesehatan terpercaya. 
              Komitmen kami adalah memberikan solusi medis terbaik dengan layanan yang profesional.
            </p>
          </div>
          <div>
            <h4 className="font-semibold text-lg mb-4 text-secondary-container">Tautan Penting</h4>
            <ul className="space-y-2 text-sm text-white/80">
              <li>
                <Link href="/" className="hover:text-white transition-colors">
                  Beranda
                </Link>
              </li>
              <li>
                <Link href="/products" className="hover:text-white transition-colors">
                  Semua Produk
                </Link>
              </li>
              <li>
                <Link href="/categories" className="hover:text-white transition-colors">
                  Kategori Alat
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h4 className="font-semibold text-lg mb-4 text-secondary-container">Kontak Kami</h4>
            <ul className="space-y-4 text-sm text-white/80">
              <li className="flex items-start gap-3">
                <BrandIcon name="Phone" containerSize="sm" brandColor="secondary" containerClassName="bg-secondary-container/20 text-secondary-container" size="sm" />
                <span className="mt-1">+62 858 9474 4507</span>
              </li>
              <li className="flex items-start gap-3">
                <BrandIcon name="Mail" containerSize="sm" brandColor="secondary" containerClassName="bg-secondary-container/20 text-secondary-container" size="sm" />
                <span className="mt-1">support@sarigunturmedical.com</span>
              </li>
              <li className="flex items-start gap-3">
                <BrandIcon name="MapPin" containerSize="sm" brandColor="secondary" containerClassName="bg-secondary-container/20 text-secondary-container" size="sm" />
                <span className="mt-1">Jakarta, Indonesia</span>
              </li>
            </ul>
          </div>
        </div>
        <div className="mt-12 pt-8 border-t border-white/20 text-center text-sm text-white/60">
          <p>&copy; {new Date().getFullYear()} SariGuntur Medical. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};
