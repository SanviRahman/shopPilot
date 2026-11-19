# ShopPilot Vendor Portal — Route Map

This is the Phase 4 route contract. It is not registered in the running application until Phase 5 implements the corresponding controllers and `vendor.active` middleware.

| Method | URI | Route name | Controller action |
|---|---|---|---|
| GET | `/vendor/dashboard` | `vendor.dashboard` | `Vendor\\DashboardController@index` |
| GET | `/vendor/products` | `vendor.products.index` | `Vendor\\ProductController@index` |
| GET | `/vendor/products/create` | `vendor.products.create` | `Vendor\\ProductController@create` |
| POST | `/vendor/products` | `vendor.products.store` | `Vendor\\ProductController@store` |
| GET | `/vendor/products/{product}/edit` | `vendor.products.edit` | `Vendor\\ProductController@edit` |
| PUT/PATCH | `/vendor/products/{product}` | `vendor.products.update` | `Vendor\\ProductController@update` |
| DELETE | `/vendor/products/{product}` | `vendor.products.destroy` | `Vendor\\ProductController@destroy` |
| GET | `/vendor/inventory` | `vendor.inventory.index` | `Vendor\\InventoryController@index` |
| GET | `/vendor/orders` | `vendor.orders.index` | `Vendor\\OrderController@index` |
| GET | `/vendor/orders/{vendorOrder}` | `vendor.orders.show` | `Vendor\\OrderController@show` |
| PATCH | `/vendor/orders/{vendorOrder}/status` | `vendor.orders.status` | `Vendor\\OrderController@updateStatus` |
| GET | `/vendor/store` | `vendor.store.edit` | `Vendor\\StoreController@edit` |
| PUT/PATCH | `/vendor/store` | `vendor.store.update` | `Vendor\\StoreController@update` |
| GET | `/vendor/profile` | `vendor.profile.edit` | `Vendor\\ProfileController@edit` |
| PATCH | `/vendor/profile` | `vendor.profile.update` | `Vendor\\ProfileController@update` |

## Shared middleware contract

```text
auth:web
customer.active
vendor.active
```

`vendor.active` must resolve the authenticated user's active membership and an approved vendor. Each resource Policy must additionally confirm that the resource belongs to the same vendor.

## Route invariants

- Never accept `vendor_id` from form input as the source of ownership.
- Product variants inherit ownership through their parent product.
- Vendor order routes operate on `vendor_orders`, not the master `orders` resource.
- Vendor order actions may change only allowed fulfilment states.
- Vendor users cannot change master payment status.
- Finance/payout routes are withheld until D-02 through D-08 are approved.
- Team invitation routes are withheld until D-12 is approved.


## Phase 5A vendor routes now registered

The routes below are implemented in `routes/vendor.php` and included by `routes/web.php`. All require `auth:web`, `customer.active` and `vendor.active`.

- `GET /vendor/dashboard` — `vendor.dashboard`
- `Route::resource('products', ...)->except(['show'])` — `vendor.products.*`
- `PATCH /vendor/products/{product}/submit-review` — `vendor.products.submit-review`
- `PATCH /vendor/products/{product}/publish` — `vendor.products.publish`
- `PATCH /vendor/products/{product}/unpublish` — `vendor.products.unpublish`
- `GET /vendor/inventory` — `vendor.inventory.index`
- `PATCH /vendor/inventory/products/{product}` — `vendor.inventory.products.update`
- `PATCH /vendor/inventory/variants/{variant}` — `vendor.inventory.variants.update`
- `GET/PUT /vendor/store` — `vendor.store.*`
- `GET/PATCH /vendor/profile` — `vendor.profile.*`

Vendor order routes remain intentionally deferred to Phase 5B.


## Phase 5B routes

Vendor: `/vendor/orders`, `/vendor/finance`. Admin: `/admin/marketplace-finance`, `/admin/marketplace-finance/seller-orders`, `/admin/marketplace-finance/payouts`, `/admin/marketplace-finance/refunds`. Customer refund requests: `/account/orders/{orderNumber}/vendor-orders/{vendorOrder}/refunds`.
