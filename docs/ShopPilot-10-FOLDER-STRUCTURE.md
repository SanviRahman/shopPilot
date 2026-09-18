# ShopPilot E-commerce — Folder Structure
# শপপাইলট ই-কমার্স — ফোল্ডার স্ট্রাকচার

> **Document:** Laravel Folder & File Structure / Laravel ফোল্ডার ও ফাইল স্ট্রাকচার  
> **Project:** ShopPilot E-commerce  
> **Source Documents:**  
> `01-PROJECT-OVERVIEW-UPDATED.md` v1.1  
> `ShopPilot-02-PRD.md` v1.0  
> `ShopPilot-03-FEATURES.md` v1.0  
> `ShopPilot-04-USER-ROLES-AND-PERMISSIONS.md` v1.0  
> `ShopPilot-05-BUSINESS-RULES.md` v1.0  
> `ShopPilot-06-ARCHITECTURE.md` v1.0  
> `ShopPilot-07-DATABASE-ERD.md` v1.0  
> `ShopPilot-08-DATABASE-SCHEMA.md` v1.0  
> `ShopPilot-09-APPLICATION-FLOW.md` v1.0  
> **Architecture:** Service-Oriented Modular Laravel Monolith  
> **Backend:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Implementation-Ready Folder & File Placement Definition  
> **Next Document:** `AGENTS.md`

---

# Table of Contents

1. Document Purpose  
2. Folder Structure Authority  
3. Structural Goals  
4. Structural Non-Goals  
5. Core Placement Principles  
6. Final Project Tree — P0  
7. Root-Level Files  
8. `app/` Overview  
9. Models  
10. Enums  
11. Services  
12. Policies  
13. Rules  
14. Observers  
15. Traits  
16. HTTP Layer Overview  
17. Controllers — Storefront  
18. Controllers — Customer  
19. Controllers — Backoffice  
20. Controllers — Agent  
21. Form Requests  
22. Middleware Boundary  
23. Console Commands  
24. Jobs / Queue Boundary  
25. Events / Listeners Boundary  
26. Notifications Boundary  
27. Providers Boundary  
28. `bootstrap/` Boundary  
29. `config/` Boundary  
30. `database/` Overview  
31. Migrations  
32. Seeders  
33. Factories  
34. `resources/` Overview  
35. Blade Layouts  
36. Blade Components  
37. Storefront Views  
38. Customer Views  
39. Backoffice Views  
40. Admin Dashboard Views  
41. Manager Dashboard Views  
42. Agent Views  
43. Error Views  
44. Assets  
45. `routes/` Overview  
46. Route File Decision  
47. `routes/web.php` Aggregator  
48. `routes/storefront.php`  
49. `routes/customer.php`  
50. `routes/admin.php`  
51. `routes/manager.php`  
52. `routes/agent.php`  
53. `routes/auth.php`  
54. Route Prefix / Name Convention  
55. Route Middleware Convention  
56. Route-to-Controller Mapping  
57. Controller-to-Service Mapping  
58. Request-to-Service Mapping  
59. Policy Mapping  
60. Service-to-Model Mapping  
61. Model-to-Table Mapping  
62. View-to-Route Mapping  
63. Checkout Placement  
64. Cart Placement  
65. Coupon Placement  
66. Product / Category Placement  
67. Stock Placement  
68. Payment Placement  
69. Order Placement  
70. Assignment Placement  
71. Dashboard Placement  
72. Authentication Placement  
73. Guest Checkout Placement  
74. Customer Ownership Placement  
75. Agent Ownership Placement  
76. SoftDelete / Restore Placement  
77. Media Library Placement  
78. RBAC Placement  
79. Console / Scheduler Placement  
80. Test Structure  
81. Feature Test Folders  
82. Unit Test Folders  
83. Test Naming Convention  
84. Test-to-Requirement Mapping  
85. Documentation Folder  
86. Storage Boundary  
87. Public Boundary  
88. Environment Boundary  
89. Naming Conventions  
90. Namespace Conventions  
91. Class Responsibility Rules  
92. Dependency Rules  
93. Folder Creation Rules  
94. Files Not to Create for P0  
95. P1 / Future Folder Additions  
96. Folder Structure Anti-Patterns  
97. Implementation Creation Order  
98. Folder Structure Checklist  
99. Definition of Folder Structure Complete  
100. Final Folder Structure Summary  
101. Next Documentation

---

# 1. Document Purpose

This document defines the **final recommended Laravel folder and file placement** for ShopPilot.

আগের documents define করেছে:

```text
What the product does
Who can do what
Business rules
Architecture
Database model
Physical schema
Runtime flow
```

এই document define করবে:

```text
Which Laravel folder owns which responsibility
Which class should live where
Which route file owns which surface
Which Blade directory owns which UI
Which tests live where
Which files must NOT be introduced
```

This document is implementation-oriented.

It is intentionally:

```text
Laravel-native
Simple
Modular
Service-oriented
13-day-project friendly
Not enterprise-overengineered
```

---

# 2. Folder Structure Authority

Folder placement follows this precedence:

```text
05-BUSINESS-RULES.md
        ↓
06-ARCHITECTURE.md
        ↓
07-DATABASE-ERD.md
        ↓
08-DATABASE-SCHEMA.md
        ↓
09-APPLICATION-FLOW.md
        ↓
10-FOLDER-STRUCTURE.md
```

If folder placement conflicts with approved behavior:

```text
Business Rule wins.
```

If a folder would introduce a new business module:

```text
Do not create it merely for architectural appearance.
```

---

# 3. Structural Goals

The final structure must make these responsibilities obvious:

```text
Controllers
→ HTTP coordination

Form Requests
→ Input validation

Policies / Permissions
→ Authorization

Rules
→ Focused reusable validation

Services
→ Business workflows

Models
→ Eloquent domain/persistence representation

Enums
→ Status/value definitions

Observers
→ Lightweight lifecycle behavior

Traits
→ Focused reusable model behavior

Commands
→ CLI/scheduled entry points

Blade
→ Presentation

Tests
→ Business behavior verification
```

---

# 4. Structural Non-Goals

Do not introduce for P0:

```text
Repositories
Repository Interfaces
Domain-Driven Design bounded contexts
Microservice folders
CQRS
Command Bus architecture
Event sourcing
Saga orchestration
Separate application/domain/infrastructure packages
API gateway layer
Warehouse module
Inventory ledger module
Payment gateway adapters
Courier adapters
Refund engine
Multi-tenant workspace structure
Multi-vendor structure
```

Reason:

```text
Not required by approved MVP.
```

---

# 5. Core Placement Principles

## Principle 1 — Thin Controllers

Controllers should:

```text
Receive request
Authorize
Use validated data
Call Service
Return View / Redirect
```

Controllers should not own:

```text
Full Checkout transaction
Stock orchestration
Coupon engine
Payment verification workflow
Order transition workflow
```

---

## Principle 2 — Services Own Workflows

Approved Services:

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

---

## Principle 3 — Policies Own Resource Scope

Recommended Policies:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

---

## Principle 4 — Rules Stay Focused

Approved reusable Rules:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

---

## Principle 5 — Enums Own Status Values

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

Enums do not own transition workflows.

---

## Principle 6 — Blade Is Presentation

Blade may:

```text
Render
Loop
Format
Show validation errors
Show flash messages
Use @can / @role for visibility
```

Blade must not become authoritative for:

```text
Price
Stock
Discount
Order status
Payment status
Authorization
```

---

# 6. Final Project Tree — P0

Recommended final P0 structure:

