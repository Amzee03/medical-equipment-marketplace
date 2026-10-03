import React from "react";
import { cn } from "@/lib/utils";
import { Icon, IconName } from "./Icon";

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  leftIcon?: IconName;
  rightNode?: React.ReactNode;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, label, id, leftIcon, rightNode, ...props }, ref) => {
    const inputId = id || (label ? `input-${label.toLowerCase().replace(/\s+/g, "-")}` : undefined);

    return (
      <div className="flex flex-col gap-1.5 w-full">
        {label && (
          <label
            htmlFor={inputId}
            className="text-on-surface-variant font-semibold text-sm"
          >
            {label}
          </label>
        )}
        <div className="relative flex items-center">
          {leftIcon && (
            <div className="absolute left-3 text-on-surface-variant/60">
              <Icon name={leftIcon} size="md" variant="inherit" />
            </div>
          )}
          <input
            id={inputId}
            ref={ref}
            className={cn(
              "bg-white border border-gray-300 rounded-[8px] py-2.5 text-base w-full transition-colors focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary disabled:opacity-50 disabled:bg-gray-50 placeholder:text-gray-400 text-on-surface",
              leftIcon ? "pl-10" : "px-3",
              rightNode ? "pr-10" : "pr-3",
              className
            )}
            {...props}
          />
          {rightNode && (
            <div className="absolute right-3 text-on-surface-variant/60 flex items-center">
              {rightNode}
            </div>
          )}
        </div>
      </div>
    );
  }
);
Input.displayName = "Input";
