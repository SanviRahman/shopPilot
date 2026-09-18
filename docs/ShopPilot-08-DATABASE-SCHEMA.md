# ShopPilot E-commerce — Database Schema
# শপপাইলট ই-কমার্স — ডেটাবেজ স্কিমা

> **Document:** Physical Database Schema / ফিজিক্যাল ডেটাবেজ স্কিমা  
> **Project:** ShopPilot E-commerce  
> **Source Documents:**  
> `01-PROJECT-OVERVIEW-UPDATED.md` v1.1  
> `ShopPilot-02-PRD.md` v1.0  
> `ShopPilot-03-FEATURES.md` v1.0  
> `ShopPilot-04-USER-ROLES-AND-PERMISSIONS.md` v1.0  
> `ShopPilot-05-BUSINESS-RULES.md` v1.0  
> `ShopPilot-06-ARCHITECTURE.md` v1.0  
> `ShopPilot-07-DATABASE-ERD.md` v1.0  
> **Database:** MySQL  
> **ORM / Migration:** Laravel Eloquent + Laravel Schema Builder  
> **Architecture:** Service-Oriented Modular Laravel Monolith  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Cart:** Laravel Session — no P0 `carts` / `cart_items` tables  
> **Payment:** Manual bKash / Nagad / Rocket Submission  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Implementation-Ready P0 Physical Schema Definition  
> **Next Document:** `09-APPLICATION-FLOW.md`

---

# Table of Contents

1. Document Purpose  
2. Schema Authority & Precedence  
3. P0 Schema Scope  
4. Physical Schema Conventions  
5. Data-Type Conventions  
6. Money Convention  
7. Status Storage Convention  
8. Timestamp Convention  
9. SoftDelete Convention  
10. Foreign-Key Convention  
11. Delete-Action Convention  
12. Indexing Convention  
13. Schema Decision Log  
14. P0 Table Inventory  
15. `users` Table  
16. `categories` Table  
17. `products` Table  
18. `coupons` Table  
19. `payment_methods` Table  
20. `orders` Table  
21. `order_items` Table  
22. `order_histories` Table  
23. `payment_submissions` Table  
24. Order ↔ Coupon Persistence Decision  
25. Buyer Snapshot Columns  
26. Product Snapshot Columns  
27. Payment Snapshot / State Boundary  
28. Order State Columns  
29. Nullable FK Matrix  
30. Foreign-Key Action Matrix  
31. Unique Constraint Matrix  
32. Index Matrix  
33. Composite Index Guidance  
34. Status Value Catalogue  
35. Laravel Enum Mapping  
36. Laravel Model Cast Guidance  
37. Spatie Permission Package Tables  
38. Spatie Media Library Table  
39. Framework Tables  
40. Session Cart — No Database Tables  
41. Optional P1 Tables  
42. No `customers` Table  
43. No Inventory Ledger Tables  
44. No Real Gateway Tables  
45. No Settings Table in P0  
46. Checkout Transaction Write Set  
47. Stock Mutation Schema Rule  
48. Historical Preservation Rules  
49. Guest Checkout Schema Behavior  
50. Logged-in Customer Schema Behavior  
51. Agent Assignment Schema Behavior  
52. Payment Verification Schema Behavior  
53. Order History Schema Behavior  
54. Coupon Schema Behavior  
55. SoftDelete / Restore Behavior  
56. Hard Delete Protection  
57. Migration Dependency Order  
58. Migration File Plan  
59. Laravel Migration Blueprint — Users  
60. Laravel Migration Blueprint — Categories  
61. Laravel Migration Blueprint — Products  
62. Laravel Migration Blueprint — Coupons  
63. Laravel Migration Blueprint — Payment Methods  
64. Laravel Migration Blueprint — Orders  
65. Laravel Migration Blueprint — Order Items  
66. Laravel Migration Blueprint — Order Histories  
67. Laravel Migration Blueprint — Payment Submissions  
68. Package Migration Strategy  
69. Seeder Data Requirements  
70. Factory / Testing Data Guidance  
71. Critical Schema Test Cases  
72. Remaining Business TBDs — No Schema Automation  
73. Schema Anti-Patterns  
74. Schema Review Checklist  
75. Definition of Schema Complete  
76. Final Schema Summary  
77. Next Documentation

---

# 1. Document Purpose

This document converts the approved logical ERD into an **implementation-ready Laravel + MySQL physical schema**.

এই file-এর কাজ হলো:

- exact P0 tables define করা
- column names define করা
- practical MySQL/Laravel data types define করা
- nullable/default behavior define করা
- unique constraints define করা
- indexes define করা
- foreign keys এবং hard-delete actions define করা
- SoftDelete columns define করা
- Guest Checkout physical persistence define করা
- Order buyer/product snapshots define করা
- Order → PaymentSubmission P0 `hasOne` decision implement করা
- Coupon-to-Order persistence strategy finalize করা
- Spatie package tables custom rewrite না করা
- migration creation order define করা
- later implementation-এর জন্য Laravel migration blueprints দেওয়া

This document must not introduce new P0 business modules.

---

# 2. Schema Authority & Precedence

For database implementation, use this precedence:

```text
05-BUSINESS-RULES.md
        ↓
06-ARCHITECTURE.md
        ↓
07-DATABASE-ERD.md
        ↓
08-DATABASE-SCHEMA.md
        ↓
Laravel migrations
```

Earlier Project Overview / PRD / Features / Permission docs remain requirements sources.

If this file conflicts with an approved business rule:

```text
Business Rule wins.
```

If a physical decision is not defined by earlier documents, this file may make an explicit **SCHEMA-DEC** decision.

It must not make that decision silently.

---

# 3. P0 Schema Scope

Application-owned P0 tables:

```text
users
categories
products
coupons
payment_methods
orders
order_items
order_histories
payment_submissions
```

