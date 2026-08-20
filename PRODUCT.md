# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
- **IT & GA Teams**: Internal administrators and support staff managing hardware, software licenses, consumables, GA assets, stock opname, and handling IT/GA helpdesk tickets.
- **All Employees**: End-users who view assigned assets, request consumables/assets (self-service), and submit helpdesk tickets.

## Product Purpose
Assetika is a unified Enterprise Asset & Maintenance System (EAM) designed to streamline hardware and software asset lifecycle tracking, General Affairs asset management, consumable stock, licensing, and integrated helpdesk ticketing with SLA tracking.

## Positioning
All-in-one IT & GA Asset Management combined with an integrated Helpdesk Ticketing System, auto-generated BAST (Berita Acara Serah Terima) PDFs, QR code scanning, and stock audit capabilities in a single Laravel application.

## Operating Context
- Asset lifecycle management: Intake, auto asset-tag generation (`[Company]/[Category]/[yyMMdd]/[Seq]`), checkout/checkin, maintenance logging, BAST generation, and retirement/disposal.
- Stock opname / auditing and consumable inventory tracking.
- Helpdesk ticket lifecycle: Submission, category/subcategory classification, assignment, SLA status tracking, commenting, and resolution.
- Desktop and mobile web usage (including mobile QR scanning).

## Capabilities and Constraints
- **Backend Stack**: Laravel 13, PHP 8.3, MySQL/PostgreSQL, Livewire, Spatie Permission (RBAC), Spatie Backup, DomPDF, Maatwebsite Excel.
- **Frontend Stack**: Tailwind CSS v4, Alpine.js, ApexCharts, FullCalendar, Lucide/Feather icons.
- **Role-Based Access Control**: Admins, GA managers, IT staff, and general employees.
- **Auditability**: Complete transaction logs for assets, consumables, components, and software licenses.

## Brand Commitments
- **Name**: Assetika
- Clean, operational admin interface prioritizing clarity, scanability, and quick data entry.

## Evidence on Hand
- Full codebase: Models, Controllers, Livewire components, Blade views, and migrations in repository.
- System PRD: `docs/prd to nextjs.md`.
- CRUD standardization guide: `docs/CRUD_STANDARDIZATION.md`.

## Product Principles
1. **Operational Efficiency First**: Speed up daily tasks like checkouts, stock counts, and ticket resolution with minimal friction.
2. **Audit & Traceability**: Every asset movement, checkout, checkin, maintenance, and ticket update must leave a clear audit trail.
3. **Scanability over Decoration**: Data density, clear status badges, and rapid navigation take priority for administrative tools.
4. **Self-Service Empowerment**: Make asset requests and helpdesk ticket submission simple and intuitive for all employees.

## Accessibility & Inclusion
- Responsive web UI for desktop management and mobile/tablet scanning.
- High contrast, clear status indicators, and keyboard-navigable forms.
