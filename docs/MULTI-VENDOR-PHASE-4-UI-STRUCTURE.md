# ShopPilot Multi-Vendor — Phase 4: UI & Structure

**Status:** UI and structure blueprint prepared.  
**Baseline:** Existing Laravel 13 application, Blade, Bootstrap-compatible assets, jQuery/AJAX, `admin` and `web` guards.  
**Authority:** Phase 3 design documents and the active Multi-Vendor PRD.

## 1. Phase 4 boundary

Phase 4 defines the vendor portal's route contract, folder conventions, layouts, navigation and responsive screen structure.

This package includes Blade UI templates and portal assets so the visual direction is concrete and reusable. It does **not** activate vendor routes or implement vendor business workflows. Those belong to Phase 5.

No database migrations, seeders, package changes, authentication-guard changes or existing admin/customer module changes are included.

## 2. Vendor portal actors and access boundary

- Vendor users continue to authenticate through the existing `web` guard and `User` model.
- A vendor-owned page requires an active `vendor_users` membership for the selected vendor.
- The vendor must be in the approved state for seller operations.
- Resource ownership must be checked server-side through the membership and the resource's vendor ownership.
- A global Spatie role is not proof of vendor ownership.
- Vendor IDs must be derived from the authorized membership, never from a hidden field or request payload.
- Seller applications that are pending, rejected or suspended continue to use the existing seller-application status experience.
- Vendor staff invitations and multi-vendor switching are excluded until D-12 and membership-selection rules are approved.

## 3. Portal navigation

| Group | Navigation item | Route name | Phase 5 controller |
|---|---|---|---|
| Workspace | Overview | `vendor.dashboard` | `Vendor\\DashboardController@index` |
| Catalogue | Products | `vendor.products.index` | `Vendor\\ProductController@index` |
| Catalogue | Add product | `vendor.products.create` | `Vendor\\ProductController@create` |
| Catalogue | Inventory | `vendor.inventory.index` | `Vendor\\InventoryController@index` |
| Sales | Orders | `vendor.orders.index` | `Vendor\\OrderController@index` |
| Sales | Order details | `vendor.orders.show` | `Vendor\\OrderController@show` |
| Store | Store profile | `vendor.store.edit` | `Vendor\\StoreController@edit` |
| Store | Account settings | `vendor.profile.edit` | `Vendor\\ProfileController@edit` |

Finance, commission, earnings, settlements and payout navigation are intentionally omitted while D-02 through D-08 remain unresolved. Vendor team management is omitted while D-12 remains unresolved.

## 4. Route contract

The Phase 5 route file is planned as `routes/vendor.php`, loaded by `routes/web.php` only when its controllers and middleware exist.

| Method | URI | Name | Purpose |
|---|---|---|---|
| GET | `/vendor/dashboard` | `vendor.dashboard` | Vendor overview |
| GET | `/vendor/products` | `vendor.products.index` | Vendor-owned catalogue |
| GET | `/vendor/products/create` | `vendor.products.create` | Product form |
| POST | `/vendor/products` | `vendor.products.store` | Create product |
| GET | `/vendor/products/{product}/edit` | `vendor.products.edit` | Edit owned product |
| PUT/PATCH | `/vendor/products/{product}` | `vendor.products.update` | Update owned product |
| DELETE | `/vendor/products/{product}` | `vendor.products.destroy` | Delete/archive owned product |
| GET | `/vendor/inventory` | `vendor.inventory.index` | Stock overview |
| GET | `/vendor/orders` | `vendor.orders.index` | Seller suborders |
| GET | `/vendor/orders/{vendorOrder}` | `vendor.orders.show` | Seller suborder details |
| PATCH | `/vendor/orders/{vendorOrder}/status` | `vendor.orders.status` | Allowed fulfilment transition |
| GET | `/vendor/store` | `vendor.store.edit` | Store profile form |
| PUT/PATCH | `/vendor/store` | `vendor.store.update` | Update owned store |
| GET | `/vendor/profile` | `vendor.profile.edit` | Account details |
| PATCH | `/vendor/profile` | `vendor.profile.update` | Update account details |

Required route middleware contract:

```text
auth:web
customer.active
vendor.active
```

`vendor.active` is a Phase 5 middleware contract. It must resolve an active membership and approved vendor, then expose the authorized vendor context to the request. It does not exist in the current application yet.

Every mutation must also use a Form Request and resource Policy. Vendor order status updates must use the proposed vendor-suborder state machine; sellers must never directly update the master order or payment status.

## 5. Folder conventions

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Vendor/
│   │       ├── DashboardController.php
│   │       ├── ProductController.php
│   │       ├── InventoryController.php
│   │       ├── OrderController.php
│   │       ├── StoreController.php
│   │       └── ProfileController.php
│   └── Requests/
│       └── Vendor/
│           ├── StoreProductRequest.php
│           ├── UpdateProductRequest.php
│           ├── UpdateStoreRequest.php
│           └── UpdateProfileRequest.php
├── Policies/
│   ├── VendorProductPolicy.php
│   ├── VendorOrderPolicy.php
│   └── VendorStorePolicy.php
└── Services/
    └── Vendor/
        ├── VendorDashboardService.php
        ├── VendorProductService.php
        ├── VendorInventoryService.php
        ├── VendorOrderService.php
        └── VendorStoreService.php

