"use client";

import React from "react";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Input } from "@/components/ui/Input";
import { Badge } from "@/components/ui/Badge";

export default function Home() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
      <div className="space-y-4">
        <h1 className="text-4xl font-bold text-primary">SariGuntur Medical Design System</h1>
        <p className="text-on-surface-variant">
          Ini adalah halaman sementara untuk memverifikasi komponen dasar.
        </p>
      </div>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold text-primary">Typography</h2>
        <div className="space-y-2">
          <h1 className="text-4xl font-bold text-primary">Headline 1 (Navy)</h1>
          <h2 className="text-2xl font-semibold text-primary">Headline 2 (Navy)</h2>
          <p className="text-base text-on-surface">Body Text: Sistem ini didesain untuk ranah medis.</p>
        </div>
      </section>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold text-primary">Buttons</h2>
        <div className="flex flex-wrap gap-4">
          <Button variant="primary">Primary Button</Button>
          <Button variant="secondary">Secondary Button</Button>
          <Button variant="outline">Outline Button</Button>
          <Button disabled>Disabled Button</Button>
        </div>
      </section>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold text-primary">Badges</h2>
        <div className="flex gap-4">
          <Badge variant="success">Tersedia</Badge>
          <Badge variant="danger">Habis</Badge>
        </div>
      </section>

      <section className="space-y-4">
        <h2 className="text-2xl font-semibold text-primary">Cards & Inputs</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <Card>
            <h3 className="text-xl font-semibold mb-4 text-primary">Form Login (Placeholder)</h3>
            <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
              <Input label="Email Address" type="email" placeholder="contoh@email.com" />
              <Input label="Password" type="password" placeholder="••••••••" />
              <Button variant="primary" className="w-full">Login Sekarang</Button>
            </form>
          </Card>
          <Card>
            <h3 className="text-xl font-semibold mb-4 text-primary">Informasi Produk</h3>
            <div className="flex items-center gap-2 mb-2">
              <Badge variant="success">Ready Stock</Badge>
            </div>
            <p className="text-on-surface-variant mb-6 text-sm">
              Ini adalah contoh tampilan Card dengan sedikit deskripsi untuk memverifikasi padding dan warna.
            </p>
            <div className="flex gap-2">
              <Button variant="secondary" className="flex-1">Sewa Alat</Button>
              <Button variant="outline" className="flex-1">Beli Alat</Button>
            </div>
          </Card>
        </div>
      </section>
    </div>
  );
}
