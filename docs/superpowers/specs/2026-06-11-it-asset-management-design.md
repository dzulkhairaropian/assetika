# Design Spec: IT Asset Management & BAST System

## Goal
Implement a comprehensive CRUD for IT Assets and a management system for assigning/returning assets (Checkout/Checkin) with integrated Berita Acara Serah Terima (BAST).

## Architecture
- **Models:** 
    - `Asset`: Main IT asset entity.
    - `AssetCheckout`: Records the movement of assets.
- **Controllers:**
    - `AssetController`: Handles standard CRUD operations.
    - `AssetManagementController`: Handles Checkout, Checkin, and BAST display/generation logic.
- **Routing:**
    - `assets.*`: Standard resource routes.
    - `assets.checkout`: GET/POST for assigning assets.
    - `assets.checkin`: GET/POST for returning assets.
    - `assets.bast`: GET for viewing the handover document.

## UI/UX Design (TailAdmin Style)
- **Asset Index:** 
    - Table-based list with search and filters.
    - Visual indicators (Badges) for statuses:
        - `Ready`: Green
        - `Deployed`: Blue
        - `Broken`: Red
        - `Archived`: Gray
- **Asset Forms:** 
    - Multi-section forms (General, Technical, Financial, Licenses).
    - Client-side validation for required IT fields.
- **Management Interface:**
    - Modal or dedicated page for Checkout/Checkin.
    - Tabbed view in Asset Detail for "Movement History".

## BAST (Berita Acara Serah Terima)
- **Handover View:** 
    - Professional document layout.
    - Includes: Handover parties, Asset specifications, Terms of use, and signature blocks.
    - Printable via browser (CSS @media print).

## Data Schema Considerations
- Ensure `Asset` model correctly handles `asset_tag` and `device_name` via its `booted` method.
- `AssetCheckout` must track both the user receiving the asset and the supervisor/admin performing the action.
- Evidence files (BAST scans) stored in `storage/app/public/checkouts`.

## Success Criteria
- IT Assets can be created, updated, and deleted.
- Assets can be assigned to users, updating their status and creating a checkout record.
- Assets can be returned, recording condition and updating status back to "Ready".
- BAST documents can be viewed and printed for any active or past assignment.