```text
shoppilot/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ExpireCoupons.php
│   │
│   ├── Enums/
│   │   ├── CouponStatus.php
│   │   ├── OrderStatus.php
│   │   ├── PaymentMethodStatus.php
│   │   ├── PaymentStatus.php
│   │   └── ProductStatus.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Storefront/
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── ShopController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── SearchController.php
│   │   │   │   ├── CartController.php
│   │   │   │   └── CheckoutController.php
│   │   │   │
│   │   │   ├── Customer/
│   │   │   │   ├── ProfileController.php
│   │   │   │   └── OrderController.php
│   │   │   │
│   │   │   ├── Backoffice/
│   │   │   │   ├── Admin/
│   │   │   │   │   └── DashboardController.php
│   │   │   │   ├── Manager/
│   │   │   │   │   └── DashboardController.php
│   │   │   │   ├── StaffController.php
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── PermissionController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── StockController.php
│   │   │   │   ├── CouponController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── OrderAssignmentController.php
│   │   │   │   ├── OrderStatusController.php
│   │   │   │   ├── PaymentMethodController.php
│   │   │   │   └── PaymentSubmissionController.php
│   │   │   │
│   │   │   └── Agent/
│   │   │       ├── DashboardController.php
│   │   │       └── OrderController.php
│   │   │
│   │   └── Requests/
│   │       ├── Category/
│   │       │   ├── StoreCategoryRequest.php
│   │       │   └── UpdateCategoryRequest.php
│   │       ├── Product/
│   │       │   ├── StoreProductRequest.php
│   │       │   └── UpdateProductRequest.php
│   │       ├── Coupon/
│   │       │   └── StoreCouponRequest.php
│   │       ├── Checkout/
│   │       │   └── CheckoutRequest.php
│   │       ├── Order/
│   │       │   ├── AssignOrderRequest.php
│   │       │   └── UpdateOrderStatusRequest.php
│   │       └── Payment/
│   │           └── VerifyPaymentRequest.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Coupon.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── OrderHistory.php
│   │   ├── PaymentMethod.php
│   │   └── PaymentSubmission.php
│   │
│   ├── Observers/
│   │   ├── ProductObserver.php
│   │   └── OrderObserver.php
│   │
│   ├── Policies/
│   │   ├── ProductPolicy.php
│   │   ├── OrderPolicy.php
│   │   ├── StaffPolicy.php
│   │   ├── CouponPolicy.php
│   │   ├── PaymentMethodPolicy.php
│   │   └── PaymentSubmissionPolicy.php
│   │
│   ├── Rules/
│   │   ├── ValidCoupon.php
│   │   ├── SufficientStock.php
│   │   ├── ValidManualTransactionId.php
│   │   └── ValidOrderStatusTransition.php
│   │
│   ├── Services/
│   │   ├── ProductService.php
│   │   ├── StockService.php
│   │   ├── CartService.php
│   │   ├── CouponService.php
│   │   ├── CheckoutService.php
│   │   ├── OrderService.php
│   │   ├── OrderAssignmentService.php
│   │   ├── OrderStatusService.php
│   │   ├── PaymentService.php
│   │   └── DashboardService.php
│   │
│   └── Traits/
│       ├── HasSlug.php
│       ├── HasOrderNumber.php
│       └── HasActiveScope.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── permission.php
│   └── media-library.php
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── CategoryFactory.php
│   │   ├── ProductFactory.php
│   │   ├── CouponFactory.php
│   │   └── OrderFactory.php
│   │
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   ├── *_create_permission_tables.php
│   │   ├── *_create_media_table.php
│   │   ├── *_create_categories_table.php
│   │   ├── *_create_products_table.php
│   │   ├── *_create_coupons_table.php
│   │   ├── *_create_payment_methods_table.php
│   │   ├── *_create_orders_table.php
│   │   ├── *_create_order_items_table.php
│   │   ├── *_create_order_histories_table.php
│   │   └── *_create_payment_submissions_table.php
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php
│       ├── AdminUserSeeder.php
│       └── PaymentMethodSeeder.php
│
├── public/
│   ├── index.php
│   └── build/
│
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── layouts/
│       │   ├── storefront.blade.php
│       │   ├── customer.blade.php
│       │   ├── backoffice.blade.php
│       │   └── guest.blade.php
│       │
│       ├── components/
│       │   ├── storefront/
│       │   ├── customer/
│       │   ├── backoffice/
│       │   └── common/
│       │
│       ├── storefront/
│       │   ├── home.blade.php
│       │   ├── shop/
│       │   │   └── index.blade.php
│       │   ├── products/
│       │   │   └── show.blade.php
│       │   ├── search/
│       │   │   └── index.blade.php
│       │   ├── cart/
│       │   │   └── index.blade.php
│       │   └── checkout/
│       │       ├── index.blade.php
│       │       └── thank-you.blade.php
│       │
│       ├── customer/
│       │   ├── profile/
│       │   │   └── edit.blade.php
│       │   └── orders/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       │
│       ├── backoffice/
│       │   ├── admin/
│       │   │   └── dashboard.blade.php
│       │   ├── manager/
│       │   │   └── dashboard.blade.php
│       │   ├── staff/
│       │   ├── roles/
│       │   ├── permissions/
│       │   ├── customers/
│       │   ├── categories/
│       │   ├── products/
│       │   ├── stock/
│       │   ├── coupons/
│       │   ├── orders/
│       │   ├── payment-methods/
│       │   └── payments/
│       │
│       ├── agent/
│       │   ├── dashboard.blade.php
│       │   └── orders/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       │
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       │
│       └── errors/
│           ├── 403.blade.php
│           └── 404.blade.php
│
├── routes/
│   ├── web.php
│   ├── storefront.php
│   ├── customer.php
│   ├── admin.php
│   ├── manager.php
│   ├── agent.php
│   ├── auth.php
│   └── console.php
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Storefront/
│   │   ├── Cart/
│   │   ├── Coupon/
│   │   ├── Checkout/
│   │   ├── Authorization/
│   │   ├── Payment/
│   │   ├── Order/
│   │   └── SoftDelete/
│   └── Unit/
│       ├── Services/
│       └── Rules/
│
├── .env
├── .env.example
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── AGENTS.md
└── README.md
```

This is the **recommended target tree**, not a requirement to create every empty folder on Day 1.

Create folders progressively as features are implemented.

---

# 7. Root-Level Files

Important root files:

```text
artisan
composer.json
composer.lock
package.json
package-lock.json / equivalent
phpunit.xml
vite.config.js
.env
.env.example
README.md
AGENTS.md
```

Documentation files may stay at root or inside a dedicated `docs/` folder.

For this project, keeping planning docs together under:

```text
docs/
```

is recommended once implementation begins.

See Section 85.

---

# 8. `app/` Overview

`app/` contains ShopPilot application code.

Recommended domain/application placement:

```text
app/
├── Console/
├── Enums/
├── Http/
├── Models/
├── Observers/
├── Policies/
├── Rules/
├── Services/
└── Traits/
```

Do not create:

```text
app/Repositories/
app/Domain/
app/Application/
app/Infrastructure/
```

for pattern-count purposes.

---

# 9. Models

Path:

```text
app/Models/
```

P0 models:

```text
User.php
Category.php
Product.php
Coupon.php
Order.php
OrderItem.php
OrderHistory.php
PaymentMethod.php
PaymentSubmission.php
```

Responsibilities:

```text
Eloquent table mapping
Relationships
Casts
SoftDeletes where approved
Media Library interfaces/traits where required
Simple scopes
Small model helpers
```

Models should not contain:

```text
Full Checkout orchestration
Payment verification workflow
Agent assignment workflow
Complex state machine orchestration
```

Those belong to Services.

---

# 10. Enums

Path:

```text
app/Enums/
```

Files:

```text
ProductStatus.php
OrderStatus.php
PaymentStatus.php
CouponStatus.php
PaymentMethodStatus.php
```

Recommended namespaces:

```php
namespace App\Enums;
```

Use Enums for:

```text
Allowed values
Central naming
Model casts
Comparisons
Validation support
```

Do not place order transition logic inside `OrderStatus` Enum unless the approved design is later changed deliberately.

Current transition logic belongs to:

```text
OrderStatusService
+
ValidOrderStatusTransition
```

---

# 11. Services

Path:

```text
app/Services/
```

P0:

```text
ProductService.php
StockService.php
CartService.php
CouponService.php
CheckoutService.php
OrderService.php
OrderAssignmentService.php
OrderStatusService.php
PaymentService.php
DashboardService.php
```

## ProductService

Owns reusable Product business operations.

Does not own raw HTML response logic.

---

## StockService

Owns:

```text
Check stock
Validate stock business logic
Deduct stock
Authorized manual stock update
```

No inventory ledger.

---

## CartService

Owns:

```text
Session Cart read
Add
Update
Remove
Clear
Subtotal calculation
Cart-level state
```

Cart is session-based.

---

## CouponService

Owns:

```text
Coupon lookup
Validity checks
Minimum amount checks
Discount calculation
Applied coupon handling
```

Focused validation may delegate to `ValidCoupon`.

---

## CheckoutService

Most important orchestration Service.

Owns:

```text
Resolve buyer context
Validate/revalidate checkout state
Coordinate Cart
Coordinate Stock
Coordinate Coupon
Calculate server totals
Persist buyer snapshots
Create Order
Create OrderItems
Create PaymentSubmission
Deduct Stock
Create initial OrderHistory
Use DB transaction
Return committed Order
```

Must not be moved into `CheckoutController`.

---

## OrderService

Owns general Order operations that are not assignment/status/payment-specific.

---

## OrderAssignmentService

Owns:

```text
Validate assignment target context
Assign Agent
Reassign where authorized
Create assignment history
```

---

## OrderStatusService

Owns:

```text
Validate transition
Update order_status
Create OrderHistory
```

---

## PaymentService

Owns:

```text
Manual payment submission state handling
Verify PaymentSubmission
Reject PaymentSubmission
Sync Order.payment_status
Set verifier
Set verified_at
Store rejection note
Create history
```

---

## DashboardService

Owns:

```text
Admin dashboard metrics
Manager dashboard metrics
Agent dashboard metrics
Customer summary metrics if required
```

No mutations.

---

# 12. Policies

Path:

```text
app/Policies/
```

Recommended:

```text
ProductPolicy.php
OrderPolicy.php
StaffPolicy.php
CouponPolicy.php
PaymentMethodPolicy.php
PaymentSubmissionPolicy.php
```

## OrderPolicy

Critical rules:

```text
Customer → own Order only
Agent → assigned Order only
Staff → permission/resource checks
```

## PaymentSubmissionPolicy

Protect:

```text
View
Verify
Reject
```

## PaymentMethodPolicy

Protect sensitive MFS configuration.

Policies answer:

```text
Can this actor perform this action on this exact resource?
```

Permissions answer:

```text
Can this actor perform this class of action?
```

Both may be required.

---

# 13. Rules

Path:

```text
app/Rules/
```

P0:

```text
ValidCoupon.php
SufficientStock.php
ValidManualTransactionId.php
ValidOrderStatusTransition.php
```

Rules must be:

```text
Focused
Reusable
Small
Side-effect free where practical
```

Do not put:

```text
DB transaction
Order creation
Payment verification
Agent assignment
```

inside Custom Rule classes.

---

# 14. Observers

Path:

```text
app/Observers/
```

Possible P0 observers:

```text
ProductObserver.php
OrderObserver.php
```

`UserObserver.php` may be added only if a meaningful lightweight User lifecycle concern actually exists.

Allowed:

```text
Lightweight lifecycle bookkeeping
Small slug preparation
Dispatch local event
```

Forbidden:

```text
Full Checkout
Payment verification
Order state orchestration
Complex stock mutation
```

Do not create Observer merely because the project checklist says “Observer”.

At least one Observer should have a real purpose.

---

# 15. Traits

Path:

```text
app/Traits/
```

Recommended:

```text
HasSlug.php
HasOrderNumber.php
HasActiveScope.php
```

Potential usage:

```text
Category / Product
→ HasSlug

Order
→ HasOrderNumber

Product / Coupon / PaymentMethod
→ HasActiveScope where behavior is genuinely shared
```

Trait rules:

```text
Reusable
Focused
Predictable
No hidden orchestration
```

---

# 16. HTTP Layer Overview

Primary path:

```text
app/Http/
```

Use:

```text
Controllers/
Requests/
Middleware/ only when custom middleware is genuinely required
```

Authorization is mainly:

```text
Laravel auth middleware
Spatie role/permission middleware
Policies
Business Rules
```

---

# 17. Controllers — Storefront

Path:

```text
app/Http/Controllers/Storefront/
```

Recommended:

```text
HomeController.php
ShopController.php
ProductController.php
SearchController.php
CartController.php
CheckoutController.php
```

## HomeController

Read-only home content.

---

## ShopController

Product listing/category browsing coordination.

---

## ProductController

Public Product detail.

---

## SearchController

Search/filter request coordination.

---

## CartController

Thin Cart HTTP layer:

```text
index
store/add
update
destroy/remove
clear
coupon application may call CouponService
```

Do not calculate authoritative pricing in Blade.

---

## CheckoutController

Recommended actions:

```text
create
store
thankYou
```

Responsibilities:

```text
Use CheckoutRequest
Call CheckoutService
Redirect after commit
Render Thank You
```

No transaction orchestration inside Controller.

---

# 18. Controllers — Customer

Path:

```text
app/Http/Controllers/Customer/
```

Files:

```text
ProfileController.php
OrderController.php
```

## ProfileController

Own Customer profile UI/actions.

---

## OrderController

Own:

```text
My Orders list
Own Order details
```

Must authorize through:

```text
Authentication
+
OrderPolicy
```

It must never expose:

```text
Other Customer Order
Internal Note
Staff controls
```

---

# 19. Controllers — Backoffice

Path:

```text
app/Http/Controllers/Backoffice/
```

Shared backoffice operational controllers:

```text
StaffController.php
RoleController.php
PermissionController.php
CustomerController.php
CategoryController.php
ProductController.php
StockController.php
CouponController.php
OrderController.php
OrderAssignmentController.php
OrderStatusController.php
PaymentMethodController.php
PaymentSubmissionController.php
```

Why shared backoffice controllers?

Admin and authorized Manager may act on the same Product/Stock/Order domain operations.

Business behavior should not be duplicated into:

```text
Admin/ProductController
Manager/ProductController
```

when the underlying action is identical.

Role/permission differences are handled through:

```text
Route middleware
Permission
Policy
```

---

# 20. Controllers — Agent

Path:

```text
app/Http/Controllers/Agent/
```

Files:

```text
DashboardController.php
OrderController.php
```

Agent OrderController is separate because Agent view/actions are strongly scoped to:

```text
assigned_agent_id == auth()->id()
```

It should call the same shared:

```text
OrderStatusService
OrderService
```

instead of duplicating order workflow logic.

---

# 21. Form Requests

Path:

```text
app/Http/Requests/
```

Recommended grouping:

```text
Category/
Product/
Coupon/
Checkout/
Order/
Payment/
```

Approved Requests:

```text
Category/StoreCategoryRequest.php
Category/UpdateCategoryRequest.php

Product/StoreProductRequest.php
Product/UpdateProductRequest.php

Coupon/StoreCouponRequest.php

Checkout/CheckoutRequest.php

Order/AssignOrderRequest.php
Order/UpdateOrderStatusRequest.php

Payment/VerifyPaymentRequest.php
```

Important:

`StoreCouponRequest` may be reused for create/update initially if rules are identical.

Do not create an `UpdateCouponRequest` merely for symmetry unless implementation needs different rules.

Form Requests own:

```text
Required fields
Formats
Basic validation
Request-specific validation
```

Services own business workflows.

---

# 22. Middleware Boundary

Use framework / package middleware where possible.

Examples conceptually:

```text
auth
guest
role
permission
```

Do not create custom middleware such as:

```text
EnsureOrderBelongsToCustomer
EnsureAgentOwnsOrder
```

when a Laravel Policy cleanly owns that resource authorization.

Custom middleware is justified only for cross-cutting request concerns not better represented by Policy/Permission.

