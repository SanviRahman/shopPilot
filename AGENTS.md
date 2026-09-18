# AGENTS.md — ShopPilot E-commerce
# AI Coding Agent Operating Contract

> **Project:** ShopPilot E-commerce  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Architecture:** Service-Oriented Modular Laravel Monolith  
> **Backend:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Cart:** Laravel Session  
> **Payment:** Manual bKash / Nagad / Rocket Submission  
> **Primary Goal:** Complete, secure, practice-oriented 13-day Laravel build  
> **Status:** Coding-Agent Instruction File  
> **Applies To:** Claude Code, Codex, ChatGPT coding agents, IDE agents, and human contributors using AI assistance  
> **Version:** 1.0  
> **Language:** English + Bangla  
> **Next Documentation:** `README.md`

---

# Table of Contents

1. Purpose of This File  
2. Agent Mission  
3. Source Documents  
4. Source-of-Truth Rules  
5. Authority / Precedence Rules  
6. Agent Working Philosophy  
7. Scope Discipline  
8. Non-Negotiable Product Invariants  
9. Actor Model  
10. Authorization Model  
11. Role Rules  
12. Guest Rules  
13. Customer Rules  
14. Manager Rules  
15. Agent Role Rules  
16. Admin Rules  
17. Permission Rules  
18. Architecture Contract  
19. Dependency Direction  
20. Folder Structure Contract  
21. Controller Contract  
22. Form Request Contract  
23. Service Contract  
24. Policy Contract  
25. Custom Rule Contract  
26. Enum Contract  
27. Model Contract  
28. Observer Contract  
29. Trait Contract  
30. Console Command Contract  
31. Queue Contract  
32. Event / Listener Contract  
33. Route Contract  
34. Blade Contract  
35. Database Contract  
36. Core Tables  
37. Schema Invariants  
38. Money / Status / Timestamp Rules  
39. SoftDelete Rules  
40. Session Cart Rules  
41. Product Rules  
42. Category Rules  
43. Media Rules  
44. Stock Rules  
45. Coupon Rules  
46. Checkout Rules  
47. Buyer Snapshot Rules  
48. Checkout Transaction Rules  
49. Manual Payment Rules  
50. Payment Submission Rules  
51. Payment Verification Rules  
52. Order Rules  
53. Order Item Snapshot Rules  
54. Agent Assignment Rules  
55. Order Status Rules  
56. Cancellation Rules  
57. Order History Rules  
58. Customer Order Access Rules  
59. Guest Order Exposure Rules  
60. Dashboard Rules  
61. Search Rules  
62. Error / Denial Rules  
63. Security Rules  
64. Mass Assignment Rules  
65. Validation Rules  
66. Testing Rules  
67. Required Feature Tests  
68. Seeder Rules  
69. Migration Rules  
70. Naming Rules  
71. Code Style Rules  
72. Comment / Documentation Rules  
73. Implementation Workflow for Agents  
74. Before Editing Code  
75. While Editing Code  
76. After Editing Code  
77. New Feature Workflow  
78. Bug Fix Workflow  
79. Refactor Workflow  
80. Migration Change Workflow  
81. Authorization Change Workflow  
82. Checkout Change Workflow  
83. Payment Change Workflow  
84. Order Status Change Workflow  
85. Folder / Architecture Change Workflow  
86. Documentation Update Workflow  
87. TBD / Undefined Rules  
88. Explicitly Prohibited P0 Changes  
89. Files / Folders Not to Add  
90. Package Boundaries  
91. Performance Guidance  
92. Logging / Traceability  
93. Failure Safety  
94. Definition of Done for Agent Work  
95. Pull Request / Handoff Checklist  
96. Quick Reference — Critical Paths  
97. Quick Reference — Critical Statuses  
98. Quick Reference — Critical Permissions  
99. Quick Reference — Critical Tables  
100. Final Agent Contract

---

# 1. Purpose of This File

This file tells coding agents **how to implement ShopPilot without breaking the approved requirements, business rules, architecture, schema, application flow, or folder structure**.

এই file coding agent-এর জন্য project-level operating contract.

An agent working on ShopPilot must:

```text
Read the relevant documentation
Understand the current approved scope
Preserve existing invariants
Make the smallest correct implementation change
Use the approved Laravel layers
Add/update tests
Avoid silent scope expansion
Avoid speculative architecture
Avoid inventing undefined business rules
```

This file is not a replacement for the detailed project documents.

It is the **implementation guardrail** that points agents back to them.

---

# 2. Agent Mission

The agent's mission is:

> Build ShopPilot as a clean, secure, testable, Laravel-native, service-oriented modular monolith that is realistic enough for professional practice and still finishable within the approved MVP scope.

Prioritize:

```text
Correctness
Security
Business-rule compliance
Laravel conventions
Clarity
Testability
Small, reviewable changes
Scope discipline
```

Do not prioritize:

```text
Pattern count
Enterprise architecture
Novel abstractions
Premature scalability
Unapproved features
```

---

# 3. Source Documents

The approved documentation set is:

```text
01-PROJECT-OVERVIEW-UPDATED.md
02-PRD.md
03-FEATURES.md
04-USER-ROLES-AND-PERMISSIONS.md
05-BUSINESS-RULES.md
06-ARCHITECTURE.md
07-DATABASE-ERD.md
08-DATABASE-SCHEMA.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```

Agents must use these documents together.

Do not implement from memory when the exact rule is documented.

---

# 4. Source-of-Truth Rules

Different documents own different kinds of decisions.

## Product Scope

Primary:

```text
01-PROJECT-OVERVIEW-UPDATED.md
02-PRD.md
03-FEATURES.md
```

They define:

