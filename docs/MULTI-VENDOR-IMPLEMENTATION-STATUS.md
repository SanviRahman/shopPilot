# ShopPilot Multi-Vendor Conversion

## Active authority

`Multi_Vendor_Ecommerce_Requirements_PRD_BN` is the active marketplace specification. The previous ShopPilot documents describe the original single-vendor implementation and remain useful only where they do not conflict with the marketplace PRD or explicit owner decisions.

## Existing architecture retained

- Laravel 13.34.0 / PHP requirement `^8.3`
- Blade, Bootstrap, JavaScript and jQuery/AJAX
- MySQL/InnoDB migrations and Eloquent
- Spatie Laravel Permission
- Spatie Media Library
- Separate `admin` and `web` guards
- Session-based customer cart
- Existing CMS, customer, category, product, wishlist and manual-payment modules

## Phase 1 implemented in this release

- Vendor business profile and lifecycle statuses
- One store profile per vendor with unique slug
- User-to-vendor membership through `vendor_users`
- Seller application form for authenticated web users
- Private verification-document storage on the `local` disk
- Admin vendor review, approval, rejection, suspension and reactivation
- Vendor status history
- Vendor view/approval/rejection/suspension/reactivation permissions
- Nullable `products.vendor_id` and `products.moderation_status` foundation

## Important compatibility notes

- Existing products retain `vendor_id = NULL` and remain legacy platform-managed products until an explicit ownership migration is approved.
- Existing product `status` remains `active` / `inactive`; `moderation_status` is a separate lifecycle field.
- Existing single-order checkout and product-level stock deduction are not yet multi-vendor compatible. Do not treat checkout, inventory, commission or payout as marketplace-ready until their later phases are implemented and tested.
- Product color, size, attributes, variant SKU/price/media and variant-level stock are scheduled for Phase 2. The current product-level stock field remains in use until the variant inventory cutover is complete.
- Seller dashboard, vendor-scoped product CRUD and vendor-scoped order fulfilment are not part of Phase 1.

## Decisions that remain blocked

The PRD decision register items D-02 to D-08 and D-10 to D-12 must be approved before implementing the affected payment, COD, commission, tax, payout, shipping, returns, hosting and tracking behavior. Do not invent those policies in code.

## Migration safety

The new migrations add marketplace tables and nullable product ownership fields. They do not delete existing products, orders, order items, stock quantities or payment records. Review and back up the database before applying migrations to a live environment.
