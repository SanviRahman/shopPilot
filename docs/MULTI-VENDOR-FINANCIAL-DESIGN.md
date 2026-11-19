# ShopPilot Multi-Vendor Financial Design

**Source:** Multi-Vendor E-commerce Requirements PRD v1.0, Sections 4.3-4.4, 5.2-5.5 and 7.4  
**Status:** Illustrative design only. Financial sign-off is blocked by D-02 through D-08.

> This document defines data separation and audit invariants. It does not approve a legal merchant-of-record model, commission formula, tax treatment, shipping allocation, payout schedule or COD collector.

## 1. Separate financial concepts

The system must not collapse the following into one `orders.grand_total` or one mutable vendor balance:

1. Customer payment due/received.
2. Provider settlement received by the marketplace.
3. Gross merchandise value and order-level discounts.
4. Tax and shipping amounts/obligations.
5. Marketplace commission.
6. Vendor receivable/earning.
7. Held and eligible vendor funds.
8. Actual seller payout/transfer.
9. Refund, chargeback and adjustment liability.

A successful customer payment does not mean a seller has been paid. A ledger credit does not prove cash has been transferred to the seller.

## 2. Proposed financial records

| Record | Purpose | Immutable facts |
|---|---|---|
| `payment_attempts` | One customer payment attempt against a master order | Provider, method, amount, currency, idempotency key and attempt status |
| `payment_transactions` | Verified provider transaction/result | Provider transaction reference, verified amount/currency and outcome |
| `webhook_inbox` | Duplicate-safe provider event processing | Provider event ID, received/processed status and timestamps |
| `settlements` | Provider-to-marketplace settlement batch/reconciliation | Provider batch reference, gross/fee/net amounts and settlement evidence |
| `commission_rules` | Effective-dated policy configuration | Scope, rate, basis and effective period |
| `commission_entries` | Purchase-time calculation snapshot | Applied rule, basis amount, rate, amount and currency |
| `vendor_financial_ledger` | Append-only seller subledger | Signed amount, currency, bucket, event reference and posting time |
| `vendor_payout_accounts` | Seller payout destination | Encrypted destination details and verification state |
| `payout_requests` | Seller request and approval workflow | Requested amount, currency, vendor and review decisions |
| `payout_transactions` | Actual transfer attempt and evidence | Provider/channel, transfer reference, amount and proof |
| `financial_adjustments` | Authorized correction or recovery | Reason, approval, source reference and ledger postings |
| `refunds` / `refund_items` | Full/partial refund workflow | Requested/approved/refunded amounts and item allocation |

## 3. Commission rule design

### 3.1 Candidate scope

A rule may apply at one of these scopes:

- Marketplace default
- Category
- Vendor
- Product

The PRD suggests this precedence, but marks it for confirmation:

```text
Product exception > Vendor rule > Category rule > Marketplace default
```

### 3.2 Candidate rule fields

`commission_rules` should store:

- `scope_type` and exactly one applicable scope reference (`vendor_id`, `category_id` or `product_id`; all null for marketplace default).
- `calculation_type` (for example percentage or fixed amount).
- `rate_value` as DECIMAL with documented precision.
- `currency_code` when a fixed monetary rate is used.
- `commission_basis` as a policy key; do not hard-code until D-04.
- `effective_from`, `effective_to`, `is_active`.
- `created_by_admin_id`, timestamps and audit history.

Overlapping active rules for the same scope must be rejected transactionally. A rule change affects future purchases only; existing `commission_entries` retain their purchase-time snapshot.

### 3.3 Purchase-time snapshot

Each `commission_entries` row records:

- vendor order and optional order item;
- rule ID, if any;
- JSON snapshot of rule scope, calculation type, rate and basis definition;
- basis amount, commission amount and currency;
- unique source event/idempotency key;
- reversal reference when a later refund/chargeback reverses it.

Never recalculate old commission entries using today's rule configuration.

## 4. Vendor financial ledger

### 4.1 Design

`vendor_financial_ledger` is an append-only per-vendor subledger. Suggested columns:

| Column | Purpose |
|---|---|
| `id` | Primary key |
| `vendor_id` | Owning vendor |
| `vendor_order_id` | Optional source order |
| `payout_request_id` | Optional payout source |
| `refund_id` | Optional refund source |
| `journal_key` | Groups rows that move value between buckets |
| `bucket` | `pending`, `held`, `available`, `paid_out`, `receivable` |
| `entry_type` | Sale, commission, release, refund, payout, chargeback or adjustment |
| `amount` | Signed DECIMAL; positive increases the bucket, negative decreases it |
| `currency_code` | ISO currency code stored per entry |
| `source_type`, `source_id` | Business source reference |
| `idempotency_key` | Unique source event key |
| `metadata` | Minimal non-secret context |
| `posted_at` | Posting time |

A ledger balance is calculated by summing entries for `(vendor_id, currency_code, bucket)`. A cached balance may be added for performance only if it can be rebuilt from the ledger and is updated in the same transaction.

### 4.2 Ledger invariants