---

# 23. Console Commands

Path:

```text
app/Console/Commands/
```

P0:

```text
ExpireCoupons.php
```

Signature:

```text
coupons:expire
```

Command flow:

```text
Command
   ↓
Query / existing Coupon Service logic
   ↓
Apply approved expiration state
```

Optional P1:

```text
LowStockReport.php
CleanupPendingOrders.php
```

However:

`orders:cleanup-pending` must not delete/cancel anything unless a future business rule explicitly defines that behavior.

---

# 24. Jobs / Queue Boundary

P0 does not require:

```text
app/Jobs/
```

Create it only when optional Queue functionality is implemented.

Possible P1:

```text
SendOrderConfirmation.php
SendPaymentVerifiedNotification.php
ProcessMediaConversion.php
```

Core Checkout must never depend on these jobs.

Do not queue:

```text
Order creation
Stock deduction
PaymentSubmission creation
Checkout commit
```

---

# 25. Events / Listeners Boundary

Current approved architecture does not require a formal Events/Listeners layer for P0.

Therefore do not pre-create:

```text
app/Events/
app/Listeners/
```

unless a real need appears.

If later used:

```text
Business transaction commits
        ↓
Event
        ↓
Non-critical listener
```

Do not use Events to hide core Checkout orchestration.

---

# 26. Notifications Boundary

P0 core does not require notification implementation.

If P1 is implemented:

```text
app/Notifications/
```

Possible:

```text
OrderPlacedNotification.php
PaymentVerifiedNotification.php
```

But database/email notifications remain optional.

---

# 27. Providers Boundary

Use Laravel providers only where needed by the installed Laravel version/project scaffold.

Potential application concerns:

```text
Policy registration if framework version requires explicit setup
Observer registration if not using attributes/other registration mechanism
Package/bootstrap configuration
```

Do not create domain-specific providers for every module.

---

# 28. `bootstrap/` Boundary

Use framework standard:

```text
bootstrap/app.php
bootstrap/providers.php
```

Possible responsibilities depending Laravel version:

```text
Middleware aliases
Exception handling
Routing bootstrap
Provider registration
```

This folder must not contain business workflows.

---

# 29. `config/` Boundary

Relevant framework/package config:

```text
config/app.php
config/auth.php
config/database.php
config/filesystems.php
config/permission.php
config/media-library.php
```

Do not add:

```text
config/payment-gateway.php
config/courier.php
```

for systems that do not exist in P0.

MFS account numbers belong to:

```text
payment_methods table
```

not hard-coded config.

---

# 30. `database/` Overview

```text
database/
├── factories/
├── migrations/
└── seeders/
```

This follows the physical schema document.

---

# 31. Migrations

Path:

```text
database/migrations/
```

Application-owned migration order concept:

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

Package migrations:

```text
Spatie Permission tables
Spatie Media Library media table
```

Framework/configuration-dependent:

```text
sessions
password_reset_tokens
notifications
```

Migration filenames should use Laravel timestamp ordering.

Example:

```text
2026_09_17_000001_create_categories_table.php
2026_09_17_000002_create_products_table.php
...
```

Actual timestamps may differ.

Do not manually rewrite package table schemas unless necessary.

---

# 32. Seeders

Path:

```text
database/seeders/
```

Recommended:

```text
DatabaseSeeder.php
RolePermissionSeeder.php
AdminUserSeeder.php
PaymentMethodSeeder.php
```

## RolePermissionSeeder

Seeds:

```text
Admin
Manager
Agent
Customer
```

and approved permissions.

Guest is not seeded as a Role.

---

## AdminUserSeeder

Creates development/admin bootstrap account.

Credentials must not be hard-coded insecurely for production use.

---

## PaymentMethodSeeder

Creates initial:

```text
bkash
nagad
rocket
```

with safe development placeholders or explicit seed values.

Real operational numbers should be managed from authorized backoffice.

---

# 33. Factories

Path:

```text
database/factories/
```

Recommended where useful for tests:

```text
UserFactory.php
CategoryFactory.php
ProductFactory.php
CouponFactory.php
OrderFactory.php
```

Additional factories may be created only when tests benefit.

Do not create every possible factory before tests need it.

---

# 34. `resources/` Overview

Primary folders:

```text
resources/
├── css/
├── js/
└── views/
```

Blade remains the primary frontend.

No SPA directory architecture required.

---

# 35. Blade Layouts

Path:

```text
resources/views/layouts/
```

Recommended:

```text
storefront.blade.php
customer.blade.php
backoffice.blade.php
guest.blade.php
```

`guest.blade.php` can support login/register if auth scaffold needs it.

Do not create separate full layouts for every CRUD screen.

---

# 36. Blade Components

Path:

```text
resources/views/components/
```

Recommended subfolders:

```text
storefront/
customer/
backoffice/
common/
```

Examples:

```text
common/flash-message.blade.php
common/validation-errors.blade.php
storefront/product-card.blade.php
storefront/cart-summary.blade.php
backoffice/status-badge.blade.php
backoffice/pagination.blade.php
```

Components should be presentation-focused.

---

# 37. Storefront Views

Path:

```text
resources/views/storefront/
```

Recommended:

```text
home.blade.php

shop/
└── index.blade.php

products/
└── show.blade.php

search/
└── index.blade.php

cart/
└── index.blade.php

checkout/
├── index.blade.php
└── thank-you.blade.php
```

Guest and logged-in Customer use the same Storefront.

Do not duplicate:

```text
guest/checkout
customer/checkout
```

unless UI diverges significantly.

Buyer context is resolved server-side.

---

# 38. Customer Views

Path:

```text
resources/views/customer/
```

Recommended:

```text
profile/
└── edit.blade.php

orders/
├── index.blade.php
└── show.blade.php
```

Customer Order show must not render:

```text
internal_note
payment verification controls
agent assignment controls
staff-only timeline detail
```

---

# 39. Backoffice Views

Path:

```text
resources/views/backoffice/
```

Shared operational modules:

```text
staff/
roles/
permissions/
customers/
categories/
products/
stock/
coupons/
orders/
payment-methods/
payments/
```

Typical CRUD folders may contain:

```text
index.blade.php
create.blade.php
edit.blade.php
show.blade.php
```

Only create pages actually required by each module.

---

# 40. Admin Dashboard Views

Path:

```text
resources/views/backoffice/admin/
```

Primary:

```text
dashboard.blade.php
```

Admin operational CRUD can use shared `backoffice/*` views.

No need to duplicate every backoffice module under `admin/`.

---

# 41. Manager Dashboard Views

Path:

```text
resources/views/backoffice/manager/
```

Primary:

```text
dashboard.blade.php
```

Manager may use shared operational module views only where permissions allow.

Blade should hide unavailable actions via authorization helpers, but backend still authorizes independently.

---

# 42. Agent Views

Path:

```text
resources/views/agent/
```

Recommended:

```text
dashboard.blade.php

orders/
├── index.blade.php
└── show.blade.php
```

Agent views are assignment-centric.

Do not expose broad backoffice Product/Staff/RBAC navigation.

---

# 43. Error Views

Path:

```text
resources/views/errors/
```

Recommended P0 custom pages:

```text
403.blade.php
404.blade.php
```

Optional:

```text
419.blade.php
500.blade.php
```

Error pages must not expose debug-sensitive information.

---

# 44. Assets

Use standard Vite-oriented structure:

```text
resources/css/app.css
resources/js/app.js
```

Do not introduce SPA routing/state management libraries for P0.

Small JavaScript may support:

```text
cart interactions
confirmation dialogs
image previews
checkout UI
```

Server remains authoritative.

---

# 45. `routes/` Overview

Recommended route files:

```text
routes/
├── web.php
├── storefront.php
├── customer.php
├── admin.php
├── manager.php
├── agent.php
├── auth.php
└── console.php
```

Reason:

Architecture already separates:

```text
Public Storefront
Customer Account
Admin Backoffice
Manager Backoffice
Agent Backoffice
Authentication
```