Package-managed:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
media
```

Framework / configuration-dependent:

```text
sessions
password_reset_tokens
notifications
```

Optional P1:

```text
addresses
activity_logs
```

---

# 4. Physical Schema Conventions

Recommended P0 database assumptions:

```text
Database Engine: InnoDB
Character Set: utf8mb4
Default Collation: utf8mb4_unicode_ci
Primary Key: BIGINT UNSIGNED AUTO_INCREMENT
Foreign Keys: BIGINT UNSIGNED
Money: DECIMAL(12,2)
Boolean: BOOLEAN / TINYINT(1)
Status Storage: VARCHAR + PHP Enum cast
Created/Updated: Laravel timestamps
Soft Delete: deleted_at nullable timestamp
```

Why:

- Laravel-native
- MySQL-friendly
- simple for a 13-day project
- avoids unnecessary database-specific complexity
- works cleanly with Eloquent relationships

---

# 5. Data-Type Conventions

## IDs

Use:

```text
BIGINT UNSIGNED
```

Laravel:

```php
$table->id();
$table->foreignId(...);
```

---

## Short Names

Typical:

```text
VARCHAR(120–180)
```

---

## Slugs

Use:

```text
VARCHAR(200)
```

---

## Email

Use:

```text
VARCHAR(191)
```

---

## Phone

Use:

```text
VARCHAR(30)
```

Reason:

Phone is an identifier/contact string, not arithmetic data.

---

## SKU

Use:

```text
VARCHAR(100)
```

---

## Transaction ID

Use:

```text
VARCHAR(100)
```

Do not use numeric type.

---

## Description / Notes

Use:

```text
TEXT
LONGTEXT where long product description is expected
```

---

# 6. Money Convention

Use:

```text
DECIMAL(12,2)
```

for:

```text
regular_price
sale_price
discount_value
minimum_order_amount
subtotal
discount
shipping
grand_total
unit_price
line_total
payment amount
```

Never use:

```text
FLOAT
DOUBLE
```

for authoritative monetary calculations.

Application/service logic must calculate money server-side.

---

# 7. Status Storage Convention

P0 uses PHP Enums, but database columns use:

```text
VARCHAR
```

rather than native MySQL `ENUM`.

Reason:

```text
Laravel Enum casts
Easier migration changes
Readable schema
Less DB-vendor coupling
```

Application Enums remain the source of allowed status values.

Database schema does not attempt to implement the state machine.

---

# 8. Timestamp Convention

Normal mutable entities:

```text
created_at
updated_at
```

via:

```php
$table->timestamps();
```

Historical event table `order_histories` needs:

```text
created_at
```

and does not require `updated_at` for P0.

Soft-deleted tables additionally use:

```text
deleted_at
```

---

# 9. SoftDelete Convention

Approved P0 SoftDeletes:

```text
users
categories
products
coupons
orders
payment_methods
```

Use:

```php
$table->softDeletes();
```

Historical tables should **not** use SoftDeletes:

```text
order_items
order_histories
payment_submissions
```

These records should remain as historical evidence.

---

# 10. Foreign-Key Convention

Use real MySQL foreign keys for core P0 relations.

Preferred Laravel style:

```php
$table->foreignId('...')->constrained(...);
```

Foreign-key behavior must preserve historical Order data.

Do not rely only on application-level IDs without constraints for the core schema.

---

# 11. Delete-Action Convention

Because core master records use SoftDelete, normal application deletion does not fire hard-delete FK actions.

For the rare hard-delete case, schema uses:

```text
SET NULL
```

when historical child data may remain valid without the parent identity.

Use:

```text
RESTRICT
```

when deleting the parent would break historical meaning.

Core policy:

```text
Never cascade-delete historical Order data.
```

---

# 12. Indexing Convention

Create indexes for:

```text
Foreign keys
Unique business identifiers
Common status filters
Customer My Orders
Agent Assigned Orders
Payment verification queue
Coupon expiry lookup
Product storefront filtering
```

Avoid speculative over-indexing.

Every extra index has write/storage cost.

---

# 13. Schema Decision Log

## SCHEMA-DEC-001 — Primary Keys

Use:

```text
BIGINT UNSIGNED AUTO_INCREMENT
```

for all application-owned P0 table PKs.

---

## SCHEMA-DEC-002 — Money Precision

Use:

```text
DECIMAL(12,2)
```

for P0 monetary values.

---

## SCHEMA-DEC-003 — Status Columns

Use:

```text
VARCHAR
```

with Laravel PHP Enum casts.

Do not use MySQL native ENUM for P0.

---

## SCHEMA-DEC-004 — Order → PaymentSubmission

07-ERD finalized:

```text
Order hasOne PaymentSubmission
```

Physical implementation:

```text
payment_submissions.order_id
= NOT NULL
= FK
= UNIQUE
```

Therefore one Order may have:

```text
0..1 PaymentSubmission
```

---

## SCHEMA-DEC-005 — Guest Order Ownership

Use:

```text
orders.user_id NULL
```

Guest Order:

```text
user_id = null
```

Authenticated Customer Order:

```text
user_id = authenticated User ID
```

---

## SCHEMA-DEC-006 — Agent Assignment

Use:

```text
orders.assigned_agent_id NULL
```

FK target:

```text
users.id
```

Application Service validates that the selected User has Agent role/context.

---

## SCHEMA-DEC-007 — Coupon Persistence on Order

07-ERD intentionally deferred the exact Order/Coupon persistence strategy.

P0 physical schema uses both:

```text
orders.coupon_id nullable
orders.coupon_code nullable snapshot
```

and keeps:

```text
orders.discount
```

as the historical monetary discount snapshot.

Why:

```text
coupon_id
→ current relational trace

coupon_code
→ historical human-readable snapshot