```text
What the product is
Who uses it
Which features are P0 / P1 / Future
What is out of scope
```

---

## Authorization

Primary:

```text
04-USER-ROLES-AND-PERMISSIONS.md
```

It defines:

```text
Roles
Permissions
Default grants
Sensitive grants
Policy ownership
Deny-by-default behavior
```

---

## Business Behavior

Primary:

```text
05-BUSINESS-RULES.md
```

It defines:

```text
What is allowed
What is denied
What must be validated
What state may change
Which behavior remains TBD
```

---

## Architecture

Primary:

```text
06-ARCHITECTURE.md
```

It defines:

```text
Layer responsibilities
Service boundaries
Policy placement
Request placement
Rule placement
Transaction boundaries
Package boundaries
```

---

## Data Relationships

Primary:

```text
07-DATABASE-ERD.md
```

It defines:

```text
Core entities
Logical relationships
Cardinality
Ownership
Historical relationship boundaries
```

---

## Physical Database

Primary:

```text
08-DATABASE-SCHEMA.md
```

It defines:

```text
Table names
Columns
Data types
Nullability
Defaults
Unique constraints
Indexes
Foreign keys
Migration decisions
SoftDelete physical behavior
```

---

## Runtime Sequence

Primary:

```text
09-APPLICATION-FLOW.md
```

It defines:

```text
Request sequence
Checkout sequence
Payment sequence
Assignment sequence
Status sequence
Failure / rollback sequence
```

---

## Physical Code Placement

Primary:

```text
10-FOLDER-STRUCTURE.md
```

It defines:

```text
Class placement
Route files
Controller namespaces
Blade folders
Service paths
Test folders
Files that must not be created
```

---

# 5. Authority / Precedence Rules

Never use one flat precedence list for every question.

Use the authority that matches the decision type.

## If the question is about business behavior

```text
05-BUSINESS-RULES.md
wins over
technical implementation assumptions
```

---

## If the question is about roles / permissions

```text
04-USER-ROLES-AND-PERMISSIONS.md
+
05-BUSINESS-RULES.md
```

must be followed.

---

## If the question is about exact database columns

```text
08-DATABASE-SCHEMA.md
```

is authoritative unless it contradicts an approved Business Rule.

---

## If the question is about relationships

```text
07-DATABASE-ERD.md
+
08-DATABASE-SCHEMA.md
```

are authoritative.

---

## If the question is about code location

```text
10-FOLDER-STRUCTURE.md
```

is authoritative.

---

## If the question is about execution sequence

```text
09-APPLICATION-FLOW.md
```

is authoritative.

---

## Universal conflict rule

If a technical document accidentally conflicts with an approved business behavior:

```text
Business behavior wins.
```

Do not silently “fix” the conflict by inventing a third design.

Stop the implementation of that conflicting behavior and make the conflict explicit.

---

# 6. Agent Working Philosophy

Every change should follow:

```text
Understand
   ↓
Locate applicable rule
   ↓
Locate applicable architecture
   ↓
Inspect current code
   ↓
Make smallest correct change
   ↓
Test
   ↓
Review security / scope
   ↓
Update docs only when approved behavior changed
```

Agent behavior must be:

```text
Conservative with scope
Strict with authorization
Strict with server-authoritative data
Strict with transactions
Strict with historical data
Pragmatic with Laravel
```

---

# 7. Scope Discipline

Do not add a feature because:

```text
"It is common in e-commerce"
"It may be useful later"
"It is more enterprise"
"It is cleaner architecture"
"It is a best practice in another project"
```

Only add it if:

```text
Approved docs require it
OR
the user explicitly requests it
OR
it is a minimal technical necessity to implement an approved feature
```

If a behavior is missing from docs:

```text
Do not invent it.
Do not silently implement it.
Preserve TBD.
```

---

# 8. Non-Negotiable Product Invariants

These must never be broken.

## INV-001 — Guest Checkout Exists

```text
Guest Customer can purchase without login.
```

Never redirect all Checkout attempts to Login.

---

## INV-002 — Logged-in Checkout Exists

Logged-in Customer can also Checkout.

ShopPilot supports both:

```text
Guest Checkout
OR
Authenticated Customer Checkout
```

---

## INV-003 — Guest Order User ID

Guest Order:

```text
orders.user_id = null
```

is valid.

---

## INV-004 — Authenticated Order User ID

Authenticated Customer Order:

```text
orders.user_id = auth()->id()
```

---

## INV-005 — Buyer Snapshot

Every Order stores final buyer/contact/shipping snapshots.

Do not depend entirely on current User profile.

---

## INV-006 — Product Snapshot

Every OrderItem stores purchase-time:

```text
product_name
sku
unit_price
quantity
line_total
```

---

## INV-007 — Session Cart

P0 Cart is Session-based.

Do not create:

```text
carts
cart_items
Cart model
CartItem model
```

---

## INV-008 — Server Price Authority

Browser/client values are never authoritative for:

```text
Price
Discount
Grand Total
```

---

## INV-009 — Server Stock Authority

Checkout revalidates stock server-side.

---

## INV-010 — Submitted Is Not Verified

```text
submitted != verified
```

Transaction ID submission is not provider verification.

---

## INV-011 — Manual Payment Verification

Authorized staff manually verifies/rejects Payment Submission.

No automatic provider API verification exists in P0.

---

## INV-012 — Separate States

Keep:

```text
order_status
```

and:

```text
payment_status
```

separate.

---

## INV-013 — Customer Ownership

Customer can only access own Order:

```text
order.user_id == auth()->id()
```

---

## INV-014 — Agent Ownership

Agent can only process assigned Order by default:

```text
order.assigned_agent_id == auth()->id()
```

