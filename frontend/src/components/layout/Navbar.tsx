"use client";

import React, { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../ui/Button";
import { Icon } from "../ui/Icon";

export const Navbar = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const toggleMobileMenu = () => setIsMobileMenuOpen(!isMobileMenuOpen);

  return (
    <nav className="bg-primary-container text-white sticky top-0 z-40 w-full shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div className="flex-shrink-0 flex items-center gap-2.5">
            <div className="bg-white p-1 rounded-full">
              <Image src="/logosariguntur.jpeg" alt="Logo SariGuntur Medical" width={28} height={28} className="rounded-sm object-contain" />
            </div>
            <Link href="/" className="font-bold text-xl tracking-tight">
              SariGuntur Medical
            </Link>
          </div>

          {/* Desktop Nav Items */}
          <div className="hidden md:flex flex-1 items-center justify-center space-x-8">
            <Link href="/" className="hover:text-secondary-container transition-colors font-medium">
              Beranda
            </Link>
            <Link href="/categories" className="hover:text-secondary-container transition-colors font-medium">
              Kategori
            </Link>
            <Link href="/products" className="hover:text-secondary-container transition-colors font-medium">
              Produk
            </Link>
          </div>

          {/* Right section (Search, Cart, User) */}
          <div className="hidden md:flex items-center space-x-4">
            <div className="relative">
              <input
                type="text"
                placeholder="Cari produk..."
                className="bg-white/10 text-white placeholder:text-white/60 border border-white/20 rounded-full py-1.5 pl-4 pr-10 focus:outline-none focus:border-white focus:bg-white/20 transition-colors text-sm w-48 lg:w-64"
              />
              <div className="absolute right-3 top-1/2 -translate-y-1/2 text-white/60">
                <Icon name="Search" size="sm" variant="inherit" />
              </div>
            </div>
            <Link href="/cart" className="p-2 hover:bg-white/10 rounded-full transition-colors relative">
              <Icon name="ShoppingCart" size="md" variant="white" />
            </Link>
            <Link href="/login" className="p-2 hover:bg-white/10 rounded-full transition-colors">
              <Icon name="User" size="md" variant="white" />
            </Link>
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden flex items-center">
            <button
              onClick={toggleMobileMenu}
              className="p-2 rounded-md hover:bg-white/10 focus:outline-none"
            >
              {isMobileMenuOpen ? (
                <Icon name="X" size="lg" variant="white" />
              ) : (
                <Icon name="Menu" size="lg" variant="white" />
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-primary pb-4 px-4 shadow-lg border-t border-white/10">
          <div className="flex flex-col space-y-2 pt-2 pb-3">
            <Link
              href="/"
              className="px-3 py-2 rounded-md text-base font-medium hover:bg-white/10"
              onClick={toggleMobileMenu}
            >
              Beranda
            </Link>
            <Link
              href="/categories"
              className="px-3 py-2 rounded-md text-base font-medium hover:bg-white/10"
              onClick={toggleMobileMenu}
            >
              Kategori
            </Link>
            <Link
              href="/products"
              className="px-3 py-2 rounded-md text-base font-medium hover:bg-white/10"
              onClick={toggleMobileMenu}
            >
              Produk
            </Link>
          </div>
          <div className="pt-2 border-t border-white/10 flex flex-col space-y-4">
            <div className="relative mt-2">
              <input
                type="text"
                placeholder="Cari produk..."
                className="w-full bg-white/10 text-white placeholder:text-white/60 border border-white/20 rounded-md py-2 pl-4 pr-10 focus:outline-none focus:bg-white/20"
              />
              <div className="absolute right-3 top-1/2 -translate-y-1/2 text-white/60">
                <Icon name="Search" size="md" variant="inherit" />
              </div>
            </div>
            <div className="flex space-x-2">
              <Link href="/cart" className="flex-1" onClick={toggleMobileMenu}>
                <Button variant="outline" className="w-full justify-center bg-white/5 border-white/20 text-white hover:bg-white/10">
                  <Icon name="ShoppingCart" size="sm" variant="inherit" className="mr-2" /> Keranjang
                </Button>
              </Link>
              <Link href="/login" className="flex-1" onClick={toggleMobileMenu}>
                <Button variant="outline" className="w-full justify-center bg-white/5 border-white/20 text-white hover:bg-white/10">
                  <Icon name="User" size="sm" variant="inherit" className="mr-2" /> Masuk
                </Button>
              </Link>
            </div>
          </div>
        </div>
      )}
    </nav>
  );
};
