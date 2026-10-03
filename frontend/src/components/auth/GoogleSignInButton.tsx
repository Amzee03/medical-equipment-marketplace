"use client";

import React, { useEffect, useRef, useState } from "react";
import Script from "next/script";
import { useMutation } from "@tanstack/react-query";
import { apiClient } from "@/lib/api-client";
import { useAuthStore } from "@/store/authStore";
import { useRouter } from "next/navigation";

export const GoogleSignInButton = () => {
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);
  const buttonRef = useRef<HTMLDivElement>(null);
  const [error, setError] = useState<string | null>(null);

  const googleMutation = useMutation({
    mutationFn: async (idToken: string) => {
      const response = await apiClient.post("/api/auth/google", { id_token: idToken });
      return response.data;
    },
    onSuccess: (data) => {
      setAuth(data.token, data.user);
      router.push("/");
    },
    onError: (err: any) => {
      setError(err.response?.data?.message || "Google Sign-In failed.");
    },
  });

  const handleCredentialResponse = (response: any) => {
    if (response.credential) {
      googleMutation.mutate(response.credential);
    }
  };

  const renderGoogleButton = () => {
    const google = (window as any).google;
    if (google && buttonRef.current) {
      google.accounts.id.initialize({
        client_id: process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID || "",
        callback: handleCredentialResponse,
      });
      google.accounts.id.renderButton(buttonRef.current, {
        theme: "outline",
        size: "large",
      });
    }
  };

  useEffect(() => {
    renderGoogleButton();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div className="w-full flex flex-col items-center">
      <Script
        src="https://accounts.google.com/gsi/client"
        strategy="afterInteractive"
        onLoad={renderGoogleButton}
      />
      <div ref={buttonRef} className="w-full flex justify-center h-[40px]"></div>
      {error && <p className="text-error text-sm mt-2">{error}</p>}
    </div>
  );
};