---

## INV-015 — Historical Preservation

Do not destroy Order history because a Product/User/Coupon/PaymentMethod changes later.

---

## INV-016 — Internal Notes Are Staff-Only

Never expose `internal_note` to Customer/Guest UI.

---

## INV-017 — Business Rules Are Server-Side

Blade visibility is not security.

---

## INV-018 — Checkout Is Transaction-Safe

Critical Checkout writes must succeed or roll back together.

---

# 9. Actor Model

Approved actors:

```text
Admin
Manager
Agent
Customer
Guest Customer / Visitor
System
```

Authenticated roles:

```text
Admin
Manager
Agent
Customer
```

Public actor:

```text
Guest Customer / Visitor
```

Internal actor:

```text
System
```

System may include:

```text
Console Command
Observer
Scheduled Task
Optional Queue Job
```

System actions do not bypass business rules.

---

# 10. Authorization Model

Protected actions follow:

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

Important:

```text
Role alone is not enough.
Permission alone may not be enough.
Blade visibility is never enough.
```

Backend authorization is mandatory.

---

# 11. Role Rules

## Admin

Default sensitive operational owner.

Still must follow:

```text
Validation
State rules
Database constraints
Business rules
```

Admin does not get permission to create invalid state transitions.

---

## Manager

Permission-driven.

Manager is not “Admin Lite” with automatic access.

---

## Agent

Operational actor for assigned Orders.

---

## Customer

Authenticated buyer.

No staff operational permissions.

---

## Guest

Public buyer.

No RBAC role.

---

# 12. Guest Rules

Guest may:

```text
Browse
Search
View Product
Use Session Cart
Apply valid Coupon
Checkout without login
Enter buyer/shipping data
Select active MFS method
View configured instruction
Submit Transaction ID
Place Order
View Thank You
```

Guest does not get P0:

```text
Authenticated My Orders dashboard
Staff dashboard
Payment verification controls
Internal notes
Arbitrary Order access
```

Do not create:

```text
Guest role
Guest model
GuestPolicy
Guest account requirement
```

---

# 13. Customer Rules

Customer may:

```text
Register
Login
Logout
Shop
Use Cart
Apply Coupon
Checkout
Submit Transaction ID
Place Order
View own Orders
View own Order details
Update profile
```

Customer cannot:

```text
View another Customer Order
View Internal Notes
Assign Agent
Verify Payment
Reject Payment
Directly control staff workflow
```

---

# 14. Manager Rules

Default Manager permission set:

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

customers.view

payments.view
payments.verify

reports.view
```

Not granted by default:

```text
staff.*
roles.*
permissions.*
categories.*
products.delete
products.restore
orders.cancel
orders.restore
payments.reject
payment-methods.manage
settings.update
```

Additional Manager capability must be explicit.

---

# 15. Agent Role Rules

Recommended default permissions:

```text
dashboard.view
orders.view
orders.update
orders.cancel
```

These permissions do not grant global Order access.

Mandatory policy condition:

```text
order.assigned_agent_id == auth()->id()
```

Agent does not receive by default:

```text
orders.assign
payments.verify
payments.reject
products.*
stock.*
roles.*
permissions.*
payment-methods.*
settings.*
```

---

# 16. Admin Rules

Admin receives approved explicit permissions.

Do not rely on an unbounded wildcard simply to avoid seeding permissions.

Admin may operate approved modules including:

```text
Staff
Roles
Permissions
Customers
Categories
Products
Media
Stock
Coupons
Orders
Assignment
Payment Methods
Payment Verification
Reports
Settings permissions where applicable
Supported Restore flows
```

Note:

`08-DATABASE-SCHEMA.md` does not introduce a P0 `settings` table.

Do not create one silently.

---

# 17. Permission Rules

Permission format:

```text
module.action
```

Approved catalogue includes:

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

Do not seed documentation-only umbrella aliases such as:

```text
staff.manage
settings.manage
```

unless explicitly approved later.

---

# 18. Architecture Contract

Architecture:

> **Service-Oriented Modular Laravel Monolith**

Meaning:

```text
One Laravel app
One MySQL database
Blade frontend
Eloquent persistence
Logical modules
Thin Controllers
Reusable Services
Policies
Form Requests
Custom Rules
Enums
Observers
Traits
Console Commands
```

It is not:

```text
Microservices
DDD package architecture
Repository-heavy architecture
Event-sourced system
CQRS system
```

---

# 19. Dependency Direction

Preferred dependency direction:

```text
Route
   ↓
Controller
   ↓
Authorization + Form Request
   ↓
Service
   ↓
Rule / Enum / Model
   ↓
Database / Session / Package
   ↓