discount
→ historical financial truth
```

This does not add coupon stacking.

Only one nullable Coupon link exists per Order.

---

## SCHEMA-DEC-008 — Coupon Code Uniqueness

P0 uses:

```text
coupons.code UNIQUE
```

Database default collation:

```text
utf8mb4_unicode_ci
```

therefore normal MySQL uniqueness is case-insensitive under this schema convention.

Recommended application behavior:

```text
trim coupon input
normalize consistently before validation
```

This explicitly resolves the previously undefined case-sensitivity behavior for the P0 physical schema.

---

## SCHEMA-DEC-009 — Transaction ID Uniqueness

Earlier documents explicitly leave duplicate Transaction ID policy undefined.

Therefore P0 schema uses:

```text
transaction_id INDEX
```

but **NOT**:

```text
UNIQUE(transaction_id)
```

Do not silently reject duplicates at database level until the business rule is approved.

---

## SCHEMA-DEC-010 — Sale Price Storage

Store:

```text
sale_price NULL
```

No sale schedule columns are added.

Exact sale-price activation rule remains a business/application TBD.

Schema only stores the optional value.

---

## SCHEMA-DEC-011 — Shipping Storage

Store:

```text
shipping DECIMAL(12,2) DEFAULT 0.00
```

Exact shipping calculation remains TBD.

No shipping-engine tables are introduced.

---

## SCHEMA-DEC-012 — No Database Triggers

P0 uses no DB triggers for:

```text
Stock deduction
Stock restore
Order history
Payment synchronization
Status transition
Coupon expiry
```

Those concerns remain in Services / Rules / Console where approved.

---

# 14. P0 Table Inventory

| Table | Ownership | SoftDelete | Main Role |
|---|---|---:|---|
| `users` | Application | Yes | Authenticated accounts |
| `categories` | Application | Yes | Product grouping |
| `products` | Application | Yes | Catalog + stock |
| `coupons` | Application | Yes | Discount definitions |
| `payment_methods` | Application | Yes | Manual MFS config |
| `orders` | Application | Yes | Purchase aggregate |
| `order_items` | Application | No | Historical line snapshots |
| `order_histories` | Application | No | Workflow history |
| `payment_submissions` | Application | No | Manual payment submission |
| Spatie RBAC tables | Package | Package | Roles / permissions |
| `media` | Package | Package | Media Library |
| framework tables | Framework | Varies | Laravel infrastructure |

---

# 15. `users` Table

## Purpose

Authenticated:

```text
Admin
Manager
Agent
Customer
```

Guest does not require a User record.

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | Laravel `id()` |
| `name` | VARCHAR(120) | No | — | — | Display/account name |
| `email` | VARCHAR(191) | No | — | UNIQUE | Login identity |
| `email_verified_at` | TIMESTAMP | Yes | NULL | — | P1-compatible |
| `password` | VARCHAR(255) | No | — | — | Hashed |
| `remember_token` | VARCHAR(100) | Yes | NULL | — | Laravel auth |
| `created_at` | TIMESTAMP | Yes | NULL | — | Laravel timestamps |
| `updated_at` | TIMESTAMP | Yes | NULL | — | Laravel timestamps |
| `deleted_at` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

## Not Added

P0 does not require dedicated:

```text
role column
customer_type column
agent flag
```

Role/permission comes from Spatie Permission.

---

# 16. `categories` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `name` | VARCHAR(150) | No | — | — | |
| `slug` | VARCHAR(200) | No | — | UNIQUE | Route/storefront identifier |
| `description` | TEXT | Yes | NULL | — | |
| `status` | VARCHAR(20) | No | `active` | INDEX | Enum-cast |
| `sort_order` | INT UNSIGNED | No | `0` | INDEX | Display order |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | SoftDelete |

## Media

Category image is not stored as a string column.

Use Media Library collection:

```text
category_image
```

---

# 17. `products` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `category_id` | BIGINT UNSIGNED | No | — | FK + INDEX | → `categories.id` |
| `name` | VARCHAR(180) | No | — | INDEX optional | Product name |
| `slug` | VARCHAR(200) | No | — | UNIQUE | |
| `sku` | VARCHAR(100) | No | — | UNIQUE | Business identifier |
| `short_description` | VARCHAR(500) | Yes | NULL | — | |
| `description` | LONGTEXT | Yes | NULL | — | |
| `regular_price` | DECIMAL(12,2) | No | — | — | Server-authoritative |
| `sale_price` | DECIMAL(12,2) | Yes | NULL | — | Activation rule remains TBD |
| `stock_quantity` | INT UNSIGNED | No | `0` | INDEX | P0 stock source |
| `status` | VARCHAR(20) | No | `active` | INDEX | active/inactive |
| `featured` | BOOLEAN | No | `false` | INDEX | Home/storefront |
| `created_at` | TIMESTAMP | Yes | NULL | INDEX optional | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

## FK

```text
products.category_id
→ categories.id
ON DELETE RESTRICT
ON UPDATE CASCADE
```

## Recommended Composite Index

```text
(category_id, status)
```

Optional:

```text
(status, featured)
```

for storefront queries.

## Media

Use:

```text
product_thumbnail
product_gallery
```

through Spatie Media Library.

---

# 18. `coupons` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `code` | VARCHAR(80) | No | — | UNIQUE | Case-insensitive under DB collation |
| `discount_type` | VARCHAR(20) | No | — | INDEX optional | fixed/percentage |
| `discount_value` | DECIMAL(12,2) | No | — | — | |
| `minimum_order_amount` | DECIMAL(12,2) | No | `0.00` | — | |
| `start_date` | DATETIME | No | — | INDEX | |
| `end_date` | DATETIME | No | — | INDEX | Expiry |
| `status` | VARCHAR(20) | No | `active` | INDEX | active/inactive/expired |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | SoftDelete |

## Recommended Composite Index

```text
(status, end_date)
```

Useful for:

```text
coupons:expire
```

---

# 19. `payment_methods` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `name` | VARCHAR(100) | No | — | — | bKash/Nagad/Rocket display name |
| `code` | VARCHAR(50) | No | — | UNIQUE | `bkash`, `nagad`, `rocket` |
| `account_number` | VARCHAR(50) | No | — | — | String, not numeric |
| `account_type` | VARCHAR(50) | No | — | — | e.g. configured account type |
| `instruction` | TEXT | No | — | — | Checkout instruction |
| `status` | VARCHAR(20) | No | `active` | INDEX | active/inactive |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | SoftDelete |

No provider API credential columns are required.

---

# 20. `orders` Table

## Purpose

Primary purchase aggregate for Guest and authenticated Customer.

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `order_number` | VARCHAR(40) | No | — | UNIQUE | e.g. `ORD-2026-000001` |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Auth Customer; null for Guest |
| `assigned_agent_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | → `users.id` |
| `coupon_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | One optional Coupon |
| `coupon_code` | VARCHAR(80) | Yes | NULL | INDEX optional | Historical snapshot |
| `buyer_name` | VARCHAR(150) | No | — | — | Snapshot |
| `buyer_phone` | VARCHAR(30) | No | — | INDEX optional | Snapshot |
| `buyer_email` | VARCHAR(191) | No | — | INDEX optional | Snapshot |
| `shipping_address` | TEXT | No | — | — | Snapshot |
| `city_or_area` | VARCHAR(150) | No | — | — | Snapshot |
| `subtotal` | DECIMAL(12,2) | No | — | — | Server-calculated |
| `discount` | DECIMAL(12,2) | No | `0.00` | — | Historical discount |
| `shipping` | DECIMAL(12,2) | No | `0.00` | — | Calculation rule TBD |
| `grand_total` | DECIMAL(12,2) | No | — | — | Server-calculated |
| `payment_status` | VARCHAR(20) | No | `unpaid` | INDEX | Enum |
| `order_status` | VARCHAR(20) | No | `pending` | INDEX | Enum |
| `customer_note` | TEXT | Yes | NULL | — | Customer-visible own input |
| `internal_note` | TEXT | Yes | NULL | — | Staff-only |
| `created_at` | TIMESTAMP | Yes | NULL | INDEX | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

## FK — Customer

```text
orders.user_id
→ users.id
ON DELETE SET NULL
ON UPDATE CASCADE
```

This preserves Order history even if a User is ever hard-deleted.

---

## FK — Assigned Agent

```text
orders.assigned_agent_id
→ users.id
ON DELETE SET NULL
ON UPDATE CASCADE
```

---

## FK — Coupon

```text
orders.coupon_id
→ coupons.id
ON DELETE SET NULL
ON UPDATE CASCADE
```

`coupon_code` + `discount` preserve Order history if Coupon identity becomes unavailable.

---

## Recommended Indexes

```text
UNIQUE(order_number)

