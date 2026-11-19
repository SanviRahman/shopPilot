# ShopPilot Multi-Vendor Implementation Status

## Active authority

`Multi_Vendor_Ecommerce_Requirements_PRD_BN` is the active marketplace specification. The Phase 3 documents in this folder define the current target design. Older single-vendor documents remain references only where they do not conflict.

## Retained technical baseline

- Laravel 13.34.0 / PHP requirement `^8.3` (verify against the current `composer.json` before implementation).
- Blade, Bootstrap, JavaScript and jQuery/AJAX.
- MySQL/InnoDB and Eloquent.
- Spatie Laravel Permission and Spatie Media Library.
- Separate `admin` and `web` guards.
- Session-based customer cart.
- Existing CMS, customer, category, product, wishlist and manual-payment modules.

## Phase 1 - Vendor foundation

**Implementation present in the uploaded project:**

- Vendor business profile and lifecycle statuses.
- One store profile per vendor with unique slug.
- User-to-vendor membership through `vendor_users`.
- Seller application form for authenticated web users.
- Private verification-document storage.
- Admin vendor review, approval, rejection, suspension and reactivation.
- Vendor status history and permissions.
- Nullable `products.vendor_id` and separate `moderation_status`.

## Phase 2 - Product variations and inventory foundation

**Implementation present in the uploaded project:**

- Global product attributes and values.
- Simple and variable product type support.
- Product variants, attribute-value pivot, variant SKU and price.
- Variant-level inventory balance and movement history.
- Variant image through Media Library.
- Product type and order-item variant snapshot fields.
- Customer-side variant-aware product/cart/checkout code is present in the uploaded source.

**Runtime verification still required:** The owner previously reported that the first Phase 2 migration failed because four variation tables already existed while the migration was pending. A guarded migration repair was provided. The final `php artisan migrate` and `php artisan migrate:status` output has not been confirmed in this project conversation. Do not claim database/runtime validation is complete until that output is reviewed.

## Phase 3 - Data Design

**Design documents prepared:**

- `MULTI-VENDOR-PHASE-3-DATA-DESIGN.md`
- `MULTI-VENDOR-ARCHITECTURE-AND-ADRS.md`
- `MULTI-VENDOR-ERD.md`
- `MULTI-VENDOR-TABLE-SPECIFICATIONS.md`
- `MULTI-VENDOR-STATE-MACHINES.md`
- `MULTI-VENDOR-FINANCIAL-DESIGN.md`

No application code or migration files are changed by this Phase 3 documentation package. The design identifies the missing master/suborder, reservation, payment-attempt, webhook, refund, commission, ledger, payout and shipment entities.

## Current compatibility invariants

- Existing products with `vendor_id = NULL` remain legacy platform-managed products. Never silently assign them to a seller.
- Existing product `status` remains separate from `moderation_status`.
- Existing `orders` and `order_items` remain legacy records; no automatic retroactive split into vendor suborders.
- Phase 5A adds `inventory_balances.product_id` for simple products while retaining `products.stock_quantity` as a compatibility mirror. Variant balances remain keyed by `product_variant_id`.
- Existing manual `payment_submissions` remain separate from future provider attempts/webhooks.
- Existing single-vendor checkout is not multi-vendor ready. Do not claim marketplace checkout, commission or payout is ready until implemented and tested.

## Open business decisions

The PRD decision register D-01 through D-12 remains `[CONFIRM]`. In particular, D-02 through D-08 block final financial, payment, tax, shipping, refund and payout schema sign-off. Do not invent those policies in code.

## Phase 4 - UI & Structure

**UI and structure blueprint prepared:**

- `MULTI-VENDOR-PHASE-4-UI-STRUCTURE.md`
- `MULTI-VENDOR-ROUTE-MAP.md`
- `MULTI-VENDOR-FOLDER-STRUCTURE.md`
- Vendor portal Blade shell, navigation and responsive screen blueprints under `resources/views/vendor-portal/`.
- Vendor portal styles and responsive navigation behavior under `public/assets/vendor-portal/`.

The Phase 4 UI is now wired by the Phase 5A vendor operations implementation. Vendor routes, middleware, Policies, Services and additive inventory/moderation migration are present.

## Phase 5A - Vendor operations

**Implementation present in the latest source package:**

- `routes/vendor.php` registered with the existing `web` guard.
- `vendor.active` middleware requires exactly one approved active owner membership; ambiguous multiple vendor workspaces are blocked until workspace switching is approved.
- Vendor product and store Policies enforce ownership.
- Vendor dashboard, simple/variable product CRUD, variant combinations, inventory adjustments, store profile and account profile are operational in code.
- Simple-product and variant inventory balances/movements support vendor actor attribution.
- Product moderation history and admin approve/reject/suspend actions are included.
- Public product, cart and checkout queries exclude unapproved vendor products.
- Feature tests added under `tests/Feature/VendorPortal`.

See `docs/PHASE-5A-VENDOR-OPERATIONS.md` for routes, migration and verification details.

The safe default is review-before-publish. Keep `SHOP_VENDOR_PRODUCTS_REQUIRE_MODERATION=true` until D-12 is approved. Finance/payout navigation and seller order management remain excluded until Phase 5B and the relevant decisions are complete.

## Next sequence

1. Run the Phase 5A additive migration in the local MySQL environment after backup.
2. Run the VendorPortal feature tests and complete browser/mobile UAT.
3. Complete Phase 5B only after the required payment, tax, commission, shipping, refund and payout decisions are approved.
4. Complete Phase 6 QA/UAT.

## Migration safety

All future migrations must be additive and staged. Back up and verify restore before live migration. Do not run `migrate:fresh`, truncate tables or delete legacy products/orders to implement marketplace ownership.


## Phase 5B — Marketplace Operations

Implemented: master orders with seller suborders, per-seller shipping, manual MFS payment verification, stock reservations and consumption, order fulfilment tracking, configurable commission snapshots, append-only vendor ledger, verified payout accounts, manual payout requests, partial/full item refunds and inspection-based restock. See `PHASE-5B-MARKETPLACE-OPERATIONS.md` for setup and boundaries.