Response / Blade
```

Forbidden examples:

```text
Model → Controller
Service → Blade rendering
Policy → Controller
Rule → Redirect response
Blade → direct authoritative mutation
```

---

# 20. Folder Structure Contract

Use:

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

Do not restructure the app into unrelated architecture without explicit approval.

---

# 21. Controller Contract

Controllers must stay thin.

Controllers may:

```text
Receive request
Authorize
Use validated data
Call Service
Return View
Return Redirect
Return appropriate response
```

Controllers must not own:

```text
Full Checkout transaction
Stock orchestration
Coupon engine
Payment verification workflow
Order assignment workflow
Order state machine
```

---

# 22. Form Request Contract

Approved Request concepts:

```text
StoreCategoryRequest
UpdateCategoryRequest
StoreProductRequest
UpdateProductRequest
StoreCouponRequest
CheckoutRequest
AssignOrderRequest
UpdateOrderStatusRequest
VerifyPaymentRequest
```

Form Requests own:

```text
Required fields
Types
Formats
Basic request validation
Request-specific reusable rules
```

Do not turn Form Requests into transaction orchestration classes.

---

# 23. Service Contract

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

Do not create parallel:

```text
Actions/
UseCases/
Managers/
Repositories/
```

for the same workflow unless explicitly approved.

Avoid generic dump classes:

```text
HelperService
UtilityService
CommonService
```

---

# 24. Policy Contract

Recommended Policies:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

Critical `OrderPolicy` responsibilities:

```text
Customer own Order
Agent assigned Order
Staff resource authorization
```

Critical `PaymentSubmissionPolicy` responsibilities:

```text
View
Verify
Reject
```

---

# 25. Custom Rule Contract

Approved rules:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

Rules should:

```text
Validate one focused reusable condition
Return validation outcome/message
Avoid side effects where practical
```

Rules must not:

```text
Create Order
Deduct Stock
Verify Payment
Assign Agent
Run large transaction
```

---

# 26. Enum Contract

Use:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

Enums provide:

```text
Allowed values
Type-safe comparisons
Central naming
Model casts
```

Enums do not replace:

```text
OrderStatusService
ValidOrderStatusTransition
```

---

# 27. Model Contract

Core application Models:

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

Do not create P0 models for:

```text
Customer
Guest
Cart
CartItem
ProductVariant
Warehouse
InventoryMovement
PaymentGatewayTransaction
Refund
CourierShipment
Setting
```

unless future scope explicitly changes.

---

# 28. Observer Contract

Possible observers:

```text
ProductObserver
OrderObserver
```

Add `UserObserver` only if a meaningful lightweight concern exists.

Allowed:

```text
Lightweight lifecycle bookkeeping
Small slug preparation
Dispatch local non-critical event
```

Forbidden:

```text
Checkout orchestration
Payment verification
Stock transaction
Assignment workflow
Order state machine
```

Do not use Observer to hide business logic.

---

# 29. Trait Contract

Suggested:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

Traits must be:

```text
Small
Reusable
Predictable
Focused
```

Do not hide large workflows in Traits.

---

# 30. Console Command Contract

Minimum P0 command:

```text
coupons:expire
```

Recommended class:

```text
app/Console/Commands/ExpireCoupons.php
```

Command should use approved logic.

Do not make a command bypass business rules.

Optional commands must not perform undefined destructive behavior.

---

# 31. Queue Contract

Queue is optional / P1.

Appropriate:

```text
Email
Image conversion
Non-critical notification
```

Core Checkout must not require Queue for:

```text
Order creation
OrderItem creation
PaymentSubmission creation
Stock deduction
Transaction commit
```

---

# 32. Event / Listener Contract

P0 does not require a formal Events/Listeners layer.

Do not add it just for architectural decoration.

If later introduced:

```text
Core transaction commits first
Then non-critical event side effect
```

Do not hide critical state mutation in event listeners.

---

# 33. Route Contract

Approved route files:

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

`web.php` acts as aggregator where compatible with the installed Laravel scaffold.

---

## Public Storefront

```text
routes/storefront.php
```

Owns:

```text
Home
Shop
Category/Product discovery
Search
Cart
Coupon application
Checkout
Thank You
```

---

## Customer

```text
routes/customer.php
```

Prefix:

```text
/account
```

Name prefix:

```text
customer.
```

---

## Admin

```text
routes/admin.php
```

Prefix:

```text
/admin
```

---

## Manager

```text
routes/manager.php
```

Prefix:

```text
/manager
```

---

## Agent

```text
routes/agent.php
```

Prefix:

```text
/agent
```

---

# 34. Blade Contract

Blade is presentation-only.

Blade may:

```text
Render
Loop
Format
Show errors
Show flash messages
Use @can
Use @role
```

Blade must not be authoritative for:

```text
Price
Discount
Stock
Payment Status
Order Status
Permission
Ownership
```

Customer Blade must not render:

```text
internal_note
payment verification controls
assignment controls
staff-only data
```

---

# 35. Database Contract

Database:

```text
MySQL
InnoDB
utf8mb4
```

Primary IDs:

```text
BIGINT UNSIGNED AUTO_INCREMENT
```

Money:

```text
DECIMAL(12,2)
```

Statuses:

```text
VARCHAR + PHP Enum cast
```

Do not use `FLOAT` / `DOUBLE` for authoritative money.

---

# 36. Core Tables

Application-owned P0:

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

Framework/configuration-dependent:

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

# 37. Schema Invariants

## Order Customer FK

```text
orders.user_id
```

must be nullable.

---

## Agent FK

```text
orders.assigned_agent_id
```

must be nullable.

---

## Coupon Link

P0 stores:

```text
orders.coupon_id nullable
orders.coupon_code nullable snapshot
orders.discount monetary snapshot
```

---

## PaymentSubmission Cardinality

P0:

```text
Order hasOne PaymentSubmission
```

Physical:

```text
payment_submissions.order_id UNIQUE
```

One Order:

```text
0..1 PaymentSubmission
```

Do not silently change to `hasMany`.

---

## Transaction ID

```text
payment_submissions.transaction_id
```

is indexed but:

```text
NOT UNIQUE
```

because global duplicate policy remains TBD.

---

# 38. Money / Status / Timestamp Rules

Money:

```text
DECIMAL(12,2)
```

Important Order statuses:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Payment statuses:

```text
unpaid
submitted
verified
rejected
```

Product:

```text
active
inactive
```

Coupon:

```text
active
inactive
expired
```

Payment Method:

```text
active
inactive
```

Historical OrderHistory is append-oriented.

---

# 39. SoftDelete Rules

SoftDelete-enabled:

```text
users
categories
products
coupons
orders
payment_methods
```

Historical tables do not use SoftDelete:

```text
order_items
order_histories
payment_submissions
```

Never cascade-delete historical Order data.

Force delete is not a P0 requirement.

---

# 40. Session Cart Rules

P0 Cart:

```text
Laravel Session
```

Cart operations:

```text
Add
Update
Remove
Clear
Subtotal
Coupon Discount
Grand Total
```

Server recalculates authoritative totals.

Successful Checkout:

```text
COMMIT
   ↓
