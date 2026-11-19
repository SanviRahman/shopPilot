# ShopPilot Phase 2 — Product Attributes, Variations & Inventory

## Scope delivered

- Admin-managed product attributes and values (Color, Size, and custom attributes).
- Simple and variable product types.
- Cartesian variation generation from selected attribute values.
- Variant-level SKU, regular price, sale price, image, and stock.
- Inventory balances with on-hand, reserved, and calculated available quantity.
- Inventory movement records for opening stock, adjustments, and checkout sales.
- Customer-side option selection with live SKU, price, image, and availability updates.
- Variant-aware cart, Buy Now, checkout, and order-item snapshots.
- Admin order-item variation selection.
- Responsive product and attribute management interfaces.

## Database additions

- `product_attributes`
- `product_attribute_values`
- `product_variants`
- `product_variant_attribute_values`
- `inventory_balances`
- `inventory_movements`

Existing `products` gains `product_type` (default `simple`). Existing `order_items` gains nullable variant reference and immutable variation snapshot fields.

## Local setup

Run these commands from the project root:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ProductAttributeSeeder
php artisan optimize:clear
```

The migration is additive. It does not reset or delete existing products, orders, customers, or stock records. Existing products remain `simple` by default.

## Admin workflow

1. Open **E-commerce → Attributes**.
2. Confirm Color and Size exist, or create the attributes and values needed by the catalogue.
3. Open **Products → Add New Product**.
4. Choose **Variable Product**.
5. Select attributes and values, then generate variations.
6. Set each variation's SKU, prices, stock, and optional image.
7. Save the product.

## Inventory rules

- Available stock is calculated as `on_hand_quantity - reserved_quantity`.
- Stock cannot be adjusted below already-reserved quantity.
- Variant stock changes are recorded in `inventory_movements`.
- Checkout locks the product, variant, and balance rows before checking and deducting stock.
- Order items retain the selected SKU and variation labels/attributes as snapshots.

## Verification status

PHP syntax checks and JavaScript syntax checks were performed for the changed files. A full Laravel migration, browser, and database integration run must still be performed in the local project environment because the build workspace does not include the application's Composer dependencies and configured database connection.
