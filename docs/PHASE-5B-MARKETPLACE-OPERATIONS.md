# ShopPilot Phase 5B — Marketplace Operations

## Approved operating configuration

- Currency: configured by `SHOP_MARKETPLACE_CURRENCY` (default `BDT`).
- Payment collection: existing manual bKash, Nagad and Rocket payment-submission flow.
- COD and external payment gateways: disabled/not integrated in this phase.
- Commission: marketplace-wide percentage managed by Admin. New orders snapshot the active rule. Initial rate is `0%` until Admin configures it.
- Commission basis: net merchandise value after allocated order discount; shipping is excluded.
- Vendor shipping: the selected delivery-method fee is applied per seller order, plus the seller's configured store shipping fee.
- Vendor earnings: net merchandise after commission plus the seller order's shipping amount.
- Payout: manual Bank/MFS transfer with Admin verification and transfer-reference recording.
- Tax: disabled until a tax policy is approved.
- Refund: full or partial item-quantity requests after delivery; Admin approves/rejects and records manual refund transfer. Shipping is not included in the refundable merchandise amount. Restock is optional and only performed when Admin confirms inspection.

## Order lifecycle

1. Checkout creates one master `orders` record.
2. Items are grouped by seller into `vendor_orders` records (one platform-owned seller group may have a null `vendor_id`).
3. Discount is allocated across line items and seller orders proportionally.
4. Stock is reserved in `inventory_balances`; `inventory_reservations` records each reserved order item.
5. One manual payment submission is attached to the master order.
6. Admin payment verification consumes reservations, confirms seller orders and posts vendor earnings to the pending ledger bucket.
7. Vendors move their own orders through `processing`, `ready_to_ship`, and `shipped`, adding tracking details at dispatch.
8. Admin confirms delivery. Pending earnings are moved to the available ledger bucket.
9. Vendors may request payout from available balance. The request moves funds to held; Admin confirms or rejects the manual transfer.
10. Customers can request a full or partial item refund for a delivered seller order. Admin records the refund transfer and may restock inspected items.

## Ledger rules

- `vendor_financial_ledger` is append-only and uses unique idempotency keys.
- Order earnings are pending until Admin confirms delivery.
- Payout requests move available funds to held; paid transfers move held funds to paid-out.
- Rejected payouts release held funds to available.
- Refund debits may make a vendor's available balance negative; future released earnings offset that balance before further payouts.
- Commission rate and amount are stored as order-time snapshots and are not recalculated when the Admin changes the current rule.

## Routes

### Vendor

- `GET /vendor/orders`
- `GET /vendor/orders/{vendorOrder}`
- `PATCH /vendor/orders/{vendorOrder}/status`
- `GET /vendor/finance`
- `POST /vendor/finance/payout-accounts`
- `POST /vendor/finance/payout-requests`

### Admin

- `GET /admin/marketplace-finance`
- `PUT /admin/marketplace-finance/commission`
- `GET /admin/marketplace-finance/seller-orders`
- `PATCH /admin/marketplace-finance/seller-orders/{vendorOrder}/delivered`
- `GET /admin/marketplace-finance/payouts`
- `PATCH /admin/marketplace-finance/payouts/{payout}/approve|reject|paid`
- `PATCH /admin/marketplace-finance/payout-accounts/{account}/verify`
- `GET /admin/marketplace-finance/refunds`
- `PATCH /admin/marketplace-finance/refunds/{refund}/approve|reject|complete`

### Customer

- `POST /account/orders/{orderNumber}/vendor-orders/{vendorOrder}/refunds`

## Environment configuration

The marketplace uses configuration values from `config/shop.php` and the `.env` file:

- `SHOP_MARKETPLACE_CURRENCY`
- `SHOP_MARKETPLACE_CURRENCY_SYMBOL`
- `SHOP_MINIMUM_PAYOUT_AMOUNT`
- `SHOP_MAXIMUM_PAYOUT_AMOUNT`
- `SHOP_MAXIMUM_VENDOR_SHIPPING_FEE`
- Existing `SHOP_VENDOR_PRODUCTS_REQUIRE_MODERATION`

Copy only the required variable names/values from `.env.example` into the deployment environment. Do not overwrite the existing production `.env` or `APP_KEY`.

## Database migration

The additive migration `2026_11_19_170000_create_marketplace_fulfilment_and_finance_tables` creates seller orders, seller order history, stock reservations, commission snapshots, payout accounts/requests/transactions, refunds/refund items and the append-only vendor ledger. It also adds `order_items.vendor_order_id`, `order_items.discount_amount` and `stores.shipping_fee` when absent.

Back up the database before running migrations. Do not use `migrate:fresh` or manually insert migration rows.

## Setup after merging the updated files

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan route:list --path=vendor
php artisan route:list --path=marketplace-finance
```

Set the marketplace commission rate from Admin → Marketplace Finance before accepting live seller orders. The initial rate is zero by design, so no commission is charged until configured.

## Known boundaries

- No COD, automated gateway API or webhook is included; the existing manual MFS verification is used.
- Manual transfer is not automatically confirmed by a bank/MFS provider. Admin must record the transfer reference.
- No tax calculation is applied.
- Seller orders are delivered by Admin confirmation; courier integration is not included.
- Paid-order cancellation is handled through the refund workflow; the master order cannot be cancelled directly after payment verification.
- Refund eligibility is limited to delivered seller orders. No time-window policy was approved, so none is hardcoded.