Clear Cart
```

Failed Checkout:

```text
Do not clear Cart as successful completion.
```

---

# 41. Product Rules

Fields include:

```text
name
slug
sku
category_id
short_description
description
regular_price
sale_price
stock_quantity
status
featured
media
```

Rules:

```text
SKU unique
Slug unique
Inactive hidden from normal Storefront
Soft-deleted hidden from normal Storefront
Price server-authoritative
Complex variants out of scope
```

Sale price activation behavior remains TBD.

Do not invent sale schedule fields.

---

# 42. Category Rules

P0 category:

```text
Single-Level
```

Do not add nested hierarchy without explicit future approval.

Category uses Media Library for:

```text
category_image
```

---

# 43. Media Rules

Use Spatie Laravel Media Library.

Collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional
```

Do not create custom:

```text
product_images
category_images
ProductImage model
CategoryImage model
```

for P0.

---

# 44. Stock Rules

P0 source:

```text
products.stock_quantity
```

Rules:

```text
> 0 = available
0 = out of stock
Checkout revalidates stock
Insufficient stock blocks Order
Successful Order deducts stock
Deduction occurs transaction-safely
```

No:

```text
Warehouse
Inventory ledger
Stock movement engine
```

---

# 45. Coupon Rules

P0:

```text
fixed
percentage
```

Rules:

```text
One Coupon per Order
No stacking
Inactive → reject
Expired → reject
Minimum order amount validated
Discount server-authoritative
```

Coupon code is unique under approved schema/collation.

---

# 46. Checkout Rules

Checkout pipeline:

```text
Resolve Buyer Context
   ↓
Cart Validation
   ↓
Stock Validation
   ↓
Coupon Validation
   ↓
Server Price Calculation
   ↓
Buyer Details Validation
   ↓
Payment Method Validation
   ↓
Transaction ID Validation
   ↓
Order Transaction
```

Do not reorder in a way that creates partial writes or trusts stale client state.

---

# 47. Buyer Snapshot Rules

Every Order stores:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

For Guest:

```text
user_id = null
```

For Customer:

```text
user_id = authenticated customer id
```

In both cases:

```text
snapshot fields are mandatory
```

Historical Order display uses snapshot data.

---

# 48. Checkout Transaction Rules

Critical write set:

```text
BEGIN TRANSACTION

1. INSERT orders
2. INSERT order_items
3. INSERT payment_submissions
4. UPDATE products.stock_quantity
5. INSERT initial order_histories

COMMIT
```

If required write fails:

```text
ROLLBACK
```

Do not create partial success.

Do not clear Cart before commit.

---

# 49. Manual Payment Rules

P0 methods:

```text
bKash
Nagad
Rocket
```

PaymentMethod stores:

```text
name
code
account_number
account_type
instruction
status
```

Only active methods are offered at Checkout.

Customer pays externally.

ShopPilot receives Transaction ID.

No real provider callback.

---

# 50. Payment Submission Rules

At successful manual MFS Checkout:

```text
PaymentSubmission.status = submitted
Order.payment_status = submitted
verified_by = null
verified_at = null
```

Transaction format validation does not prove payment.

---

# 51. Payment Verification Rules

Authorized verify flow:

```text
Permission
Policy
Request validation
PaymentService
Update PaymentSubmission
Update Order.payment_status
Create OrderHistory
```

Verify:

```text
status = verified
verified_by = staff user id
verified_at = now()
```

Reject:

```text
status = rejected
verified_by = staff user id
verified_at = now()
rejection_note = ...
```

Do not automatically change `order_status` unless a future approved rule requires it.

Default:

```text
Admin → verify + reject
Manager → verify
Manager reject → explicit permission only
Agent → no verify/reject
Customer → no verify/reject
Guest → no verify/reject
```

---

# 52. Order Rules

Order stores separate:

```text
order_status
payment_status
```

Order includes:

```text
Buyer snapshots
Financial snapshots
Optional coupon snapshot
Assignment
Notes
History
```

Do not combine fulfillment and payment into one `status`.

---

# 53. Order Item Snapshot Rules

OrderItem must preserve purchase-time commercial truth:

```text
product_name
sku
unit_price
quantity
line_total
```

Do not render past Order solely from current Product price/name.

---

# 54. Agent Assignment Rules

Initial:

```text
assigned_agent_id = null
```

Assignment:

```text
Admin / authorized Manager
   ↓
orders.assign
   ↓
Policy
   ↓
AssignOrderRequest
   ↓
OrderAssignmentService
   ↓
Validate selected User is eligible Agent
   ↓
Set assigned_agent_id
   ↓
Create OrderHistory
```

Do not let client send arbitrary user ID without Agent validation.

---

# 55. Order Status Rules

Normal flow:

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

Allowed cancellation sources:

```text
pending
confirmed
processing
```

to:

```text
cancelled
```

Every transition must pass:

```text
Permission
Policy
ValidOrderStatusTransition
OrderStatusService
```

Invalid transition:

```text
reject
no DB state mutation
```

---

# 56. Cancellation Rules

Cancellation may be allowed from:

```text
pending
confirmed
processing
```

Current project does not define automatic stock restoration after cancellation.