INDEX(user_id)
INDEX(assigned_agent_id)
INDEX(coupon_id)
INDEX(order_status)
INDEX(payment_status)
INDEX(created_at)

INDEX(assigned_agent_id, order_status)
INDEX(user_id, created_at)
```

---

# 21. `order_items` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `order_id` | BIGINT UNSIGNED | No | — | FK + INDEX | |
| `product_id` | BIGINT UNSIGNED | No | — | FK + INDEX | Current Product reference |
| `product_name` | VARCHAR(180) | No | — | — | Snapshot |
| `sku` | VARCHAR(100) | No | — | — | Snapshot |
| `unit_price` | DECIMAL(12,2) | No | — | — | Snapshot |
| `quantity` | INT UNSIGNED | No | — | — | |
| `line_total` | DECIMAL(12,2) | No | — | — | Snapshot/calculated |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |

## FKs

```text
order_items.order_id
→ orders.id
ON DELETE RESTRICT

order_items.product_id
→ products.id
ON DELETE RESTRICT
```

Reason:

Historical Order Items must not disappear through cascade deletion.

---

# 22. `order_histories` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `order_id` | BIGINT UNSIGNED | No | — | FK + INDEX | |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Actor; nullable for Guest/System context |
| `from_status` | VARCHAR(20) | Yes | NULL | — | Can be null for initial/non-status event |
| `to_status` | VARCHAR(20) | Yes | NULL | INDEX optional | |
| `note` | TEXT | Yes | NULL | — | Event/trace text |
| `created_at` | TIMESTAMP | No | current timestamp | INDEX | Immutable event time |

No:

```text
updated_at
deleted_at
```

for P0 history.

## FKs

```text
order_histories.order_id
→ orders.id
ON DELETE RESTRICT

order_histories.user_id
→ users.id
ON DELETE SET NULL
```

## Recommended Composite Index

```text
(order_id, created_at)
```

---

# 23. `payment_submissions` Table

## Physical Schema

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `order_id` | BIGINT UNSIGNED | No | — | FK + UNIQUE | P0 hasOne |
| `payment_method_id` | BIGINT UNSIGNED | No | — | FK + INDEX | |
| `transaction_id` | VARCHAR(100) | No | — | INDEX, **not UNIQUE** | Uniqueness rule TBD |
| `amount` | DECIMAL(12,2) | No | — | — | Submitted payment amount |
| `status` | VARCHAR(20) | No | `submitted` | INDEX | submitted/verified/rejected |
| `verified_by` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Authorized staff User |
| `verified_at` | TIMESTAMP | Yes | NULL | — | |
| `rejection_note` | TEXT | Yes | NULL | — | |
| `created_at` | TIMESTAMP | Yes | NULL | INDEX | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |

## FKs

```text
payment_submissions.order_id
→ orders.id
ON DELETE RESTRICT

payment_submissions.payment_method_id
→ payment_methods.id
ON DELETE RESTRICT

payment_submissions.verified_by
→ users.id
ON DELETE SET NULL
```

## Critical Constraint

```text
UNIQUE(order_id)
```

implements:

```text
Order hasOne PaymentSubmission
```

for P0.

---

# 24. Order ↔ Coupon Persistence Decision

Physical P0 design:

```text
orders.coupon_id       nullable FK
orders.coupon_code     nullable snapshot
orders.discount        non-null monetary snapshot
```

Example:

```text
Coupon:
id = 8
code = SAVE10

