# ShopPilot Multi-Vendor Table Specifications

**Source:** Multi-Vendor E-commerce Requirements PRD v1.0  
**Status:** Phase 3 logical/physical schema draft; final migrations are not included in this phase.  
**Database:** MySQL / InnoDB / utf8mb4. Use the actual production MySQL/MariaDB version to validate check constraints and generated columns before implementation.

## 1. Design conventions

- Primary keys: `BIGINT UNSIGNED AUTO_INCREMENT`.
- Foreign keys: matching `BIGINT UNSIGNED` types.
- Money: `DECIMAL(12,2)` to align with the current project; never `FLOAT` or `DOUBLE`.
- Currency: persist a 3-character currency code on each financial snapshot. BDT is proposed in the PRD, not yet approved as a final legal/accounting setting.
- Time: store UTC timestamps; render in the configured application timezone.
- Status: validated string/enum values with explicit transition services.
- Ownership: use `vendor_id` and membership scope; never rely on frontend hiding.
- Delete behavior: RESTRICT for historical commercial/financial records; SET NULL only for optional actor references; avoid destructive cascade on orders, refunds, shipments and ledger.
- All money/quantity calculations are server-side and transactional.

## 2. Existing tables in the uploaded project

These are implemented in Phase 1/2 and should be preserved.

| Table | Current role | Key fields / constraints | Status |
|---|---|---|---|
| `vendors` | Seller business profile | `id`, business/legal details, contact details, `status`, reviewer/timestamps, soft delete | Implemented |
| `stores` | One store per vendor | `vendor_id` unique FK, globally unique `slug`, store profile/policies, status, vacation mode | Implemented |
| `vendor_users` | Vendor membership pivot | `vendor_id`, `user_id`, `role`, `status`, `permissions`, `joined_at`; unique `(vendor_id,user_id)` | Implemented |
| `vendor_documents` | Private verification documents | `vendor_id`, uploader/reviewer, type, disk/path, MIME/size, status, expiry/reason | Implemented |
| `vendor_status_histories` | Append-only vendor status audit | vendor, actor FKs, from/to status, reason, metadata, created_at | Implemented |
| `products` | Catalog product | Existing fields plus nullable `vendor_id`, `moderation_status`, `product_type`; legacy null vendor is preserved | Implemented |
| `product_attributes` | Global reusable attributes | name, slug, display_type, active, sort_order | Implemented |
| `product_attribute_values` | Attribute options | attribute FK, label, slug, optional color_code, active, sort_order | Implemented |
| `product_variants` | Product SKU/price variants | product FK, globally unique SKU, regular/sale price, active, soft delete | Implemented |
| `product_variant_attribute_values` | Variant attribute pivot | variant FK, value FK, unique `(product_variant_id, product_attribute_value_id)` | Implemented |
| `inventory_balances` | Variant stock balance | unique `product_variant_id`, on_hand, reserved | Implemented; variant-only |
| `inventory_movements` | Variant stock audit | variant, movement type, quantity delta, before/after, reference, actor | Implemented; variant-only |
| `orders` | Current order header | buyer snapshot, totals, payment/order status, optional user/agent/coupon | Implemented; currently single-order model |
| `order_items` | Current order lines | order/product FKs, optional variant FK, product/SKU/variant/price/quantity snapshots | Implemented; no vendor suborder link yet |
| `payment_submissions` | Existing manual payment proof | order/payment method, transaction ID, submit/verify state | Implemented; preserve |

The current project does not yet contain `vendor_orders`, `stock_reservations`, provider payment attempts/webhooks, commission ledger, payout, shipment or refund tables.

## 3. Identity and vendor ownership

### 3.1 `vendor_users` (existing)

| Column | Rule |
|---|---|
| `vendor_id` | FK to `vendors.id`, RESTRICT on delete |
| `user_id` | FK to `users.id`, RESTRICT on delete |
| `role` | `owner` or approved staff role |
| `status` | Active/inactive membership lifecycle |
| `permissions` | Nullable scoped permissions JSON; never replaces server-side Policy checks |
| `joined_at` | Membership activation time |

Constraints:

- Unique `(vendor_id, user_id)`.
- Index `(user_id, status)` and `(vendor_id, status)`.
- Exactly one active owner per vendor is a transactional business invariant. Do not add a generated-column constraint until host DB compatibility is verified.
- Staff invitation workflow is P1 and remains subject to D-12.