Therefore:

```text
Do not auto-restore stock unless explicitly approved later.
```

---

# 57. Order History Rules

Record meaningful events such as:

```text
Order Created
Payment Submitted
Payment Verified
Payment Rejected
Assigned To Agent
Confirmed
Processing
Shipped
Delivered
Cancelled
```

OrderHistory should remain historical evidence.

Do not mutate/remove old history to “clean up” state.

---

# 58. Customer Order Access Rules

Customer route must require authentication.

Order detail must enforce:

```text
order.user_id == auth()->id()
```

Direct ID guessing must not bypass Policy.

---

# 59. Guest Order Exposure Rules

Guest has no P0 My Orders dashboard.

Do not implement:

```text
/orders/{id}
```

as a public predictable Guest Order lookup.

Future Guest tracking requires secure verification/token design.

---

# 60. Dashboard Rules

Use:

```text
DashboardService
```

for aggregate dashboard queries.

Dashboard controllers remain thin.

Agent dashboard queries assigned Orders only.

Manager dashboard capabilities remain permission-driven.

---

# 61. Search Rules

Public search returns only normal Storefront-visible Products.

At minimum:

```text
active
not soft-deleted
```

No external search engine is required.

---

# 62. Error / Denial Rules

Use appropriate behavior:

```text
Validation failure
→ validation errors

Authentication failure
→ login / unauthenticated response

Permission / Policy failure
→ 403 / safe denial

Business rule failure
→ domain/validation-style rejection

Database transaction failure
→ rollback + safe error
```

Never expose:

```text
Stack trace in production
Secrets
Internal Notes
Other Customer data
Unauthorized staff configuration
```

---

# 63. Security Rules

Always enforce server-side.

Protect against:

```text
IDOR
Mass assignment
Client price tampering
Client status tampering
Client role tampering
Unauthorized Order access
Cross-Agent Order access
Unauthorized payment verification
```

Use:

```text
Authentication
Permissions
Policies
Validated data
Services
DB constraints
```

---

# 64. Mass Assignment Rules

Never trust client input for authoritative fields such as:

```text
payment_status
order_status
verified_by
verified_at
assigned_agent_id
grand_total
discount
server price
user_id for Guest/Customer context
```

These values must be resolved or constructed server-side by the authorized workflow.

---

# 65. Validation Rules

Write operations use Form Requests where appropriate.

Reusable focused validation may use Custom Rules.

Database constraints remain the last integrity boundary.

Do not rely on client-side JavaScript validation for business integrity.

---

# 66. Testing Rules

Testing priority:

```text
Business behavior
Authorization
Ownership
Transactions
State transitions
Historical preservation
```

Feature tests are the primary critical layer.

Unit tests are useful for focused Rules/Services where isolation adds value.

---

# 67. Required Feature Tests

At minimum cover:

```text
Guest Checkout
Authenticated Checkout
Customer Ownership
Agent Ownership
Permission Denial
Stock Validation
Coupon Validation
Price Tampering
Manual Payment Submission
Payment Verification Authorization
Payment Verification
Order Assignment
Order Status Transition
Order History
Internal Note Protection
SoftDelete Visibility
Restore
Checkout Rollback
Cart Clear After Commit
```

Critical assertions include:

```text
Guest can buy without login
Guest Order user_id is null
Customer Order user_id links customer
Both preserve snapshots
submitted is not verified
Agent cannot access another Agent Order
Customer cannot access another Customer Order
Invalid transition does not mutate state
```

---

# 68. Seeder Rules

Recommended:

```text
DatabaseSeeder
RolePermissionSeeder
AdminUserSeeder
PaymentMethodSeeder
```

Seed roles:

```text
Admin
Manager
Agent
Customer
```

Do not seed:

```text
Guest
```

as Spatie role.

Seed approved granular permissions.

Seed test/development MFS method records safely.

---

# 69. Migration Rules

Migrations must follow `08-DATABASE-SCHEMA.md`.

Do not:

```text
Invent columns
Rename approved columns casually
Change nullability casually
Change cardinality casually
Remove historical constraints
```

Important:

```text
orders.user_id nullable
orders.assigned_agent_id nullable
payment_submissions.order_id unique
transaction_id NOT unique
```

Before changing schema:

```text
Check ERD
Check Database Schema
Check Business Rules
Check impacted tests
```

---

# 70. Naming Rules

Classes:

```text
PascalCase
```

Methods:

```text
camelCase
```

Database:

```text
snake_case
```

Permission:

```text
module.action
```

Routes:

```text
dot.notation
```

Blade folders:

```text
lowercase / kebab-case where needed
```

Namespace must match physical folder.

---

# 71. Code Style Rules

Use Laravel/PHP conventions.

Prefer:

```text
Typed method signatures where practical
Small focused methods
Constructor dependency injection
Clear variable names
Early validation/guard clauses
Framework helpers where appropriate
Eloquent relationships
DB transactions for critical multi-write operations
```

Avoid:

```text
Huge controllers
Huge god services
Magic arrays with unexplained keys
Duplicated role-specific business logic
Unnecessary static utility classes
```

Follow project formatter/linter configuration if present.

---

# 72. Comment / Documentation Rules

Comments should explain:

```text
Why
Business invariant
Non-obvious tradeoff
TBD boundary
Security reason
```

Do not comment obvious syntax.

Examples of useful comments:

```text
// Guest checkout intentionally allows nullable user_id.
// Transaction ID format validation does not mean provider verification.
// Do not auto-restore stock on cancellation until business rule is approved.
```

---

# 73. Implementation Workflow for Agents

For every task:

