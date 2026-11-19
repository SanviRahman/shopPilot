# ShopPilot Phase 5A - Vendor Operations

## Scope delivered

Phase 5A activates the seller workspace without implementing multi-vendor checkout, seller suborders, payments, commission, payout or shipping allocation.

- Approved vendor membership resolution through the existing `web` guard and `vendor_users` pivot.
- Vendor route protection through `vendor.active` middleware.
- Product ownership Policies for vendor product and store operations.
- Vendor dashboard summary and recent catalogue.
- Vendor-owned simple and variable product CRUD.
- Attribute-driven variant combinations with per-variant SKU, price, sale price, image and stock.
- Simple-product and variant inventory balances with stock movement history.
- Vendor store profile, policies, logo, banner and vacation-mode settings.
- Vendor account name and phone updates.
- Draft, review-pending, approved, rejected and suspended product moderation states with audit history.
- Customer-facing product/cart/checkout queries now exclude vendor products that are not approved.
- Feature tests for access isolation, simple/variable product creation, inventory adjustments and moderation.

## Vendor routes

All routes use `auth:web`, `customer.active` and `vendor.active` middleware.

| Method | Route | Name |
|---|---|---|
| GET | `/vendor/dashboard` | `vendor.dashboard` |
| GET | `/vendor/products` | `vendor.products.index` |
| GET/POST | `/vendor/products/create` / `/vendor/products` | `vendor.products.create` / `vendor.products.store` |
| GET/PUT/PATCH/DELETE | `/vendor/products/{product}` | `vendor.products.edit` / `vendor.products.update` / `vendor.products.destroy` |
| PATCH | `/vendor/products/{product}/submit-review` | `vendor.products.submit-review` |
| PATCH | `/vendor/products/{product}/publish` | `vendor.products.publish` |
| PATCH | `/vendor/products/{product}/unpublish` | `vendor.products.unpublish` |
| GET | `/vendor/inventory` | `vendor.inventory.index` |
| PATCH | `/vendor/inventory/products/{product}` | `vendor.inventory.products.update` |
| PATCH | `/vendor/inventory/variants/{variant}` | `vendor.inventory.variants.update` |
| GET/PUT | `/vendor/store` | `vendor.store.edit` / `vendor.store.update` |
| GET/PATCH | `/vendor/profile` | `vendor.profile.edit` / `vendor.profile.update` |

Vendor order management is intentionally not exposed until Phase 5B creates the master/suborder model and seller-specific fulfilment workflow.

## Product moderation

The safe default is review-before-publish. New vendor products are created as `draft` and `inactive`. A seller can submit a draft for review. Admins with `products.update` can approve or reject a pending vendor product, or suspend an approved one. Approval does not automatically publish a product; the seller explicitly publishes it after approval. Editing a product returns it to draft when moderation is required.

The default can be configured with `SHOP_VENDOR_PRODUCTS_REQUIRE_MODERATION` in `.env`. Keep it `true` until the product publication decision D-12 is formally approved. Existing platform products (`vendor_id = NULL`) and their existing moderation behavior are retained.

## Inventory extension

Migration `2026_11_19_160000_extend_inventory_for_simple_products_and_vendor_moderation.php` is additive:

- Adds nullable unique `inventory_balances.product_id` for simple-product balances.
- Makes `inventory_balances.product_variant_id` nullable while retaining the existing unique variant key.
- Adds nullable `inventory_balance_id`, `product_id` and `created_by_user_id` to inventory movements.
- Makes `inventory_movements.product_variant_id` nullable for simple-product movements.
- Creates `product_moderation_histories` for seller/admin moderation audit records.

Existing variant inventory and movement records are preserved. Legacy simple products continue reading `products.stock_quantity` until a balance is created on the first stock adjustment or product edit; no bulk stock backfill is performed. The migration's rollback refuses to proceed after vendor stock or moderation audit data exists rather than silently deleting history.

Low-stock display uses `SHOP_LOW_STOCK_THRESHOLD` (default `5`).

## Runtime setup

After taking a database backup, run:

```bash
php artisan migrate
php artisan optimize:clear
```

No seeder is required for Phase 5A. The vendor portal uses the existing owner membership created during seller application and approval.

## Verification boundary

PHP syntax checks and the vendor portal JavaScript syntax check passed in the build environment. Laravel runtime, migration execution against the owner's MySQL database, Blade rendering, feature tests and browser/mobile interaction still require verification in the full local environment because the uploaded archive did not include `vendor/` and Composer is unavailable in the build environment.

## Phase 5B remains pending

- Multi-vendor cart and checkout.
- Master orders and vendor suborders.
- Reservation and order-level inventory allocation.
- Payment attempts, provider webhooks and refunds.
- Commission calculation, ledger, settlements and payouts.
- Seller shipping, fulfilment and returns.

These workflows remain blocked by the relevant PRD business decisions. Do not represent them as operational in Phase 5A.
