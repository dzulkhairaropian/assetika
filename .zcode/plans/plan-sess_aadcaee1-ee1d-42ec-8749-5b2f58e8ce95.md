### Enhancement: Add Direct Checkin to Actions Dropdown & Refine Table Presentation

#### 1. Add Direct Checkin in Table Actions Dropdown (`index.blade.php`)
- In the actions dropdown for each component row:
  - If the component is currently assigned (`assigned_asset_id` is present):
    - Add **Check-in** action button with confirmation: `<form action="{{ route('components.checkin', $component) }}" method="POST">...` (orange text with rotate-ccw icon).

#### 2. Refine Assigned Asset Column (No Bulky Badge, Clean Clickable Link)
- Remove bulky button/badge container around assigned asset.
- Replace with clean, minimal clickable link:
  - Asset Tag in bold font-mono (`DB/NB/250217/008`).
  - Device / Product model name in subtle gray (`Dell Latitude 3410`).
  - Assigned user name (`User: Dzul Khair Aropian`).

#### 3. Refine Status Badge Sizing & Icons
- Use `size="sm"` with subtle font size and proper Lucide icon rendering from status master definition.

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/index.blade.php`