This document now finalizes those route file names.

---

# 46. Route File Decision

## FS-DEC-001 — Route Segmentation

Use separate route fragments:

```text
storefront.php
customer.php
admin.php
manager.php
agent.php
auth.php
```

and load them from:

```text
web.php
```

Why:

```text
Clear role surfaces
Easy navigation
Less giant web.php
Still one Laravel application
No custom route package
```

---

# 47. `routes/web.php` Aggregator

Recommended concept:

```php
<?php

require __DIR__.'/storefront.php';
require __DIR__.'/customer.php';
require __DIR__.'/admin.php';
require __DIR__.'/manager.php';
require __DIR__.'/agent.php';
require __DIR__.'/auth.php';
```

If the installed Laravel authentication scaffold already wires `auth.php` differently, keep the scaffold convention rather than duplicating route registration.

No business logic in route files.

---

# 48. `routes/storefront.php`

Own public/customer shopping routes:

```text
/
shop
category
product details
search
cart
coupon application
checkout
thank-you
```

Example naming style:

```text
home
shop.index
products.show
search.index

cart.index
cart.store
cart.update
cart.destroy
cart.clear

checkout.create
checkout.store
checkout.thank-you
```

Guest can reach approved Storefront/Cart/Checkout routes.

---

# 49. `routes/customer.php`

Prefix:

```text
/account
```

Name prefix:

```text
customer.
```

Middleware:

```text
auth
Customer role/context
```

Routes:

```text
profile.edit
profile.update
orders.index
orders.show
```

`orders.show` must additionally use `OrderPolicy`.

---

# 50. `routes/admin.php`

Prefix:

```text
/admin
```

Name prefix:

```text
admin.
```

Middleware concept:

```text
auth
role:Admin
```

Contains Admin-only/sensitive surfaces:

```text
Admin dashboard
Staff management
Role management
Permission management
Sensitive Payment Method management
Supported restore flows
Other Admin-only management
```

Admin still follows Policies and business rules.

---

# 51. `routes/manager.php`

Prefix:

```text
/manager
```

Name prefix:

```text
manager.
```

Middleware:

```text
auth
role:Manager
```

Then individual actions use explicit permissions such as:

```text
products.view
products.create
products.update
stock.view
stock.update
orders.view
orders.update
orders.assign
customers.view
payments.view
payments.verify
reports.view
```

Do not treat Manager role as unrestricted backoffice.

---

# 52. `routes/agent.php`

Prefix:

```text
/agent
```

Name prefix:

```text
agent.
```

Middleware:

```text
auth
role:Agent
```

Routes:

```text
dashboard
orders.index
orders.show
orders.status.update
orders.cancel where allowed
internal note action where implemented
```

Every Order resource action must pass assigned-resource Policy.

---

# 53. `routes/auth.php`

Authentication routes may include:

```text
register
login
logout
```

P1:

```text
forgot password
reset password
email verification
```

Exact generated controller/file arrangement may follow the installed Laravel authentication starter/scaffold.

Do not rewrite scaffold internals unnecessarily.

---

# 54. Route Prefix / Name Convention

Recommended:

| Surface | URI Prefix | Route Name Prefix |
|---|---|---|
| Public Storefront | none | module-based |
| Customer | `/account` | `customer.` |
| Admin | `/admin` | `admin.` |
| Manager | `/manager` | `manager.` |
| Agent | `/agent` | `agent.` |

Examples:

```text
admin.products.index
manager.products.index
agent.orders.index
customer.orders.show
checkout.store
cart.update
```

---

# 55. Route Middleware Convention

Use broad surface middleware at group level.

Then use permission/policy per capability.

Example concept:

```text
Manager route group
    ↓
auth
    ↓
role:Manager
    ↓
permission for action
    ↓
Policy for resource
```

Agent:

```text
auth
    ↓
role:Agent
    ↓
orders.view / orders.update
    ↓
OrderPolicy assigned ownership
```

Customer:

```text
auth
    ↓
Customer context
    ↓
OrderPolicy own ownership
```

Guest Checkout:

```text
No auth requirement
    ↓
CheckoutRequest
    ↓
Business rules
    ↓
CheckoutService
```

---

# 56. Route-to-Controller Mapping

| Route Surface | Controller Namespace |
|---|---|
| Home/Shop/Product/Search/Cart/Checkout | `App\Http\Controllers\Storefront` |
| Customer Profile/My Orders | `App\Http\Controllers\Customer` |
| Shared Admin/Manager operations | `App\Http\Controllers\Backoffice` |
| Admin dashboard | `App\Http\Controllers\Backoffice\Admin` |
| Manager dashboard | `App\Http\Controllers\Backoffice\Manager` |
| Agent dashboard/orders | `App\Http\Controllers\Agent` |

---

# 57. Controller-to-Service Mapping

| Controller | Service |
|---|---|
| Storefront `CartController` | `CartService`, `CouponService` |
| Storefront `CheckoutController` | `CheckoutService` |
| Backoffice `ProductController` | `ProductService` |
| Backoffice `StockController` | `StockService` |
| Backoffice `CouponController` | `CouponService` |
| Backoffice `OrderController` | `OrderService` |
| `OrderAssignmentController` | `OrderAssignmentService` |
| `OrderStatusController` | `OrderStatusService` |
| `PaymentSubmissionController` | `PaymentService` |
| Admin/Manager/Agent Dashboard | `DashboardService` |
| Agent `OrderController` | `OrderService`, `OrderStatusService` |
| Customer `OrderController` | read/query + `OrderPolicy`; `OrderService` if shared read logic exists |

Do not force Service usage for a trivial read if Eloquent query in a thin read controller is clearer, except where Dashboard/query reuse justifies Service.

---

# 58. Request-to-Service Mapping

```text
StoreProductRequest
UpdateProductRequest
        ↓
ProductService
```

```text
CheckoutRequest
        ↓
CheckoutService
```

```text
AssignOrderRequest
        ↓
OrderAssignmentService
```

```text
UpdateOrderStatusRequest
        ↓
OrderStatusService
```

```text
VerifyPaymentRequest
        ↓
PaymentService
```

---

# 59. Policy Mapping

```text
Product operations
→ ProductPolicy

Customer / Staff / Agent Order access
→ OrderPolicy

Staff management
→ StaffPolicy

Coupon operations
→ CouponPolicy

Payment Method configuration
→ PaymentMethodPolicy

PaymentSubmission review/verify/reject
→ PaymentSubmissionPolicy
```

---

# 60. Service-to-Model Mapping

## ProductService

```text
Product
Category
Media
```

## StockService

```text
Product
```

## CartService

```text
Session
Product reads
```

## CouponService

```text
Coupon
Session
Order discount snapshot inputs
```

## CheckoutService

```text
User context
Product
Coupon
PaymentMethod
Order
OrderItem
PaymentSubmission
OrderHistory
Session
DB Transaction
```

## OrderAssignmentService

```text
Order
User
OrderHistory
```

## OrderStatusService

```text
Order
OrderHistory
```

## PaymentService

```text
PaymentSubmission
PaymentMethod
Order
User
OrderHistory
```

## DashboardService

```text
Read-only aggregate queries
```

---

# 61. Model-to-Table Mapping

| Model | Table |
|---|---|
| `User` | `users` |
| `Category` | `categories` |
| `Product` | `products` |
| `Coupon` | `coupons` |
| `Order` | `orders` |
| `OrderItem` | `order_items` |
| `OrderHistory` | `order_histories` |
| `PaymentMethod` | `payment_methods` |
| `PaymentSubmission` | `payment_submissions` |

Package:

```text
Spatie Permission
→ roles / permissions / model_has_* / role_has_permissions

Spatie Media Library
→ media
```

---

# 62. View-to-Route Mapping

## Public

```text
home
→ storefront/home.blade.php

shop.index
→ storefront/shop/index.blade.php

products.show
→ storefront/products/show.blade.php

cart.index
→ storefront/cart/index.blade.php

checkout.create
→ storefront/checkout/index.blade.php

checkout.thank-you
→ storefront/checkout/thank-you.blade.php
```