Order:
coupon_id = 8
coupon_code = SAVE10
discount = 250.00
```

If Coupon is later soft-deleted:

```text
Order remains historically understandable.
```

If a rare hard delete occurs:

```text
coupon_id → NULL
coupon_code remains
discount remains
```

---

# 25. Buyer Snapshot Columns

Every Order stores:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

These columns are:

```text
NOT NULL
```

for both:

```text
Guest
Authenticated Customer
```

Do not render historical Order delivery information directly from current User profile.

---

# 26. Product Snapshot Columns

Every OrderItem stores:

```text
product_name
sku
unit_price
quantity
line_total
```

Historical display should use snapshots for purchase-time facts.

`product_id` remains useful for current-reference/admin navigation.

---

# 27. Payment Snapshot / State Boundary

Order contains:

```text
payment_status
```

PaymentSubmission contains:

```text
status
payment_method_id
transaction_id
amount
verified_by
verified_at
rejection_note
```

Important:

```text
Order payment_status
```

is the aggregate-level payment state.

```text
PaymentSubmission.status
```

is submission verification state.

Service logic must keep relevant state changes consistent.

Do not use a DB trigger to synchronize them.

---

# 28. Order State Columns

Order uses:

```text
order_status VARCHAR(20)
payment_status VARCHAR(20)
```

They are intentionally separate.

Do not combine into:

```text
status
```

because Order fulfillment and Payment verification are separate workflows.

---

# 29. Nullable FK Matrix

| Column | Nullable | Reason |
|---|---:|---|
| `products.category_id` | No | Product requires Category |
| `orders.user_id` | Yes | Guest Checkout |
| `orders.assigned_agent_id` | Yes | New Order may be unassigned |
| `orders.coupon_id` | Yes | Coupon is optional |
| `order_items.order_id` | No | Item requires Order |
| `order_items.product_id` | No | P0 Product reference |
| `order_histories.order_id` | No | History requires Order |
| `order_histories.user_id` | Yes | Guest/System actor context |
| `payment_submissions.order_id` | No | Submission requires Order |
| `payment_submissions.payment_method_id` | No | Submission requires selected method |
| `payment_submissions.verified_by` | Yes | Pending submission has no verifier |

---

# 30. Foreign-Key Action Matrix

| Child FK | Parent | On Delete | Reason |
|---|---|---|---|
| `products.category_id` | `categories.id` | RESTRICT | Prevent broken Product link |
| `orders.user_id` | `users.id` | SET NULL | Preserve Order |
| `orders.assigned_agent_id` | `users.id` | SET NULL | Preserve Order |
| `orders.coupon_id` | `coupons.id` | SET NULL | Snapshot remains |
| `order_items.order_id` | `orders.id` | RESTRICT | Preserve historical item |
| `order_items.product_id` | `products.id` | RESTRICT | Preserve reference |
| `order_histories.order_id` | `orders.id` | RESTRICT | Preserve history |
| `order_histories.user_id` | `users.id` | SET NULL | Preserve history |
| `payment_submissions.order_id` | `orders.id` | RESTRICT | Preserve payment evidence |
| `payment_submissions.payment_method_id` | `payment_methods.id` | RESTRICT | Preserve method reference |
| `payment_submissions.verified_by` | `users.id` | SET NULL | Preserve payment evidence |

All core FK updates:

```text
ON UPDATE CASCADE
```

may be used consistently, though primary IDs are not expected to change.

---

# 31. Unique Constraint Matrix

| Table | Column(s) | Unique |
|---|---|---:|
| `users` | `email` | Yes |
| `categories` | `slug` | Yes |
| `products` | `slug` | Yes |
| `products` | `sku` | Yes |
| `coupons` | `code` | Yes |
| `payment_methods` | `code` | Yes |
| `orders` | `order_number` | Yes |
| `payment_submissions` | `order_id` | Yes |
| `payment_submissions` | `transaction_id` | **No — TBD** |

---

# 32. Index Matrix

## Users

```text
UNIQUE(email)
```

---

## Categories

```text
UNIQUE(slug)
INDEX(status)
INDEX(sort_order)
```

---

## Products

```text
UNIQUE(slug)
UNIQUE(sku)
INDEX(category_id)
INDEX(status)
INDEX(featured)
INDEX(stock_quantity)
INDEX(category_id, status)
INDEX(status, featured)
```

---

## Coupons

```text
UNIQUE(code)
INDEX(status)
INDEX(start_date)
INDEX(end_date)
INDEX(status, end_date)
```

---

## Payment Methods

```text
UNIQUE(code)
INDEX(status)
```

---

## Orders

```text
UNIQUE(order_number)
INDEX(user_id)
INDEX(assigned_agent_id)
INDEX(coupon_id)
INDEX(order_status)
INDEX(payment_status)
INDEX(created_at)
INDEX(user_id, created_at)
INDEX(assigned_agent_id, order_status)
```

---

## Order Items

```text
INDEX(order_id)
INDEX(product_id)
```

---

## Order Histories

```text
INDEX(order_id)
INDEX(user_id)
INDEX(created_at)
INDEX(order_id, created_at)
```

---

## Payment Submissions

```text
UNIQUE(order_id)
INDEX(payment_method_id)
INDEX(transaction_id)
INDEX(status)
INDEX(verified_by)
INDEX(created_at)
```

---

# 33. Composite Index Guidance

Use composite indexes only for known P0 query patterns.

Approved/useful:

```text
products(category_id, status)
products(status, featured)

orders(user_id, created_at)
orders(assigned_agent_id, order_status)

coupons(status, end_date)

order_histories(order_id, created_at)
```

Do not add dozens of speculative indexes.

---

# 34. Status Value Catalogue

## Product

```text
active
inactive
```

---

## Order

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

---

## Payment

```text
unpaid
submitted
verified
rejected
```

---

## Coupon

```text
active
inactive
expired
```

---

## Payment Method

```text
active
inactive
```

---

# 35. Laravel Enum Mapping

Recommended PHP Enums:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

Potential casts:

```php
protected function casts(): array
{
    return [
        'status' => ProductStatus::class,
    ];
}
```

For Order:

```php
protected function casts(): array
{
    return [
        'order_status'   => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
    ];
}
```

Enums define valid values.

Services/Rules define valid transitions.

---

# 36. Laravel Model Cast Guidance

## Product

```text
regular_price → decimal:2
sale_price → decimal:2
stock_quantity → integer
featured → boolean
status → ProductStatus
```

## Coupon

```text
discount_value → decimal:2
minimum_order_amount → decimal:2
start_date → datetime
end_date → datetime
status → CouponStatus
```

## Order

```text
subtotal → decimal:2
discount → decimal:2
shipping → decimal:2
grand_total → decimal:2
order_status → OrderStatus
payment_status → PaymentStatus
```

## PaymentMethod

```text
status → PaymentMethodStatus
```

## PaymentSubmission

```text
amount → decimal:2
status → PaymentStatus
verified_at → datetime
```

---

# 37. Spatie Permission Package Tables

Use Spatie Laravel Permission published migration.

Expected package-managed concepts:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

Do not manually redesign package table column definitions in ShopPilot migrations.

Application role values:

```text
Admin
Manager
Agent
Customer
```

Guest:

```text
No role row required.
```

Package migration remains authoritative for its exact physical schema.

---

# 38. Spatie Media Library Table

Use Spatie Media Library published migration.

Package-managed:

```text
media
```

Collections used by ShopPilot:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional
```

Do not add custom:

```text
product_images
category_images
```

for P0.

Package migration remains authoritative for exact Media table fields.

---

# 39. Framework Tables

Depending on Laravel configuration:

```text
sessions
password_reset_tokens
notifications
```

Use Laravel-generated/published framework migrations.

Do not hand-design a second competing version.

---

# 40. Session Cart — No Database Tables

P0 Cart remains:

```text
Laravel Session
```

Therefore do not create:

```text
carts
cart_items
```

Persistent purchase data begins after successful Checkout transaction:

```text
orders
order_items
payment_submissions
order_histories
```

---

# 41. Optional P1 Tables

Not required for core MVP:

```text
addresses
activity_logs
```

Do not create them unless P1 is intentionally started.

---

# 42. No `customers` Table

Authenticated Customer:

```text
users row
+
Customer Spatie role/context
```

Guest:

```text
no User row required
```

Order-owned buyer snapshots handle historical purchase identity.

Therefore:

```text
No P0 customers table
```

---

# 43. No Inventory Ledger Tables

P0 stock source:

```text
products.stock_quantity
```

Do not add:

```text
inventory_movements
stock_ledgers
warehouses
warehouse_stocks
```

---

# 44. No Real Gateway Tables

Manual MFS P0 does not need:

```text
payment_intents
gateway_transactions
webhook_events
provider_callbacks
refund_transactions
```

Use:

```text
payment_methods
payment_submissions
```

---

# 45. No Settings Table in P0

Permission docs contain:

```text
settings.view
settings.update
```

