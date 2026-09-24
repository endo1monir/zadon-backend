---
name: Vibrant Fresh Commerce
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#3d4a42'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6d7a72'
  outline-variant: '#bccac0'
  surface-tint: '#006c4a'
  primary: '#006948'
  on-primary: '#ffffff'
  primary-container: '#00855d'
  on-primary-container: '#f5fff7'
  inverse-primary: '#68dba9'
  secondary: '#855300'
  on-secondary: '#ffffff'
  secondary-container: '#fea619'
  on-secondary-container: '#684000'
  tertiary: '#006194'
  on-tertiary: '#ffffff'
  tertiary-container: '#007bb9'
  on-tertiary-container: '#fdfcff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#85f8c4'
  primary-fixed-dim: '#68dba9'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#005137'
  secondary-fixed: '#ffddb8'
  secondary-fixed-dim: '#ffb95f'
  on-secondary-fixed: '#2a1700'
  on-secondary-fixed-variant: '#653e00'
  tertiary-fixed: '#cce5ff'
  tertiary-fixed-dim: '#93ccff'
  on-tertiary-fixed: '#001d31'
  on-tertiary-fixed-variant: '#004b73'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '800'
    lineHeight: 48px
    letterSpacing: -0.03em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
  price-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '800'
    lineHeight: 28px
    letterSpacing: -0.02em
  price-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 17px
    fontWeight: '700'
    lineHeight: 22px
    letterSpacing: -0.01em
  price-strike:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0em
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 10px
    fontWeight: '700'
    lineHeight: 12px
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-mobile: 0.75rem
  margin: 1.5rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

The design system is engineered for quick-commerce confidence, instant clarity, and sensory freshness. Designed for hyper-local delivery spanning supermarkets, neighborhood grocers, pharmacies, and fresh daily bakeries, the aesthetic communicates vitality, surgical reliability, and frictionless speed. 

The aesthetic sits at the intersection of **Modern Tactile Utility** and **Fresh Minimalism**:
- **Pristine & Vital:** High-key, clean backgrounds convey absolute hygiene and produce freshness, avoiding visual clutter to spotlight vibrant goods.
- **Speed & Reassurance:** High-contrast wayfinding and purposeful color-coding deliver rapid scanning under split-second ordering scenarios.
- **Mobile Ergonomics:** Deep consideration for single-hand thumb reaches, tactile stepper interfaces, dynamic order trackers, and persistent low-friction checkouts.

## Colors

The palette balances clinical cleanliness with the vibrant abundance of open-air markets and fresh produce:

- **Primary (`#059669` / Core Emerald):** Represents life, freshness, health, and verified operations. Used for primary CTAs, active states, cart counters, delivery guarantees, and brand anchors. Light emerald (`#ECFDF5`) acts as the standard soft background for positive confirmations and produce-related tags.
- **Secondary (`#F59E0B` / Sunlit Amber):** Drives urgency, flash discounts, limited-stock warnings, ratings, and promotional alerts. Paired with soft amber tints (`#FFFBEB`) for offer chips and membership badges.
- **Tertiary (`#0284C7` / Clinical Cyan-Blue):** Designated for pharmaceutical validation, prescription status tags, cold-chain logistics badges, and healthcare compliance indicators.
- **Accent Orange (`#EA580C` / Warm Crust):** Reserved specifically for bakery goods, ready meals, and breakfast express tags.
- **Neutrals & Surfaces:** Pure White (`#FFFFFF`) serves as the base surface for product cards and bottom sheets. Canvas Slate (`#F8FAFC`) grounds the app background. Border Gray (`#E2E8F0`) establishes subtle structure. Slate Dark (`#0F172A`) anchors headlines and critical product pricing for maximum contrast against light surfaces.

## Typography

The design system utilizes **Plus Jakarta Sans** across all roles to achieve a contemporary, welcoming, yet structurally disciplined interface.

- **Numerics & Prices:** Product prices rely on `price-lg` and `price-md` with tabular numbers enabled (`tnum`) to eliminate layout jitter during cart tallying. Currency symbols are set at 75% relative size with baseline alignment. Discount strikethroughs use `price-strike` colored with `#94A3B8`.
- **Hierarchical Discipline:** Headers maintain tight negative tracking (`-0.02em` to `-0.03em`) to deliver modern editorial snap, while utility microcopy (estimated delivery times, stock counts, category labels) relies on heightened tracking (`0.02em` to `0.04em`) and semi-bold weights for scanning at a glance.

## Layout & Spacing

The layout is built on an **8pt responsive spatial grid** with an intentional single-column/dual-column mobile focus:

- **Mobile Viewports (<640px):** 4-column layout with `1rem` outer canvas padding and `0.75rem` gutters. Product feeds arrange strictly in 2-column cards or 1-column comprehensive lists. Thumb-accessible interactive areas sit firmly within a 56px minimum touch zone at the screen bottom.
- **Tablet / Expanded (640px - 1024px):** 8-column layout with `1.5rem` margins and `1rem` gutters. Product catalog expands to 3 or 4 columns.
- **Desktop (>1024px):** 12-column layout maxing out at `1280px` centered canvas with fixed sidebar navigation and a persistent right-hand basket summary.
- **Rhythm & Density:** Standard gap between product card items is `space-sm` (8px) internally and `space-md` (16px) externally between module groups.

## Elevation & Depth

Visual hierarchy uses **ambient, soft tinted shadows** over low-contrast borders to evoke lightness and cleanliness without muddying the screen.

- **Level 0 (Flat):** Used for background canvas (`#F8FAFC`) and structural sections.
- **Level 1 (Card Default):** `#FFFFFF` surfaces paired with a 1px border of `#E2E8F0` and an ultra-subtle ambient drop shadow: `0px 2px 8px -2px rgba(15, 23, 42, 0.04)`.
- **Level 2 (Floating Controls & Steppers):** Applied to active quantity steppers, category filter pills on scroll, and vendor profile headers: `0px 6px 16px -4px rgba(15, 23, 42, 0.08)`.
- **Level 3 (Sticky Bottom Bars & Modals):** Persistent cart bar, express checkout drawers, and prescription upload modals: `0px -8px 24px -6px rgba(15, 23, 42, 0.08)` along with a top stroke of `#E2E8F0`.

## Shapes

The shape vocabulary emphasizes friendliness, ergonomic safety, and organic freshness:

- **Cards & Sheets (`rounded-2xl` / 16px):** Primary vendor cards, product tiles, promotional hero banners, and sheet surfaces use a 16px corner radius.
- **Micro-Elements & Badges (`rounded-full` / Pill):** All status pills (e.g., "15 min", "Prescription Required", "In Stock"), discount chips, and product image tags must use full pill radius to contrast clearly against rectangular card containers.
- **Inputs & CTAs (`rounded-xl` / 12px):** Search inputs, primary checkout buttons, and delivery tip selectors use 12px corners for a dependable, non-slippery feel.

## Components

### Buttons & Quick CTAs
- **Primary CTA:** Solid Emerald (`#059669`) with white typography, 48px height on mobile, 52px on sticky checkouts, font-weight 700. Active tap reduces scale to `0.98`.
- **Secondary CTA:** Tinted emerald base (`#ECFDF5`) with `#047857` label, no border.
- **Ghost/Tertiary:** Transparent background with subtle `#E2E8F0` outline and Slate Dark text.

### Quantity Stepper (Zero-to-One Cart State)
- When count = 0: Compact pill button labeled `"ADD"` with primary green border and bold green text (`#059669`).
- When count > 0: Expands into a unified solid green pill (`#059669`) featuring minus icon, bold white number count (`price-md`), and plus icon. Stepper buttons have a minimum hit area of 36x36px.

### Badges & Status Chips
- **Fast Delivery:** Emerald tint (`#ECFDF5`), `#065F46` text, lightning bolt leading icon.
- **Special Deal:** Amber tint (`#FFFBEB`), `#B45309` text, fire or percent leading icon.
- **Pharmacy & Rx:** Blue tint (`#F0F9FF`), `#0369A1` text, shield or cross leading icon.
- **Out of Stock:** Gray tint (`#F1F5F9`), `#64748B` text, strikethrough styling.

### Cards (Vendor & Product)
- **Product Card:** White surface, 1px border (`#E2E8F0`), 16px radius, containing a 1:1 aspect ratio centered product image on neutral cool gray (`#F8FAFC`), pill discount badge top-left, heart wishlist icon top-right, title clamped to 2 lines, weight/unit spec in `body-sm` (`#64748B`), and bottom row anchoring price stack on left and add/stepper on right.
- **Vendor Card:** Full-width header image (16:9 or 21:9 ratio), floating vendor logo avatar overlapping bottom border, delivery time chip, distance, and rating star badge with count.

### Input Fields
- White fill, 1px border `#CBD5E1`, 12px corner radius, 44px minimum height. Focused state switches border to `#059669` with a 3px soft outer ring of `rgba(5, 150, 105, 0.15)`. Leading search icon in `#94A3B8`.

### Sticky Action Container
- Anchored to bottom viewport with dynamic safe-area-inset padding. White background, top hairline border (`#E2E8F0`), elevation Level 3. Houses total cart item count preview, live estimated time, and full-width checkout action button.