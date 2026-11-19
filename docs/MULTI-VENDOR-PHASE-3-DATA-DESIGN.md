# ShopPilot - Phase 3 Data Design

**Phase:** 3 - Data Design  
**Authority:** `Multi_Vendor_Ecommerce_Requirements_PRD_BN.pdf` v1.0  
**Project baseline:** Latest uploaded `shoppilot(3).zip`  
**Status:** Design package prepared for review; financial sign-off is pending owner decisions.

## 1. Phase 3 deliverables

| PRD deliverable | Document |
|---|---|
| Architecture diagram and decision records | `MULTI-VENDOR-ARCHITECTURE-AND-ADRS.md` |
| Logical relationships / ERD | `MULTI-VENDOR-ERD.md` |
| Final target table specifications, keys and constraints | `MULTI-VENDOR-TABLE-SPECIFICATIONS.md` |
| Vendor/order/payment/shipment/refund/payout state transitions | `MULTI-VENDOR-STATE-MACHINES.md` |
| Commission, vendor ledger, settlement and payout examples | `MULTI-VENDOR-FINANCIAL-DESIGN.md` |

## 2. What is already present in the uploaded codebase

- Laravel 13.34.0 project baseline; PHP requirement `^8.3`.
- Separate `admin` and `web` authentication guards.
- Phase 1 vendor foundation: vendors, stores, vendor membership, documents and status history.
- Nullable `products.vendor_id` and separate product moderation status.
- Phase 2 attributes, values, product variants, variant attribute pivot, inventory balances and inventory movements.
- Product type and order-item variant snapshot fields.
- Existing customer-facing `orders` and `order_items` are still a single-order structure; `vendor_orders` does not exist.
- Existing inventory balance is variant-only; simple products still use `products.stock_quantity`.
- Existing manual payment submission remains; provider payment attempts, webhook inbox, commission ledger and payouts do not yet exist.

## 3. Phase 3 design decisions

1. Preserve the current Laravel modular monolith and existing guards.
2. Vendor ownership is verified through active `vendor_users` membership plus resource ownership.
3. Keep `orders` as the customer-facing master order and introduce `vendor_orders` as seller fulfilment records.
4. New marketplace order items link to one vendor suborder and retain immutable product/variant/vendor/price snapshots.
5. Stock reservation needs one consistent balance model for both simple products and variants; current variant-only inventory requires a staged extension.
6. Payment attempts, provider transactions and webhook inbox records are separate from existing manual payment submissions.
7. Vendor earnings and payouts use an append-only financial subledger; payment collection and seller transfer remain separate.
8. Legacy products/orders are not auto-assigned, split or rewritten.
9. No final migrations are included in Phase 3. Policy-dependent migrations must wait for owner/finance decisions.

## 4. Important unresolved decisions

The PRD marks D-01 through D-12 as `[CONFIRM]`. The following block final finance/logistics schema sign-off:

- D-02: legal merchant of record and payment collector.
- D-03: gateway/COD provider and supported payout capability.
- D-04: commission formula, basis, precedence, discount and fee treatment.
- D-05: hold period, payout frequency/minimum and chargeback liability.
- D-06: courier account, shipping allocation and COD collector/remittance.
- D-07: tax/VAT, invoice, currency and rounding policy.
- D-08: cancellation/return windows, restock and dispute rules.

D-01, D-09, D-10, D-11 and D-12 also affect catalog ownership, onboarding, hosting, tracking, guest checkout, publication and staff permissions.

## 5. Phase 3 exit checklist

- [x] Current Phase 1/2 database implementation inspected.
- [x] Logical architecture and ADRs documented.
- [x] Current and target ERD documented.
- [x] Target table specifications and key constraints documented.
- [x] State machines documented with current vs proposed states separated.
- [x] Illustrative financial examples documented without treating them as policy.
- [ ] Owner confirms D-01 through D-12.
- [ ] Finance/operations reviewer signs off commission, tax, shipping, refund, settlement and payout rules.
- [ ] Technical lead approves the final physical schema and migration plan.

**Phase 3 status:** Documentation draft is complete. The PRD exit criterion, financial design sign-off, is not complete until the open decisions are approved.

## 6. Next PRD phase

After the owner reviews this design and confirms the blocking decisions, proceed to **Phase 4 - UI & Structure** (routes, folder conventions, layouts and dashboard navigation), then Phase 5 implementation. Do not skip directly to final finance migrations while D-02 through D-08 remain unresolved.