but approved core entity list does not define a `settings` entity.

Therefore:

```text
No P0 settings table is introduced by this schema.
```

If settings persistence becomes necessary later, define it explicitly rather than silently adding it here.

---

# 46. Checkout Transaction Write Set

Critical writes:

```text
BEGIN TRANSACTION

1. INSERT orders
2. INSERT order_items
3. INSERT payment_submissions
4. UPDATE products.stock_quantity
5. INSERT initial order_histories

COMMIT
```

On error:

```text
ROLLBACK
```

Do not clear successful Cart state until transaction commits.

---

# 47. Stock Mutation Schema Rule

Stock lives in:

```text
products.stock_quantity
```

Checkout must:

```text
Reload Product
Revalidate stock
Deduct inside transaction
```

Database schema does not auto-deduct stock.

No DB trigger.

Stock restoration after cancellation remains undefined.

---

# 48. Historical Preservation Rules

Historical data:

```text
Order buyer snapshot
Order coupon snapshot
Order financial totals
OrderItem product snapshot
OrderHistory
PaymentSubmission
```

must remain stable even if:

```text
User profile changes
Product name changes
Product price changes
Coupon changes
Payment Method is soft-deleted
```

---

# 49. Guest Checkout Schema Behavior

Guest successful Order:

```text
orders.user_id = NULL
orders.buyer_name = submitted name
orders.buyer_phone = submitted phone
orders.buyer_email = submitted email
orders.shipping_address = submitted address
orders.city_or_area = submitted city/area
```

Then:

```text
OrderItems created
PaymentSubmission created
Initial history created
```

No Guest User row is required.

---

# 50. Logged-in Customer Schema Behavior

Authenticated Order:

```text
orders.user_id = auth()->id()
```

But also persist:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

The relation does not replace the snapshot.

---

# 51. Agent Assignment Schema Behavior

Initially:

```text
assigned_agent_id = NULL
```

Authorized Admin/Manager assignment:

```text
assigned_agent_id = Agent User ID
```

Database FK proves User existence.

Application Service/Policy proves that the User is an eligible Agent.

---

# 52. Payment Verification Schema Behavior

At checkout:

```text
payment_submissions.status = submitted
orders.payment_status = submitted
verified_by = NULL
verified_at = NULL
```

On authorized verify:

```text
payment_submissions.status = verified
payment_submissions.verified_by = staff user id
payment_submissions.verified_at = now()

orders.payment_status = verified
```

On authorized reject:

```text
payment_submissions.status = rejected
payment_submissions.verified_by = staff user id
payment_submissions.verified_at = now()
payment_submissions.rejection_note = ...
orders.payment_status = rejected
```

Do not automatically change `order_status` unless a later approved business rule requires it.

---

# 53. Order History Schema Behavior

Examples written into `order_histories`:

```text
Order Created
Payment Submitted
Payment Verified
Assigned To Agent
Confirmed
Processing
Shipped
Delivered
Cancelled
```

For an Order-status transition:

```text
from_status
to_status
```

should be populated.

For a non-order-status event such as payment verification or assignment:

```text
from_status / to_status may remain null
note carries the trace
```

No generic enterprise audit log is required for P0.

---

# 54. Coupon Schema Behavior

Coupon validation uses:

```text
code
discount_type
discount_value
minimum_order_amount
start_date
end_date
status
```

Order stores:

```text
coupon_id
coupon_code
discount
```

Rules:

```text
One Coupon per Order
No stacking
Expired rejected
Inactive rejected
Server-side calculation
```

---

# 55. SoftDelete / Restore Behavior

SoftDelete tables:

```text
users
categories
products
coupons
orders
payment_methods
```

Normal delete:

```text
UPDATE deleted_at
```

not hard delete.

Historical child rows remain.

Restore permission/business logic is enforced by application authorization.

---

# 56. Hard Delete Protection

P0 does not require force delete.

Therefore migration FK design should make destructive hard deletes difficult.

Examples:

```text
Product with OrderItems
→ RESTRICT hard delete

Order with Items/History/Payment
→ RESTRICT hard delete

PaymentMethod with PaymentSubmission
→ RESTRICT hard delete
```

This is intentional.

---

# 57. Migration Dependency Order

Recommended order:

```text
01 users
02 Spatie Permission package tables
03 categories
04 products
05 coupons
06 payment_methods
07 orders
08 order_items
09 order_histories
10 payment_submissions
11 Media Library package migration
12 optional framework/P1 migrations
```

Package timestamp order may differ when installed.

Requirement:

```text
Parent table must exist before FK child table migration executes.
```

---

# 58. Migration File Plan

Example names:

```text
0001_01_01_000000_create_users_table.php

2026_09_17_000100_create_categories_table.php
2026_09_17_000200_create_products_table.php
2026_09_17_000300_create_coupons_table.php
2026_09_17_000400_create_payment_methods_table.php
2026_09_17_000500_create_orders_table.php
2026_09_17_000600_create_order_items_table.php
2026_09_17_000700_create_order_histories_table.php
2026_09_17_000800_create_payment_submissions_table.php
```

Exact timestamps are not important.

Dependency order is important.

---

# 59. Laravel Migration Blueprint — Users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('name', 120);
    $table->string('email', 191)->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();

    $table->timestamps();
    $table->softDeletes();
});
```

Notes:

```text
No role column.
No guest row requirement.
Spatie handles role/permission relations.
```

---

# 60. Laravel Migration Blueprint — Categories

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();

    $table->string('name', 150);
    $table->string('slug', 200)->unique();
    $table->text('description')->nullable();
    $table->string('status', 20)->default('active')->index();
    $table->unsignedInteger('sort_order')->default(0)->index();

    $table->timestamps();
    $table->softDeletes();
});
```

No image-path column.

Media Library handles:

```text
category_image
```

---

