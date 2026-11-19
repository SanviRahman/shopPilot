# ShopPilot Multi-Vendor Logical ERD

**Source:** Multi-Vendor E-commerce Requirements PRD v1.0  
**Status:** Phase 3 design draft; policy-dependent relationships are marked as proposed.  
**Physical field specifications:** `MULTI-VENDOR-TABLE-SPECIFICATIONS.md`.

## 1. Current implemented Phase 1 and Phase 2 relationships

This diagram describes the marketplace-related relationships already present in the uploaded project. It does not imply that master/suborders, reservations, payment gateway records or finance tables already exist.

```mermaid
erDiagram
    USERS ||--o{ VENDOR_USERS : membership
    VENDORS ||--o{ VENDOR_USERS : has_members
    VENDORS ||--|| STORES : owns
    VENDORS ||--o{ VENDOR_DOCUMENTS : verifies_with
    VENDORS ||--o{ VENDOR_STATUS_HISTORIES : status_audit
    VENDORS o|--o{ PRODUCTS : owns_nullable_legacy
    CATEGORIES ||--o{ PRODUCTS : categorizes
    PRODUCTS ||--o{ PRODUCT_VARIANTS : has
    PRODUCT_ATTRIBUTES ||--o{ PRODUCT_ATTRIBUTE_VALUES : defines
    PRODUCT_VARIANTS }o--o{ PRODUCT_ATTRIBUTE_VALUES : selected_values
    PRODUCT_VARIANTS ||--o| INVENTORY_BALANCES : balance
    PRODUCT_VARIANTS ||--o{ INVENTORY_MOVEMENTS : movement_history
    ORDERS ||--o{ ORDER_ITEMS : contains_legacy_or_current
    PRODUCTS ||--o{ ORDER_ITEMS : snapshot_source
    PRODUCT_VARIANTS o|--o{ ORDER_ITEMS : optional_variant
```

Current facts:

- `products.vendor_id` is nullable to preserve existing platform-managed products.
- `product_variants` are children of products; their vendor is derived through `products.vendor_id`.
- `inventory_balances` is currently variant-only. Simple products still use `products.stock_quantity`.
- `orders` is currently a single order header; `vendor_orders` does not exist yet.
- `order_items` already has nullable variant reference and variant snapshot fields from Phase 2.

## 2. Target marketplace order and inventory relationships

```mermaid
erDiagram
    ORDERS ||--|{ VENDOR_ORDERS : splits_into
    VENDORS ||--o{ VENDOR_ORDERS : fulfils
    VENDOR_ORDERS ||--|{ ORDER_ITEMS : contains
    ORDERS ||--o{ ORDER_ITEMS : legacy_master_link
    PRODUCTS ||--o{ ORDER_ITEMS : snapshot_source
    PRODUCT_VARIANTS o|--o{ ORDER_ITEMS : selected_variant

    PRODUCTS ||--o| INVENTORY_BALANCES : simple_product_balance
    PRODUCT_VARIANTS ||--o| INVENTORY_BALANCES : variant_balance
    INVENTORY_BALANCES ||--o{ STOCK_RESERVATIONS : reserves
    ORDER_ITEMS ||--o{ STOCK_RESERVATIONS : reserved_for
    INVENTORY_BALANCES ||--o{ INVENTORY_MOVEMENTS : records

    ORDERS ||--o{ ORDER_ADDRESSES : snapshots
    VENDOR_ORDERS ||--o{ SHIPMENTS : fulfils_by
    SHIPMENTS ||--o{ SHIPMENT_ITEMS : contains
    ORDER_ITEMS ||--o{ SHIPMENT_ITEMS : shipped_line
    SHIPMENTS ||--o{ SHIPMENT_EVENTS : tracks
```

### Order ownership invariant

- `orders` is the customer-facing master record.
- `vendor_orders` is the seller-facing fulfilment and financial unit.
- Every new vendor-owned marketplace line belongs to exactly one `vendor_order`.
- `vendor_orders.vendor_id` is immutable after checkout.
- `order_items.order_id` is retained for compatibility with existing code/history.
- New marketplace order items carry `vendor_order_id` and a vendor ownership snapshot.
- A composite foreign-key design must prevent an item from referencing a vendor suborder that belongs to a different master order or vendor.
- Existing historical order items keep `vendor_order_id = NULL` until a separately approved backfill mapping exists.

## 3. Target payment and financial relationships

```mermaid
erDiagram
    ORDERS ||--o{ PAYMENT_ATTEMPTS : attempts
    PAYMENT_ATTEMPTS ||--o{ PAYMENT_TRANSACTIONS : provider_results
    PAYMENT_TRANSACTIONS ||--o{ WEBHOOK_INBOX : verified_events
    ORDERS ||--o{ PAYMENT_SUBMISSIONS : manual_proof_legacy

    VENDORS ||--o{ COMMISSION_RULES : scoped_rules
    VENDOR_ORDERS ||--o{ COMMISSION_ENTRIES : earns_or_charges
    COMMISSION_RULES o|--o{ COMMISSION_ENTRIES : applied_rule
    ORDER_ITEMS o|--o{ COMMISSION_ENTRIES : line_basis
    COMMISSION_ENTRIES o|--o{ VENDOR_FINANCIAL_LEDGER : ledger_posting
    VENDORS ||--o{ VENDOR_FINANCIAL_LEDGER : subledger
    VENDOR_ORDERS o|--o{ VENDOR_FINANCIAL_LEDGER : source_order
    VENDORS ||--o{ VENDOR_PAYOUT_ACCOUNTS : payout_destination
    VENDORS ||--o{ PAYOUT_REQUESTS : requests
    PAYOUT_REQUESTS ||--o{ PAYOUT_TRANSACTIONS : attempts
    SETTLEMENTS ||--o{ PAYMENT_TRANSACTIONS : reconciles_provider_batch
    REFUNDS ||--o{ REFUND_ITEMS : contains
    VENDOR_ORDERS ||--o{ REFUNDS : may_refund
    ORDER_ITEMS ||--o{ REFUND_ITEMS : refunded_line
    REFUNDS ||--o{ VENDOR_FINANCIAL_LEDGER : reversal_source
    PAYOUT_REQUESTS ||--o{ VENDOR_FINANCIAL_LEDGER : payout_source
```