## Customer

```text
customer.orders.index
→ customer/orders/index.blade.php

customer.orders.show
→ customer/orders/show.blade.php

customer.profile.edit
→ customer/profile/edit.blade.php
```

## Backoffice

Module views live under:

```text
backoffice/<module>/
```

---

# 63. Checkout Placement

Canonical placement:

```text
Route
routes/storefront.php
        ↓
Controller
app/Http/Controllers/Storefront/CheckoutController.php
        ↓
Request
app/Http/Requests/Checkout/CheckoutRequest.php
        ↓
Rules
app/Rules/*
        ↓
Service
app/Services/CheckoutService.php
        ↓
Models
Order / OrderItem / PaymentSubmission / Product / ...
        ↓
Database transaction
        ↓
View
resources/views/storefront/checkout/*
```

Do not split Guest and Customer into separate Checkout Services for P0.

Buyer context is resolved inside the shared checkout workflow.

---

# 64. Cart Placement

```text
routes/storefront.php
        ↓
Storefront/CartController
        ↓
CartService
        ↓
Session
```

No:

```text
Cart model
CartItem model
carts table
cart_items table
```

for P0.

---

# 65. Coupon Placement

```text
Backoffice CRUD
→ Backoffice/CouponController
→ StoreCouponRequest
→ CouponService
→ Coupon model

Cart/Checkout validation
→ CouponService
→ ValidCoupon
→ Coupon model
```

Expiration:

```text
ExpireCoupons command
→ approved Coupon logic
```

---

# 66. Product / Category Placement

## Product

```text
Backoffice/ProductController
→ ProductService
→ Product
→ Media Library
```

Public:

```text
Storefront/ProductController
→ Product read
→ Blade
```

## Category

```text
Backoffice/CategoryController
→ Form Request
→ Category model/service-level logic as needed
→ Media Library category_image
```

No nested category module in P0.

---

# 67. Stock Placement

Staff:

```text
Backoffice/StockController
→ StockService
→ Product.stock_quantity
```

Checkout:

```text
CheckoutService
→ StockService
→ SufficientStock
→ transaction-safe Product update
```

No:

```text
InventoryMovement model
Warehouse model
```

---

# 68. Payment Placement

Configuration:

```text
Backoffice/PaymentMethodController
→ PaymentMethodPolicy
→ PaymentMethod model
```

Checkout read:

```text
CheckoutController
→ active PaymentMethod
```

Submission:

```text
CheckoutService
→ PaymentSubmission
```

Verification:

```text
Backoffice/PaymentSubmissionController
→ VerifyPaymentRequest
→ PaymentSubmissionPolicy
→ PaymentService
```

No provider adapter/API folder for P0.

---

# 69. Order Placement

Customer read:

```text
Customer/OrderController
→ OrderPolicy
→ Order / OrderItem
```

Backoffice read/manage:

```text
Backoffice/OrderController
→ Permission
→ OrderPolicy
→ OrderService
```

Status:

```text
OrderStatusController
→ UpdateOrderStatusRequest
→ ValidOrderStatusTransition
→ OrderStatusService
```

History:

```text
OrderStatusService / PaymentService / AssignmentService
→ OrderHistory
```

---

# 70. Assignment Placement

```text
routes/admin.php / routes/manager.php
        ↓
Backoffice/OrderAssignmentController
        ↓
orders.assign
        ↓
OrderPolicy
        ↓
AssignOrderRequest
        ↓
OrderAssignmentService
        ↓
Order.assigned_agent_id
        +
OrderHistory
```

Agent role validation occurs in Service/business logic.

---

# 71. Dashboard Placement

Controllers:

```text
Backoffice/Admin/DashboardController
Backoffice/Manager/DashboardController
Agent/DashboardController
```

Service:

```text
DashboardService
```

Views:

```text
backoffice/admin/dashboard.blade.php
backoffice/manager/dashboard.blade.php
agent/dashboard.blade.php
```

Customer account summary may remain inside Customer pages unless a dedicated dashboard becomes necessary.

---

# 72. Authentication Placement

Exact auth implementation may follow installed Laravel starter/scaffold.

Keep:

```text
routes/auth.php
resources/views/auth/
```

or scaffold-generated equivalent.

Do not force custom Auth Service unless actual logic requires it.

Laravel authentication should remain framework-native.

---

# 73. Guest Checkout Placement

Guest is:

```text
Not a User role
Not a Spatie role
```

Therefore do not create:

```text
Guest.php model
GuestRole
GuestPolicy
GuestMiddleware
```

Guest Checkout uses:

```text
Public route
CheckoutRequest
CheckoutService
Business Rules
Order snapshot
orders.user_id = null
```

---

# 74. Customer Ownership Placement

Primary location:

```text
app/Policies/OrderPolicy.php
```

Rule:

```text
order.user_id == auth()->id()
```

Supporting route:

```text
routes/customer.php
```

Controller:

```text
Customer/OrderController.php
```

Do not rely on Blade hiding.

---

# 75. Agent Ownership Placement

Primary location:

```text
OrderPolicy.php
```

Rule:

```text
order.assigned_agent_id == authenticated_agent.id
```

Agent routes:

```text
routes/agent.php
```

Agent controller:

```text
Agent/OrderController.php
```

Permission plus Policy are both relevant.

---

# 76. SoftDelete / Restore Placement

Models with approved SoftDelete:

```text
User
Category
Product
Coupon
Order
PaymentMethod
```

Each applicable Model:

```php
use Illuminate\Database\Eloquent\SoftDeletes;
```

Restore routes/actions live in the relevant authorized backoffice surface.

Do not create a generic enterprise `TrashService` for P0.

---

# 77. Media Library Placement

Spatie package owns persistence.

Models may implement:

```text
HasMedia
InteractsWithMedia
```

where required.

Collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional/P1
```

No custom:

```text
ProductImage model
CategoryImage model
product_images table
```

for P0.

---

# 78. RBAC Placement

Package:

```text
spatie/laravel-permission
```

User model integrates package trait.

Permission seeding:

```text
database/seeders/RolePermissionSeeder.php
```

Protected routes/controllers use:

```text
role
permission
Policy
```

Guest receives no role.

---

# 79. Console / Scheduler Placement

Command:

```text
app/Console/Commands/ExpireCoupons.php
```

Route/schedule configuration depends on installed Laravel version.

Use standard framework location such as:

```text
routes/console.php
```

or the version-supported scheduler bootstrap.

Do not build a custom scheduling subsystem.

---

# 80. Test Structure

Recommended:

```text
tests/
├── Feature/
└── Unit/
```

Priority:

```text
Feature tests first for business behavior.
```

Do not spend disproportionate time unit-testing trivial framework glue while critical workflows remain untested.

---

# 81. Feature Test Folders

Recommended:

```text
tests/Feature/
├── Auth/
├── Storefront/
├── Cart/
├── Coupon/
├── Checkout/
├── Authorization/
├── Payment/
├── Order/
└── SoftDelete/
```

Possible P0 files:

```text
Auth/AuthenticationTest.php

Storefront/ProductVisibilityTest.php

Cart/CartTest.php

Coupon/CouponValidationTest.php

Checkout/GuestCheckoutTest.php
Checkout/AuthenticatedCheckoutTest.php
Checkout/CheckoutTransactionTest.php
Checkout/StockValidationTest.php
Checkout/PriceAuthorityTest.php

Authorization/CustomerOrderOwnershipTest.php
Authorization/AgentOrderOwnershipTest.php
Authorization/PermissionDenialTest.php
Authorization/InternalNoteProtectionTest.php

Payment/ManualPaymentSubmissionTest.php
Payment/PaymentVerificationAuthorizationTest.php
Payment/PaymentVerificationTest.php

Order/OrderAssignmentTest.php
Order/OrderStatusTransitionTest.php
Order/OrderHistoryTest.php