resources/
└── views/
    └── vendor-portal/
        ├── layouts/
        │   └── app.blade.php
        ├── partials/
        │   ├── sidebar.blade.php
        │   └── topbar.blade.php
        ├── dashboard/
        │   └── index.blade.php
        ├── catalog/
        │   ├── products/
        │   │   ├── index.blade.php
        │   │   └── form.blade.php
        │   └── inventory/
        │       └── index.blade.php
        ├── sales/
        │   └── orders/
        │       ├── index.blade.php
        │       └── show.blade.php
        ├── store/
        │   └── profile.blade.php
        └── account/
            └── profile.blade.php

public/
└── assets/
    └── vendor-portal/
        ├── css/
        │   └── vendor-portal.css
        └── js/
            └── vendor-portal.js

routes/
└── vendor.php                 # Phase 5 activation; not registered in Phase 4

tests/
└── Feature/
    └── VendorPortal/
```

Existing onboarding request `App\\Http\\Requests\\Vendor\\StoreVendorApplicationRequest` remains in place. Portal requests are separate from seller application requests.

## 6. Screen inventory

| Screen | Purpose | Main content |
|---|---|---|
| Overview | Daily seller workspace | Catalogue/order/stock summaries, recent orders, setup checklist |
| Products | Manage own catalogue | Search, status filters, product type, SKU, price, stock |
| Product form | Create/update product | Basic details, simple/variable type, pricing, media and variation area |
| Inventory | Review stock | On-hand, reserved and available quantities; low/out-of-stock states |
| Orders | Seller fulfilment queue | Vendor suborder reference, customer, line count, fulfilment and payment summaries |
| Order details | Review one vendor suborder | Customer snapshot, item snapshots and fulfilment timeline |
| Store profile | Present the store | Store identity, contact, logo/banner and customer-facing policies |
| Account settings | Manage seller identity | User details and membership context |

The current UI templates are presentation blueprints. Their form actions and route links become active only when Phase 5 routes, controllers, Policies and Services are implemented.

## 7. Responsive behavior

- Desktop: persistent 258px sidebar, sticky topbar, spacious content canvas and multi-column summary cards.
- Tablet: sidebar becomes an off-canvas drawer; summary cards reduce to two columns.
- Mobile: off-canvas navigation with overlay, one-column forms, stacked page actions and horizontally scrollable data tables.
- Small mobile: compact profile chip, tighter stat cards and hidden non-essential status metadata.
- Motion: short transitions only; `prefers-reduced-motion` is respected.
- Inputs retain visible focus states and controls include accessible labels.

## 8. UI principles

- Use the existing ShopPilot blue accent and Font Awesome asset already bundled with the project.
- Do not add a frontend framework, CDN dependency or new package.
- Use neutral surfaces, restrained shadows, clear hierarchy and readable spacing.
- Empty states explain what will appear and what the seller can do next.
- Do not show fabricated revenue, payout or conversion metrics.
- Do not expose finance screens until the financial decisions are approved.
- Do not expose staff management until D-12 is approved.
- Seller-facing fulfilment status is distinct from master order/payment status.

## 9. Data integration contract for Phase 5

The UI expects controller-provided data rather than calculating business values in Blade.

Suggested dashboard view data:

```php
[
    'vendor' => $authorizedVendor,
    'stats' => [
        'products_total' => 0,
        'products_active' => 0,
        'orders_to_review' => 0,
        'low_stock' => 0,
    ],
    'recentOrders' => $recentVendorOrders,
]
```

Suggested catalogue data:

```php
[
    'vendor' => $authorizedVendor,
    'products' => $vendorScopedProducts,
]
```

Suggested inventory data:

```php
[
    'vendor' => $authorizedVendor,
    'stats' => $inventoryStats,
    'inventoryRows' => $vendorScopedInventoryRows,
]
```

These are view contracts, not implemented query logic. Phase 5 must scope every query through the authorized vendor and must not trust client-supplied vendor IDs.

## 10. Explicit exclusions

Phase 4 does not implement:

- Vendor product CRUD persistence or moderation transitions.
- Vendor order/suborder persistence or fulfilment mutations.
- Unified stock reservations for simple and variable products.
- Payment attempts, webhooks, refunds, commission, ledger, settlements or payouts.
- Vendor staff invitation or membership permission editing.
- Marketplace checkout or mixed legacy/vendor cart behavior.
- Database migrations, seeders or changes to existing customer/admin behavior.

## 11. Phase 4 exit checklist

- [x] Vendor portal navigation and route names documented.
- [x] Folder and view conventions documented.
- [x] Responsive vendor portal layout prepared.
- [x] Dashboard, catalogue, inventory, order, store and account screen blueprints prepared.
- [x] Finance and staff navigation withheld pending owner decisions.
- [x] No existing routes, guards, database schema or business logic changed.
- [ ] Owner visual review.
- [ ] Phase 5 route/controller/Policy/Service implementation and tests.
