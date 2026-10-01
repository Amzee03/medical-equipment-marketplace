"use client";

import React from "react";
import { Icon } from "../ui/Icon";

export const WhatsAppFloatingButton = () => {
  const handleWhatsAppClick = () => {
    window.open("https://wa.me/6285894744507", "_blank");
  };

  return (
    <button
      onClick={handleWhatsAppClick}
      className="fixed bottom-6 left-6 z-50 group flex items-center p-3 md:p-4 bg-[#25D366] text-white shadow-lg border-none hover:shadow-xl hover:bg-[#1da851] rounded-full transition-all duration-300 ease-out focus:outline-none focus:ring-4 focus:ring-[#25D366]/50"
      aria-label="Chat with us on WhatsApp"
    >
      <Icon 
        name="MessageCircle" 
        size="lg" 
        variant="inherit"
        className="shrink-0" 
      />
      <div className="max-w-0 overflow-hidden transition-all duration-300 ease-out group-hover:max-w-[220px] whitespace-nowrap">
        <span className="pl-3 pr-2 font-semibold text-sm">
          Hubungi Customer Service
        </span>
      </div>
    </button>
  );
};
