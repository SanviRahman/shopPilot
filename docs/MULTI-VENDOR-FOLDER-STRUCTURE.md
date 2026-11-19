# ShopPilot Multi-Vendor Folder Structure

## Phase 5A implemented structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Vendor/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── StoreController.php
│   │   │   └── ProfileController.php
│   │   └── Middleware/EnsureVendorIsActive.php
│   └── Requests/Vendor/
│       ├── StoreVendorProductRequest.php
│       ├── UpdateVendorProductRequest.php
│       ├── UpdateVendorInventoryRequest.php
│       ├── UpdateVendorStoreRequest.php
│       └── UpdateVendorProfileRequest.php
├── Models/
│   └── ProductModerationHistory.php
├── Policies/
│   ├── VendorProductPolicy.php
│   └── VendorStorePolicy.php
└── Services/
    ├── ProductModerationService.php
    └── Vendor/
        ├── VendorProductService.php
        ├── VendorInventoryService.php
        └── VendorStoreService.php

resources/views/vendor-portal/
├── layouts/app.blade.php
├── partials/
│   ├── sidebar.blade.php
│   └── topbar.blade.php
├── dashboard/index.blade.php
├── catalog/
│   ├── products/
│   │   ├── index.blade.php
│   │   └── form.blade.php
│   └── inventory/index.blade.php
├── store/profile.blade.php
└── account/profile.blade.php

public/assets/vendor-portal/
├── css/vendor-portal.css
└── js/vendor-portal.js

routes/vendor.php
database/migrations/2026_11_19_160000_extend_inventory_for_simple_products_and_vendor_moderation.php
tests/Feature/VendorPortal/
```

## Phase 5B planned structure

Vendor order controllers, order Policies/Services, seller fulfilment views and marketplace finance modules are intentionally not registered or exposed until the master/suborder and financial workflows are implemented and the relevant PRD decisions are approved.

## Placement rules

- Keep the public seller application under `App\\Http\\Controllers\\Website` and its existing `website.account.seller-*` routes.
- Keep vendor approval and moderation operations under `Backoffice/Admin`.
- Vendor portal controllers belong in `App\\Http\\Controllers\\Vendor`.
- Vendor users continue to authenticate through the existing `web` guard.
- `vendor.active` resolves the approved active owner membership and blocks ambiguous multiple workspaces until vendor switching is approved.
- Product and store ownership checks are enforced through Policies, not request-supplied vendor IDs.
- Finance, payout, staff and order modules remain deferred to Phase 5B.
