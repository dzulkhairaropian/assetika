---
name: Assetika Design System
description: TailAdmin-based Enterprise Asset & Maintenance Management UI system
colors:
  primary: "#465fff"
  primary-hover: "#3641f5"
  neutral-bg: "#f9fafb"
  neutral-card: "#ffffff"
  neutral-text: "#101828"
  neutral-muted: "#667085"
  neutral-border: "#e4e7ec"
  success: "#12b76a"
  error: "#f04438"
  warning: "#f79009"
typography:
  display:
    fontFamily: "Outfit, sans-serif"
    fontSize: "48px"
    fontWeight: 700
    lineHeight: "60px"
  headline:
    fontFamily: "Outfit, sans-serif"
    fontSize: "36px"
    fontWeight: 600
    lineHeight: "44px"
  title:
    fontFamily: "Outfit, sans-serif"
    fontSize: "20px"
    fontWeight: 600
    lineHeight: "30px"
  body:
    fontFamily: "Outfit, sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: "20px"
  label:
    fontFamily: "Outfit, sans-serif"
    fontSize: "12px"
    fontWeight: 500
    lineHeight: "18px"
rounded:
  sm: "6px"
  md: "8px"
  lg: "12px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "10px 20px"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  card:
    backgroundColor: "{colors.neutral-card}"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.lg}"
    padding: "24px"
  input:
    backgroundColor: "{colors.neutral-card}"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.md}"
    padding: "10px 14px"
---

# Design System: Assetika

## Overview

**Creative North Star: "The Precision Asset Console"**

Assetika operates on a high-density, clean TailAdmin dashboard pattern optimized for IT and General Affairs operations. The UI prioritizes visual hierarchy, explicit status signals, and rapid data scanning over decorative embellishments.

Key Characteristics:
- High scanability with clear tabular layouts and status badges.
- Cohesive brand accent `#465fff` guiding interactive and active states.
- Clean typography utilizing `Outfit` for display and data density.
- Native dark mode adaptation across all cards, sidebars, and data tables.

## Colors

The palette uses a vivid electric indigo primary accent over a neutral cool gray base.

### Primary
- **Electric Indigo** (#465fff): Used for primary call-to-action buttons, active navigation states, selected tabs, and interactive highlights.

### Neutral
- **Page Canvas** (#f9fafb / dark: #0c111d): Main application background.
- **Card Background** (#ffffff / dark: #1a2231): Container background for metrics, forms, and tables.
- **Primary Text** (#101828 / dark: #ffffff): High-contrast text readability.
- **Muted Text** (#667085 / dark: #98a2b3): Subtitles, table header labels, and field descriptions.
- **Border** (#e4e7ec / dark: #344054): Standard divider and container outline.

### Status Signals
- **Success Green** (#12b76a): Available status, completed checks, active subscriptions.
- **Error Red** (#f04438): Overdue SLA, retired assets, critical errors, bulk deletions.
- **Warning Amber** (#f79009): Maintenance pending, low stock warnings, expiring licenses.

### Named Rules
**The Single Accent Rule.** Electric Indigo (#465fff) is strictly reserved for primary user actions and selected navigation items. Status badges use semantic colors exclusively.

## Typography

**Display & Body Font:** Outfit (Google Fonts stack with sans-serif fallback)

### Hierarchy
- **Display** (Bold, 48px, line-height 60px): High-level dashboard metric figures and landing hero text.
- **Headline** (SemiBold, 36px, line-height 44px): Page headers and primary section titles.
- **Title** (SemiBold, 20px, line-height 30px): Card titles, modal headers, and table title sections.
- **Body** (Regular, 14px, line-height 20px): Form labels, table cell content, and descriptions.
- **Label** (Medium, 12px, line-height 18px): Badge text, metadata tags, and micro-copy.

## Layout

- **Sidebar Grid**: Collapsible 290px left sidebar navigation with icon-only collapsed state.
- **Container**: Max-width responsive container with 16px/24px padding grid.
- **Breakpoints**: Extended Tailwind scale (`2xsm`: 375px, `xsm`: 425px, `sm`: 640px, `md`: 768px, `lg`: 1024px, `xl`: 1280px, `2xl`: 1536px, `3xl`: 2000px).

## Elevation & Depth

Surfaces are flat by default with subtle border definition (`#e4e7ec`). Shadows indicate elevation and active floating states (dropdowns, tooltips, datepickers).

### Shadow Vocabulary
- **Theme XS** (`0px 1px 2px 0px rgba(16, 24, 40, 0.05)`): Subtle button and card elevation.
- **Theme SM** (`0px 1px 3px 0px rgba(16, 24, 40, 0.1)`): Standard card at rest.
- **Theme LG** (`0px 12px 16px -4px rgba(16, 24, 40, 0.08)`): Modals and floating panels.
- **Focus Ring** (`0px 0px 0px 4px rgba(70, 95, 255, 0.12)`): Primary focus state on inputs and buttons.

## Shapes

- **Corner Radius**: Standardized 8px (`rounded-lg` / `{rounded.md}`) for cards, inputs, and buttons. 12px for floating popovers.
- **Borders**: 1px subtle stroke (`#e4e7ec`) across table headers, input boundaries, and card containers.

## Components

### Buttons
- **Primary**: Background `#465fff`, text `#ffffff`, radius 8px, padding `10px 20px`, font-weight 500. Hover: `#3641f5`.
- **Secondary / Outline**: Background transparent, border `#d0d5dd`, text `#344054`. Hover background `#f9fafb`.

### Cards & Tables
- **Card**: Background `#ffffff`, border `#e4e7ec`, radius 12px, internal padding 24px.
- **Table**: Striped or clean row layouts, table header background `#f9fafb` with 12px uppercase muted labels.

### Inputs
- **Text & Select**: Border `#d0d5dd`, background `#ffffff`, radius 8px, padding `10px 14px`. Focus ring `#465fff` with 4px ambient halo.

## Do's and Don'ts

### Do:
- **Do** use uppercase 12px Outfit Medium for table headers and badge text.
- **Do** maintain high visual contrast in status badges with semi-transparent background tints (e.g. `bg-brand-50 text-brand-500`).
- **Do** apply `--shadow-focus-ring` on custom interactive element focus states.

### Don't:
- **Don't** use multi-color gradients for action buttons; keep primary actions solid `#465fff`.
- **Don't** mix non-Outfit fonts unless displaying raw code snippets.
- **Don't** omit dark mode variants (`dark:...`) on custom components.
