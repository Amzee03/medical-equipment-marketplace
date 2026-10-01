import React from 'react';
import { icons } from 'lucide-react';
import { cn } from '@/lib/utils';

export type IconName = keyof typeof icons;

export interface IconProps extends React.SVGProps<SVGSVGElement> {
  name: IconName;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  variant?: 'default' | 'accent-primary' | 'accent-secondary' | 'muted' | 'inherit' | 'white';
  strokeWidth?: number;
}

const sizeMap = {
  sm: 16,
  md: 20,
  lg: 24,
  xl: 28,
};

export const Icon = React.forwardRef<SVGSVGElement, IconProps>(
  ({ name, size = 'md', variant = 'inherit', strokeWidth = 1.75, className, ...props }, ref) => {
    const LucideIcon = icons[name];
    
    if (!LucideIcon) {
      console.warn(`Icon "${name}" not found in lucide-react`);
      return null;
    }

    return (
      <LucideIcon
        ref={ref}
        size={sizeMap[size]}
        strokeWidth={strokeWidth}
        className={cn(
          {
            'text-on-surface': variant === 'default',
            'text-primary': variant === 'accent-primary',
            'text-secondary': variant === 'accent-secondary',
            'text-on-surface-variant': variant === 'muted',
            'text-white': variant === 'white',
          },
          className
        )}
        {...props}
      />
    );
  }
);
Icon.displayName = 'Icon';

export interface BrandIconProps extends Omit<IconProps, 'variant'> {
  containerSize?: 'sm' | 'md' | 'lg';
  brandColor?: 'primary' | 'secondary' | 'error';
  containerClassName?: string;
}

export const BrandIcon = React.forwardRef<HTMLDivElement, BrandIconProps>(
  ({ containerSize = 'md', brandColor = 'secondary', containerClassName, className, ...iconProps }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          "flex shrink-0 items-center justify-center rounded-full",
          {
            'bg-primary/10 text-primary': brandColor === 'primary',
            'bg-secondary/10 text-secondary': brandColor === 'secondary',
            'bg-error/10 text-error': brandColor === 'error',
            'w-8 h-8': containerSize === 'sm',
            'w-10 h-10': containerSize === 'md',
            'w-14 h-14': containerSize === 'lg',
          },
          containerClassName
        )}
      >
        <Icon {...iconProps} variant="inherit" className={className} />
      </div>
    );
  }
);
BrandIcon.displayName = 'BrandIcon';