SoftDelete/SoftDeleteVisibilityTest.php
SoftDelete/RestoreTest.php
```

---

# 82. Unit Test Folders

Use only where focused isolated logic benefits.

```text
tests/Unit/
├── Services/
└── Rules/
```

Examples:

```text
Rules/ValidManualTransactionIdTest.php
Rules/ValidOrderStatusTransitionTest.php
Services/CouponServiceTest.php
```

Do not duplicate the same scenario across many test layers without value.

---

# 83. Test Naming Convention

Recommended:

```text
Feature behavior:
GuestCheckoutTest
CustomerOrderOwnershipTest
PaymentVerificationTest

Focused unit:
ValidCouponTest
ValidOrderStatusTransitionTest
```

Test method names should describe behavior.

Example:

```text
guest_can_place_order_without_login
customer_cannot_view_another_customers_order
agent_cannot_update_unassigned_order
submitted_payment_is_not_verified
```

---

# 84. Test-to-Requirement Mapping

Critical mapping:

```text
Guest Checkout
→ Checkout/GuestCheckoutTest

Authenticated Checkout
→ Checkout/AuthenticatedCheckoutTest

Customer Ownership
→ Authorization/CustomerOrderOwnershipTest

Agent Ownership
→ Authorization/AgentOrderOwnershipTest

Permission Denial
→ Authorization/PermissionDenialTest

Stock Validation
→ Checkout/StockValidationTest

Coupon Validation
→ Coupon/CouponValidationTest

Price Tampering
→ Checkout/PriceAuthorityTest

Payment Submission
→ Payment/ManualPaymentSubmissionTest

Payment Verification
→ Payment/PaymentVerificationTest

Order Transition
→ Order/OrderStatusTransitionTest

History
→ Order/OrderHistoryTest

