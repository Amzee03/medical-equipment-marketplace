"use client";

import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useMutation } from "@tanstack/react-query";
import { apiClient } from "@/lib/api-client";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { GoogleSignInButton } from "@/components/auth/GoogleSignInButton";
import { BrandIcon, Icon } from "@/components/ui/Icon";

const loginSchema = z.object({
  email: z.string().email("Format email tidak valid"),
  password: z.string().min(1, "Kata sandi wajib diisi"),
});

type LoginFormValues = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
  });

  const loginMutation = useMutation({
    mutationFn: async (data: LoginFormValues) => {
      const response = await apiClient.post("/api/auth/login", data);
      return response.data;
    },
    onSuccess: (data) => {
      setAuth(data.token, data.user);
      router.push("/");
    },
    onError: (error: any) => {
      if (error.response?.status === 401) {
        setErrorMsg("Email atau kata sandi salah, atau akun belum diverifikasi.");
      } else {
        setErrorMsg("Terjadi kesalahan sistem. Silakan coba lagi.");
      }
    },
  });

  const onSubmit = (data: LoginFormValues) => {
    setErrorMsg(null);
    loginMutation.mutate(data);
  };

  return (
    <div className="min-h-screen bg-surface flex items-center justify-center p-4 md:p-8">
      <div className="max-w-6xl w-full bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
        
        {/* Left Column (Information) */}
        <div className="hidden md:flex flex-col bg-primary p-12 text-white md:w-5/12 lg:w-1/2">
          <h1 className="text-3xl lg:text-4xl font-bold leading-tight mb-4">
            Standar Mutu & Keandalan Pengadaan Alat Medis
          </h1>
          <p className="text-white/80 text-sm lg:text-base mb-10 leading-relaxed">
            Platform pengadaan, penyewaan, dan pengelolaan alat medis berkualitas tinggi.
          </p>

          <div className="space-y-4">
            <div className="bg-primary-hover/30 p-4 rounded-xl flex gap-4 items-start">
              <BrandIcon name="BriefcaseMedical" containerSize="sm" brandColor="secondary" containerClassName="bg-white/10 text-secondary-container" />
              <div>
                <h3 className="font-bold text-sm lg:text-base">Pengadaan Alat Medis Resmi</h3>
                <p className="text-white/70 text-xs mt-1">Sertifikasi AKL/AKD terjamin 100% legalitasnya.</p>
              </div>
            </div>
            <div className="bg-primary-hover/30 p-4 rounded-xl flex gap-4 items-start">
              <BrandIcon name="CalendarCheck" containerSize="sm" brandColor="secondary" containerClassName="bg-white/10 text-secondary-container" />
              <div>
                <h3 className="font-bold text-sm lg:text-base">Pengajuan Sewa Cepat & Transparan</h3>
                <p className="text-white/70 text-xs mt-1">Kalkulasi biaya sewa harian hingga tahunan.</p>
              </div>
            </div>
            <div className="bg-primary-hover/30 p-4 rounded-xl flex gap-4 items-start">
              <BrandIcon name="Truck" containerSize="sm" brandColor="secondary" containerClassName="bg-white/10 text-secondary-container" />
              <div>
                <h3 className="font-bold text-sm lg:text-base">Pelacakan Pesanan Real-time</h3>
                <p className="text-white/70 text-xs mt-1">Pantau status pengiriman alat secara aktual dan akurat.</p>
              </div>
            </div>
            <div className="bg-primary-hover/30 p-4 rounded-xl flex gap-4 items-start">
              <BrandIcon name="Headset" containerSize="sm" brandColor="secondary" containerClassName="bg-white/10 text-secondary-container" />
              <div>
                <h3 className="font-bold text-sm lg:text-base">Dukungan Teknis 24/7</h3>
                <p className="text-white/70 text-xs mt-1">Konsultasi instalasi operasional 24 jam.</p>
              </div>
            </div>
          </div>
        </div>

        {/* Right Column (Form) */}
        <div className="p-8 md:p-12 w-full md:w-7/12 lg:w-1/2 flex flex-col justify-center">
          {/* <div className="flex items-center gap-3 mb-8">
            <div className="bg-white border border-gray-200 p-1.5 rounded-lg shadow-sm flex items-center justify-center overflow-hidden">
              <Image src="/logosariguntur.jpeg" alt="Logo SariGuntur Medical" width={32} height={32} className="object-cover rounded-md" />
            </div>
            <div>
              <h2 className="font-bold text-lg text-primary leading-tight">SariGuntur Medical</h2>
            </div>
          </div> */}

          <h2 className="text-2xl font-bold text-on-surface mb-2">
            Masuk ke Akun Anda
          </h2>
          <p className="text-sm text-on-surface-variant mb-8 leading-relaxed">
            Silakan masukkan email dan kata sandi institusi Anda untuk mengakses layanan sistem SariGuntur Medical.
          </p>

          <form className="space-y-5" onSubmit={handleSubmit(onSubmit)}>
            {errorMsg && (
              <div className="bg-error/10 text-error p-3 rounded-md text-sm font-medium">
                {errorMsg}
              </div>
            )}

            <div>
              <Input
                label="Alamat Email"
                type="email"
                placeholder="Masukkan email anda"
                leftIcon="Mail"
                {...register("email")}
              />
              {errors.email && (
                <p className="mt-1 text-sm text-error">{errors.email.message}</p>
              )}
            </div>

            <div>
              <Input
                label="Kata Sandi"
                type={showPassword ? "text" : "password"}
                placeholder="••••••••"
                leftIcon="Lock"
                rightNode={
                  <button 
                    type="button" 
                    onClick={() => setShowPassword(!showPassword)}
                    className="hover:text-primary transition-colors focus:outline-none"
                  >
                    <Icon name={showPassword ? "EyeOff" : "Eye"} size="sm" variant="inherit" />
                  </button>
                }
                {...register("password")}
              />
              {errors.password && (
                <p className="mt-1 text-sm text-error">{errors.password.message}</p>
              )}
            </div>

            <div className="flex items-center justify-between pt-2">
              <label className="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" className="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
                <span className="text-sm text-on-surface-variant font-medium">Ingat Saya</span>
              </label>
              <Link href="#" className="text-sm font-bold text-primary hover:text-primary-hover">
                Lupa Kata Sandi?
              </Link>
            </div>

            <div className="pt-4">
              <Button
                type="submit"
                className="w-full flex justify-center items-center gap-2"
                disabled={loginMutation.isPending}
              >
                {loginMutation.isPending ? "Memproses..." : "Masuk"} 
                {!loginMutation.isPending && <Icon name="ArrowRight" size="sm" variant="inherit" />}
              </Button>
            </div>
          </form>

          <div className="mt-8 relative">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-gray-200" />
            </div>
            <div className="relative flex justify-center text-xs font-semibold tracking-widest text-on-surface-variant uppercase">
              <span className="bg-white px-4">Atau Masuk Dengan</span>
            </div>
          </div>

          <div className="mt-6 flex justify-center">
            <GoogleSignInButton />
          </div>

          <p className="mt-10 text-center text-sm text-on-surface-variant">
            Belum memiliki akun institusi?{" "}
            <Link href="/register" className="font-bold text-primary hover:text-primary-hover">
              Daftar Sekarang <Icon name="ArrowRight" size="sm" variant="inherit" className="inline mb-0.5 w-3 h-3" />
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
