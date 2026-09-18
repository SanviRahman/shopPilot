# ShopPilot E-commerce

> Role-Based Single-Store E-commerce & Order Operations System built as a Laravel full-stack practice project.

ShopPilot is a medium-size, single-store e-commerce application designed to practice a realistic Laravel workflow without turning the project into an enterprise commerce platform. The approved MVP supports both **Guest Customers** and **logged-in Customers**, staff-side role/permission control, product and stock management, session-based cart, coupons, manual bKash/Nagad/Rocket payment submission, payment review, agent assignment, controlled order processing, historical snapshots, and critical feature tests.

> **Project status:** Documentation baseline is complete. Implementation status should be updated as code is added. This README describes the approved target behavior and developer workflow; it does not claim that every feature is already implemented.

---

## Table of Contents

1. [Project Goals](#project-goals)
2. [Core Stack](#core-stack)
3. [Architecture](#architecture)
4. [Core User Flow](#core-user-flow)
5. [Actors and Roles](#actors-and-roles)
6. [Main Features](#main-features)
7. [Critical Business Invariants](#critical-business-invariants)
8. [Order and Payment Statuses](#order-and-payment-statuses)
9. [Manual Payment Model](#manual-payment-model)
10. [Checkout Transaction](#checkout-transaction)
11. [Database Overview](#database-overview)
12. [Folder Structure](#folder-structure)
13. [Route Surfaces](#route-surfaces)
14. [Requirements](#requirements)
15. [Installation](#installation)
16. [Environment Configuration](#environment-configuration)
17. [Database and Seed Data](#database-and-seed-data)
18. [Media Storage](#media-storage)
19. [Running the Application](#running-the-application)
20. [Testing](#testing)
21. [Authorization and Security](#authorization-and-security)
22. [Soft Deletes and Historical Data](#soft-deletes-and-historical-data)
23. [Console Command](#console-command)
24. [13-Day Delivery Plan](#13-day-delivery-plan)
25. [TBD / Intentionally Unresolved Rules](#tbd--intentionally-unresolved-rules)
26. [Out of Scope](#out-of-scope)
27. [Documentation](#documentation)
28. [AI Coding Agents](#ai-coding-agents)
29. [Implementation Definition of Done](#implementation-definition-of-done)
30. [Project Notes](#project-notes)

---

## Project Goals

ShopPilot is intended to be:

- professional enough to demonstrate realistic Laravel application flow;
- practice-oriented and finishable within a controlled scope;
- secure and server-authoritative for sensitive data;
- role- and permission-aware;
- testable;
- reusable through Services, Policies, Form Requests, Rules, Enums, Observers, Traits, and Console Commands;
- deliberately simpler than a marketplace or enterprise commerce platform.

The project should demonstrate practical Laravel skills around:

```text
Authentication
Spatie Laravel Permission
Spatie Laravel Media Library
Blade
CRUD
Services
Policies
Form Requests
Custom Rules
Enums
Observers
Traits
Console Commands
Session Cart
Coupon Validation
Checkout
Transactions
Manual Payment Submission
Payment Verification
Order Workflow
Agent Assignment
SoftDeletes
Feature Tests
```

---

## Core Stack

| Area | Approved Technology / Approach |
|---|---|
| Backend | PHP + Laravel |
| Frontend | Laravel Blade |
| Database | MySQL |
| ORM | Laravel Eloquent |
| RBAC | Spatie Laravel Permission |
| Media | Spatie Laravel Media Library |
| Cart | Laravel Session |
| Architecture | Service-Oriented Modular Laravel Monolith |
| Payment | Manual bKash / Nagad / Rocket Transaction ID submission |
| Queue | Optional / P1; not a core Checkout dependency |

### Version note

The current project documents do **not** pin exact PHP, Laravel, Node.js, Spatie package, or MySQL versions. Use the versions declared by the actual application scaffold and `composer.json` / `package.json` once implementation exists.

---

## Architecture

ShopPilot uses a **Service-Oriented Modular Laravel Monolith**.

```text
Blade / HTTP Request
        ↓
Routes / Middleware
        ↓
Controller
        ↓
Form Request + Permission + Policy
        ↓
Service
        ↓
Custom Rule / Enum / Eloquent Model
        ↓
MySQL / Session / Package-managed persistence
        ↓
Response / Blade
```

Core principles:

- Controllers stay thin.
- Blade stays presentation-focused.
- Services own reusable business workflows.
- Form Requests validate request input.
- Policies enforce resource ownership.
- Spatie permissions control operational capability.
- Custom Rules contain focused reusable validation.
- Enums define controlled status values.
- Eloquent is the primary persistence abstraction.
- Checkout critical writes are transaction-safe.
- Observers remain lightweight.
- Traits contain genuinely reusable behavior only.
- Queue is optional and cannot be required for core Checkout.
- Repository, CQRS, microservice, DDD package, or event-sourcing layers are not required for P0.

---

## Core User Flow

```text
Guest / Logged-in Customer
        ↓
Home / Shop / Search
        ↓
Category / Product Details
        ↓
Session Cart
        ↓
Coupon (optional)
        ↓
Checkout
        ↓
Resolve Buyer Context
        ├── Guest
        └── Authenticated Customer
        ↓
Validate Cart / Stock / Coupon / Server Price / Buyer Data
        ↓
Choose Active bKash / Nagad / Rocket Method
        ↓
View Manual Payment Instruction
        ↓
Pay Externally
        ↓
Submit Transaction ID
        ↓
Atomic Checkout Transaction
        ↓
Order + Order Items + Payment Submission + Stock Deduction + Initial History
        ↓
COMMIT
        ↓
Clear Session Cart
        ↓
Thank You
        ↓
Admin / Manager Payment Review
        ↓
Agent Assignment
        ↓
Order Processing
        ↓
Delivery
```

---

## Actors and Roles

### Authenticated roles

```text
Admin
Manager
Agent
Customer
```

### Public actor

```text
Guest Customer / Visitor
```

Guest is **not** a Spatie role.

| Actor | Main Responsibility |
|---|---|
| Admin | Full approved store administration and sensitive controls |
| Manager | Permission-driven operational management |
| Agent | Assigned-order processing |
| Customer | Shopping, account, Checkout, own Orders |
| Guest | Public shopping and Checkout without login |
| System | Commands, observers, scheduler, optional queue jobs |

### Authorization model

```text
Authentication
    ↓
Role / Permission
    ↓
Policy / Resource Ownership
    ↓
Business Rule
    ↓
Validation
    ↓
Service / Action
```

Frontend visibility is never the final security boundary.

---

## Main Features

### Authentication

P0 includes:

- Customer registration
- Login
- Logout
- Role-aware access
- Staff route protection
- Customer account protection

P1 may include:

- Forgot Password
- Email Verification

### Storefront

- Home
- Shop
- Single-level Category browsing
- Product details
- Search
- Featured / latest / sale product presentation where implemented

### Product and Category

- Category CRUD
- Product CRUD
- Product status
- SKU / slug management
- Product thumbnail and gallery
- Category image
- Simple stock through `products.stock_quantity`

Complex product variants are not part of P0.

### Cart

Session-based Cart supports:

- Add
- Update quantity
- Remove
- Clear
- Subtotal
- Coupon discount
- Grand total

There are no P0 `carts` or `cart_items` database tables.

### Coupon

P0 supports:

- fixed discount
- percentage discount
- one coupon per Order
- no stacking
- start/end date validation
- minimum Order amount
- active/inactive/expired state
- server-side discount calculation

### Checkout

Both Guest and authenticated Customer can Checkout.

Required buyer/shipping information includes:

```text
name
phone
email
address
city_or_area
```

`order_note` may be optional.

### Orders

- Order creation
- Buyer snapshot
- Product snapshot
- Order item lines
- Order history
- Admin/Manager management
- Agent assignment
- controlled status transitions
- Customer My Orders
- Customer own Order details

### Dashboards

Role-specific dashboards are part of the P0 design:

```text
Admin
Manager
Agent
Customer account context
```

Dashboard data should be read through the approved query/service boundary and must respect actor scope.

---

## Critical Business Invariants

These rules are non-negotiable unless the approved project specification is explicitly changed.

### Guest Checkout

Guest must be able to purchase without registration or login.

```text
Guest Order → orders.user_id = null
```

is valid.

### Authenticated Checkout

Logged-in Customer also uses the same core Checkout workflow.

```text
Authenticated Order → orders.user_id = authenticated customer id
```

### Buyer snapshot

Every Order preserves final buyer/contact/shipping snapshots, including authenticated purchases.

### Product snapshot

Every Order Item preserves purchase-time commercial data such as:

```text
product_name
sku
unit_price
quantity
line_total
```

### Server authority

The browser is never authoritative for:

```text
Price
Discount
Stock
Payment Status
Order Status
Role
Permission
Agent Assignment
Resource Ownership
```

### Customer ownership

```text
order.user_id == auth()->id()
```

Customer must not access another Customer's Order.

### Agent ownership

```text
order.assigned_agent_id == auth()->id()
```

Agent must not process another Agent's Order by default.

### Internal notes

Staff-only internal notes must never be exposed to Customer or Guest UI.

---

## Order and Payment Statuses

### Order Status

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Normal forward path:

```text
pending
  ↓
confirmed
  ↓
processing
  ↓
shipped
  ↓
delivered
```

Approved cancellation sources:

```text
pending / confirmed / processing
            ↓
        cancelled
```

Invalid transitions must be rejected without mutating state.

### Payment Status

```text
unpaid
submitted
verified
rejected
```

Keep Order Status and Payment Status separate.

---

## Manual Payment Model

P0 uses manual Mobile Financial Service payment submission:

```text
bKash
Nagad
Rocket
```

Authorized staff configure active Payment Methods and instructions.

Customer/Guest:

1. selects an active method;
2. views the configured number/instruction;
3. pays outside ShopPilot;
4. submits the Transaction ID;
5. places the Order.

Initial payment state:

```text
PaymentSubmission.status = submitted
Order.payment_status      = submitted
verified_by               = null
verified_at               = null
```

Critical rule:

```text
submitted != verified
```

Transaction ID format validation is **not** independent payment verification.

Admin can verify/reject. Manager can verify by default when `payments.verify` is granted. Manager rejection requires explicit `payments.reject`. Agent, Customer, and Guest do not verify/reject Payments.

The Thank You page confirms successful Order/payment-information submission. It must not falsely state that the MFS provider has verified the payment.

---

## Checkout Transaction

Critical persistent Checkout writes are designed as one atomic transaction:

```text
BEGIN TRANSACTION

1. INSERT orders
2. INSERT order_items
3. INSERT payment_submissions
4. UPDATE products.stock_quantity
5. INSERT initial order_histories

COMMIT
```

If a required write fails:

```text
ROLLBACK
```

Rules:

- stock is revalidated server-side;
- insufficient stock blocks Order creation;
- authoritative prices/totals are calculated on the server;
- Cart is not cleared before a successful commit;
- no partial Checkout success should remain after rollback.

---

## Database Overview

### Application-owned P0 tables

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

### Spatie Permission package tables

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

### Spatie Media Library

```text
media
```

### Framework/configuration-dependent tables

```text
sessions
password_reset_tokens
notifications
```

### Optional P1 tables

```text
addresses
activity_logs
```

### Important schema decisions

- `orders.user_id` is nullable for Guest Checkout.
- `orders.assigned_agent_id` is nullable until assignment.
- Order buyer/shipping snapshots are persisted.
- Order Items preserve commercial snapshots.
- P0 uses `Order hasOne PaymentSubmission`.
- `payment_submissions.order_id` is unique under the approved P0 schema.
- `transaction_id` is not globally unique because duplicate Transaction ID policy remains unresolved.
- historical Order data must not be cascade-deleted.
- authoritative money uses `DECIMAL(12,2)`.
- application statuses are stored as strings and mapped to PHP Enums.

---

## Folder Structure

Target application structure:

```text
app/
├── Console/Commands/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Storefront/
│   │   ├── Customer/
│   │   ├── Backoffice/
│   │   │   ├── Admin/
│   │   │   └── Manager/
│   │   └── Agent/
│   └── Requests/
├── Models/
├── Observers/
├── Policies/
├── Rules/
├── Services/
└── Traits/
```

Approved core Services:

```text
ProductService
StockService
CartService
CouponService
CheckoutService
OrderService
OrderAssignmentService
OrderStatusService
PaymentService
DashboardService
```

Do not add a Repository layer merely for pattern count.

---

## Route Surfaces

Recommended route files:

```text
routes/web.php
routes/storefront.php
routes/customer.php
routes/admin.php
routes/manager.php
routes/agent.php
routes/auth.php
routes/console.php
```

Recommended URI groups:

| Surface | URI Prefix | Route Name Prefix |
|---|---|---|
| Public Storefront | none | module-based |
| Customer | `/account` | `customer.` |
| Admin | `/admin` | `admin.` |
| Manager | `/manager` | `manager.` |
| Agent | `/agent` | `agent.` |

Guest Checkout belongs to the public Storefront flow and must not require authentication.

---

## Requirements

The documentation defines the technology choices but does not pin exact software versions.

Expected development requirements for a standard Laravel implementation are:

- PHP compatible with the selected Laravel version
- Composer
- MySQL
- Node.js and npm (or the package manager selected by the Laravel scaffold)
- required PHP extensions for the selected Laravel/Spatie package versions

Exact versions must come from the implemented repository's:

```text
composer.json
composer.lock
package.json
package-lock.json / equivalent
```

---

## Installation

The commands below describe the expected **standard Laravel setup flow**. Adjust only where the actual generated Laravel scaffold or installed package version requires it.

### 1. Clone the repository

```bash
git clone <repository-url>
cd shoppilot
```

If the repository directory uses a different name, use that actual directory name.

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Create the environment file

```bash
cp .env.example .env
```

On Windows without a Unix-like shell, copy `.env.example` to `.env` using the available file command or editor.

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Configure the database

Update `.env` with the local MySQL connection values.

Example key names:

```text
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Do not copy example credentials into production blindly.

### 6. Install frontend dependencies

```bash
npm install
```

### 7. Package migrations/configuration

ShopPilot uses:

```text
spatie/laravel-permission
spatie/laravel-medialibrary
```

Use the publish/install/migration commands required by the **installed package versions**. Do not duplicate package-owned RBAC or `media` tables with custom migrations.

### 8. Run migrations and seeders

When the migrations and seeders have been implemented:

```bash
php artisan migrate --seed
```

### 9. Create the public storage link when the selected media filesystem requires it

```bash
php artisan storage:link
```

This is conditional on the configured filesystem/storage strategy.

### 10. Build or run frontend assets

For development:

```bash
npm run dev
```

For a production build, use the production build command defined by the actual `package.json`.

---

## Environment Configuration

`.env` is for environment-specific values such as:

```text
APP_KEY
Database credentials
Mail configuration if used
Queue configuration if P1 Queue is used
Filesystem configuration
```

Rules:

- do not commit operational secrets;
- keep safe placeholders in `.env.example`;
- MFS account numbers are application data managed through `payment_methods`, not values that must be hard-coded in `.env`;
- production payment numbers should be managed by authorized backoffice users.

---

## Database and Seed Data

Recommended P0 seeders:

```text
DatabaseSeeder
RolePermissionSeeder
AdminUserSeeder
PaymentMethodSeeder
```

### Roles

Seed:

```text
Admin
Manager
Agent
Customer
```

Do **not** seed:

```text
Guest
```

as a Spatie role.

### Permissions

Seed the approved granular `module.action` permission catalogue.

Examples:

```text
dashboard.view
products.view
products.create
products.update
stock.view
stock.update
orders.view
orders.update
orders.assign
payments.view
payments.verify
payments.reject
payment-methods.manage
roles.manage
permissions.manage
```

### Payment Methods

Initial development/test rows may include:

```text
bkash
nagad
rocket
```

Use safe development/test numbers or instructions in seed data. Real operational values should be configured from the authorized backoffice.

### Admin user

The docs require a development/admin bootstrap user, but do not define a canonical username/password. Do not hard-code insecure production credentials. Document the actual local bootstrap strategy after implementation.

---

## Media Storage

Product and Category media use **Spatie Laravel Media Library**.

Approved/recommended collections include:

```text
product_thumbnail
product_gallery
category_image
user_avatar   # optional / where implemented
```

Do not introduce custom `product_images` / `category_images` persistence for P0.

Media storage follows the configured Laravel filesystem and Media Library setup.

---

## Running the Application

Typical local development processes:

### Laravel server

```bash
php artisan serve
```

### Frontend assets

```bash
npm run dev
```

Run MySQL through the local development environment of choice.

If the application is deployed through a local web server, container, Laravel-specific development environment, or another runtime, follow that environment's normal Laravel serving procedure.

---

## Testing

Critical business behavior should be protected primarily with Feature Tests.

Run the full Laravel test suite with the command supported by the implemented Laravel scaffold, normally:

```bash
php artisan test
```

Critical scenarios include:

- Guest Checkout without login
- Guest Order with `user_id = null`
- authenticated Customer Checkout
- buyer snapshot persistence
- Product/price authority
- Stock validation
- Coupon validation
- insufficient Stock blocking Order creation
- Checkout rollback
- Cart clearing only after commit
- manual Payment Submission
- `submitted != verified`
- Payment verification authorization
- Customer own-Order protection
- Agent assigned-Order protection
- cross-Agent denial
- Agent assignment
- valid Order transitions
- invalid transition rejection
- Order History creation
- Internal Note protection
- SoftDelete visibility
- Restore behavior

Suggested test organization:

```text
tests/
├── Feature/
│   ├── Auth/
│   ├── Storefront/
│   ├── Cart/
│   ├── Coupon/
│   ├── Checkout/
│   ├── Authorization/
│   ├── Payment/
│   ├── Order/
│   └── SoftDelete/
└── Unit/
    ├── Services/
    └── Rules/
```

---

## Authorization and Security

### Permission naming

```text
module.action
```

Examples:

```text
products.view
products.create
orders.view
orders.update
orders.assign
payments.verify
payments.reject
```

### Manager

Default Manager scope is explicit and operational. Sensitive RBAC/settings capability is not automatic.

### Agent

Agent permissions do not grant global Order access. `OrderPolicy` must enforce assigned ownership.

### Customer

Customer operational access is primarily through authenticated Customer routes + `OrderPolicy` own-Order rules.

### Guest

Guest has no Spatie role or permission.

### Security principles

Protect against:

```text
IDOR
Client price tampering
Client discount tampering
Client status tampering
Client role tampering
Mass assignment
Unauthorized payment verification
Cross-Agent Order access
Cross-Customer Order access
```

Never rely on Blade-only hiding for authorization.

---

## Soft Deletes and Historical Data

P0 SoftDelete-enabled entities/tables:

```text
users
categories
products
coupons
orders
payment_methods
```

Historical records that should not use normal SoftDelete behavior:

```text
order_items
order_histories
payment_submissions
```

Historical purchase facts must remain readable even if current Product/User/master data later changes.

Do not cascade-delete historical Order data.

---

## Console Command

Minimum approved P0 Console Command:

```text
coupons:expire
```

Recommended class:

```text
app/Console/Commands/ExpireCoupons.php
```

The command must follow the same business rules as normal application code.

---

## 13-Day Delivery Plan

The approved practice plan is:

| Day | Focus |
|---|---|
| 1 | Laravel setup, Authentication, database planning |
| 2 | Spatie Permission, roles, permissions, dashboards |
| 3 | Category CRUD, Media Library setup |
| 4 | Product CRUD, Stock, Product Media |
| 5 | Home, Shop, Category, Product Details, Search |
| 6 | Session Cart, Coupon |
| 7 | Guest Checkout, logged-in Checkout, MFS display |
| 8 | Buyer Snapshot, Order, Order Items, Payment Submission, Stock deduction, Thank You |
| 9 | Admin Orders, Payment Verification |
| 10 | Manager → Agent Assignment, reassignment |
| 11 | Agent workflow, Customer My Orders, Order Details |
| 12 | Services, Observer, Trait, Rules, Console, Policies, Requests, Enums, Security, Dashboard Stats |
| 13 | Critical Tests, bug fixes, UI polish, README, Git cleanup |

The sequence may be adjusted during implementation as long as dependencies and approved scope remain intact.

---

## TBD / Intentionally Unresolved Rules

The current specification deliberately leaves several behaviors unresolved. Do not implement a mandatory rule silently.

### Shipping calculation

The schema stores shipping, but the calculation strategy is not yet approved.

Unknown examples:

```text
Flat rate?
Area-based?
Free threshold?
```

### Stock restoration after cancellation

Automatic stock restoration is not yet approved.

### Payment rejection → Order status

Do not automatically cancel an Order merely because Payment is rejected unless a future rule explicitly requires it.

### Payment verification before fulfillment

No mandatory verification-before-fulfillment gate has been approved.

### Assignment timing

Do not assume Assignment must happen before or after Payment verification.

### Transaction ID uniqueness

Global uniqueness is not approved; do not add a DB unique constraint silently.

### Sale price activation

Do not invent scheduling fields or activation rules.

### Guest Order tracking

Future/P1 only. Any public tracking design must use secure verification rather than predictable Order IDs.

---

## Out of Scope

The current P0 MVP does not include:

```text
Multi-Vendor
Marketplace
Multi-Tenancy / Workspace model
Real bKash API
Real Nagad API
Real Rocket API
Automatic provider payment verification
Courier API
Complex Product Variants
Multi-Warehouse
Advanced Inventory Ledger
Automatic Refund Gateway
Multi-Currency
Multi-Language
Native Mobile App
Microservices
AI module
Accounting
Affiliate
Advanced Tax engine
```

The codebase should not grow speculative folders, migrations, permissions, services, or adapters for these areas.

---

## Documentation

Recommended repository documentation layout:

```text
docs/
├── 01-PROJECT-OVERVIEW-UPDATED.md
├── 02-PRD.md
├── 03-FEATURES.md
├── 04-USER-ROLES-AND-PERMISSIONS.md
├── 05-BUSINESS-RULES.md
├── 06-ARCHITECTURE.md
├── 07-DATABASE-ERD.md
├── 08-DATABASE-SCHEMA.md
├── 09-APPLICATION-FLOW.md
└── 10-FOLDER-STRUCTURE.md

AGENTS.md
README.md
```

### Document ownership

| Document | Primary Authority |
|---|---|
| Project Overview / PRD / Features | Product scope and required capabilities |
| User Roles & Permissions | Roles, permissions, access model |
| Business Rules | Allowed/denied behavior and invariants |
| Architecture | Layer ownership and technical boundaries |
| Database ERD | Logical entities and relationships |
| Database Schema | Physical columns, keys, constraints, migrations |
| Application Flow | Runtime sequence and failure flow |
| Folder Structure | Physical Laravel file placement |
| AGENTS.md | Coding-agent implementation guardrails |
| README.md | Developer entry point and project summary |

When documents appear to conflict, use the decision-type authority defined in `AGENTS.md`. A technical implementation must not silently override an approved Business Rule.

---

## AI Coding Agents

Before using Claude Code, Codex, ChatGPT coding agents, IDE agents, or another automated coding assistant, read:

```text
AGENTS.md
```

The agent must:

- read relevant documentation before core behavior changes;
- preserve Guest + authenticated Checkout;
- preserve `submitted != verified`;
- preserve server-authoritative price/stock/state/ownership;
- keep Controllers thin and Services responsible for workflows;
- enforce Permission + Policy authorization;
- respect schema/cardinality decisions;
- add/update critical tests;
- keep TBD behavior unresolved until explicitly approved;
- avoid out-of-scope enterprise architecture.

---

## Implementation Definition of Done

A feature/change is complete only when all applicable conditions are satisfied:

- requested behavior works;
- relevant Business Rules are preserved;
- authentication/permission/policy checks are correct;
- request validation is present;
- the approved Service/layer boundary is respected;
- schema constraints are respected;
- critical multi-write operations are transaction-safe;
- historical snapshots remain correct;
- failure/denial behavior is safe;
- critical tests are added/updated and pass;
- no unrelated P0 scope was added;
- no TBD was silently resolved;
- no prohibited architectural layer/module was introduced.

---

## Project Notes

### License

A project license is not specified in the current approved documentation. Add a license only when the project owner selects one.

### Production readiness

The design is production-minded for Laravel practice, but the project is intentionally scoped as a 13-day practice build. Before a real production launch, review the implemented application for environment-specific deployment, secrets, infrastructure, security hardening, backup/restore, monitoring, mail delivery, media storage, and operational requirements that are outside the current P0 specification.

---

## Documentation Status

```text
01-PROJECT-OVERVIEW-UPDATED.md ✅
02-PRD.md ✅
03-FEATURES.md ✅
04-USER-ROLES-AND-PERMISSIONS.md ✅
05-BUSINESS-RULES.md ✅
06-ARCHITECTURE.md ✅
07-DATABASE-ERD.md ✅
08-DATABASE-SCHEMA.md ✅
09-APPLICATION-FLOW.md ✅
10-FOLDER-STRUCTURE.md ✅
AGENTS.md ✅
README.md ✅
```

ShopPilot's documentation baseline is now ready to guide implementation.