### 3.2 `products` ownership (existing + target rule)

- Existing `vendor_id` remains nullable for legacy platform-managed products.
- New vendor-created products must have `vendor_id` set from the authenticated membership.
- `vendor_id` must not be editable by a vendor through ordinary product update.
- Product ownership transfer after a sale is not allowed in the initial release unless a reviewed transfer process preserves order and ledger history.
- Existing null-vendor products must not be silently assigned to a seller.
- Add/retain index `(vendor_id, status, moderation_status, created_at)` for vendor catalogue queries.
- Global unique product SKU/slug are retained initially for compatibility; per-vendor SKU uniqueness is not approved.

## 4. Inventory schema required for multi-vendor checkout

### 4.1 `inventory_balances` (existing table; target extension)

Current table is variant-only. The target must also support simple products so checkout can reserve stock uniformly.

| Column | Target rule |
|---|---|
| `id` | PK |
| `product_id` | Nullable FK to products for simple-product stock |
| `product_variant_id` | Nullable FK to product_variants for variant stock |
| `on_hand_quantity` | Unsigned integer |
| `reserved_quantity` | Unsigned integer maintained transactionally |
| timestamps | Standard |

Target invariant: exactly one of `product_id` or `product_variant_id` is non-null. Unique index on each target column. Use a DB CHECK only after verifying the production MySQL/MariaDB version; also enforce in the service layer.

Migration impact: make the current `product_variant_id` nullable, add nullable `product_id`, add its FK/unique index, and preserve every existing variant balance row unchanged. Existing simple product `stock_quantity` is backfilled only after a reviewed migration plan; do not drop or stop reading it until the new inventory path is verified.

### 4.2 `inventory_movements` (existing table; target extension)

Retain current movement fields and add `inventory_balance_id` plus the simple-product target reference as needed. A movement must identify exactly one stock balance. Preserve `on_hand_before`, `on_hand_after`, quantity delta, movement type, business reference, actor and timestamp. Historical rows remain immutable.

### 4.3 `stock_reservations` (new)

| Column | Rule |
|---|---|
| `id` | PK |
| `inventory_balance_id` | FK to `inventory_balances`, RESTRICT |
| `order_item_id` | FK to `order_items`, RESTRICT |
| `quantity` | Positive unsigned quantity |
| `status` | `active`, `consumed`, `released`, `expired` (proposed) |
| `idempotency_key` | Unique checkout/reservation key |
| `expires_at` | Reservation expiry |
| `consumed_at`, `released_at` | Nullable lifecycle timestamps |
| timestamps | Standard |

Indexes: `(status, expires_at)`, `(order_item_id, status)`, `(inventory_balance_id, status)`. `inventory_balances.reserved_quantity` must equal the active reservation total and is updated in the same transaction. A reservation may not be consumed/released/expired twice.

## 5. Order schema

### 5.1 `orders` (existing master order)

Keep the current buyer-facing master order and existing legacy columns. Target additions for new marketplace orders:

- `currency_code CHAR(3)` snapshot (no hard-coded default until D-07).
- Optional allocation totals such as tax/fee totals only after D-04/D-06/D-07.
- Existing `order_status` becomes an aggregate display/status for marketplace orders; child `vendor_orders` are the fulfilment source of truth.
- Existing `payment_status` remains the customer-facing master payment summary; `payment_attempts`/`payment_transactions` are the attempt-level source records. COD collection is a separate D-06-dependent record, not a second payment status on the vendor order.
- Existing legacy orders without vendor suborders keep the current status workflow.

### 5.2 `vendor_orders` (new)

One row per vendor within a master order.

| Column | Rule |
|---|---|
| `id` | PK |
| `order_id` | FK to `orders`, RESTRICT |
| `vendor_id` | FK to `vendors`, RESTRICT; required for vendor suborders |
| `suborder_number` | Unique human-readable identifier |
| `status` | Vendor fulfilment state (proposed state machine) |
| `store_name_snapshot` | Store name at purchase time |
| `vendor_name_snapshot` | Vendor display/legal name at purchase time |
| `item_subtotal` | DECIMAL snapshot |
| `discount_total` | DECIMAL allocation snapshot; formula D-04 pending |
| `shipping_total` | DECIMAL allocation snapshot; rule D-06 pending |
| `tax_total` | DECIMAL allocation snapshot; rule D-07 pending |
| `fee_total` | DECIMAL snapshot; fee owner D-04 pending |
| `grand_total` | Vendor suborder amount snapshot |
| `currency_code` | Currency snapshot |
| timestamps / soft delete | Historical retention policy; finance rows must not be deleted |