SoftDelete
→ SoftDelete/*
```

---

# 85. Documentation Folder

Once coding begins, recommended:

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
```

Root keeps:

```text
AGENTS.md
README.md
```

Reason:

```text
Implementation repository stays clean
Planning documents remain ordered
AGENTS.md stays visible to coding agents
README.md stays standard repository entry
```

---

# 86. Storage Boundary

Use framework standard:

```text
storage/app/
storage/framework/
storage/logs/
```

Spatie Media Library storage follows configured filesystem.

Do not manually place Product upload code into arbitrary public folders if package-managed media is being used.

---

# 87. Public Boundary

`public/` contains publicly served application entry/build assets.

Examples:

```text
public/index.php
public/build/
storage symlink if configured
```

Do not store secrets or source docs under `public/`.

---

# 88. Environment Boundary

`.env` stores environment-specific secrets/configuration.

Examples:

```text
APP_KEY
DB credentials
mail config if used
queue config if P1 used
filesystem config
```

Do not commit operational secrets.

`.env.example` should contain keys with safe placeholders.

MFS payment account numbers are application data managed through `payment_methods`, not secrets that must live only in `.env`.

---

# 89. Naming Conventions

## Classes

Use PascalCase:

```text
CheckoutService
OrderPolicy
ValidCoupon
OrderStatus
```

## Methods

Use camelCase:

```text
assignAgent()
verifyPayment()
calculateDiscount()
transition()
```

## Routes

Use dot notation:

```text
admin.products.index
manager.orders.index
agent.orders.show
customer.orders.show
```

## Permissions

Use:

```text
module.action
```

Examples:

```text
products.view
orders.assign
payments.verify
```

## Blade folders

Use lowercase kebab-case where multi-word folder names exist:

```text
payment-methods/
```

## Database

Use snake_case:

```text
assigned_agent_id
payment_status
order_histories
```

---

# 90. Namespace Conventions

Examples:

```php
App\Models\Order
App\Services\CheckoutService
App\Enums\OrderStatus
App\Rules\ValidCoupon
App\Policies\OrderPolicy
App\Observers\ProductObserver
App\Traits\HasOrderNumber

App\Http\Controllers\Storefront\CheckoutController
App\Http\Controllers\Customer\OrderController
App\Http\Controllers\Backoffice\ProductController
App\Http\Controllers\Agent\OrderController

App\Http\Requests\Checkout\CheckoutRequest
App\Http\Requests\Order\AssignOrderRequest
```

Namespace must match physical folder.

---

# 91. Class Responsibility Rules

## Controller

```text
HTTP coordination only
```

## Request

```text
Request validation
```

## Rule

```text
Focused validation
```

## Policy

```text
Authorization + resource ownership
```

## Service

```text
Business workflow
```

## Model

```text
Persistence + relationships + casts + lightweight domain helpers
```

## Observer

```text
Small lifecycle concern
```

## Trait

```text
Reusable focused behavior
```

## Enum

```text
Allowed status/value naming
```

## Blade

```text
Presentation
```

---

# 92. Dependency Rules

Preferred direction:

```text
Route
 ↓
Controller
 ↓
Request / Policy
 ↓
Service
 ↓
Rule / Enum / Model
 ↓
Database / Session / Package
```

Blade receives prepared data.

Avoid reverse dependencies such as:

```text
Model calling Controller
Service rendering Blade
Policy calling Controller
Rule redirecting HTTP
Blade mutating DB directly
```

---

# 93. Folder Creation Rules

Create a folder when:

```text
There is an approved responsibility
At least one real class/file belongs there
It improves navigation
It avoids mixing unrelated concerns
```

Do not create empty architecture shells merely because another project uses them.

---

# 94. Files Not to Create for P0

Avoid:

```text
app/Repositories/*
app/Contracts/Repository/*
app/Domain/*
app/Application/*
app/Infrastructure/*
app/DTOs/* unless a concrete approved need appears
app/Actions/* as a parallel Service layer
app/Gateways/*
app/Couriers/*
app/Tenants/*
app/Workspaces/*
app/Inventory/*
app/Refunds/*
app/Vendors/*
app/Accounting/*
```

Avoid models:

```text
Cart
CartItem
Customer
ProductVariant
Warehouse
InventoryMovement
PaymentGatewayTransaction
Refund
CourierShipment
Setting
```

unless future requirements explicitly add them.

---

# 95. P1 / Future Folder Additions

Only if optional features are implemented:

```text
app/Jobs/
app/Notifications/
app/Events/
app/Listeners/
```

Possible optional model/table additions:

```text
Address
ActivityLog
```

P1 Auth may add scaffold-managed files for:

```text
Forgot Password
Email Verification
```

Guest tracking may add secure route/controller logic but not a fake Guest account role.

---

# 96. Folder Structure Anti-Patterns

## Anti-Pattern 1 — Fat Controllers

Bad:

```text
CheckoutController
→ 300 lines
→ price calculation
→ stock deduction
→ order insert
→ payment insert
```

Correct:

```text
CheckoutController
→ CheckoutService
```

---

## Anti-Pattern 2 — Duplicate Role Business Logic

Bad:

```text
Admin/ProductController
Manager/ProductController
```

both containing duplicated Product workflow.

Correct:

```text
Shared Backoffice ProductController
+
Permissions
+
ProductService
```

---

## Anti-Pattern 3 — Policy Logic in Blade

Bad:

```text
Hide button only
```

Correct:

```text
Backend Policy + permission
Blade visibility additionally
```

---

## Anti-Pattern 4 — Repository for Every Model

Not required.

Eloquent is approved persistence abstraction.

---

## Anti-Pattern 5 — Guest as Spatie Role

Wrong:

```text
roles: Guest
```

P0 Guest is unauthenticated public actor.

---

## Anti-Pattern 6 — Payment Gateway Folder

Wrong for P0:

```text
app/Gateways/BkashGateway.php
```

because manual submission is not provider API integration.

---

## Anti-Pattern 7 — Cart Database Module

Wrong for P0:

```text
Cart.php
CartItem.php
carts migration
cart_items migration
```

Cart is Session-based.

---

## Anti-Pattern 8 — Hidden Business Logic in Observer

Observer must not perform Checkout/Payment/Order orchestration.

---

## Anti-Pattern 9 — Generic Service Dump

Avoid:

```text
HelperService.php
CommonService.php
UtilityService.php
```

for unrelated domain behavior.

Use named responsibility.

---

# 97. Implementation Creation Order

Recommended folder/file implementation sequence aligned with the 13-day plan:

## Phase 1 — Foundation

```text
app/Models/User.php
app/Enums/
database/migrations/
database/seeders/RolePermissionSeeder.php
routes/auth.php
```

---

## Phase 2 — Category / Product / Media

```text
Models/Category.php
Models/Product.php
Services/ProductService.php
Requests/Category/*
Requests/Product/*
Backoffice/CategoryController.php
Backoffice/ProductController.php
Policies/ProductPolicy.php
Observers/ProductObserver.php
Traits/HasSlug.php
resources/views/backoffice/categories/
resources/views/backoffice/products/
```

---

## Phase 3 — Storefront

```text
Storefront/HomeController.php
Storefront/ShopController.php
Storefront/ProductController.php
Storefront/SearchController.php
routes/storefront.php
resources/views/storefront/*
```

---

## Phase 4 — Cart / Coupon

```text
CartService.php
CouponService.php
ValidCoupon.php
Storefront/CartController.php
Models/Coupon.php
Backoffice/CouponController.php
resources/views/storefront/cart/
```

---

## Phase 5 — Checkout / Payment / Order

```text
CheckoutRequest.php
ValidManualTransactionId.php
SufficientStock.php

CheckoutService.php
PaymentService.php
OrderService.php
StockService.php

Order.php
OrderItem.php
OrderHistory.php
PaymentMethod.php
PaymentSubmission.php

Storefront/CheckoutController.php
resources/views/storefront/checkout/
```

---

## Phase 6 — Backoffice Orders

```text
Backoffice/OrderController.php
Backoffice/PaymentSubmissionController.php
PaymentSubmissionPolicy.php
OrderPolicy.php
resources/views/backoffice/orders/
resources/views/backoffice/payments/
```

---

## Phase 7 — Assignment / Agent

```text
OrderAssignmentController.php
OrderAssignmentService.php
AssignOrderRequest.php

Agent/DashboardController.php
Agent/OrderController.php
routes/agent.php
resources/views/agent/*
```

---

## Phase 8 — Status Workflow

```text
OrderStatusController.php
OrderStatusService.php
UpdateOrderStatusRequest.php
ValidOrderStatusTransition.php
```

---

## Phase 9 — Customer Account

```text
Customer/ProfileController.php
Customer/OrderController.php
routes/customer.php
resources/views/customer/*
```

---

## Phase 10 — Commands / Polish / Tests

```text
Console/Commands/ExpireCoupons.php
DashboardService.php
Feature tests
Unit tests where useful
README.md
AGENTS.md
```

---

# 98. Folder Structure Checklist

## Core

- [ ] `app/Models` contains only approved P0 models.
- [ ] `app/Services` contains approved Services.
- [ ] `app/Policies` contains resource Policies.
- [ ] `app/Rules` contains focused reusable Rules.
- [ ] `app/Enums` contains status Enums.
- [ ] `app/Observers` contains meaningful lightweight Observer(s).
- [ ] `app/Traits` contains focused reusable Traits.
- [ ] `app/Console/Commands` contains `ExpireCoupons`.
- [ ] Controllers remain thin.
- [ ] Requests own request validation.

## Routes

- [ ] `web.php` is an aggregator.
- [ ] Storefront routes are separate.
- [ ] Customer routes are protected.
- [ ] Admin routes are protected.
- [ ] Manager routes are permission-driven.
- [ ] Agent routes use assigned-order Policy.
- [ ] Guest Checkout remains public.

## Blade

- [ ] Shared Storefront views support Guest + Customer.
- [ ] Customer account has own-order views.
- [ ] Admin/Manager share operational backoffice views where practical.
- [ ] Agent has assignment-focused views.
- [ ] Internal Notes are not rendered to Customer.
- [ ] Blade hiding is not treated as backend security.

## Persistence

- [ ] No Cart tables/models.
- [ ] No separate Customer model/table.
- [ ] No inventory ledger.
- [ ] No real gateway models.
- [ ] Spatie tables are package-managed.
- [ ] Media table is package-managed.
- [ ] Migrations align with `08-DATABASE-SCHEMA.md`.

## Scope

- [ ] No Repository layer.
- [ ] No microservices.
- [ ] No DDD folder hierarchy.
- [ ] No duplicate Admin/Manager business logic.
- [ ] No fake Guest role.
- [ ] No unnecessary Queue dependency.
- [ ] P1 folders are created only if P1 is implemented.

## Tests

- [ ] Guest Checkout test.
- [ ] Authenticated Checkout test.
- [ ] Customer ownership test.
- [ ] Agent ownership test.
- [ ] Permission denial test.
- [ ] Stock validation test.
- [ ] Coupon validation test.
- [ ] Price authority test.
- [ ] Payment submission test.
- [ ] Payment verification authorization test.
- [ ] Order transition test.
- [ ] Order history test.
- [ ] SoftDelete visibility test.

---

# 99. Definition of Folder Structure Complete

Folder structure is complete when a developer can answer these questions without guessing:

```text
Where does Checkout orchestration live?
→ app/Services/CheckoutService.php

Where does Guest/Customer Checkout HTTP entry live?
→ Storefront/CheckoutController.php

Where does Customer Order ownership live?
→ OrderPolicy.php

Where does Agent assignment ownership live?
→ OrderPolicy.php

Where does Agent assignment workflow live?
→ OrderAssignmentService.php

Where does Order transition logic live?
→ OrderStatusService.php + ValidOrderStatusTransition.php

Where does Payment verify/reject live?
→ PaymentService.php

Where does request validation live?
→ app/Http/Requests/

Where do statuses live?
→ app/Enums/

Where does Session Cart logic live?
→ CartService.php

Where do bKash/Nagad/Rocket account records live?
→ payment_methods table / PaymentMethod model

Where do Product images live?
→ Spatie Media Library

Where does Guest identity live?
→ No Guest model; Order snapshots + nullable user_id

Where do Customer Orders display?
→ resources/views/customer/orders/

Where do shared backoffice CRUD views live?
→ resources/views/backoffice/

Where do Agent views live?
→ resources/views/agent/

Where are route surfaces separated?
→ routes/storefront.php / customer.php / admin.php / manager.php / agent.php

Where are core tests?
→ tests/Feature/
```

If these answers remain clear and no unnecessary architectural layer is added, the structure is implementation-ready.

---

# 100. Final Folder Structure Summary

Final architecture-to-folder mapping:

```text
Presentation
→ resources/views
→ resources/css
→ resources/js

HTTP / Access
→ routes
→ app/Http/Controllers
→ app/Http/Requests
→ Middleware
→ app/Policies

Application / Business
→ app/Services
→ app/Rules
→ app/Enums

Domain / Data
→ app/Models
→ app/Observers
→ app/Traits

Operational
→ app/Console/Commands

Persistence
→ database/migrations
→ database/seeders
→ database/factories
→ MySQL

External Packages
→ Spatie Permission
→ Spatie Media Library

Testing
→ tests/Feature
→ tests/Unit
```

The most important structural invariant is:

```text
Route
 ↓
Controller
 ↓
Authorization + Validation
 ↓
Service
 ↓
Model / Session / Database
 ↓
Response / Blade
```

ShopPilot remains:

```text
One Laravel Application
One MySQL Database
Service-Oriented Modular Monolith
Blade Frontend
Session Cart
Manual MFS Payment Submission
Permission + Policy Authorization
Guest + Logged-in Customer Checkout
No enterprise over-engineering
```

---

# 101. Next Documentation

Next:

```text
AGENTS.md
```

Then:

```text
README.md
```

Documentation sequence:

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
AGENTS.md ← NEXT
README.md
```