```text
1. Read task.
2. Identify affected module.
3. Read relevant project docs.
4. Inspect existing implementation.
5. Identify invariants.
6. Identify tests that should exist/change.
7. Make minimal change.
8. Run focused tests.
9. Run broader impacted tests.
10. Review auth/security.
11. Review schema/transaction safety.
12. Report what changed.
```

Do not skip documentation lookup for core business behavior.

---

# 74. Before Editing Code

Before editing, determine:

```text
Which actor performs the action?
Is the route public or protected?
Which permission applies?
Which Policy applies?
Which Form Request applies?
Which Service owns the workflow?
Which Model/table changes?
Which state transitions apply?
Is a DB transaction required?
Which tests prove the change?
Is the behavior P0, P1, Future, or TBD?
```

If you cannot answer from docs:

```text
Do not invent.
```

---

# 75. While Editing Code

During implementation:

```text
Preserve existing public interfaces where possible
Avoid unrelated refactors
Avoid package swaps
Avoid framework-wide rewrites
Keep business logic out of Blade
Keep business logic out of route closures
Keep controllers thin
Keep authorization explicit
Keep transaction boundaries obvious
```

---

# 76. After Editing Code

Review:

```text
Guest flow still works?
Customer flow still works?
Permission checks still work?
Policy ownership still works?
Server-authoritative calculations preserved?
Payment submitted/verified separation preserved?
Order/payment statuses separate?
Snapshots preserved?
Transaction safe?
No out-of-scope feature introduced?
Tests pass?
```

---

# 77. New Feature Workflow

If user requests a new feature:

1. Determine whether it is already:
   - P0
   - P1
   - Future
   - Out of Scope
   - New request

2. If it changes approved business behavior:
   - identify impacted docs
   - do not quietly retrofit behavior

3. Design smallest Laravel-native implementation.

4. Place classes according to `10-FOLDER-STRUCTURE.md`.

5. Add tests.

6. Update docs if the user approves the feature as part of the specification.

---

# 78. Bug Fix Workflow

For a bug:

```text
Reproduce
   ↓
Identify violated invariant
   ↓
Add/adjust failing test
   ↓
Fix at correct layer
   ↓
Run tests
```

Do not fix a Policy bug by merely hiding a Blade button.

Do not fix a Service bug by adding duplicated Controller logic.

---

# 79. Refactor Workflow

Refactor only when behavior remains unchanged unless explicitly requested.

Before refactor:

```text
Identify protected behaviors
Ensure tests exist
```

During refactor:

```text
Keep public route behavior stable
Keep schema stable
Keep statuses stable
Keep permission semantics stable
```

Do not introduce a new architecture style as a “refactor”.

---

# 80. Migration Change Workflow

Before creating/editing migration:

```text
Read 07-DATABASE-ERD.md
Read 08-DATABASE-SCHEMA.md
Check relevant Business Rule
```

Then verify:

```text
FK
Nullability
Index
Unique
SoftDelete
Historical preservation
Delete action
```

Do not change schema solely to make coding easier.

---

# 81. Authorization Change Workflow

Any authorization change must answer:

```text
Actor?
Permission?
Resource ownership?
Sensitive action?
Default grant?
Policy?
Route middleware?
Feature test?
```

Deny-by-default.

Do not grant broad permission because a UI page is inconvenient.

---

# 82. Checkout Change Workflow

Checkout is high-risk.

Before changing:

```text
Read Business Rules
Read Architecture Checkout sections
Read Database Schema
Read Application Flow
```

Must preserve:

```text
Guest + Customer
Server price
Stock validation
Coupon validation
Buyer snapshot
OrderItem snapshot
Manual payment submission
Atomic writes
Rollback safety
Cart clear after commit
```

Any Checkout change requires focused Feature Tests.

---

# 83. Payment Change Workflow

Before changing Payment:

Check:

```text
submitted != verified
Permission
Policy
Order.payment_status sync
Verifier audit fields
History creation
No automatic provider assumption
```

Do not make Transaction ID format validation act as verification.

---

# 84. Order Status Change Workflow

Any new transition or state must be explicitly approved.

Do not add:

```text
returned
refunded
failed
on_hold
ready_to_ship
```

without approved scope.

Current P0 values are fixed.

---

# 85. Folder / Architecture Change Workflow

Do not move classes casually.

Before adding a new top-level folder under `app/`, ask:

```text
Is this responsibility already owned by Service/Policy/Request/Rule/Model?
Is this required by an approved feature?
Does this duplicate an existing layer?
```

If yes to duplication:

```text
Do not add the layer.
```

---

# 86. Documentation Update Workflow

Update project docs when:

```text
Approved business behavior changes
Schema contract changes
Architecture contract changes
Route/folder contract changes
A TBD becomes explicitly resolved
```

Do not rewrite docs to match an accidental implementation.

Implementation should match approved docs first.

---

# 87. TBD / Undefined Rules

Current unresolved behavior must stay unresolved unless user explicitly decides it.

## TBD — Shipping Calculation

Unknown:

```text
Flat?
Area based?
Free threshold?
```

Schema stores `shipping`.

Do not invent calculator/rate table.

---

## TBD — Stock Restore After Cancellation

Do not auto-restore until approved.

---

## TBD — Rejected Payment Impact on Order Status

Do not auto-cancel.

---

## TBD — Payment Verification Before Fulfillment

Do not add mandatory gate unless approved.

---

## TBD — Assignment Timing

Do not enforce payment-before-assignment or assignment-before-payment unless approved.

---

## TBD — Transaction ID Global Uniqueness

Do not add DB unique constraint.

---

## TBD — Sale Price Activation

Do not add sale scheduling fields/logic unless approved.

---

## P1 / Future — Guest Order Tracking