Constraints: unique `(order_id, vendor_id)`, unique `suborder_number`, composite unique `(id, order_id, vendor_id)`, index `(vendor_id, status, created_at)`. Delete behavior: RESTRICT.

### 5.3 `order_items` (existing table; target extension)

Keep existing `order_id`, `product_id`, optional `product_variant_id`, `product_name`, `sku`, `variant_name`, `variant_attributes`, `unit_price`, `quantity` and `line_total`.

Add for new marketplace items:

- `vendor_order_id` FK to `vendor_orders`.
- `vendor_id` immutable vendor snapshot (nullable only for legacy platform-managed items).
- `discount_amount`, `tax_amount`, `shipping_amount` and `currency_code` snapshots once D-04/D-06/D-07 are approved.
- Optional `store_name_snapshot` only if not already covered by the vendor order snapshot.

Constraints:

- Composite FK `(vendor_order_id, order_id, vendor_id)` -> `vendor_orders(id, order_id, vendor_id)`.
- Composite FK `(product_id, vendor_id)` -> unique `(products.id, products.vendor_id)` for non-null vendor-owned items.
- Keep `order_id` for backward compatibility and efficient master order retrieval.
- Existing historical items retain `vendor_order_id = NULL` and `vendor_id = NULL` unless a deterministic backfill is approved.
- Product and vendor foreign keys must not cascade-delete order history.

### 5.4 `order_addresses` (new or staged extension)

Purpose: immutable delivery/billing address snapshot at checkout. Suggested fields: `order_id`, `address_type`, recipient name/phone/email, address lines, city/area, region, postal code, country, timestamps. A master order may have one shipping and one billing address. Vendor suborders reference the master address unless separate delivery addresses are later approved.

## 6. Payment schema

### 6.1 `payment_attempts` (new)

Fields: `order_id`, payment method/provider, amount, currency, status, idempotency key (unique), provider request reference, expiry, initiated/finished timestamps and minimal metadata. Index `(order_id, created_at)`.

### 6.2 `payment_transactions` (new)

Fields: `payment_attempt_id`, optional `settlement_id`, provider, provider transaction reference, amount, currency, verified status, provider settled/verified timestamp and minimal normalized response metadata. Unique provider transaction reference. Do not store secrets or unredacted card/token payloads.

### 6.3 `webhook_inbox` (new)

Fields: provider, provider event ID, event type, payload hash, received/processed timestamps, status, attempt count and limited failure code. Unique `(provider, provider_event_id)`. Raw payload retention must be minimized/redacted. Verify signature and timestamp before processing.

### 6.4 Existing `payment_submissions`

Preserve current manual MFS proof flow. It is not a provider webhook transaction. A manual submission remains `submitted` until an authorized admin verifies it. Whether multiple submissions/attempts are allowed is part of the later payment workflow design.

## 7. Refund and return schema

### 7.1 `refunds` (new)

Fields: master `order_id`, `vendor_order_id`, requester, reviewer, reason, status, requested/approved/refunded amounts, currency, provider/COD repayment reference, processed timestamp and evidence reference. Partial refunds are supported.

### 7.2 `refund_items` (new)

Fields: `refund_id`, `order_item_id`, quantity, item amount, allocated discount/tax/shipping amounts once policy is approved. A refund item may not exceed the remaining refundable quantity/amount of the order item.

### 7.3 `return_requests` / `return_items` (target; D-08 dependent)

Keep physical return authorization and goods receipt separate from money refund. Fields include vendor order, order item, requested quantity, reason, status, inspection outcome, received timestamp and restock decision. Exact windows and restock triggers remain D-08.

## 8. Marketplace finance schema

### 8.1 `commission_rules`

Fields: scope type, optional vendor/category/product scope reference, calculation type, rate value, optional currency for fixed fee, commission basis key, effective dates, active state, creator and timestamps. Exactly one scope is populated. Rule priority is proposed as product > vendor > category > marketplace default and requires D-04 approval.

### 8.2 `commission_entries`

