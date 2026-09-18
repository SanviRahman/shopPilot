**# ShopPilot E-commerce — Database Schema**

**# শপপাইলট ই-কমার্স — ডেটাবেজ স্কিমা**

\> **\*\*Document:\*\*** Physical Database Schema / ফিজিক্যাল ডেটাবেজ স্কিমা  

\> **\*\*Project:\*\*** ShopPilot E-commerce  

\> **\*\*Source Documents:\*\***  

\> \`01-PROJECT-OVERVIEW-UPDATED.md\` v1.1  

\> \`ShopPilot-02-PRD.md\` v1.0  

\> \`ShopPilot-03-FEATURES.md\` v1.0  

\> \`ShopPilot-04-USER-ROLES-AND-PERMISSIONS.md\` v1.0  

\> \`ShopPilot-05-BUSINESS-RULES.md\` v1.0  

\> \`ShopPilot-06-ARCHITECTURE.md\` v1.0  

\> \`ShopPilot-07-DATABASE-ERD.md\` v1.0  

\> **\*\*Database:\*\*** MySQL  

\> **\*\*ORM / Migration:\*\*** Laravel Eloquent + Laravel Schema Builder  

\> **\*\*Architecture:\*\*** Service-Oriented Modular Laravel Monolith  

\> **\*\*RBAC:\*\*** Spatie Laravel Permission  

\> **\*\*Media:\*\*** Spatie Laravel Media Library  

\> **\*\*Cart:\*\*** Laravel Session — no P0 \`carts\` / \`cart\_items\` tables  

\> **\*\*Payment:\*\*** Manual bKash / Nagad / Rocket Submission  

\> **\*\*Document Version:\*\*** 1.1  

\> **\*\*Language:\*\*** English + Bangla  

\> **\*\*Status:\*\*** Implementation-Ready P0 Physical Schema Definition — Universal Application SoftDeletes + Trash/Restore  

\> **\*\*Next Document:\*\*** \`09-APPLICATION-FLOW\.md\`

\---

**# Table of Contents**

1\. Document Purpose  

2\. Schema Authority & Precedence  

3\. P0 Schema Scope  

4\. Physical Schema Conventions  

5\. Data-Type Conventions  

6\. Money Convention  

7\. Status Storage Convention  

8\. Timestamp Convention  

9\. SoftDelete Convention  

10\. Foreign-Key Convention  

11\. Delete-Action Convention  

12\. Indexing Convention  

13\. Schema Decision Log  

14\. P0 Table Inventory  

15\. \`users\` Table  

16\. \`categories\` Table  

17\. \`products\` Table  

18\. \`coupons\` Table  

19\. \`payment\_methods\` Table  

20\. \`orders\` Table  

21\. \`order\_items\` Table  

22\. \`order\_histories\` Table  

23\. \`payment\_submissions\` Table  

24\. Order ↔ Coupon Persistence Decision  

25\. Buyer Snapshot Columns  

26\. Product Snapshot Columns  

27\. Payment Snapshot / State Boundary  

28\. Order State Columns  

29\. Nullable FK Matrix  

30\. Foreign-Key Action Matrix  

31\. Unique Constraint Matrix  

32\. Index Matrix  

33\. Composite Index Guidance  

34\. Status Value Catalogue  

35\. Laravel Enum Mapping  

36\. Laravel Model Cast Guidance  

37\. Spatie Permission Package Tables  

38\. Spatie Media Library Table  

39\. Framework Tables  

40\. Session Cart — No Database Tables  

41\. Optional P1 Tables  

42\. No \`customers\` Table  

43\. No Inventory Ledger Tables  

44\. No Real Gateway Tables  

45\. No Settings Table in P0  

46\. Checkout Transaction Write Set  

47\. Stock Mutation Schema Rule  

48\. Historical Preservation Rules  

49\. Guest Checkout Schema Behavior  

50\. Logged-in Customer Schema Behavior  

51\. Agent Assignment Schema Behavior  

52\. Payment Verification Schema Behavior  

53\. Order History Schema Behavior  

54\. Coupon Schema Behavior  

55\. SoftDelete / Restore Behavior  

56\. Hard Delete Protection  

57\. Migration Dependency Order  

58\. Migration File Plan  

59\. Laravel Migration Blueprint — Users  

60\. Laravel Migration Blueprint — Categories  

61\. Laravel Migration Blueprint — Products  

62\. Laravel Migration Blueprint — Coupons  

63\. Laravel Migration Blueprint — Payment Methods  

64\. Laravel Migration Blueprint — Orders  

65\. Laravel Migration Blueprint — Order Items  

66\. Laravel Migration Blueprint — Order Histories  

67\. Laravel Migration Blueprint — Payment Submissions  

68\. Package Migration Strategy  

69\. Seeder Data Requirements  

70\. Factory / Testing Data Guidance  

71\. Critical Schema Test Cases  

72\. Remaining Business TBDs — No Schema Automation  

73\. Schema Anti-Patterns  

74\. Schema Review Checklist  

75\. Definition of Schema Complete  

76\. Final Schema Summary  

77\. Universal Application Model SoftDelete Contract  

78\. Trash Blade View Contract  

79\. Trash Route / Restore Action Contract  

80\. Next Documentation

\---

**# 1. Document Purpose**

This document converts the approved logical ERD into an **\*\*implementation-ready Laravel + MySQL physical schema\*\***.

এই file-এর কাজ হলো:

\- exact P0 tables define করা

\- column names define করা

\- practical MySQL/Laravel data types define করা

\- nullable/default behavior define করা

\- unique constraints define করা

\- indexes define করা

\- foreign keys এবং hard-delete actions define করা

\- SoftDelete columns define করা

\- Guest Checkout physical persistence define করা

\- Order buyer/product snapshots define করা

\- Order → PaymentSubmission P0 \`hasOne\` decision implement করা

\- Coupon-to-Order persistence strategy finalize করা

\- Spatie package tables custom rewrite না করা

\- migration creation order define করা

\- later implementation-এর জন্য Laravel migration blueprints দেওয়া

This document must not introduce new P0 business modules.

\---

**# 2. Schema Authority & Precedence**

For database implementation, use this precedence:

\`\`\`text

05-BUSINESS-RULES.md

        ↓

06-ARCHITECTURE.md

        ↓

07-DATABASE-ERD.md

        ↓

08-DATABASE-SCHEMA.md

        ↓

Laravel migrations

\`\`\`

Earlier Project Overview / PRD / Features / Permission docs remain requirements sources.

If this file conflicts with an approved business rule:

\`\`\`text

Business Rule wins.

\`\`\`

If a physical decision is not defined by earlier documents, this file may make an explicit **\*\*SCHEMA-DEC\*\*** decision.

It must not make that decision silently.

\---

**# 3. P0 Schema Scope**

Application-owned P0 tables:

\`\`\`text

users

categories

products

coupons

payment\_methods

orders

order\_items

order\_histories

payment\_submissions

\`\`\`

Package-managed:

\`\`\`text

roles

permissions

model\_has\_roles

model\_has\_permissions

role\_has\_permissions

media

\`\`\`

Framework / configuration-dependent:

\`\`\`text

sessions

password\_reset\_tokens

notifications

\`\`\`

Optional P1:

\`\`\`text

addresses

activity\_logs

\`\`\`

\---

**# 4. Physical Schema Conventions**

Recommended P0 database assumptions:

\`\`\`text

Database Engine: InnoDB

Character Set: utf8mb4

Default Collation: utf8mb4\_unicode\_ci

Primary Key: BIGINT UNSIGNED AUTO\_INCREMENT

Foreign Keys: BIGINT UNSIGNED

Money: DECIMAL(12,2)

Boolean: BOOLEAN / TINYINT(1)

Status Storage: VARCHAR + PHP Enum cast

Created/Updated: Laravel timestamps

Soft Delete: deleted\_at nullable timestamp

\`\`\`

Why:

\- Laravel-native

\- MySQL-friendly

\- simple for a 13-day project

\- avoids unnecessary database-specific complexity

\- works cleanly with Eloquent relationships

\---

**# 5. Data-Type Conventions**

**## IDs**

Use:

\`\`\`text

BIGINT UNSIGNED

\`\`\`

Laravel:

\`\`\`php

$table->id();

$table->foreignId(...);

\`\`\`

\---

**## Short Names**

Typical:

\`\`\`text

VARCHAR(120–180)

\`\`\`

\---

**## Slugs**

Use:

\`\`\`text

VARCHAR(200)

\`\`\`

\---

**## Email**

Use:

\`\`\`text

VARCHAR(191)

\`\`\`

\---

**## Phone**

Use:

\`\`\`text

VARCHAR(30)

\`\`\`

Reason:

Phone is an identifier/contact string, not arithmetic data.

\---

**## SKU**

Use:

\`\`\`text

VARCHAR(100)

\`\`\`

\---

**## Transaction ID**

Use:

\`\`\`text

VARCHAR(100)

\`\`\`

Do not use numeric type.

\---

**## Description / Notes**

Use:

\`\`\`text

TEXT

LONGTEXT where long product description is expected

\`\`\`

\---

**# 6. Money Convention**

Use:

\`\`\`text

DECIMAL(12,2)

\`\`\`

for:

\`\`\`text

regular\_price

sale\_price

discount\_value

minimum\_order\_amount

subtotal

discount

shipping

grand\_total

unit\_price

line\_total

payment amount

\`\`\`

Never use:

\`\`\`text

FLOAT

DOUBLE

\`\`\`

for authoritative monetary calculations.

Application/service logic must calculate money server-side.

\---

**# 7. Status Storage Convention**

P0 uses PHP Enums, but database columns use:

\`\`\`text

VARCHAR

\`\`\`

rather than native MySQL \`ENUM\`.

Reason:

\`\`\`text

Laravel Enum casts

Easier migration changes

Readable schema

Less DB-vendor coupling

\`\`\`

Application Enums remain the source of allowed status values.

Database schema does not attempt to implement the state machine.

\---

**# 8. Timestamp Convention**

Normal mutable entities:

\`\`\`text

created\_at

updated\_at

\`\`\`

via:

\`\`\`php

$table->timestamps();

\`\`\`

Historical event table \`order\_histories\` needs:

\`\`\`text

created\_at

\`\`\`

and does not require \`updated\_at\` for P0.

Soft-deleted tables additionally use:

\`\`\`text

deleted\_at

\`\`\`

\---

**# 9. SoftDelete Convention**

All **application-owned Eloquent models** in ShopPilot use Laravel `SoftDeletes`.

Application-owned models:

\`\`\`text

User

Category

Product

Coupon

PaymentMethod

Order

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

Therefore every corresponding application-owned table contains:

\`\`\`text

deleted_at TIMESTAMP NULL

\`\`\`

Use in every application-owned model:

\`\`\`php

use Illuminate\Database\Eloquent\SoftDeletes;

class Example extends Model
{
    use SoftDeletes;
}

\`\`\`

Use in every application-owned migration:

\`\`\`php

$table->softDeletes();

\`\`\`

**## Historical Model Rule**

These models are historical but still use SoftDeletes:

\`\`\`text

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

Adding SoftDeletes does **not** mean normal business workflow should delete them.

Rules:

\`\`\`text

Normal checkout/order/payment workflow
→ never deletes historical rows.

Authorized explicit delete action
→ soft delete only.

Trash view
→ lists only trashed rows.

Restore action
→ restores the same row.

Force delete
→ not required for P0.

\`\`\`

Historical preservation remains mandatory.

**## Package / Framework Exception**

This universal SoftDelete rule applies to **ShopPilot application-owned Eloquent models only**.

Do not modify package-managed schemas solely to force this convention:

\`\`\`text

Spatie Permission tables

Spatie Media Library media table

Laravel framework tables

\`\`\`

Those remain controlled by their package/framework migrations.

\---

**# 10. Foreign-Key Convention**

Use real MySQL foreign keys for core P0 relations.

Preferred Laravel style:

\`\`\`php

$table->foreignId('...')->constrained(...);

\`\`\`

Foreign-key behavior must preserve historical Order data.

Do not rely only on application-level IDs without constraints for the core schema.

\---

**# 11. Delete-Action Convention**

Because core master records use SoftDelete, normal application deletion does not fire hard-delete FK actions.

For the rare hard-delete case, schema uses:

\`\`\`text

SET NULL

\`\`\`

when historical child data may remain valid without the parent identity.

Use:

\`\`\`text

RESTRICT

\`\`\`

when deleting the parent would break historical meaning.

Core policy:

\`\`\`text

Never cascade-delete historical Order data.

\`\`\`

\---

**# 12. Indexing Convention**

Create indexes for:

\`\`\`text

Foreign keys

Unique business identifiers

Common status filters

Customer My Orders

Agent Assigned Orders

Payment verification queue

Coupon expiry lookup

Product storefront filtering

\`\`\`

Avoid speculative over-indexing.

Every extra index has write/storage cost.

\---

**# 13. Schema Decision Log**

**## SCHEMA-DEC-001 — Primary Keys**

Use:

\`\`\`text

BIGINT UNSIGNED AUTO\_INCREMENT

\`\`\`

for all application-owned P0 table PKs.

\---

**## SCHEMA-DEC-002 — Money Precision**

Use:

\`\`\`text

DECIMAL(12,2)

\`\`\`

for P0 monetary values.

\---

**## SCHEMA-DEC-003 — Status Columns**

Use:

\`\`\`text

VARCHAR

\`\`\`

with Laravel PHP Enum casts.

Do not use MySQL native ENUM for P0.

\---

**## SCHEMA-DEC-004 — Order → PaymentSubmission**

07-ERD finalized:

\`\`\`text

Order hasOne PaymentSubmission

\`\`\`

Physical implementation:

\`\`\`text

payment\_submissions.order\_id

\= NOT NULL

\= FK

\= UNIQUE

\`\`\`

Therefore one Order may have:

\`\`\`text

0..1 PaymentSubmission

\`\`\`

\---

**## SCHEMA-DEC-005 — Guest Order Ownership**

Use:

\`\`\`text

orders.user\_id NULL

\`\`\`

Guest Order:

\`\`\`text

user\_id = null

\`\`\`

Authenticated Customer Order:

\`\`\`text

user\_id = authenticated User ID

\`\`\`

\---

**## SCHEMA-DEC-006 — Agent Assignment**

Use:

\`\`\`text

orders.assigned\_agent\_id NULL

\`\`\`

FK target:

\`\`\`text

users.id

\`\`\`

Application Service validates that the selected User has Agent role/context.

\---

**## SCHEMA-DEC-007 — Coupon Persistence on Order**

07-ERD intentionally deferred the exact Order/Coupon persistence strategy.

P0 physical schema uses both:

\`\`\`text

orders.coupon\_id nullable

orders.coupon\_code nullable snapshot

\`\`\`

and keeps:

\`\`\`text

orders.discount

\`\`\`

as the historical monetary discount snapshot.

Why:

\`\`\`text

coupon\_id

→ current relational trace

coupon\_code

→ historical human-readable snapshot

discount

→ historical financial truth

\`\`\`

This does not add coupon stacking.

Only one nullable Coupon link exists per Order.

\---

**## SCHEMA-DEC-008 — Coupon Code Uniqueness**

P0 uses:

\`\`\`text

coupons.code UNIQUE

\`\`\`

Database default collation:

\`\`\`text

utf8mb4\_unicode\_ci

\`\`\`

therefore normal MySQL uniqueness is case-insensitive under this schema convention.

Recommended application behavior:

\`\`\`text

trim coupon input

normalize consistently before validation

\`\`\`

This explicitly resolves the previously undefined case-sensitivity behavior for the P0 physical schema.

\---

**## SCHEMA-DEC-009 — Transaction ID Uniqueness**

Earlier documents explicitly leave duplicate Transaction ID policy undefined.

Therefore P0 schema uses:

\`\`\`text

transaction\_id INDEX

\`\`\`

but **\*\*NOT\*\***:

\`\`\`text

UNIQUE(transaction\_id)

\`\`\`

Do not silently reject duplicates at database level until the business rule is approved.

\---

**## SCHEMA-DEC-010 — Sale Price Storage**

Store:

\`\`\`text

sale\_price NULL

\`\`\`

No sale schedule columns are added.

Exact sale-price activation rule remains a business/application TBD.

Schema only stores the optional value.

\---

**## SCHEMA-DEC-011 — Shipping Storage**

Store:

\`\`\`text

shipping DECIMAL(12,2) DEFAULT 0.00

\`\`\`

Exact shipping calculation remains TBD.

No shipping-engine tables are introduced.

\---

**## SCHEMA-DEC-012 — No Database Triggers**

P0 uses no DB triggers for:

\`\`\`text

Stock deduction

Stock restore

Order history

Payment synchronization

Status transition

Coupon expiry

\`\`\`

Those concerns remain in Services / Rules / Console where approved.

\---

**# 14. P0 Table Inventory**

\| Table | Ownership | SoftDelete | Main Role |

\|---|---|---:|---|

\| \`users\` | Application | Yes | Authenticated accounts |

\| \`categories\` | Application | Yes | Product grouping |

\| \`products\` | Application | Yes | Catalog + stock |

\| \`coupons\` | Application | Yes | Discount definitions |

\| \`payment\_methods\` | Application | Yes | Manual MFS config |

\| \`orders\` | Application | Yes | Purchase aggregate |

\| \`order\_items\` | Application | Yes | Historical line snapshots; delete means soft-delete only |

\| \`order\_histories\` | Application | Yes | Workflow history; delete means soft-delete only |

\| \`payment\_submissions\` | Application | Yes | Manual payment submission; delete means soft-delete only |

\| Spatie RBAC tables | Package | Package | Roles / permissions |

\| \`media\` | Package | Package | Media Library |

\| framework tables | Framework | Varies | Laravel infrastructure |

\---

**# 15. \`users\` Table**

**## Purpose**

Authenticated:

\`\`\`text

Admin

Manager

Agent

Customer

\`\`\`

Guest does not require a User record.

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | Laravel \`id()\` |

\| \`name\` | VARCHAR(120) | No | — | — | Display/account name |

\| \`email\` | VARCHAR(191) | No | — | UNIQUE | Login identity |

\| \`email\_verified\_at\` | TIMESTAMP | Yes | NULL | — | P1-compatible |

\| \`password\` | VARCHAR(255) | No | — | — | Hashed |

\| \`remember\_token\` | VARCHAR(100) | Yes | NULL | — | Laravel auth |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | — | Laravel timestamps |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | Laravel timestamps |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

**## Not Added**

P0 does not require dedicated:

\`\`\`text

role column

customer\_type column

agent flag

\`\`\`

Role/permission comes from Spatie Permission.

\---

**# 16. \`categories\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`name\` | VARCHAR(150) | No | — | — | |

\| \`slug\` | VARCHAR(200) | No | — | UNIQUE | Route/storefront identifier |

\| \`description\` | TEXT | Yes | NULL | — | |

\| \`status\` | VARCHAR(20) | No | \`active\` | INDEX | Enum-cast |

\| \`sort\_order\` | INT UNSIGNED | No | \`0\` | INDEX | Display order |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete |

**## Media**

Category image is not stored as a string column.

Use Media Library collection:

\`\`\`text

category\_image

\`\`\`

\---

**# 17. \`products\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`category\_id\` | BIGINT UNSIGNED | No | — | FK + INDEX | → \`categories.id\` |

\| \`name\` | VARCHAR(180) | No | — | INDEX optional | Product name |

\| \`slug\` | VARCHAR(200) | No | — | UNIQUE | |

\| \`sku\` | VARCHAR(100) | No | — | UNIQUE | Business identifier |

\| \`short\_description\` | VARCHAR(500) | Yes | NULL | — | |

\| \`description\` | LONGTEXT | Yes | NULL | — | |

\| \`regular\_price\` | DECIMAL(12,2) | No | — | — | Server-authoritative |

\| \`sale\_price\` | DECIMAL(12,2) | Yes | NULL | — | Activation rule remains TBD |

\| \`stock\_quantity\` | INT UNSIGNED | No | \`0\` | INDEX | P0 stock source |

\| \`status\` | VARCHAR(20) | No | \`active\` | INDEX | active/inactive |

\| \`featured\` | BOOLEAN | No | \`false\` | INDEX | Home/storefront |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | INDEX optional | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

**## FK**

\`\`\`text

products.category\_id

→ categories.id

ON DELETE RESTRICT

ON UPDATE CASCADE

\`\`\`

**## Recommended Composite Index**

\`\`\`text

(category\_id, status)

\`\`\`

Optional:

\`\`\`text

(status, featured)

\`\`\`

for storefront queries.

**## Media**

Use:

\`\`\`text

product\_thumbnail

product\_gallery

\`\`\`

through Spatie Media Library.

\---

**# 18. \`coupons\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`code\` | VARCHAR(80) | No | — | UNIQUE | Case-insensitive under DB collation |

\| \`discount\_type\` | VARCHAR(20) | No | — | INDEX optional | fixed/percentage |

\| \`discount\_value\` | DECIMAL(12,2) | No | — | — | |

\| \`minimum\_order\_amount\` | DECIMAL(12,2) | No | \`0.00\` | — | |

\| \`start\_date\` | DATETIME | No | — | INDEX | |

\| \`end\_date\` | DATETIME | No | — | INDEX | Expiry |

\| \`status\` | VARCHAR(20) | No | \`active\` | INDEX | active/inactive/expired |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete |

**## Recommended Composite Index**

\`\`\`text

(status, end\_date)

\`\`\`

Useful for:

\`\`\`text

coupons\:expire

\`\`\`

\---

**# 19. \`payment\_methods\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`name\` | VARCHAR(100) | No | — | — | bKash/Nagad/Rocket display name |

\| \`code\` | VARCHAR(50) | No | — | UNIQUE | \`bkash\`, \`nagad\`, \`rocket\` |

\| \`account\_number\` | VARCHAR(50) | No | — | — | String, not numeric |

\| \`account\_type\` | VARCHAR(50) | No | — | — | e.g. configured account type |

\| \`instruction\` | TEXT | No | — | — | Checkout instruction |

\| \`status\` | VARCHAR(20) | No | \`active\` | INDEX | active/inactive |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete |

No provider API credential columns are required.

\---

**# 20. \`orders\` Table**

**## Purpose**

Primary purchase aggregate for Guest and authenticated Customer.

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`order\_number\` | VARCHAR(40) | No | — | UNIQUE | e.g. \`ORD-2026-000001\` |

\| \`user\_id\` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Auth Customer; null for Guest |

\| \`assigned\_agent\_id\` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | → \`users.id\` |

\| \`coupon\_id\` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | One optional Coupon |

\| \`coupon\_code\` | VARCHAR(80) | Yes | NULL | INDEX optional | Historical snapshot |

\| \`buyer\_name\` | VARCHAR(150) | No | — | — | Snapshot |

\| \`buyer\_phone\` | VARCHAR(30) | No | — | INDEX optional | Snapshot |

\| \`buyer\_email\` | VARCHAR(191) | No | — | INDEX optional | Snapshot |

\| \`shipping\_address\` | TEXT | No | — | — | Snapshot |

\| \`city\_or\_area\` | VARCHAR(150) | No | — | — | Snapshot |

\| \`subtotal\` | DECIMAL(12,2) | No | — | — | Server-calculated |

\| \`discount\` | DECIMAL(12,2) | No | \`0.00\` | — | Historical discount |

\| \`shipping\` | DECIMAL(12,2) | No | \`0.00\` | — | Calculation rule TBD |

\| \`grand\_total\` | DECIMAL(12,2) | No | — | — | Server-calculated |

\| \`payment\_status\` | VARCHAR(20) | No | \`unpaid\` | INDEX | Enum |

\| \`order\_status\` | VARCHAR(20) | No | \`pending\` | INDEX | Enum |

\| \`customer\_note\` | TEXT | Yes | NULL | — | Customer-visible own input |

\| \`internal\_note\` | TEXT | Yes | NULL | — | Staff-only |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | INDEX | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

**## FK — Customer**

\`\`\`text

orders.user\_id

→ users.id

ON DELETE SET NULL

ON UPDATE CASCADE

\`\`\`

This preserves Order history even if a User is ever hard-deleted.

\---

**## FK — Assigned Agent**

\`\`\`text

orders.assigned\_agent\_id

→ users.id

ON DELETE SET NULL

ON UPDATE CASCADE

\`\`\`

\---

**## FK — Coupon**

\`\`\`text

orders.coupon\_id

→ coupons.id

ON DELETE SET NULL

ON UPDATE CASCADE

\`\`\`

\`coupon\_code\` + \`discount\` preserve Order history if Coupon identity becomes unavailable.

\---

**## Recommended Indexes**

\`\`\`text

UNIQUE(order\_number)

INDEX(user\_id)

INDEX(assigned\_agent\_id)

INDEX(coupon\_id)

INDEX(order\_status)

INDEX(payment\_status)

INDEX(created\_at)

INDEX(assigned\_agent\_id, order\_status)

INDEX(user\_id, created\_at)

\`\`\`

\---

**# 21. \`order\_items\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`order\_id\` | BIGINT UNSIGNED | No | — | FK + INDEX | |

\| \`product\_id\` | BIGINT UNSIGNED | No | — | FK + INDEX | Current Product reference |

\| \`product\_name\` | VARCHAR(180) | No | — | — | Snapshot |

\| \`sku\` | VARCHAR(100) | No | — | — | Snapshot |

\| \`unit\_price\` | DECIMAL(12,2) | No | — | — | Snapshot |

\| \`quantity\` | INT UNSIGNED | No | — | — | |

\| \`line\_total\` | DECIMAL(12,2) | No | — | — | Snapshot/calculated |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete / Trash |

**## FKs**

\`\`\`text

order\_items.order\_id

→ orders.id

ON DELETE RESTRICT

order\_items.product\_id

→ products.id

ON DELETE RESTRICT

\`\`\`

Reason:

Historical Order Items must not disappear through cascade deletion.

\---
**# 22. \`order\_histories\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`order\_id\` | BIGINT UNSIGNED | No | — | FK + INDEX | |

\| \`user\_id\` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Actor; nullable for Guest/System context |

\| \`from\_status\` | VARCHAR(20) | Yes | NULL | — | Can be null for initial/non-status event |

\| \`to\_status\` | VARCHAR(20) | Yes | NULL | INDEX optional | |

\| \`note\` | TEXT | Yes | NULL | — | Event/trace text |

\| \`created\_at\` | TIMESTAMP | No | current timestamp | INDEX | Immutable event time |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete / Trash |

No:

\`\`\`text

updated\_at

\`\`\`

for P0 history.

`deleted\_at` exists for explicit authorized Trash/Restore behavior. Normal workflow must not delete OrderHistory rows.

**## FKs**

\`\`\`text

order\_histories.order\_id

→ orders.id

ON DELETE RESTRICT

order\_histories.user\_id

→ users.id

ON DELETE SET NULL

\`\`\`

**## Recommended Composite Index**

\`\`\`text

(order\_id, created\_at)

\`\`\`

\---
**# 23. \`payment\_submissions\` Table**

**## Physical Schema**

\| Column | Type | Null | Default | Key / Index | Notes |

\|---|---|---:|---|---|---|

\| \`id\` | BIGINT UNSIGNED | No | auto | PK | |

\| \`order\_id\` | BIGINT UNSIGNED | No | — | FK + UNIQUE | P0 hasOne |

\| \`payment\_method\_id\` | BIGINT UNSIGNED | No | — | FK + INDEX | |

\| \`transaction\_id\` | VARCHAR(100) | No | — | INDEX, **\*\*not UNIQUE\*\*** | Uniqueness rule TBD |

\| \`amount\` | DECIMAL(12,2) | No | — | — | Submitted payment amount |

\| \`status\` | VARCHAR(20) | No | \`submitted\` | INDEX | submitted/verified/rejected |

\| \`verified\_by\` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX | Authorized staff User |

\| \`verified\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`rejection\_note\` | TEXT | Yes | NULL | — | |

\| \`created\_at\` | TIMESTAMP | Yes | NULL | INDEX | |

\| \`updated\_at\` | TIMESTAMP | Yes | NULL | — | |

\| \`deleted\_at\` | TIMESTAMP | Yes | NULL | — | SoftDelete / Trash |

**## FKs**

\`\`\`text

payment\_submissions.order\_id

→ orders.id

ON DELETE RESTRICT

payment\_submissions.payment\_method\_id

→ payment\_methods.id

ON DELETE RESTRICT

payment\_submissions.verified\_by

→ users.id

ON DELETE SET NULL

\`\`\`

**## Critical Constraint**

\`\`\`text

UNIQUE(order\_id)

\`\`\`

implements:

\`\`\`text

Order hasOne PaymentSubmission

\`\`\`

for P0.

\---
**# 24. Order ↔ Coupon Persistence Decision**

Physical P0 design:

\`\`\`text

orders.coupon\_id       nullable FK

orders.coupon\_code     nullable snapshot

orders.discount        non-null monetary snapshot

\`\`\`

Example:

\`\`\`text

Coupon:

id = 8

code = SAVE10

Order:

coupon\_id = 8

coupon\_code = SAVE10

discount = 250.00

\`\`\`

If Coupon is later soft-deleted:

\`\`\`text

Order remains historically understandable.

\`\`\`

If a rare hard delete occurs:

\`\`\`text

coupon\_id → NULL

coupon\_code remains

discount remains

\`\`\`

\---

**# 25. Buyer Snapshot Columns**

Every Order stores:

\`\`\`text

buyer\_name

buyer\_phone

buyer\_email

shipping\_address

city\_or\_area

\`\`\`

These columns are:

\`\`\`text

NOT NULL

\`\`\`

for both:

\`\`\`text

Guest

Authenticated Customer

\`\`\`

Do not render historical Order delivery information directly from current User profile.

\---

**# 26. Product Snapshot Columns**

Every OrderItem stores:

\`\`\`text

product\_name

sku

unit\_price

quantity

line\_total

\`\`\`

Historical display should use snapshots for purchase-time facts.

\`product\_id\` remains useful for current-reference/admin navigation.

\---

**# 27. Payment Snapshot / State Boundary**

Order contains:

\`\`\`text

payment\_status

\`\`\`

PaymentSubmission contains:

\`\`\`text

status

payment\_method\_id

transaction\_id

amount

verified\_by

verified\_at

rejection\_note

\`\`\`

Important:

\`\`\`text

Order payment\_status

\`\`\`

is the aggregate-level payment state.

\`\`\`text

PaymentSubmission.status

\`\`\`

is submission verification state.

Service logic must keep relevant state changes consistent.

Do not use a DB trigger to synchronize them.

\---

**# 28. Order State Columns**

Order uses:

\`\`\`text

order\_status VARCHAR(20)

payment\_status VARCHAR(20)

\`\`\`

They are intentionally separate.

Do not combine into:

\`\`\`text

status

\`\`\`

because Order fulfillment and Payment verification are separate workflows.

\---

**# 29. Nullable FK Matrix**

\| Column | Nullable | Reason |

\|---|---:|---|

\| \`products.category\_id\` | No | Product requires Category |

\| \`orders.user\_id\` | Yes | Guest Checkout |

\| \`orders.assigned\_agent\_id\` | Yes | New Order may be unassigned |

\| \`orders.coupon\_id\` | Yes | Coupon is optional |

\| \`order\_items.order\_id\` | No | Item requires Order |

\| \`order\_items.product\_id\` | No | P0 Product reference |

\| \`order\_histories.order\_id\` | No | History requires Order |

\| \`order\_histories.user\_id\` | Yes | Guest/System actor context |

\| \`payment\_submissions.order\_id\` | No | Submission requires Order |

\| \`payment\_submissions.payment\_method\_id\` | No | Submission requires selected method |

\| \`payment\_submissions.verified\_by\` | Yes | Pending submission has no verifier |

\---

**# 30. Foreign-Key Action Matrix**

\| Child FK | Parent | On Delete | Reason |

\|---|---|---|---|

\| \`products.category\_id\` | \`categories.id\` | RESTRICT | Prevent broken Product link |

\| \`orders.user\_id\` | \`users.id\` | SET NULL | Preserve Order |

\| \`orders.assigned\_agent\_id\` | \`users.id\` | SET NULL | Preserve Order |

\| \`orders.coupon\_id\` | \`coupons.id\` | SET NULL | Snapshot remains |

\| \`order\_items.order\_id\` | \`orders.id\` | RESTRICT | Preserve historical item |

\| \`order\_items.product\_id\` | \`products.id\` | RESTRICT | Preserve reference |

\| \`order\_histories.order\_id\` | \`orders.id\` | RESTRICT | Preserve history |

\| \`order\_histories.user\_id\` | \`users.id\` | SET NULL | Preserve history |

\| \`payment\_submissions.order\_id\` | \`orders.id\` | RESTRICT | Preserve payment evidence |

\| \`payment\_submissions.payment\_method\_id\` | \`payment\_methods.id\` | RESTRICT | Preserve method reference |

\| \`payment\_submissions.verified\_by\` | \`users.id\` | SET NULL | Preserve payment evidence |

All core FK updates:

\`\`\`text

ON UPDATE CASCADE

\`\`\`

may be used consistently, though primary IDs are not expected to change.

\---

**# 31. Unique Constraint Matrix**

\| Table | Column(s) | Unique |

\|---|---|---:|

\| \`users\` | \`email\` | Yes |

\| \`categories\` | \`slug\` | Yes |

\| \`products\` | \`slug\` | Yes |

\| \`products\` | \`sku\` | Yes |

\| \`coupons\` | \`code\` | Yes |

\| \`payment\_methods\` | \`code\` | Yes |

\| \`orders\` | \`order\_number\` | Yes |

\| \`payment\_submissions\` | \`order\_id\` | Yes |

\| \`payment\_submissions\` | \`transaction\_id\` | **\*\*No — TBD\*\*** |

\---

**# 32. Index Matrix**

**## Users**

\`\`\`text

UNIQUE(email)

\`\`\`

\---

**## Categories**

\`\`\`text

UNIQUE(slug)

INDEX(status)

INDEX(sort\_order)

\`\`\`

\---

**## Products**

\`\`\`text

UNIQUE(slug)

UNIQUE(sku)

INDEX(category\_id)

INDEX(status)

INDEX(featured)

INDEX(stock\_quantity)

INDEX(category\_id, status)

INDEX(status, featured)

\`\`\`

\---

**## Coupons**

\`\`\`text

UNIQUE(code)

INDEX(status)

INDEX(start\_date)

INDEX(end\_date)

INDEX(status, end\_date)

\`\`\`

\---

**## Payment Methods**

\`\`\`text

UNIQUE(code)

INDEX(status)

\`\`\`

\---

**## Orders**

\`\`\`text

UNIQUE(order\_number)

INDEX(user\_id)

INDEX(assigned\_agent\_id)

INDEX(coupon\_id)

INDEX(order\_status)

INDEX(payment\_status)

INDEX(created\_at)

INDEX(user\_id, created\_at)

INDEX(assigned\_agent\_id, order\_status)

\`\`\`

\---

**## Order Items**

\`\`\`text

INDEX(order\_id)

INDEX(product\_id)

\`\`\`

\---

**## Order Histories**

\`\`\`text

INDEX(order\_id)

INDEX(user\_id)

INDEX(created\_at)

INDEX(order\_id, created\_at)

\`\`\`

\---

**## Payment Submissions**

\`\`\`text

UNIQUE(order\_id)

INDEX(payment\_method\_id)

INDEX(transaction\_id)

INDEX(status)

INDEX(verified\_by)

INDEX(created\_at)

\`\`\`

\---

**# 33. Composite Index Guidance**

Use composite indexes only for known P0 query patterns.

Approved/useful:

\`\`\`text

products(category\_id, status)

products(status, featured)

orders(user\_id, created\_at)

orders(assigned\_agent\_id, order\_status)

coupons(status, end\_date)

order\_histories(order\_id, created\_at)

\`\`\`

Do not add dozens of speculative indexes.

\---

**# 34. Status Value Catalogue**

**## Product**

\`\`\`text

active

inactive

\`\`\`

\---

**## Order**

\`\`\`text

pending

confirmed

processing

shipped

delivered

cancelled

\`\`\`

\---

**## Payment**

\`\`\`text

unpaid

submitted

verified

rejected

\`\`\`

\---

**## Coupon**

\`\`\`text

active

inactive

expired

\`\`\`

\---

**## Payment Method**

\`\`\`text

active

inactive

\`\`\`

\---

**# 35. Laravel Enum Mapping**

Recommended PHP Enums:

\`\`\`text

ProductStatus

OrderStatus

PaymentStatus

CouponStatus

PaymentMethodStatus

\`\`\`

Potential casts:

\`\`\`php

protected function casts(): array

{

    return [

        'status' => ProductStatus::class,

    ];

}

\`\`\`

For Order:

\`\`\`php

protected function casts(): array

{

    return [

        'order\_status'   => OrderStatus::class,

        'payment\_status' => PaymentStatus::class,

    ];

}

\`\`\`

Enums define valid values.

Services/Rules define valid transitions.

\---

**# 36. Laravel Model Cast Guidance**

**## Product**

\`\`\`text

regular\_price → decimal:2

sale\_price → decimal:2

stock\_quantity → integer

featured → boolean

status → ProductStatus

\`\`\`

**## Coupon**

\`\`\`text

discount\_value → decimal:2

minimum\_order\_amount → decimal:2

start\_date → datetime

end\_date → datetime

status → CouponStatus

\`\`\`

**## Order**

\`\`\`text

subtotal → decimal:2

discount → decimal:2

shipping → decimal:2

grand\_total → decimal:2

order\_status → OrderStatus

payment\_status → PaymentStatus

\`\`\`

**## PaymentMethod**

\`\`\`text

status → PaymentMethodStatus

\`\`\`

**## PaymentSubmission**

\`\`\`text

amount → decimal:2

status → PaymentStatus

verified\_at → datetime

\`\`\`

\---

**# 37. Spatie Permission Package Tables**

Use Spatie Laravel Permission published migration.

Expected package-managed concepts:

\`\`\`text

roles

permissions

model\_has\_roles

model\_has\_permissions

role\_has\_permissions

\`\`\`

Do not manually redesign package table column definitions in ShopPilot migrations.

Application role values:

\`\`\`text

Admin

Manager

Agent

Customer

\`\`\`

Guest:

\`\`\`text

No role row required.

\`\`\`

Package migration remains authoritative for its exact physical schema.

\---

**# 38. Spatie Media Library Table**

Use Spatie Media Library published migration.

Package-managed:

\`\`\`text

media

\`\`\`

Collections used by ShopPilot:

\`\`\`text

product\_thumbnail

product\_gallery

category\_image

user\_avatar optional

\`\`\`

Do not add custom:

\`\`\`text

product\_images

category\_images

\`\`\`

for P0.

Package migration remains authoritative for exact Media table fields.

\---

**# 39. Framework Tables**

Depending on Laravel configuration:

\`\`\`text

sessions

password\_reset\_tokens

notifications

\`\`\`

Use Laravel-generated/published framework migrations.

Do not hand-design a second competing version.

\---

**# 40. Session Cart — No Database Tables**

P0 Cart remains:

\`\`\`text

Laravel Session

\`\`\`

Therefore do not create:

\`\`\`text

carts

cart\_items

\`\`\`

Persistent purchase data begins after successful Checkout transaction:

\`\`\`text

orders

order\_items

payment\_submissions

order\_histories

\`\`\`

\---

**# 41. Optional P1 Tables**

Not required for core MVP:

\`\`\`text

addresses

activity\_logs

\`\`\`

Do not create them unless P1 is intentionally started.

\---

**# 42. No \`customers\` Table**

Authenticated Customer:

\`\`\`text

users row

\+

Customer Spatie role/context

\`\`\`

Guest:

\`\`\`text

no User row required

\`\`\`

Order-owned buyer snapshots handle historical purchase identity.

Therefore:

\`\`\`text

No P0 customers table

\`\`\`

\---

**# 43. No Inventory Ledger Tables**

P0 stock source:

\`\`\`text

products.stock\_quantity

\`\`\`

Do not add:

\`\`\`text

inventory\_movements

stock\_ledgers

warehouses

warehouse\_stocks

\`\`\`

\---

**# 44. No Real Gateway Tables**

Manual MFS P0 does not need:

\`\`\`text

payment\_intents

gateway\_transactions

webhook\_events

provider\_callbacks

refund\_transactions

\`\`\`

Use:

\`\`\`text

payment\_methods

payment\_submissions

\`\`\`

\---

**# 45. No Settings Table in P0**

Permission docs contain:

\`\`\`text

settings.view

settings.update

\`\`\`

but approved core entity list does not define a \`settings\` entity.

Therefore:

\`\`\`text

No P0 settings table is introduced by this schema.

\`\`\`

If settings persistence becomes necessary later, define it explicitly rather than silently adding it here.

\---

**# 46. Checkout Transaction Write Set**

Critical writes:

\`\`\`text

BEGIN TRANSACTION

1\. INSERT orders

2\. INSERT order\_items

3\. INSERT payment\_submissions

4\. UPDATE products.stock\_quantity

5\. INSERT initial order\_histories

COMMIT

\`\`\`

On error:

\`\`\`text

ROLLBACK

\`\`\`

Do not clear successful Cart state until transaction commits.

\---

**# 47. Stock Mutation Schema Rule**

Stock lives in:

\`\`\`text

products.stock\_quantity

\`\`\`

Checkout must:

\`\`\`text

Reload Product

Revalidate stock

Deduct inside transaction

\`\`\`

Database schema does not auto-deduct stock.

No DB trigger.

Stock restoration after cancellation remains undefined.

\---

**# 48. Historical Preservation Rules**

Historical data:

\`\`\`text

Order buyer snapshot

Order coupon snapshot

Order financial totals

OrderItem product snapshot

OrderHistory

PaymentSubmission

\`\`\`

must remain stable even if:

\`\`\`text

User profile changes

Product name changes

Product price changes

Coupon changes

Payment Method is soft-deleted

\`\`\`

\---

**# 49. Guest Checkout Schema Behavior**

Guest successful Order:

\`\`\`text

orders.user\_id = NULL

orders.buyer\_name = submitted name

orders.buyer\_phone = submitted phone

orders.buyer\_email = submitted email

orders.shipping\_address = submitted address

orders.city\_or\_area = submitted city/area

\`\`\`

Then:

\`\`\`text

OrderItems created

PaymentSubmission created

Initial history created

\`\`\`

No Guest User row is required.

\---

**# 50. Logged-in Customer Schema Behavior**

Authenticated Order:

\`\`\`text

orders.user\_id = auth()->id()

\`\`\`

But also persist:

\`\`\`text

buyer\_name

buyer\_phone

buyer\_email

shipping\_address

city\_or\_area

\`\`\`

The relation does not replace the snapshot.

\---

**# 51. Agent Assignment Schema Behavior**

Initially:

\`\`\`text

assigned\_agent\_id = NULL

\`\`\`

Authorized Admin/Manager assignment:

\`\`\`text

assigned\_agent\_id = Agent User ID

\`\`\`

Database FK proves User existence.

Application Service/Policy proves that the User is an eligible Agent.

\---

**# 52. Payment Verification Schema Behavior**

At checkout:

\`\`\`text

payment\_submissions.status = submitted

orders.payment\_status = submitted

verified\_by = NULL

verified\_at = NULL

\`\`\`

On authorized verify:

\`\`\`text

payment\_submissions.status = verified

payment\_submissions.verified\_by = staff user id

payment\_submissions.verified\_at = now()

orders.payment\_status = verified

\`\`\`

On authorized reject:

\`\`\`text

payment\_submissions.status = rejected

payment\_submissions.verified\_by = staff user id

payment\_submissions.verified\_at = now()

payment\_submissions.rejection\_note = ...

orders.payment\_status = rejected

\`\`\`

Do not automatically change \`order\_status\` unless a later approved business rule requires it.

\---

**# 53. Order History Schema Behavior**

Examples written into \`order\_histories\`:

\`\`\`text

Order Created

Payment Submitted

Payment Verified

Assigned To Agent

Confirmed

Processing

Shipped

Delivered

Cancelled

\`\`\`

For an Order-status transition:

\`\`\`text

from\_status

to\_status

\`\`\`

should be populated.

For a non-order-status event such as payment verification or assignment:

\`\`\`text

from\_status / to\_status may remain null

note carries the trace

\`\`\`

No generic enterprise audit log is required for P0.

\---

**# 54. Coupon Schema Behavior**

Coupon validation uses:

\`\`\`text

code

discount\_type

discount\_value

minimum\_order\_amount

start\_date

end\_date

status

\`\`\`

Order stores:

\`\`\`text

coupon\_id

coupon\_code

discount

\`\`\`

Rules:

\`\`\`text

One Coupon per Order

No stacking

Expired rejected

Inactive rejected

Server-side calculation

\`\`\`

\---

**# 55. SoftDelete / Restore Behavior**

All application-owned ShopPilot models use SoftDeletes:

\`\`\`text

User

Category

Product

Coupon

PaymentMethod

Order

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

Normal application delete:

\`\`\`text

UPDATE deleted_at = current timestamp

\`\`\`

not hard delete.

**## Default Query Behavior**

Normal Eloquent queries exclude trashed rows.

Trash listing uses:

\`\`\`php

Model::onlyTrashed()

\`\`\`

Restore uses:

\`\`\`php

$model->restore();

\`\`\`

Single-record restore lookup may use:

\`\`\`php

Model::onlyTrashed()->findOrFail($id);

\`\`\`

**## Historical Models**

These also use SoftDeletes:

\`\`\`text

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

but normal business workflows must not delete them.

They may enter Trash only through an explicit authorized administrative action.

**## Force Delete**

\`\`\`text

Not required for P0.

\`\`\`

Do not expose a permanent-delete button by default.

**## Authorization**

Restore permission/business logic is enforced by application authorization.

Existing explicit restore permissions should be used where already defined:

\`\`\`text

categories.restore

products.restore

orders.restore

\`\`\`

For application models without a documented dedicated `*.restore` permission:

\`\`\`text

Trash/Restore remains Admin-only
until the permission catalogue is explicitly expanded.

\`\`\`

Do not silently invent new permission names in this schema document.

\---

**# 56. Hard Delete Protection**

P0 does not require force delete for any application-owned model.

Every application delete should normally use SoftDeletes. Migration FK design should still make destructive hard deletes difficult.

Examples:

\`\`\`text

Product with OrderItems

→ RESTRICT hard delete

Order with Items/History/Payment

→ RESTRICT hard delete

PaymentMethod with PaymentSubmission

→ RESTRICT hard delete

\`\`\`

This is intentional.

\---

**# 57. Migration Dependency Order**

Recommended order:

\`\`\`text

01 users

02 Spatie Permission package tables

03 categories

04 products

05 coupons

06 payment\_methods

07 orders

08 order\_items

09 order\_histories

10 payment\_submissions

11 Media Library package migration

12 optional framework/P1 migrations

\`\`\`

Package timestamp order may differ when installed.

Requirement:

\`\`\`text

Parent table must exist before FK child table migration executes.

\`\`\`

\---

**# 58. Migration File Plan**

Example names:

\`\`\`text

0001\_01\_01\_000000\_create\_users\_table.php

2026\_09\_17\_000100\_create\_categories\_table.php

2026\_09\_17\_000200\_create\_products\_table.php

2026\_09\_17\_000300\_create\_coupons\_table.php

2026\_09\_17\_000400\_create\_payment\_methods\_table.php

2026\_09\_17\_000500\_create\_orders\_table.php

2026\_09\_17\_000600\_create\_order\_items\_table.php

2026\_09\_17\_000700\_create\_order\_histories\_table.php

2026\_09\_17\_000800\_create\_payment\_submissions\_table.php

\`\`\`

Exact timestamps are not important.

Dependency order is important.

\---

**# 59. Laravel Migration Blueprint — Users**

\`\`\`php

Schema::create('users', function (Blueprint $table) {

    $table->id();

    $table->string('name', 120);

    $table->string('email', 191)->unique();

    $table->timestamp('email\_verified\_at')->nullable();

    $table->string('password');

    $table->rememberToken();

    $table->timestamps();

    $table->softDeletes();

});

\`\`\`

Notes:

\`\`\`text

No role column.

No guest row requirement.

Spatie handles role/permission relations.

\`\`\`

\---

**# 60. Laravel Migration Blueprint — Categories**

\`\`\`php

Schema::create('categories', function (Blueprint $table) {

    $table->id();

    $table->string('name', 150);

    $table->string('slug', 200)->unique();

    $table->text('description')->nullable();

    $table->string('status', 20)->default('active')->index();

    $table->unsignedInteger('sort\_order')->default(0)->index();

    $table->timestamps();

    $table->softDeletes();

});

\`\`\`

No image-path column.

Media Library handles:

\`\`\`text

category\_image

\`\`\`

\---

**# 61. Laravel Migration Blueprint — Products**

\`\`\`php

Schema::create('products', function (Blueprint $table) {

    $table->id();

    $table->foreignId('category\_id')

        ->constrained('categories')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->string('name', 180);

    $table->string('slug', 200)->unique();

    $table->string('sku', 100)->unique();

    $table->string('short\_description', 500)->nullable();

    $table->longText('description')->nullable();

    $table->decimal('regular\_price', 12, 2);

    $table->decimal('sale\_price', 12, 2)->nullable();

    $table->unsignedInteger('stock\_quantity')->default(0)->index();

    $table->string('status', 20)->default('active')->index();

    $table->boolean('featured')->default(false)->index();

    $table->timestamps();

    $table->softDeletes();

    $table->index(['category\_id', 'status']);

    $table->index(['status', 'featured']);

});

\`\`\`

No:

\`\`\`text

size

color

variant\_price

variant\_stock

\`\`\`

P0 columns.

\---

**# 62. Laravel Migration Blueprint — Coupons**

\`\`\`php

Schema::create('coupons', function (Blueprint $table) {

    $table->id();

    $table->string('code', 80)->unique();

    $table->string('discount\_type', 20);

    $table->decimal('discount\_value', 12, 2);

    $table->decimal('minimum\_order\_amount', 12, 2)->default(0);

    $table->dateTime('start\_date')->index();

    $table->dateTime('end\_date')->index();

    $table->string('status', 20)->default('active')->index();

    $table->timestamps();

    $table->softDeletes();

    $table->index(['status', 'end\_date']);

});

\`\`\`

P0 case behavior:

\`\`\`text

DB collation-based case-insensitive uniqueness.

\`\`\`

\---

**# 63. Laravel Migration Blueprint — Payment Methods**

\`\`\`php

Schema::create('payment\_methods', function (Blueprint $table) {

    $table->id();

    $table->string('name', 100);

    $table->string('code', 50)->unique();

    $table->string('account\_number', 50);

    $table->string('account\_type', 50);

    $table->text('instruction');

    $table->string('status', 20)->default('active')->index();

    $table->timestamps();

    $table->softDeletes();

});

\`\`\`

P0 seeded codes:

\`\`\`text

bkash

nagad

rocket

\`\`\`

\---

**# 64. Laravel Migration Blueprint — Orders**

\`\`\`php

Schema::create('orders', function (Blueprint $table) {

    $table->id();

    $table->string('order\_number', 40)->unique();

    $table->foreignId('user\_id')

        ->nullable()

        ->constrained('users')

        ->nullOnDelete()

        ->cascadeOnUpdate();

    $table->foreignId('assigned\_agent\_id')

        ->nullable()

        ->constrained('users')

        ->nullOnDelete()

        ->cascadeOnUpdate();

    $table->foreignId('coupon\_id')

        ->nullable()

        ->constrained('coupons')

        ->nullOnDelete()

        ->cascadeOnUpdate();

    $table->string('coupon\_code', 80)->nullable();

    $table->string('buyer\_name', 150);

    $table->string('buyer\_phone', 30);

    $table->string('buyer\_email', 191);

    $table->text('shipping\_address');

    $table->string('city\_or\_area', 150);

    $table->decimal('subtotal', 12, 2);

    $table->decimal('discount', 12, 2)->default(0);

    $table->decimal('shipping', 12, 2)->default(0);

    $table->decimal('grand\_total', 12, 2);

    $table->string('payment\_status', 20)

        ->default('unpaid')

        ->index();

    $table->string('order\_status', 20)

        ->default('pending')

        ->index();

    $table->text('customer\_note')->nullable();

    $table->text('internal\_note')->nullable();

    $table->timestamps();

    $table->softDeletes();

    $table->index(['user\_id', 'created\_at']);

    $table->index(['assigned\_agent\_id', 'order\_status']);

    $table->index('coupon\_code');

});

\`\`\`

Important:

Manual MFS Checkout must explicitly set:

\`\`\`text

payment\_status = submitted

\`\`\`

inside the successful transaction.

Do not rely on \`unpaid\` default for manual MFS success.

\---

**# 65. Laravel Migration Blueprint — Order Items**

\`\`\`php

Schema::create('order\_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('order\_id')

        ->constrained('orders')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->foreignId('product\_id')

        ->constrained('products')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->string('product\_name', 180);

    $table->string('sku', 100);

    $table->decimal('unit\_price', 12, 2);

    $table->unsignedInteger('quantity');

    $table->decimal('line\_total', 12, 2);

    $table->timestamps();

    $table->softDeletes();

});

\`\`\`

`OrderItem` uses `SoftDeletes`, but normal Checkout/Order workflow must not delete historical items.

Trash/Restore is an explicit authorized administrative action.

Do not recalculate historical Item display from current Product price.

\---
**# 66. Laravel Migration Blueprint — Order Histories**

\`\`\`php

Schema::create('order\_histories', function (Blueprint $table) {

    $table->id();

    $table->foreignId('order\_id')

        ->constrained('orders')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->foreignId('user\_id')

        ->nullable()

        ->constrained('users')

        ->nullOnDelete()

        ->cascadeOnUpdate();

    $table->string('from\_status', 20)->nullable();

    $table->string('to\_status', 20)->nullable();

    $table->text('note')->nullable();

    $table->timestamp('created\_at')->useCurrent();

    $table->softDeletes();

    $table->index(['order\_id', 'created\_at']);

});

\`\`\`

No \`updated\_at\`.

`OrderHistory` uses `SoftDeletes` for explicit Trash/Restore, but History should remain append-oriented during normal business workflow.

\---
**# 67. Laravel Migration Blueprint — Payment Submissions**

\`\`\`php

Schema::create('payment\_submissions', function (Blueprint $table) {

    $table->id();

    $table->foreignId('order\_id')

        ->unique()

        ->constrained('orders')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->foreignId('payment\_method\_id')

        ->constrained('payment\_methods')

        ->restrictOnDelete()

        ->cascadeOnUpdate();

    $table->string('transaction\_id', 100)->index();

    $table->decimal('amount', 12, 2);

    $table->string('status', 20)

        ->default('submitted')

        ->index();

    $table->foreignId('verified\_by')

        ->nullable()

        ->constrained('users')

        ->nullOnDelete()

        ->cascadeOnUpdate();

    $table->timestamp('verified\_at')->nullable();

    $table->text('rejection\_note')->nullable();

    $table->timestamps();

    $table->softDeletes();

});

\`\`\`

Critical:

\`\`\`text

transaction\_id is indexed but NOT UNIQUE.

\`\`\`

This preserves the existing TBD duplicate-ID policy.

\---
**# 68. Package Migration Strategy**

Install packages and publish/use their migrations.

**## Spatie Permission**

Do not duplicate:

\`\`\`text

roles

permissions

model\_has\_roles

model\_has\_permissions

role\_has\_permissions

\`\`\`

with custom tables.

**## Media Library**

Do not duplicate:

\`\`\`text

media

\`\`\`

with a custom media table.

When package versions change, use the package migration appropriate to the installed version.

\---

**# 69. Seeder Data Requirements**

P0 seeders should create:

**## Roles**

\`\`\`text

Admin

Manager

Agent

Customer

\`\`\`

Do not create:

\`\`\`text

Guest

\`\`\`

as a Spatie role.

\---

**## Permissions**

Seed the approved granular permission catalogue.

Examples:

\`\`\`text

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

\`\`\`

Do not seed duplicate umbrella aliases unless deliberately approved.

\---

**## Payment Methods**

Recommended initial rows:

\`\`\`text

bkash

nagad

rocket

\`\`\`

with safe development/test numbers/instructions.

Production numbers should be configured by authorized staff.

\---

**# 70. Factory / Testing Data Guidance**

Factories should support:

\`\`\`text

User

Category

Product

Coupon

Order

OrderItem

OrderHistory

PaymentMethod

PaymentSubmission

\`\`\`

Critical variants:

\`\`\`text

Guest Order:

user\_id = null

Authenticated Order:

user\_id = customer id

Unassigned Order:

assigned\_agent\_id = null

Assigned Order:

assigned\_agent\_id = agent id

Submitted Payment:

verified\_by = null

verified\_at = null

Verified Payment:

verified\_by = authorized staff id

verified\_at != null

\`\`\`

\---

**# 71. Critical Schema Test Cases**

**## Test 1 — Guest Order**

Must succeed:

\`\`\`text

orders.user\_id = null

\`\`\`

with required snapshots.

\---

**## Test 2 — Authenticated Customer Order**

Must persist:

\`\`\`text

orders.user\_id = customer.id

\`\`\`

plus buyer snapshot.

\---

**## Test 3 — Invalid Agent FK**

Non-existing:

\`\`\`text

assigned\_agent\_id

\`\`\`

must fail FK validation at DB level.

Wrong-role existing User is rejected by application business logic.

\---

**## Test 4 — Duplicate Order Number**

Must fail unique constraint.

\---

**## Test 5 — Duplicate SKU**

Must fail unique constraint.

\---

**## Test 6 — Duplicate Product Slug**

Must fail unique constraint.

\---

**## Test 7 — Duplicate PaymentSubmission per Order**

Second \`payment\_submissions.order\_id\` for same Order must fail.

\---

**## Test 8 — Duplicate Transaction ID**

Database may allow it in P0 because uniqueness rule is still TBD.

Application must not falsely treat duplicate acceptance as proof of payment verification.

\---

**## Test 9 — Hard Delete Product With Historical Item**

Should be blocked by FK RESTRICT.

Normal application uses SoftDelete.

\---

**## Test 10 — Hard Delete Order With History**

Should be blocked by historical child FKs.

\---

**## Test 11 — Hard Delete Customer User**

Order may survive with:

\`\`\`text

user\_id = null

\`\`\`

while snapshots remain.

\---

**## Test 12 — Hard Delete Agent User**

Assigned Order may survive with:

\`\`\`text

assigned\_agent\_id = null

\`\`\`

\---

**## Test 13 — Soft-Deleted Product**

Must remain hidden from normal Storefront through application query scope.

\---

**## Test 14 — Payment Submission Initial State**

Must begin:

\`\`\`text

submitted

\`\`\`

not:

\`\`\`text

verified

\`\`\`

\---

**# 72. Remaining Business TBDs — No Schema Automation**

These source-level rules remain unresolved and are **\*\*not\*\*** solved with hidden database automation.

**## TBD — Shipping Calculation**

Schema only stores:

\`\`\`text

shipping

\`\`\`

No shipping engine.

\---

**## TBD — Stock Restore After Cancellation**

No DB trigger.

Do not auto-restore until business rule is approved.

\---

**## TBD — Rejected Payment → Order Status**

No trigger that changes:

\`\`\`text

order\_status

\`\`\`

when payment becomes rejected.

\---

**## TBD — Verification Before Fulfillment**

No database constraint requires:

\`\`\`text

payment\_status = verified

\`\`\`

before processing.

\---

**## TBD — Assignment Timing**

Schema allows:

\`\`\`text

assigned\_agent\_id nullable

\`\`\`

and does not enforce payment-before-assignment or assignment-before-payment.

\---

**## Resolved in 07 — PaymentSubmission Cardinality**

P0:

\`\`\`text

Order hasOne PaymentSubmission

\`\`\`

implemented by:

\`\`\`text

UNIQUE(payment\_submissions.order\_id)

\`\`\`

\---

**## TBD — Transaction ID Global Uniqueness**

No unique constraint.

\---

**## Resolved in 08 — Coupon Case Handling**

P0 schema uses case-insensitive default collation + unique Coupon code.

\---

**## TBD — Sale Price Activation**

No sale date columns or DB rule.

\---

**## P1 — Guest Tracking**

No P0 tracking token columns.

\---

**# 73. Schema Anti-Patterns**

Do not:

**## 73.1 Use FLOAT for Money**

Wrong:

\`\`\`text

FLOAT price

\`\`\`

Use:

\`\`\`text

DECIMAL(12,2)

\`\`\`

\---

**## 73.2 Put Roles in a \`users.role\` String**

Use Spatie Permission.

\---

**## 73.3 Make Guest a Database Role**

Guest is public actor, not Spatie role.

\---

**## 73.4 Require \`orders.user\_id\`**

It must be nullable for Guest Checkout.

\---

**## 73.5 Cascade Delete Historical Orders**

Do not destroy:

\`\`\`text

OrderItems

OrderHistories

PaymentSubmissions

\`\`\`

\---

**## 73.6 Make Transaction ID Automatically Verified**

A Transaction ID string is only submitted information.

\---

**## 73.7 Add Cart Tables**

P0 Cart is Session-based.

\---

**## 73.8 Add Product Variant Tables**

Out of scope.

\---

**## 73.9 Add Inventory Ledger**

Out of scope.

\---

**## 73.10 Add Gateway Webhook Tables**

Out of scope.

\---

**## 73.11 Duplicate Spatie Package Tables**

Use vendor/package migrations.

\---

**## 73.12 Store Historical Order Name/Price Only Through Product FK**

OrderItem snapshots are mandatory.

\---

**# 74. Schema Review Checklist**

\- [ ] MySQL / InnoDB assumed.

\- [ ] \`utf8mb4\` used.

\- [ ] Application PKs use BIGINT UNSIGNED.

\- [ ] Money uses DECIMAL.

\- [ ] PHP Enums map to VARCHAR status columns.

\- [ ] \`users\` uses SoftDelete.

\- [ ] \`categories\` uses SoftDelete.

\- [ ] \`products\` uses SoftDelete.

\- [ ] \`coupons\` uses SoftDelete.

\- [ ] \`orders\` uses SoftDelete.

\- [ ] \`payment\_methods\` uses SoftDelete.

\- [ ] Every application-owned Eloquent model uses SoftDeletes.

\- [ ] `order\_items` has `deleted\_at`.

\- [ ] `order\_histories` has `deleted\_at`.

\- [ ] `payment\_submissions` has `deleted\_at`.

\- [ ] Historical models are not deleted by normal workflow; Trash is explicit/admin-controlled.

\- [ ] Guest Orders allow \`user\_id = null\`.

\- [ ] Auth Customer Orders link \`user\_id\`.

\- [ ] Every Order stores buyer/shipping snapshot.

\- [ ] Agent assignment is nullable.

\- [ ] Product has simple \`stock\_quantity\`.

\- [ ] Product \`slug\` unique.

\- [ ] Product \`sku\` unique.

\- [ ] Order number unique.

\- [ ] Coupon code unique.

\- [ ] Payment Method code unique.

\- [ ] Order has max one PaymentSubmission in P0.

\- [ ] \`payment\_submissions.order\_id\` unique.

\- [ ] \`transaction\_id\` is not unique.

\- [ ] OrderItem stores Product snapshots.

\- [ ] OrderHistory is append-oriented.

\- [ ] Payment verifier is nullable User.

\- [ ] Coupon identity + code + discount history is preserved.

\- [ ] No P0 Cart tables.

\- [ ] No P0 Customer duplicate table.

\- [ ] No variants.

\- [ ] No warehouse ledger.

\- [ ] No real gateway tables.

\- [ ] Spatie tables come from package migrations.

\- [ ] Media table comes from package migration.

\- [ ] Historical child hard deletes are protected.

\- [ ] Remaining business TBDs are not hidden in triggers.

\- [ ] Every application-owned model has a documented Trash view.

\- [ ] Trash queries use `onlyTrashed()`.

\- [ ] Restore actions call `restore()`.

\- [ ] Force Delete is not exposed in P0 by default.

\---

**# 75. Definition of Schema Complete**

Database Schema is complete for P0 when the following are unambiguous:

\`\`\`text

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

SoftDelete columns on every application-owned model

Trash/Restore view contract

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

\`\`\`

\---

**# 76. Final Schema Summary**

ShopPilot P0 physical data model is:

\`\`\`text

users

    │

    ├── orders.user\_id nullable

    ├── orders.assigned\_agent\_id nullable

    ├── order\_histories.user\_id nullable

    └── payment\_submissions.verified\_by nullable

categories

    └── products

            └── order\_items

coupons

    └── orders.coupon\_id nullable

            + coupon\_code snapshot

            + discount snapshot

orders

    ├── order\_items

    ├── order\_histories

    └── payment\_submissions 0..1

payment\_methods

    └── payment\_submissions

\`\`\`

Universal application persistence rule:

\`\`\`text

Every application-owned Eloquent model
→ uses SoftDeletes
→ has deleted_at
→ has an authorized Trash view / Restore flow

\`\`\`

Historical models remain protected from normal workflow deletion.

Critical P0 database invariants:

\`\`\`text

Guest Order:

orders.user\_id may be NULL

Authenticated Order:

orders.user\_id references Customer User

Every Order:

buyer/shipping snapshot is stored

Every OrderItem:

product name/SKU/price snapshot is stored

Order → PaymentSubmission:

0..1 through UNIQUE(order\_id)

Transaction ID:

indexed but not UNIQUE while duplicate policy remains TBD

Historical children:

never cascade-destroyed

Cart:

Session only

Stock:

products.stock\_quantity

Payment:

submitted != verified

\`\`\`

\---

**# 77. Universal Application Model SoftDelete Contract**

Every application-owned ShopPilot model must implement:

\`\`\`php

use Illuminate\Database\Eloquent\SoftDeletes;

\`\`\`

Required models:

\`\`\`text

User

Category

Product

Coupon

PaymentMethod

Order

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

Corresponding application tables must contain:

\`\`\`text

deleted_at TIMESTAMP NULL

\`\`\`

**## Required Model Behavior**

Normal Eloquent queries exclude soft-deleted rows automatically.

Trash query:

\`\`\`php

Model::onlyTrashed()

\`\`\`

Restore:

\`\`\`php

$model = Model::onlyTrashed()->findOrFail($id);

$model->restore();

\`\`\`

Use `withTrashed()` only when an authorized administrative screen intentionally needs active + deleted rows.

**## Historical Safety**

For:

\`\`\`text

OrderItem

OrderHistory

PaymentSubmission

\`\`\`

SoftDelete exists because the project requires universal model-level Trash/Restore capability.

However these workflows must never delete those rows automatically:

\`\`\`text

Checkout

Payment verification

Order status transitions

Normal reporting

Normal Customer/Agent workflow

\`\`\`

\---

**# 78. Trash Blade View Contract**

Every application-owned model/module must have a Trash screen.

Recommended Blade files:

\`\`\`text

resources/views/backoffice/staff/trash.blade.php

resources/views/backoffice/customers/trash.blade.php

resources/views/backoffice/categories/trash.blade.php

resources/views/backoffice/products/trash.blade.php

resources/views/backoffice/coupons/trash.blade.php

resources/views/backoffice/payment-methods/trash.blade.php

resources/views/backoffice/orders/trash.blade.php

resources/views/backoffice/order-items/trash.blade.php

resources/views/backoffice/order-histories/trash.blade.php

resources/views/backoffice/payments/trash.blade.php

\`\`\`

`User` is one model used by Staff and Customer contexts, so separate Staff/Customer Trash screens may filter the same `users` table by role/context.

**## Trash Blade Minimum UI**

Each `trash.blade.php` should contain:

\`\`\`text

Page title

Trashed item list/table

Relevant identifier/name

Deleted At

Search/filter where useful

Restore action

Back to active list

Empty Trash state

Pagination where useful

\`\`\`

Example:

\`\`\`blade

<form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
    @csrf
    @method('PATCH')

    <button type="submit">
        Restore
    </button>
</form>

\`\`\`

Do not add a permanent-delete button in P0 by default.

**## Query Rule**

Trash pages must use:

\`\`\`php

Model::onlyTrashed()

\`\`\`

not `withTrashed()` unless the page intentionally displays both active and trashed records.

\---

**# 79. Trash Route / Restore Action Contract**

Recommended route pattern:

\`\`\`text

GET   /<module>/trash

PATCH /<module>/{id}/restore

\`\`\`

Example:

\`\`\`php

Route::get('/products/trash', [ProductController::class, 'trash'])
    ->name('products.trash');

Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])
    ->name('products.restore');

\`\`\`

Controller pattern:

\`\`\`php

public function trash()
{
    $items = Product::onlyTrashed()
        ->latest('deleted_at')
        ->paginate();

    return view('backoffice.products.trash', compact('items'));
}

public function restore(int $id)
{
    $product = Product::onlyTrashed()->findOrFail($id);

    $this->authorize('restore', $product);

    $product->restore();

    return redirect()
        ->route('admin.products.trash')
        ->with('success', 'Product restored successfully.');
}

\`\`\`

**## Authorization Rule**

Trash/Restore is never public.

Use:

\`\`\`text

Authentication
→ Permission / Role
→ Policy
→ Restore

\`\`\`

Existing explicit restore permissions:

\`\`\`text

categories.restore

products.restore

orders.restore

\`\`\`

For modules without a documented dedicated restore permission:

\`\`\`text

Admin-only restore

\`\`\`

until the permission catalogue is intentionally expanded.

**## Force Delete**

P0 rule:

\`\`\`text

No public/backoffice force-delete route.

No permanent-delete button.

No forceDelete() workflow by default.

\`\`\`

This keeps Trash recoverable and protects historical data.

\---

**# 80. Next Documentation**

Next document:

\`\`\`text

09-APPLICATION-FLOW\.md

\`\`\`

Then:

\`\`\`text

10-FOLDER-STRUCTURE.md

AGENTS.md

README.md

\`\`\`

> **Documentation Sync Note:** This revision changes SoftDelete scope from selected models to every application-owned model. `10-FOLDER-STRUCTURE.md` and `AGENTS.md` should be updated next so their Trash/Restore conventions match this schema.