# 61. Laravel Migration Blueprint — Products

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();

    $table->foreignId('category_id')
        ->constrained('categories')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('name', 180);
    $table->string('slug', 200)->unique();
    $table->string('sku', 100)->unique();

    $table->string('short_description', 500)->nullable();
    $table->longText('description')->nullable();

    $table->decimal('regular_price', 12, 2);
    $table->decimal('sale_price', 12, 2)->nullable();

    $table->unsignedInteger('stock_quantity')->default(0)->index();

    $table->string('status', 20)->default('active')->index();
    $table->boolean('featured')->default(false)->index();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['category_id', 'status']);
    $table->index(['status', 'featured']);
});
```

No:

```text
size
color
variant_price
variant_stock
```

P0 columns.

---

# 62. Laravel Migration Blueprint — Coupons

```php
Schema::create('coupons', function (Blueprint $table) {
    $table->id();

    $table->string('code', 80)->unique();
    $table->string('discount_type', 20);
    $table->decimal('discount_value', 12, 2);
    $table->decimal('minimum_order_amount', 12, 2)->default(0);

    $table->dateTime('start_date')->index();
    $table->dateTime('end_date')->index();

    $table->string('status', 20)->default('active')->index();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'end_date']);
});
```

P0 case behavior:

```text
DB collation-based case-insensitive uniqueness.
```

---

# 63. Laravel Migration Blueprint — Payment Methods

```php
Schema::create('payment_methods', function (Blueprint $table) {
    $table->id();

    $table->string('name', 100);
    $table->string('code', 50)->unique();

    $table->string('account_number', 50);
    $table->string('account_type', 50);
    $table->text('instruction');

    $table->string('status', 20)->default('active')->index();

    $table->timestamps();
    $table->softDeletes();
});
```

P0 seeded codes:

```text
bkash
nagad
rocket
```

---

# 64. Laravel Migration Blueprint — Orders

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();

    $table->string('order_number', 40)->unique();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('assigned_agent_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('coupon_id')
        ->nullable()
        ->constrained('coupons')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->string('coupon_code', 80)->nullable();

    $table->string('buyer_name', 150);
    $table->string('buyer_phone', 30);
    $table->string('buyer_email', 191);

    $table->text('shipping_address');
    $table->string('city_or_area', 150);

    $table->decimal('subtotal', 12, 2);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('shipping', 12, 2)->default(0);
    $table->decimal('grand_total', 12, 2);

    $table->string('payment_status', 20)
        ->default('unpaid')
        ->index();

    $table->string('order_status', 20)
        ->default('pending')
        ->index();

    $table->text('customer_note')->nullable();
    $table->text('internal_note')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['user_id', 'created_at']);
    $table->index(['assigned_agent_id', 'order_status']);
    $table->index('coupon_code');
});
```

Important:

Manual MFS Checkout must explicitly set:

```text
payment_status = submitted
```

inside the successful transaction.

Do not rely on `unpaid` default for manual MFS success.

---

# 65. Laravel Migration Blueprint — Order Items

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('product_id')
        ->constrained('products')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('product_name', 180);
    $table->string('sku', 100);

    $table->decimal('unit_price', 12, 2);
    $table->unsignedInteger('quantity');
    $table->decimal('line_total', 12, 2);

    $table->timestamps();
});
```

Do not recalculate historical Item display from current Product price.

---

# 66. Laravel Migration Blueprint — Order Histories

```php
Schema::create('order_histories', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->string('from_status', 20)->nullable();
    $table->string('to_status', 20)->nullable();

    $table->text('note')->nullable();

    $table->timestamp('created_at')->useCurrent();

    $table->index(['order_id', 'created_at']);
});
```

No `updated_at`.

History should be append-oriented.

---

# 67. Laravel Migration Blueprint — Payment Submissions

```php
Schema::create('payment_submissions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->unique()
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('payment_method_id')
        ->constrained('payment_methods')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('transaction_id', 100)->index();
    $table->decimal('amount', 12, 2);

    $table->string('status', 20)
        ->default('submitted')
        ->index();

    $table->foreignId('verified_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->timestamp('verified_at')->nullable();
    $table->text('rejection_note')->nullable();

    $table->timestamps();
});
```

Critical:

```text
transaction_id is indexed but NOT UNIQUE.
```

This preserves the existing TBD duplicate-ID policy.

---

# 68. Package Migration Strategy

Install packages and publish/use their migrations.

## Spatie Permission

Do not duplicate:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

with custom tables.

## Media Library

Do not duplicate:

```text
media
```

with a custom media table.

When package versions change, use the package migration appropriate to the installed version.

---

# 69. Seeder Data Requirements

P0 seeders should create:

## Roles

```text
Admin
Manager
Agent
Customer
```

Do not create:

```text
Guest
```

as a Spatie role.

---

## Permissions

Seed the approved granular permission catalogue.

Examples:

```text
dashboard.view

users.view
users.update

customers.view

staff.view
staff.create
staff.update
staff.delete

roles.view
roles.manage

permissions.view
permissions.manage

categories.view
categories.create
categories.update
categories.delete
categories.restore

products.view
products.create
products.update
products.delete
products.restore

stock.view
stock.update

coupons.view
coupons.create
coupons.update
coupons.delete

orders.view
orders.update
orders.assign
orders.cancel
orders.restore

payments.view
payments.verify
payments.reject

payment-methods.view
payment-methods.manage

reports.view

settings.view
settings.update
```

Do not seed duplicate umbrella aliases unless deliberately approved.

---

## Payment Methods

Recommended initial rows:

```text
bkash
nagad
rocket
```

with safe development/test numbers/instructions.

Production numbers should be configured by authorized staff.

---

# 70. Factory / Testing Data Guidance

Factories should support:

```text
User
Category
Product
Coupon
Order
OrderItem
OrderHistory
PaymentMethod
PaymentSubmission
```

Critical variants:

```text
Guest Order:
user_id = null

Authenticated Order:
user_id = customer id

Unassigned Order:
assigned_agent_id = null

Assigned Order:
assigned_agent_id = agent id

Submitted Payment:
verified_by = null
verified_at = null