Do not expose insecure predictable lookup.

---

# 88. Explicitly Prohibited P0 Changes

Do not add:

```text
Multi-Vendor
Marketplace
Multi-Tenancy
Workspace model
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

unless the user explicitly changes project scope.

---

# 89. Files / Folders Not to Add

Do not add for P0:

```text
app/Repositories/
app/Contracts/Repository/
app/Domain/
app/Application/
app/Infrastructure/
app/Actions/ as parallel Service architecture
app/Gateways/
app/Couriers/
app/Tenants/
app/Workspaces/
app/Inventory/
app/Refunds/
app/Vendors/
app/Accounting/
```

Do not add speculative:

```text
app/DTOs/
```

unless a concrete approved need appears.

---

# 90. Package Boundaries

## Spatie Permission

Use package-managed:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

Do not custom-rebuild RBAC tables.

---

## Spatie Media Library

Use package-managed:

```text
media
```

Do not custom-rebuild media persistence.

---

# 91. Performance Guidance

P0 performance strategy:

```text
Use documented indexes
Paginate lists
Avoid obvious N+1
Eager load where appropriate
Keep queries scoped
Avoid speculative cache layers
```

Do not add Redis/cache architecture unless explicitly needed.

Queue is optional.

---

# 92. Logging / Traceability

P0 operational traceability primarily uses:

```text
OrderHistory
Laravel logs
PaymentSubmission verifier fields
```

Do not introduce enterprise audit/event sourcing system.

Important Order events should produce OrderHistory where approved.

---

# 93. Failure Safety

High-risk actions must fail safely.

Checkout failure:

```text
ROLLBACK
No partial success
Cart not cleared as success
```

Unauthorized action:

```text
No mutation
```

Invalid status transition:

```text
No mutation
```

Payment verification failure:

```text
No partial status sync
```

---

# 94. Definition of Done for Agent Work

A coding task is complete only when applicable items are true:

```text
Requested behavior works
Relevant business rule is preserved
Authorization is enforced
Validation is present
Service boundary is respected
Schema contract is respected
Transaction safety is correct
Historical snapshots are preserved
Error behavior is safe
Critical tests exist/update
Tests pass
No unrelated scope was added
No TBD was silently decided
No prohibited layer/module was introduced
```

---

# 95. Pull Request / Handoff Checklist

Before presenting a completed implementation, report:

```text
What changed
Which files changed
Which business rule / feature it implements
Any migration added
Any permission/policy added
Any test added
Which tests were run
Any remaining TBD
```

Do not say “complete” if a required test or migration is knowingly missing.

---

# 96. Quick Reference — Critical Paths

```text
Checkout:
app/Http/Controllers/Storefront/CheckoutController.php
app/Http/Requests/Checkout/CheckoutRequest.php
app/Services/CheckoutService.php

Cart:
app/Http/Controllers/Storefront/CartController.php
app/Services/CartService.php

Coupon:
app/Services/CouponService.php
app/Rules/ValidCoupon.php

Stock:
app/Services/StockService.php
app/Rules/SufficientStock.php

Payment:
app/Services/PaymentService.php
app/Policies/PaymentSubmissionPolicy.php

Order Assignment:
app/Services/OrderAssignmentService.php

Order Status:
app/Services/OrderStatusService.php
app/Rules/ValidOrderStatusTransition.php

Order Ownership:
app/Policies/OrderPolicy.php

Enums:
app/Enums/

Models:
app/Models/

Routes:
routes/storefront.php
routes/customer.php
routes/admin.php
routes/manager.php
routes/agent.php
```

---

# 97. Quick Reference — Critical Statuses

## Order

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

## Payment

```text
unpaid
submitted
verified
rejected
```

## Product

```text
active
inactive
```

## Coupon

```text
active
inactive
expired
```

## Payment Method

```text
active
inactive
```

---

# 98. Quick Reference — Critical Permissions

```text
dashboard.view

products.view
products.create
products.update
products.delete
products.restore

stock.view
stock.update

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

customers.view

roles.view
roles.manage

permissions.view
permissions.manage
```

Remember:

```text
Permission != ownership.
```

---

# 99. Quick Reference — Critical Tables

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

Critical fields:

```text
orders.user_id
→ nullable for Guest

orders.assigned_agent_id
→ nullable

orders.coupon_id
→ nullable

orders.coupon_code
→ snapshot

orders.order_status
orders.payment_status
→ separate

payment_submissions.order_id
→ unique

payment_submissions.transaction_id
→ indexed, not globally unique

payment_submissions.verified_by
→ nullable until staff action
```

---

# 100. Final Agent Contract

Every coding agent working on ShopPilot must preserve this contract:

> **Build only the approved ShopPilot scope. Guest and logged-in Customers must both be able to purchase. Guest Checkout must never require registration. Guest Orders may use nullable `user_id`, while every Order preserves buyer/shipping snapshots. Cart remains session-based. Price, discount, stock, payment state, order state, permission, assignment, and ownership are server-authoritative. Manual MFS Transaction ID submission creates `submitted`, not `verified`. Authorized staff verify/reject payments. Customer sees only own Orders. Agent processes only assigned Orders by default. Controllers stay thin; Services own workflows; Form Requests validate input; Policies enforce resource authorization; Custom Rules remain focused; Enums own status values; Eloquent/MySQL own persistence. Checkout critical writes are atomic. Historical Order data is preserved. Undefined rules remain TBD. Out-of-scope enterprise modules must not be introduced without explicit approval.**

When uncertain:

```text
Read the docs.
Do not guess.
Do not silently expand scope.
Preserve the approved invariants.
```

---

# Documentation Sequence

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
README.md ← NEXT
```