Relationship notes:

- Payment is initiated against the master order. Vendor suborders do not each charge the customer independently in the initial single-checkout model.
- Manual `payment_submissions` remains a separate existing flow; gateway attempts and transactions are separate records.
- A commission entry stores the exact rule/basis snapshot used at purchase time.
- The vendor ledger is a per-vendor subledger. Payment-provider settlement into the marketplace and transfer from marketplace to seller are different financial events.
- A payout request may have multiple transfer attempts. Only verified transfer evidence can mark it paid.
- Refunds can be partial and must reference the relevant vendor order and order items.

## 4. Target returns and operations relationships

```mermaid
erDiagram
    VENDORS ||--o{ RETURN_REQUESTS : receives
    VENDOR_ORDERS ||--o{ RETURN_REQUESTS : relates_to
    RETURN_REQUESTS ||--o{ RETURN_ITEMS : contains
    ORDER_ITEMS ||--o{ RETURN_ITEMS : requested_line
    VENDOR_ORDERS ||--o{ REFUNDS : refunded_scope
    REFUNDS ||--o{ REFUND_ITEMS : contains
    ORDER_ITEMS ||--o{ REFUND_ITEMS : refunded_line

    VENDORS o|--o{ AUDIT_LOGS : scoped_activity
    USERS o|--o{ AUDIT_LOGS : customer_actor
    ADMINS o|--o{ AUDIT_LOGS : staff_actor
    OUTBOX_MESSAGES ||--o{ API_DELIVERY_ATTEMPTS : delivery_history
```

Returns, audit, outbox and provider delivery tables are target entities. Exact retention and courier integration are still subject to D-06, D-08, D-10 and D-11.

## 5. Ownership and key matrix

| Resource | Ownership source | Required scope |
|---|---|---|
| Store | `stores.vendor_id` | Active vendor membership + store/vendor match |
| Product | `products.vendor_id` | Active membership + product vendor match |
| Variant | `product_variants.product_id -> products.vendor_id` | Scope through parent product |
| Inventory balance | Product or variant stock target | Scope through product/variant owner |
| Vendor suborder | `vendor_orders.vendor_id` | Active membership + exact vendor match |
| Order item | `order_items.vendor_order_id` + vendor snapshot | Scope through vendor order; never by `order_id` alone |
| Shipment | `shipments.vendor_order_id` | Scope through vendor order |
| Return/refund | Vendor order and item links | Scope through vendor order; finance/admin authorization for approval |
| Vendor ledger | `vendor_financial_ledger.vendor_id` | Active membership; finance permissions for admin operations |
| Payout request/account | `vendor_id` | Vendor owner/authorized finance role only |
| Vendor document | `vendor_documents.vendor_id` | Vendor owner upload; authorized admin review; private download |

## 6. Legacy compatibility rules

1. Existing `orders` and `order_items` remain valid historical records.
2. Existing orders are not split retroactively unless a deterministic, reviewed ownership mapping exists.
3. Products with `vendor_id = NULL` remain legacy platform-managed products; no automatic vendor assignment is allowed.
4. New vendor-owned product creation must set vendor ownership from the authenticated membership, not from the request body.
5. Mixed cart behavior for legacy platform products and vendor products is blocked pending D-01.
6. Existing product-level stock remains compatible until simple-product inventory is migrated to the unified stock-balance design.
7. No historical order, order item, payment, refund or ledger row may be cascade-deleted as a side effect of product/vendor deletion.

## Phase 5A inventory and moderation additions

```mermaid
 erDiagram
    PRODUCTS ||--o| INVENTORY_BALANCES : simple_stock
    PRODUCT_VARIANTS ||--o| INVENTORY_BALANCES : variant_stock
    INVENTORY_BALANCES ||--o{ INVENTORY_MOVEMENTS : records
    PRODUCTS ||--o{ INVENTORY_MOVEMENTS : simple_or_variant_product
    PRODUCTS ||--o{ PRODUCT_MODERATION_HISTORIES : moderation_audit
    VENDORS ||--o{ PRODUCT_MODERATION_HISTORIES : vendor_audit
    ADMINS ||--o{ PRODUCT_MODERATION_HISTORIES : reviews
    USERS ||--o{ PRODUCT_MODERATION_HISTORIES : submits
```

`inventory_balances` has exactly one stock owner at application level: `product_id` for simple products or `product_variant_id` for a variant. `inventory_movements` stores the balance reference and actor attribution while retaining the legacy admin actor field.
