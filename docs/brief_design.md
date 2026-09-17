---
name: SariGuntur Medical Brand
colors:
  surface: '#f9f9f9'
  surface-dim: '#dadada'
  surface-bright: '#f9f9f9'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f4'
  surface-container: '#eeeeee'
  surface-container-high: '#e8e8e8'
  surface-container-highest: '#e2e2e2'
  on-surface: '#1a1c1c'
  on-surface-variant: '#434750'
  inverse-surface: '#2f3131'
  inverse-on-surface: '#f0f1f1'
  outline: '#747781'
  outline-variant: '#c3c6d1'
  surface-tint: '#3c5e97'
  primary: '#002552'
  on-primary: '#ffffff'
  primary-container: '#123b72'
  on-primary-container: '#85a7e5'
  inverse-primary: '#aac7ff'
  secondary: '#006b58'
  on-secondary: '#ffffff'
  secondary-container: '#82f7d8'
  on-secondary-container: '#00725e'
  tertiary: '#550004'
  on-tertiary: '#ffffff'
  tertiary-container: '#7e000a'
  on-tertiary-container: '#ff8075'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d7e3ff'
  primary-fixed-dim: '#aac7ff'
  on-primary-fixed: '#001b3e'
  on-primary-fixed-variant: '#21467e'
  secondary-fixed: '#82f7d8'
  secondary-fixed-dim: '#65dabc'
  on-secondary-fixed: '#002019'
  on-secondary-fixed-variant: '#005142'
  tertiary-fixed: '#ffdad6'
  tertiary-fixed-dim: '#ffb4ac'
  on-tertiary-fixed: '#410002'
  on-tertiary-fixed-variant: '#93000d'
  background: '#f9f9f9'
  on-background: '#1a1c1c'
  surface-variant: '#e2e2e2'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 48px
  max-width: 1280px
---

## Brand & Style

The design system is engineered for the medical sector, prioritizing trust, precision, and clinical excellence. The visual language follows a **Corporate / Modern** aesthetic, utilizing high-contrast primary colors to establish authority and clean, white surfaces to ensure a sterile and professional atmosphere. 

The brand personality is dependable and institutional. The interface evokes a sense of organized efficiency and medical reliability through structured layouts, ample whitespace, and a disciplined color application strategy.

## Colors

This color palette is strictly functional, following a hierarchical distribution to guide user attention:

- **Navy (#123B72):** The anchor of the system. Used for institutional elements including navigation bars, footers, hero backgrounds, and primary action buttons.
- **White (#FFFFFF):** Provides the clinical backdrop. Used for page backgrounds and content card surfaces to maintain readability and a feeling of cleanliness.
- **Green (#16A085):** Reserved for secondary call-to-actions, iconography, and success states. It serves as a soft "go" signal and interactive hover state.
- **Red (#E53935):** Utilized sparingly for critical alerts, emergency highlights, and error states to ensure immediate visibility without overwhelming the professional tone.

## Typography

The typography system relies exclusively on **Inter** to deliver a neutral, systematic, and highly legible experience. 

- **Headlines:** Use Bold or Semi-Bold weights in Navy (#123B72) to establish a clear information hierarchy.
- **Body Text:** Standardized on a 16px base for optimal readability across medical data and clinical reports.
- **Labels:** Small, uppercase labels with increased letter spacing should be used for metadata and badge text to distinguish them from interactive elements.
- **Responsive Scaling:** Large display headings scale down by approximately 25% on mobile devices to prevent layout breaking while maintaining visual impact.

## Layout & Spacing

The layout utilizes a **Fixed Grid** system for desktop to ensure content remains centered and readable, transitioning to a fluid model for mobile devices.

- **Grid Model:** 12-column grid on desktop with 24px gutters.
- **Spacing Rhythm:** An 8px base unit governs all dimensions. Elements should be spaced in increments of 8px (16, 24, 32, 48) to maintain vertical rhythm.
- **Safe Areas:** Generous margins are applied to containers to avoid visual clutter, reinforcing the clinical and airy feel of the brand.

## Elevation & Depth

This design system uses **Low-contrast outlines** and subtle tonal shifts rather than heavy shadows to convey depth, keeping the UI flat and modern.

- **Surface Tiers:** Cards and containers use a subtle 1px border (#E0E0E0) to separate themselves from the white background.
- **Interaction Depth:** On hover, primary and secondary buttons do not lift; instead, they transition to a slightly darker shade of their respective brand color.
- **Overlays:** Modals and dropdowns use a very soft, high-diffusion shadow (0px 8px 24px rgba(0,0,0,0.08)) to appear physically above the page content without breaking the clean aesthetic.

## Shapes

The shape language is **Rounded**, striking a balance between clinical precision and modern accessibility.

- **Standard Radius:** 0.5rem (8px) for buttons, input fields, and small UI components.
- **Large Radius:** 1rem (16px) for cards and main content containers.
- **Pill Shapes:** Used exclusively for tags, badges, and status indicators to differentiate them from actionable buttons.

## Components

### Buttons
- **Primary:** Solid Navy (#123B72) with White text. Used for main conversions.
- **Secondary:** Solid Green (#16A085) with White text. Used for supporting actions.
- **Outline:** Navy 1px border with Navy text, used for tertiary or cancel actions.

### Cards
- White background, 1px light gray border, 16px corner radius. Padding should be consistently 24px.

### Input Fields
- White background with an 8px corner radius. The border is 1px gray, turning Navy (#123B72) on focus. Labels sit above the field in Navy Bold.

### Badges & Chips
- Use the Green (#16A085) or Red (#E53935) colors with a 10% opacity background of the same hue to create "soft" status indicators. Text should remain full opacity.

### Navigation
- The Navbar is a solid Navy block. Navigation items use White text with a Green underline or weight change for the active state.