Fields: vendor order, optional order item, applied rule reference, rule snapshot JSON, basis amount, rate snapshot, commission amount, currency, unique idempotency/source event key, optional reversal reference and posted timestamp.

### 8.3 `vendor_financial_ledger`

Fields: vendor, optional vendor order/commission entry/refund/payout references, journal key, bucket, entry type, signed amount, currency, source reference, unique idempotency key, minimal metadata and posted timestamp. Append-only. Balance is `SUM(amount)` by vendor/currency/bucket.

### 8.4 `vendor_payout_accounts`

Fields: vendor, channel/provider, account holder name, encrypted account details, non-sensitive fingerprint/last-four, verification state, verified actor/time and active/default flags. Do not store account identifiers in plaintext or logs. Channel details depend on D-03/D-05.

### 8.5 `payout_requests`

Fields: vendor, payout account, amount, currency, status, requested time, reviewer/approver actors, approval/rejection reason and idempotency key. Enforce payout amount <= eligible balance in a locked transaction.

### 8.6 `payout_transactions`

Fields: payout request, channel/provider, external transfer reference, amount, currency, status, evidence disk/path, initiated/confirmed timestamps, confirmer and failure reason. Unique `(provider, transfer_reference)` when reference is present. `paid` requires external evidence and reconciliation.

### 8.7 `settlements`

Represents provider-to-marketplace settlement batches, not vendor payouts. Fields: provider, provider settlement reference, currency, gross/fee/net amount, status, statement/evidence reference, settled time and reconciliation metadata.

### 8.8 `financial_adjustments`

Fields: vendor, amount, currency, adjustment type, reason, source reference, requested/approved actors, approval timestamp and resulting ledger journal key. Never directly edit prior ledger rows.

## 9. Shipping and fulfilment schema

### 9.1 `shipments`

One vendor order may have multiple shipments. Fields: vendor order, vendor, carrier/service, tracking number, status, shipping amount snapshot, shipped/delivered timestamps and provider reference. Carrier fields depend on D-06.

### 9.2 `shipment_items`

Fields: shipment, order item, shipped quantity. Supports partial shipment. A shipped quantity cannot exceed the unshipped quantity of the order item.

### 9.3 `shipment_events`

Fields: shipment, event code/status, description, occurred_at, source/provider reference and minimal metadata. Provider event IDs must be idempotent when supplied.

### 9.4 Decision-dependent logistics tables

`shipping_zones`, `shipping_rate_rules` and `cod_collection_records` are not final until D-06 approves courier account ownership, rate calculation, cash collector and remittance rules.

## 10. Operations schema

### `audit_logs`

Append-only privileged action log: actor type/id, optional vendor, action, subject type/id, before/after summary, reason, request/correlation ID and timestamp. Exclude secrets, OTPs, payment tokens and unnecessary PII. IP/user-agent retention requires D-11/privacy policy.

### `outbox_messages`

Transactional outbox for after-commit provider/notification work: event key (unique), event type, aggregate type/id, minimal payload, status, attempts, available_at, processed_at and last failure code. Business transaction writes the outbox row before commit; worker/scheduler processes it after commit.

### `api_delivery_attempts`

Optional durable delivery/retry history for provider calls. Keep bounded response/error metadata; never persist secrets.

## 11. Candidate/deferred tables from the PRD

| Table | Treatment |
|---|---|
| `brands` | Optional catalog extension; not required for first marketplace data cutover |
| `carts`, `cart_items` | Not required while current session cart remains; revisit only for persistent cart requirement |
| `addresses` | `order_addresses` snapshot is sufficient for initial checkout; customer address book is later scope |
| `coupon_redemptions` | P1; add when vendor/product eligibility and discount funding rules are approved |
| `tax_rules` | D-07 dependent |
| `shipping_zones`, `shipping_rate_rules` | D-06 dependent |
| `support_tickets`, `reviews` | Customer experience later scope unless separately prioritized |
| `vendor_invites` | P1; D-12 staff invitation scope |

## 12. Required constraints and indexes