Verified Payment:
verified_by = authorized staff id
verified_at != null
```

---

# 71. Critical Schema Test Cases

## Test 1 — Guest Order

Must succeed:

```text
orders.user_id = null
```

with required snapshots.

---

## Test 2 — Authenticated Customer Order

Must persist:

```text
orders.user_id = customer.id
```

plus buyer snapshot.

---

## Test 3 — Invalid Agent FK

Non-existing:

```text
assigned_agent_id
```

must fail FK validation at DB level.

Wrong-role existing User is rejected by application business logic.

---

## Test 4 — Duplicate Order Number

Must fail unique constraint.

---

## Test 5 — Duplicate SKU

Must fail unique constraint.

---

## Test 6 — Duplicate Product Slug

Must fail unique constraint.

---

## Test 7 — Duplicate PaymentSubmission per Order

Second `payment_submissions.order_id` for same Order must fail.

---

## Test 8 — Duplicate Transaction ID

Database may allow it in P0 because uniqueness rule is still TBD.

Application must not falsely treat duplicate acceptance as proof of payment verification.

---

## Test 9 — Hard Delete Product With Historical Item

Should be blocked by FK RESTRICT.

Normal application uses SoftDelete.

---

## Test 10 — Hard Delete Order With History

Should be blocked by historical child FKs.

---

## Test 11 — Hard Delete Customer User

Order may survive with:

```text
user_id = null
```

while snapshots remain.

---

## Test 12 — Hard Delete Agent User

Assigned Order may survive with:

```text
assigned_agent_id = null
```

---

## Test 13 — Soft-Deleted Product

Must remain hidden from normal Storefront through application query scope.

---

## Test 14 — Payment Submission Initial State

Must begin:

```text
submitted
```

not:

```text
verified
```

---

# 72. Remaining Business TBDs — No Schema Automation

These source-level rules remain unresolved and are **not** solved with hidden database automation.

## TBD — Shipping Calculation

Schema only stores:

```text
shipping
```

No shipping engine.

---

## TBD — Stock Restore After Cancellation

No DB trigger.

Do not auto-restore until business rule is approved.

---

## TBD — Rejected Payment → Order Status

No trigger that changes:

```text
order_status
```

when payment becomes rejected.

---

## TBD — Verification Before Fulfillment

No database constraint requires:

```text
payment_status = verified
```

before processing.

---

## TBD — Assignment Timing

Schema allows:

```text
assigned_agent_id nullable
```

and does not enforce payment-before-assignment or assignment-before-payment.

---

## Resolved in 07 — PaymentSubmission Cardinality

P0:

```text
Order hasOne PaymentSubmission
```

implemented by:

```text
UNIQUE(payment_submissions.order_id)
```

---

## TBD — Transaction ID Global Uniqueness

No unique constraint.

---

## Resolved in 08 — Coupon Case Handling

P0 schema uses case-insensitive default collation + unique Coupon code.

---

## TBD — Sale Price Activation

No sale date columns or DB rule.

---

## P1 — Guest Tracking

No P0 tracking token columns.

---

# 73. Schema Anti-Patterns

Do not:

## 73.1 Use FLOAT for Money

Wrong:

```text
FLOAT price
```

Use:

```text
DECIMAL(12,2)
```

---

## 73.2 Put Roles in a `users.role` String

Use Spatie Permission.

---

## 73.3 Make Guest a Database Role

Guest is public actor, not Spatie role.

---

## 73.4 Require `orders.user_id`

It must be nullable for Guest Checkout.

---

## 73.5 Cascade Delete Historical Orders

Do not destroy:

```text
OrderItems
OrderHistories
PaymentSubmissions
```

---

## 73.6 Make Transaction ID Automatically Verified

A Transaction ID string is only submitted information.

---

## 73.7 Add Cart Tables

P0 Cart is Session-based.

---

## 73.8 Add Product Variant Tables

Out of scope.

---

## 73.9 Add Inventory Ledger

Out of scope.

---

## 73.10 Add Gateway Webhook Tables

Out of scope.

---

## 73.11 Duplicate Spatie Package Tables

Use vendor/package migrations.

---

## 73.12 Store Historical Order Name/Price Only Through Product FK

OrderItem snapshots are mandatory.

---

# 74. Schema Review Checklist

- [ ] MySQL / InnoDB assumed.
- [ ] `utf8mb4` used.
- [ ] Application PKs use BIGINT UNSIGNED.
- [ ] Money uses DECIMAL.
- [ ] PHP Enums map to VARCHAR status columns.
- [ ] `users` uses SoftDelete.
- [ ] `categories` uses SoftDelete.
- [ ] `products` uses SoftDelete.
- [ ] `coupons` uses SoftDelete.
- [ ] `orders` uses SoftDelete.
- [ ] `payment_methods` uses SoftDelete.
- [ ] Historical tables have no SoftDelete.
- [ ] Guest Orders allow `user_id = null`.
- [ ] Auth Customer Orders link `user_id`.
- [ ] Every Order stores buyer/shipping snapshot.
- [ ] Agent assignment is nullable.
- [ ] Product has simple `stock_quantity`.
- [ ] Product `slug` unique.
- [ ] Product `sku` unique.
- [ ] Order number unique.
- [ ] Coupon code unique.
- [ ] Payment Method code unique.
- [ ] Order has max one PaymentSubmission in P0.
- [ ] `payment_submissions.order_id` unique.
- [ ] `transaction_id` is not unique.
- [ ] OrderItem stores Product snapshots.
- [ ] OrderHistory is append-oriented.
- [ ] Payment verifier is nullable User.
- [ ] Coupon identity + code + discount history is preserved.
- [ ] No P0 Cart tables.
- [ ] No P0 Customer duplicate table.
- [ ] No variants.
- [ ] No warehouse ledger.
- [ ] No real gateway tables.
- [ ] Spatie tables come from package migrations.
- [ ] Media table comes from package migration.
- [ ] Historical child hard deletes are protected.
- [ ] Remaining business TBDs are not hidden in triggers.

---

# 75. Definition of Schema Complete

Database Schema is complete for P0 when the following are unambiguous:

```text
Table names
Column names
Data types
Nullable behavior
Defaults
Primary keys
Foreign keys
Unique constraints
Indexes
Composite indexes
SoftDelete columns
FK delete actions
Guest ownership behavior
Agent assignment behavior
Buyer snapshots
Product snapshots
Coupon persistence
PaymentSubmission hasOne constraint
Transaction ID non-unique TBD behavior
Status storage
Package table boundaries
Migration dependency order
Critical migration blueprints
```

---

# 76. Final Schema Summary

ShopPilot P0 physical data model is:

```text
users
    │
    ├── orders.user_id nullable
    ├── orders.assigned_agent_id nullable
    ├── order_histories.user_id nullable
    └── payment_submissions.verified_by nullable

categories
    └── products
            └── order_items

coupons
    └── orders.coupon_id nullable
            + coupon_code snapshot
            + discount snapshot

orders
    ├── order_items
    ├── order_histories
    └── payment_submissions 0..1

payment_methods
    └── payment_submissions
```

Critical P0 database invariants:

```text
Guest Order:
orders.user_id may be NULL

Authenticated Order:
orders.user_id references Customer User

Every Order:
buyer/shipping snapshot is stored

Every OrderItem:
product name/SKU/price snapshot is stored

Order → PaymentSubmission:
0..1 through UNIQUE(order_id)

Transaction ID:
indexed but not UNIQUE while duplicate policy remains TBD

Historical children:
never cascade-destroyed

Cart:
Session only

Stock:
products.stock_quantity

Payment:
submitted != verified
```

---

# 77. Next Documentation

Next document:

```text
09-APPLICATION-FLOW.md
```

Then:

```text
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```
