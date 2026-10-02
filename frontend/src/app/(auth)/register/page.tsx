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

const registerSchema = z.object({
  name: z.string().min(2, "Nama minimal 2 karakter"),
  email: z.string().email("Format email tidak valid"),
  password: z.string().min(8, "Password minimal 8 karakter"),
  password_confirmation: z.string().min(8, "Konfirmasi password minimal 8 karakter"),
}).refine((data) => data.password === data.password_confirmation, {
  message: "Konfirmasi password tidak cocok",
  path: ["password_confirmation"],
});

type RegisterFormValues = z.infer<typeof registerSchema>;

const otpSchema = z.object({
  otp: z.string().length(6, "OTP harus 6 digit"),
});

type OtpFormValues = z.infer<typeof otpSchema>;

export default function RegisterPage() {
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);
  
  const [step, setStep] = useState<"form" | "otp">("form");
  const [registeredEmail, setRegisteredEmail] = useState<string>("");
  const [globalError, setGlobalError] = useState<string | null>(null);

  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const {
    register: registerForm,
    handleSubmit: handleRegisterSubmit,
    formState: { errors: registerErrors },
    setError: setRegisterError,
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
  });

  const {
    register: otpForm,
    handleSubmit: handleOtpSubmit,
    formState: { errors: otpErrors },
    setError: setOtpError,
  } = useForm<OtpFormValues>({
    resolver: zodResolver(otpSchema),
  });

  const registerMutation = useMutation({
    mutationFn: async (data: RegisterFormValues) => {
      const response = await apiClient.post("/api/auth/register", data);
      return response.data;
    },
    onSuccess: (data, variables) => {
      setRegisteredEmail(variables.email);
      setStep("otp");
      setGlobalError(null);
    },
    onError: (error: any) => {
      if (error.response?.status === 422) {
        const validationErrors = error.response.data.errors;
        Object.keys(validationErrors).forEach((key) => {
          setRegisterError(key as keyof RegisterFormValues, {
            type: "server",
            message: validationErrors[key][0],
          });
        });
      } else {
        setGlobalError("Terjadi kesalahan saat pendaftaran. Silakan coba lagi.");
      }
    },
  });

  const otpMutation = useMutation({
    mutationFn: async (data: OtpFormValues) => {
      const response = await apiClient.post("/api/auth/verify-email-otp", {
        email: registeredEmail,
        otp: data.otp,
      });
      return response.data;
    },
    onSuccess: (data) => {
      setAuth(data.token, data.user);
      router.push("/");
    },
    onError: (error: any) => {
      if (error.response?.status === 400 || error.response?.status === 422) {
        setOtpError("otp", {
          type: "server",
          message: error.response.data.message || "OTP tidak valid",
        });
      } else {
        setGlobalError("Terjadi kesalahan saat verifikasi OTP.");
      }
    },
  });

  const onRegister = (data: RegisterFormValues) => {
    setGlobalError(null);
    registerMutation.mutate(data);
  };

  const onVerifyOtp = (data: OtpFormValues) => {
    setGlobalError(null);
    otpMutation.mutate(data);
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

        {/* Right Column (Form & Stepper) */}
        <div className="p-8 md:p-10 w-full md:w-7/12 lg:w-1/2 flex flex-col justify-center">
          
          {/* <div className="flex items-center gap-3 mb-6">
            <div className="bg-white border border-gray-200 p-1.5 rounded-lg shadow-sm flex items-center justify-center overflow-hidden">
              <Image src="/logosariguntur.jpeg" alt="Logo SariGuntur Medical" width={32} height={32} className="object-cover rounded-md" />
            </div>
            <div>
              <h2 className="font-bold text-lg text-primary leading-tight">SariGuntur Medical</h2>
              <p className="text-[10px] text-on-surface-variant uppercase tracking-wider">Registrasi Akun Institusi</p>
            </div>
          </div> */}

          {/* Stepper Horizontal Ringkas */}
          <div className="flex justify-between items-center mb-6 relative px-4">
            <div className="absolute left-8 right-8 top-1/2 h-0.5 bg-gray-100 -z-10 -translate-y-1/2"></div>
            
            {/* Step 1 */}
            <div className="flex flex-col items-center gap-2 bg-white px-2">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-colors ${step === 'otp' ? 'bg-green-50 border-green-500 text-green-600' : 'bg-primary text-white border-primary'}`}>
                {step === 'otp' ? <Icon name="Check" size="sm" variant="inherit" /> : '1'}
              </div>
              <span className={`text-[10px] uppercase tracking-wider font-bold ${step === 'otp' ? 'text-green-600' : 'text-primary'}`}>Data Akun</span>
            </div>
            
            {/* Step 2 */}
            <div className="flex flex-col items-center gap-2 bg-white px-2">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-colors ${step === 'otp' ? 'bg-primary text-white border-primary' : 'bg-white border-gray-300 text-gray-400'}`}>
                2
              </div>
              <span className={`text-[10px] uppercase tracking-wider font-bold ${step === 'otp' ? 'text-primary' : 'text-gray-400'}`}>Verifikasi</span>
            </div>
            
            {/* Step 3 */}
            <div className="flex flex-col items-center gap-2 bg-white px-2">
              <div className="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 bg-white border-gray-300 text-gray-400">
                3
              </div>
              <span className="text-[10px] uppercase tracking-wider font-bold text-gray-400">Selesai</span>
            </div>
          </div>

          {globalError && (
            <div className="mb-4 bg-error/10 text-error p-3 rounded-xl text-sm font-medium flex items-start gap-2">
              <Icon name="CircleAlert" size="md" variant="inherit" className="shrink-0 mt-0.5" />
              <p>{globalError}</p>
            </div>
          )}

          {step === "form" && (
            <>
              <h2 className="text-xl font-bold text-on-surface mb-1">
                Daftar Akun Baru
              </h2>
              <p className="text-sm text-on-surface-variant mb-5 leading-relaxed">
                Lengkapi informasi di bawah ini untuk memulai registrasi instansi Anda.
              </p>

              <form className="space-y-4" onSubmit={handleRegisterSubmit(onRegister)}>
                <div>
                  <Input
                    label="Nama Lengkap / Instansi"
                    type="text"
                    placeholder="Nama Anda atau Instansi"
                    leftIcon="User"
                    {...registerForm("name")}
                  />
                  {registerErrors.name && (
                    <p className="mt-1 text-sm text-error">{registerErrors.name.message}</p>
                  )}
                </div>

                <div>
                  <Input
                    label="Alamat Email"
                    type="email"
                    placeholder="Masukkan email anda"
                    leftIcon="Mail"
                    {...registerForm("email")}
                  />
                  {registerErrors.email && (
                    <p className="mt-1 text-sm text-error">{registerErrors.email.message}</p>
                  )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Input
                      label="Kata Sandi"
                      type={showPassword ? "text" : "password"}
                      placeholder="Minimal 8 karakter"
                      leftIcon="Lock"
                      rightNode={
                        <button type="button" onClick={() => setShowPassword(!showPassword)} className="hover:text-primary transition-colors focus:outline-none">
                          <Icon name={showPassword ? "EyeOff" : "Eye"} size="sm" variant="inherit" />
                        </button>
                      }
                      {...registerForm("password")}
                    />
                    {registerErrors.password && (
                      <p className="mt-1 text-sm text-error">{registerErrors.password.message}</p>
                    )}
                  </div>

                  <div>
                    <Input
                      label="Konfirmasi Sandi"
                      type={showConfirmPassword ? "text" : "password"}
                      placeholder="Ulangi sandi"
                      leftIcon="Lock"
                      rightNode={
                        <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="hover:text-primary transition-colors focus:outline-none">
                          <Icon name={showConfirmPassword ? "EyeOff" : "Eye"} size="sm" variant="inherit" />
                        </button>
                      }
                      {...registerForm("password_confirmation")}
                    />
                    {registerErrors.password_confirmation && (
                      <p className="mt-1 text-sm text-error">{registerErrors.password_confirmation.message}</p>
                    )}
                  </div>
                </div>

                <div className="pt-2">
                  <Button
                    type="submit"
                    className="w-full flex justify-center items-center gap-2"
                    disabled={registerMutation.isPending}
                  >
                    {registerMutation.isPending ? "Mendaftarkan..." : "Lanjutkan Pendaftaran"}
                    {!registerMutation.isPending && <Icon name="ArrowRight" size="sm" variant="inherit" />}
                  </Button>
                </div>
              </form>

              <div className="mt-5 relative">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-gray-200" />
                </div>
                <div className="relative flex justify-center text-xs font-semibold tracking-widest text-on-surface-variant uppercase">
                  <span className="bg-white px-4">Atau Daftar Dengan</span>
                </div>
              </div>

              <div className="mt-5 flex justify-center">
                <GoogleSignInButton />
              </div>
              
              <p className="mt-6 text-center text-sm text-on-surface-variant">
                Sudah memiliki akun instansi?{" "}
                <Link href="/login" className="font-bold text-primary hover:text-primary-hover">
                  Masuk di sini <Icon name="ArrowRight" size="sm" variant="inherit" className="inline mb-0.5 w-3 h-3" />
                </Link>
              </p>
            </>
          )}

          {step === "otp" && (
            <>
              <h2 className="text-2xl font-bold text-on-surface mb-2">
                Verifikasi Kode OTP
              </h2>
              
              <div className="bg-gray-50 border border-gray-100 rounded-xl p-5 mb-8 mt-4">
                <div className="flex gap-3 text-sm text-on-surface-variant mb-4">
                  <Icon name="MailOpen" size="md" variant="inherit" className="shrink-0 mt-0.5" />
                  <p>Kode verifikasi 6-digit telah dikirimkan ke email Anda:</p>
                </div>
                <div className="bg-white border border-gray-200 rounded-lg px-4 py-2.5 font-medium text-primary flex items-center gap-3">
                  <Icon name="Mail" size="sm" variant="inherit" />
                  {registeredEmail}
                </div>
              </div>

              <form className="space-y-6" onSubmit={handleOtpSubmit(onVerifyOtp)}>
                <div>
                  <div className="flex justify-between mb-2">
                    <label className="text-on-surface-variant font-semibold text-sm">Masukkan 6 Digit Kode OTP</label>
                  </div>
                  <Input
                    type="text"
                    maxLength={6}
                    placeholder="123456"
                    className="text-center tracking-[1em] text-2xl font-bold py-4 bg-gray-50 focus:bg-white"
                    {...otpForm("otp")}
                  />
                  {otpErrors.otp && (
                    <p className="mt-2 text-sm text-error text-center font-medium">{otpErrors.otp.message}</p>
                  )}
                </div>

                <div className="flex justify-between items-center text-sm text-on-surface-variant py-2">
                  <span className="flex items-center gap-2">
                    <Icon name="Timer" size="sm" variant="inherit" />
                    Batas waktu 05:00
                  </span>
                  <button type="button" className="font-bold text-secondary hover:text-secondary-hover flex items-center gap-1">
                    <Icon name="RefreshCw" size="sm" variant="inherit" />
                    Kirim Ulang Kode
                  </button>
                </div>

                <div className="pt-2 flex flex-col gap-3">
                  <Button
                    type="submit"
                    className="w-full flex justify-center items-center gap-2"
                    disabled={otpMutation.isPending}
                  >
                    {otpMutation.isPending ? "Memverifikasi..." : "Verifikasi & Buat Akun"}
                    {!otpMutation.isPending && <Icon name="ArrowRight" size="sm" variant="inherit" />}
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    className="w-full"
                    onClick={() => setStep("form")}
                    disabled={otpMutation.isPending}
                  >
                    <Icon name="Pencil" size="sm" variant="inherit" className="mr-2" />
                    Ubah Email / Data Registrasi
                  </Button>
                </div>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