| Area | Constraint / index |
|---|---|
| Membership | Unique `(vendor_id, user_id)`; indexes by vendor/status and user/status |
| Store | Unique `vendor_id`; unique slug |
| Product | Index `(vendor_id, status, moderation_status, created_at)`; preserve current SKU/slug uniqueness |
| Variant | Unique SKU; unique `(product_id, combination_key)` recommended to prevent duplicate option combinations |
| Variant attributes | Unique `(product_variant_id, product_attribute_value_id)`; validate one value per attribute per variant in service/tests |
| Inventory | Unique product balance and unique variant balance; no negative available stock |
| Reservation | Unique idempotency key; indexes by balance/status/expiry |
| Vendor order | Unique `(order_id, vendor_id)`; unique suborder number; index `(vendor_id,status,created_at)` |
| Order item | Composite FK tying item to matching master order + vendor order + vendor; preserve legacy null links |
| Payment | Unique idempotency key; unique provider transaction reference |
| Webhook | Unique `(provider, provider_event_id)` |
| Commission | Effective-date overlap check within a scope; unique source event for each entry |
| Ledger | Unique idempotency key; indexes `(vendor_id,currency_code,bucket,posted_at)` and source reference |
| Payout | Unique provider transfer reference; payout <= eligible locked balance |
| Shipment | Index `(vendor_order_id,status)`; shipment item quantity bounded by unshipped line quantity |
| Historical data | RESTRICT on commercial/financial parent deletion; SET NULL for optional actors only |

## 13. Money and allocation snapshots

Each new order/suborder/item financial record must retain:

- amount and currency;
- item subtotal and line totals;
- discount allocation and discount funder;
- shipping allocation and shipping funder;
- tax amount and tax responsibility;
- fee amount and fee responsibility;
- commission basis/rate/amount and rule snapshot.

Exact columns and formula depend on D-04, D-06 and D-07. Do not use one opaque JSON object as the only source of financial values; JSON may supplement explicit numeric columns.

## 14. Safe migration sequence (future implementation phase)

1. Back up the database and verify a restore before any live migration.
2. Add `vendor_orders` and nullable `order_items.vendor_order_id` / vendor snapshot fields; preserve legacy rows.
3. Add order address snapshots and currency fields for new orders only; do not guess historical currency.
4. Extend inventory balances/movements to support simple products; preserve current variant rows and backfill simple product stock under a reviewed script.
5. Add stock reservations and verify concurrent final-unit purchase tests.
6. Add payment attempt/transaction/webhook tables after D-03 provider selection.
7. Add commission, ledger, settlement and payout tables only after finance sign-off D-02/D-04/D-05/D-07.
8. Add refund/return/shipping/COD tables after D-06/D-08.
9. Add indexes and composite constraints; verify query plans and vendor isolation tests.
10. Cut over marketplace checkout only after QA proves idempotency, stock consistency, ledger reconciliation and legacy compatibility.

No Phase 3 migration files are delivered. The above is a design sequence, not an instruction to run SQL or alter the current database.

## Phase 5A implementation delta

The Phase 5A migration extends the Phase 3 inventory proposal to support both simple products and variants without removing the legacy `products.stock_quantity` field.

### `inventory_balances` additions

| Column | Type | Null | Key | Notes |
|---|---|---:|---|---|
| `product_id` | BIGINT UNSIGNED | Yes | UNIQUE, FK -> products.id | Set for a simple-product balance |
| `product_variant_id` | BIGINT UNSIGNED | Yes | Existing UNIQUE, FK -> product_variants.id | Set for a variant balance |

Exactly one of `product_id` or `product_variant_id` is set by the application service. The migration does not rewrite legacy simple-product stock; the inventory UI reads `products.stock_quantity` when no simple balance exists and creates a balance on the first adjustment or product edit.

### `inventory_movements` additions

| Column | Type | Null | Key | Notes |
|---|---|---:|---|---|
| `inventory_balance_id` | BIGINT UNSIGNED | Yes | FK -> inventory_balances.id, null on delete | Identifies the balance affected by a new movement |
| `product_id` | BIGINT UNSIGNED | Yes | FK -> products.id, null on delete | Set for simple products and variant movements |
| `product_variant_id` | BIGINT UNSIGNED | Yes | Existing FK -> product_variants.id | Null for simple-product movements |
| `created_by_user_id` | BIGINT UNSIGNED | Yes | FK -> users.id, null on delete | Vendor/customer-side actor attribution |

Existing `created_by_admin_id` remains for backoffice stock operations.

### `product_moderation_histories`

Stores product moderation transitions with `product_id`, `vendor_id`, optional admin/user actor IDs, `from_status`, `to_status`, `reason` and `created_at`. Product/vendor foreign keys restrict hard deletion so moderation audit history is not silently lost.
