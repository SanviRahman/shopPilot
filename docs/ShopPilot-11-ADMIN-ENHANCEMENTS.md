# ShopPilot Admin Enhancements - 2026-10-02

## Added
- Customer management from admin panel: CRUD, soft delete, trash, restore, force delete, bulk actions, active/inactive status.
- Inactive customers cannot login; active-account middleware protects authenticated customer account routes.
- Advanced AdminLTE dashboard with delivered sales, order/product/customer KPIs, order history, product history, top products, recent orders, staff workload and Chart.js charts.
- Role workload analytics for super_admin, admin, manager and agent based on assigned orders.
- Meta Pixel management with multiple Pixel IDs, optional full script storage, lifecycle (draft/testing/active/paused/archived), scheduling and per-feature tracking toggles.
- Frontend Meta Pixel runtime for PageView, ViewContent, Search, InitiateCheckout, AddToCart, CompleteRegistration and Purchase.
- Local `meta_pixel_events` audit stream containing event IDs, user/session context, payload, page URL, pixel ID snapshot and privacy-preserving IP hash.

## New migrations
- `2026_10_02_230500_add_status_to_users_table.php`
- `2026_10_02_231000_create_meta_pixels_table.php`
- `2026_10_02_231100_create_meta_pixel_events_table.php`

## Run after updating
```bash
composer install
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
```

## Security notes
- Meta full-script output is intentionally executable and is therefore permission-restricted to trusted admin roles.
- Never accept Meta script input from public/customer forms.
- `.env` and runtime logs should not be distributed in project archives.