- Posted entries are never edited or deleted.
- Every posting has a unique business/idempotency reference.
- Every entry has vendor, currency, source event, amount, bucket and timestamp.
- A correction is a new reversing/adjustment entry linked to the original.
- Vendor funds are not available until the configured hold/release conditions are satisfied.
- Payout cannot exceed the eligible available balance.
- A negative vendor balance remains visible as a receivable/negative balance; it is not hidden by clamping to zero.
- Provider settlement and seller payout are separate reconciliations.
- Account numbers, credentials, webhook secrets and raw payment tokens must never be stored in ledger metadata or application logs.

## 5. Illustrative calculation (NOT an approved policy)

The following values demonstrate how the schema keeps allocations separate. They are not the ShopPilot commission or shipping policy.

**Assumptions for this example only:**

- Currency shown as BDT only for illustration; D-07 must approve the actual currency.
- Vendor A merchandise gross: 1,000.00.
- Vendor B merchandise gross: 1,500.00.
- A seller-funded discount of 100.00 is allocated proportionally: A 40.00, B 60.00.
- Commission is illustratively 10% of net merchandise after the allocated seller-funded discount.
- Tax is excluded.
- Customer shipping charge is 150.00 (A 60.00, B 90.00) and is held outside vendor earnings until D-06 decides allocation.

| Calculation | Vendor A | Vendor B | Total |
|---|---:|---:|---:|
| Gross merchandise | 1,000.00 | 1,500.00 | 2,500.00 |
| Allocated seller-funded discount | -40.00 | -60.00 | -100.00 |
| Net merchandise | 960.00 | 1,440.00 | 2,400.00 |
| Illustrative 10% commission | -96.00 | -144.00 | -240.00 |
| Vendor earning before hold/payout | 864.00 | 1,296.00 | 2,160.00 |
| Shipping collected separately | 60.00 | 90.00 | 150.00 |

Illustrative customer total before tax: `2,400.00 + 150.00 = 2,550.00`.

This example does **not** decide who ultimately receives shipping, who funds discounts in other promotions, whether commission is calculated before/after tax, or which fees are deducted. Those are D-04, D-06 and D-07.

### 5.1 Example ledger postings

For Vendor A only, using the illustrative assumptions:

| Event | Bucket | Amount |
|---|---|---:|
| Sale earning | pending | +960.00 |
| Commission | pending | -96.00 |
| Hold release: remove from pending | pending | -864.00 |
| Hold release: make eligible | available | +864.00 |
| Payout request hold | available | -500.00 |
| Payout request hold | held | +500.00 |
| Transfer verified | held | -500.00 |
| Transfer verified | paid_out | +500.00 |

The two rows sharing a `journal_key` represent a bucket transfer. Each row has a unique `idempotency_key` and cannot be posted twice.

## 6. Partial refund example (illustrative)

If 240.00 of Vendor A's 960.00 net merchandise is refunded, and the same illustrative 10% commission basis applies:

- Customer refund amount: 240.00.
- Commission reversal: 24.00.
- Vendor earning reversal: 216.00.
- If funds are still pending/available, post the debit/reversal against the appropriate bucket.
- If the vendor was already paid, post a documented receivable/negative balance and recover it under the approved policy.
- Restock only after the D-08 inspection/return condition is satisfied.

The original sale and commission entries remain unchanged.

## 7. Payout workflow and evidence

1. Vendor submits a payout request against an eligible balance.
2. System locks/rechecks available balance and places the requested amount into `held`.
3. Authorized finance reviewer approves or rejects it.
4. A payout transaction is initiated through the approved channel.
5. Transfer reference and evidence are recorded.
6. Finance verifies the external transfer and reconciliation result.
7. Only then does the payout request become `paid` and ledger value move from `held` to `paid_out`.
8. A failed transfer is not paid; the hold is released only after failure is verified.

D-03 and D-05 determine the provider/channel, verification method, hold period, minimum threshold and retry rules.

## 8. Reconciliation boundaries

Daily reconciliation must compare:

- master order amount and payment attempts;
- verified payment transactions and provider settlement statements;
- vendor suborder allocation totals against master order totals;
- commission entries against the purchase-time rule snapshot;
- vendor ledger balances by currency and bucket;
- payout transactions against external transfer evidence;
- refund records against provider/COD repayment and ledger reversals.

A reconciliation mismatch creates an exception record/task; it must not be silently corrected by editing historical rows.

## 9. Financial decisions blocking sign-off

| PRD decision | Required owner/finance answer |
|---|---|
| D-02 | Legal merchant of record; who receives buyer funds and issues invoices? |
| D-03 | Which gateway/COD providers and what transaction/settlement evidence are available? |
| D-04 | Commission rate/basis, rule precedence, discount funding, tax/shipping/payment fee inclusion |
| D-05 | Hold period, payout frequency/minimum, negative balance and chargeback liability |
| D-06 | Shipping fee ownership, courier account and COD cash collector/remittance |
| D-07 | Tax/VAT responsibility, invoice treatment, currency and rounding rules |
| D-08 | Refund eligibility, partial refund calculation, restock condition and dispute resolution |

Until these are approved, all calculations in this document remain illustrative and must not be used as production configuration.
