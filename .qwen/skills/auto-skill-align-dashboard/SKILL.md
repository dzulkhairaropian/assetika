---
name: align-dashboard
description: Align and standardize dashboard pages with a repository reference template's styles, components, and layout structures.
source: auto-skill
extracted_at: '2026-07-09T08:31:48.360Z'
---

# Aligning Dashboards with Reference Templates

When updating or standardizing dashboard views to match a custom project style (such as TailAdmin), use the repository's main ecommerce or default dashboard as the absolute structural and visual blueprint.

## Step-by-Step Procedure

1. **Locate the Reference Template:**
   - Scan the codebase (e.g., `resources/views/pages/dashboard/` or components directory) for the main reference dashboard (often named `ecommerce.blade.php` or `dashboard.blade.php`).

2. **Analyze Structural and Visual Patterns:**
   - **Borders & Backgrounds:** Check class combinations (e.g., `bg-white dark:bg-white/[0.03]`, `border border-gray-200 dark:border-gray-800`).
   - **Paddings & Spacings:** Standardize container paddings (e.g., `p-5 md:p-6` or `px-4 pb-3 pt-4 sm:px-6`).
   - **Icons & Badges:** Identify layout style for icons (stroke vs. fill, sizing e.g. `w-12 h-12` wrappers, and color patterns for status labels like success, warning, or error badges).

3. **Incorporate Functional Polish:**
   - Never perform pure cosmetic/styling changes. Ensure that layout updates are coupled with functional value (such as dropdown filters, action links to resource management, or interactive selectors).

4. **Verify and Clean Up Component Scripts:**
   - Ensure the ApexCharts initialization or table layout container wrappers prevent responsive breaks (e.g., use scrollbars or responsive grids `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`).
