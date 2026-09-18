# ShopPilot E-commerce — Architecture
# শপপাইলট ই-কমার্স — আর্কিটেকচার

> **Document:** System Architecture / সিস্টেম আর্কিটেকচার  
> **Project:** ShopPilot E-commerce  
> **Source Documents:**  
> `01-PROJECT-OVERVIEW-UPDATED.md` v1.1  
> `ShopPilot-02-PRD.md` v1.0  
> `ShopPilot-03-FEATURES.md` v1.0  
> `ShopPilot-04-USER-ROLES-AND-PERMISSIONS.md` v1.0  
> `ShopPilot-05-BUSINESS-RULES.md` v1.0  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Primary Goal:** 13-Day Full-Stack Laravel Practice Project  
> **Architecture Style:** Service-Oriented Modular Laravel Monolith  
> **Backend:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Cart:** Session-Based  
> **Payment:** Manual bKash / Nagad / Rocket Submission; optional COD  
> **Queue:** Optional / P1 — not a core dependency  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Approved Architecture Definition  
> **Next Document:** `07-DATABASE-ERD.md`

---

# Table of Contents

1. Document Purpose  
2. Architecture Goals  
3. Architecture Constraints  
4. Architectural Style  
5. Architecture Decision Summary  
6. System Context  
7. High-Level Architecture  
8. Layer Responsibilities  
9. Dependency Direction  
10. Request Lifecycle  
11. Public Storefront Architecture  
12. Authentication Architecture  
13. Authorization Architecture  
14. Route & Access Segmentation  
15. Controller Architecture  
16. Form Request Architecture  
17. Service Layer Architecture  
18. Business Rule Architecture  
19. Policy Architecture  
20. Custom Rule Architecture  
21. Enum & State Architecture  
22. Eloquent Model Architecture  
23. Persistence Architecture  
24. Session Cart Architecture  
25. Checkout Architecture  
26. Checkout Transaction Boundary  
27. Stock Architecture  
28. Coupon Architecture  
29. Manual Payment Architecture  
30. Payment Verification Architecture  
31. Order Architecture  
32. Order Assignment Architecture  
33. Order State Transition Architecture  
34. Order History Architecture  
35. Historical Snapshot Architecture  
36. Media Architecture  
37. Soft Delete Architecture  
38. Observer Architecture  
39. Trait Architecture  
40. Console Command Architecture  
41. Queue Architecture  
42. Notification Architecture  
43. Dashboard Architecture  
44. Security Architecture  
45. Validation Architecture  
46. Error & Denial Architecture  
47. Operational Traceability  
48. Testing Architecture  
49. Module Dependency Map  
50. Main Flow Diagrams  
51. Data Ownership Boundaries  
52. State Ownership Boundaries  
53. Transaction Boundaries  
54. Read / Write Responsibility Matrix  
55. Performance & Scope Principles  
56. Architecture Anti-Patterns  
57. Explicit TBD Architecture Decisions  
58. Out-of-Scope Architecture  
59. Suggested Implementation Sequence  
60. Architecture Acceptance Criteria  
61. Definition of Architecture Complete  
62. Final Architecture Statement  
63. Next Documentation

---

# 1. Document Purpose

This document defines **how ShopPilot should be structured technically** while preserving the approved product requirements and business rules.

এই document-এর কাজ হলো:

- system-এর high-level architecture define করা
- Laravel layer responsibilities define করা
- Controller / Service / Policy / Rule / Form Request responsibilities আলাদা করা
- Checkout transaction boundary define করা
- Manual payment architecture define করা
- Order workflow architecture define করা
- Guest ও authenticated flow আলাদা করা
- Spatie Permission এবং Media Library-এর placement define করা
- Observer, Trait, Console, Enum, SoftDelete-এর proper architectural use define করা
- project-কে over-engineering থেকে protect করা

This document does **not** define final SQL columns or final physical folder structure.

Those belong to:

```text
07-DATABASE-ERD.md
08-DATABASE-SCHEMA.md
10-FOLDER-STRUCTURE.md
```

---

# 2. Architecture Goals

ShopPilot architecture must be:

```text
Professional
Practice-Oriented
Finishable in 13 Days
Laravel-Native
Modular
Service-Oriented
Secure
Testable
Maintainable
Not Over-Engineered
```

Primary architectural goals:

1. Keep Controllers thin.
2. Keep Blade presentation-focused.
3. Put reusable business logic in Services.
4. Put authorization in Permission + Policy layers.
5. Put validation in Form Requests / Custom Rules.
6. Use Enums for important statuses.
7. Use transactions around critical Checkout writes.
8. Preserve historical Order snapshots.
9. Support both Guest and logged-in Customer purchase flow.
10. Keep manual payment submission separate from verification.
11. Keep Agent access resource-scoped.
12. Use Observers only for lightweight lifecycle concerns.
13. Use Traits only for genuinely reusable behavior.
14. Keep Queue optional.
15. Avoid enterprise complexity not required by the approved MVP.

---

# 3. Architecture Constraints

The architecture must respect these approved constraints.

## 3.1 Single Store

```text
Single Store
Single Application
Single Database
No Multi-Tenant Workspace Model
No Multi-Vendor Marketplace
```

---

## 3.2 Monolith

The application is a Laravel monolith.

Do not split into:

```text
Microservices
Separate Order Service
Separate Payment Service Application
Separate Inventory Service
Separate API Gateway
```

for the MVP.

---

## 3.3 Blade Frontend

Primary frontend:

```text
Laravel Blade
```

No SPA architecture is required.

---

## 3.4 MySQL Persistence

Primary database:

```text
MySQL
```

---

## 3.5 Session Cart

Cart is:

```text
Session-Based
```

A persistent database Cart is not required for the core MVP.

---

## 3.6 Manual Payment

Payment is manual MFS submission:

```text
bKash
Nagad
Rocket
```

The architecture must not pretend this is automatic provider verification.

---

## 3.7 Optional Queue

Queue may be used for P1 work, but:

```text
Core Checkout
Order Creation
Payment Submission
Stock Deduction
```

must not depend on optional Queue infrastructure.

---

# 4. Architectural Style

ShopPilot uses:

> **Service-Oriented Modular Laravel Monolith**

Meaning:

```text
One Laravel Application
        +
Logical Business Modules
        +
Thin Controllers
        +
Reusable Services
        +
Policies
        +
Form Requests
        +
Custom Rules
        +
Enums
        +
Eloquent Models
        +
Blade Views
        +
MySQL
```

This is **not** microservices.

It is also **not** a fat-controller CRUD application.

---

# 5. Architecture Decision Summary

## AD-001 — Modular Monolith

Use one Laravel codebase with logical business modules.

Status:

```text
APPROVED
```

---

## AD-002 — Service Layer

Use Services for reusable/core business workflows.

Status:

```text
APPROVED
```

---

## AD-003 — Eloquent First

Use Laravel Eloquent Models as the primary persistence abstraction.

Status:

```text
APPROVED FOR MVP
```

No separate repository abstraction is required by current source documents.

Do not introduce Repository classes merely for pattern count.

---

## AD-004 — Blade Presentation

Use Laravel Blade for Storefront and backoffice UI.

Status:

```text
APPROVED
```

---

## AD-005 — Spatie RBAC

Use Spatie Laravel Permission for authenticated roles and permissions.

Status:

```text
APPROVED
```

---

## AD-006 — Policy Ownership

Use Laravel Policies for resource-level authorization.

Status:

```text
APPROVED
```

---

## AD-007 — Spatie Media

Use Spatie Laravel Media Library for Product/Category media.

Status:

```text
APPROVED
```

---

## AD-008 — Session Cart

Use Session-based Cart for P0.

Status:

```text
APPROVED
```

---

## AD-009 — Checkout Transaction

Checkout critical database changes must be transaction-safe.

Status:

```text
APPROVED
```

---

## AD-010 — Manual MFS

Use internal Payment Method + Payment Submission architecture.

Do not use real provider adapters/APIs in P0.

Status:

```text
APPROVED
```

---

## AD-011 — Queue Optional

Queue is optional P1.

Status:

```text
APPROVED
```

---

# 6. System Context

High-level actors:

```text
Guest Customer
Logged-in Customer
Admin
Manager
Agent
System
```

System context:

```text
                    ┌───────────────────┐
                    │ Guest Customer    │
                    └─────────┬─────────┘
                              │
                    ┌─────────▼─────────┐
                    │                   │
                    │   ShopPilot       │
                    │ Laravel Monolith  │
                    │                   │
                    └─────────┬─────────┘
                              │
                    ┌─────────▼─────────┐
                    │ MySQL Database    │
                    └───────────────────┘

Logged-in Customer ───────► ShopPilot

Admin ────────────────────► ShopPilot
Manager ──────────────────► ShopPilot
Agent ────────────────────► ShopPilot

External Manual Payment:
Customer manually uses bKash / Nagad / Rocket outside application
then submits Transaction ID into ShopPilot.
```

Important:

```text
No real payment-provider callback
No courier API
No marketplace provider
```

---

# 7. High-Level Architecture

```text
┌──────────────────────────────────────────────────────┐
│ PRESENTATION LAYER                                   │
│ Blade Views                                          │
│ Public / Customer / Admin / Manager / Agent UI      │
└───────────────────────┬──────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────┐
│ HTTP / ACCESS LAYER                                  │
│ Routes                                               │
│ Controllers                                          │
│ Middleware                                           │
│ Form Requests                                        │
│ Policies / Permission Checks                         │
└───────────────────────┬──────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────┐
│ APPLICATION / BUSINESS LAYER                         │
│ Services                                             │
│ Custom Rules                                         │
│ Enums                                                │
│ Business Rules                                       │
└───────────────────────┬──────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────┐
│ DOMAIN / DATA LAYER                                  │
│ Eloquent Models                                      │
│ Relationships                                        │
│ SoftDeletes                                          │
│ Observers                                            │
│ Traits                                               │
└───────────────────────┬──────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────┐
│ INFRASTRUCTURE                                       │
│ MySQL                                                │
│ Session                                              │
│ Spatie Permission                                    │
│ Spatie Media Library                                 │
│ Console / Scheduler                                  │
│ Optional Queue                                       │
└──────────────────────────────────────────────────────┘
```

---

# 8. Layer Responsibilities

## 8.1 Presentation Layer

Responsible for:

```text
HTML
Blade
Components
Forms
Tables
Navigation
Dashboard presentation
Validation error display
Flash messages
```

Must not own authoritative:

```text
Pricing
Discount
Stock
Payment status
Order transition rules
Authorization
```

---

## 8.2 HTTP Layer

Responsible for:

```text
Receiving request
Authentication middleware
Permission middleware
Form Request validation
Policy authorization
Calling Service
Returning response
```

---

## 8.3 Application / Business Layer

Responsible for:

```text
Checkout orchestration
Cart calculations
Coupon logic
Stock logic
Order creation
Order assignment
Order transition
Payment verification
Dashboard aggregation
Business state rules
```

---

## 8.4 Domain / Data Layer

Responsible for:

```text
Models
Relationships
Scopes
Casts
Enums integration
SoftDeletes
Media relationships
Reusable model behavior
```

---

## 8.5 Infrastructure Layer

Responsible for:

```text
MySQL
Session storage
Media storage
Console execution
Optional Queue
Laravel framework infrastructure
```

---

# 9. Dependency Direction

Preferred dependency direction:

```text
Blade
  ↓
Controller
  ↓
Form Request / Policy
  ↓
Service
  ↓
Rule / Enum / Model
  ↓
Database
```

Avoid:

```text
Blade → Database mutation
Model Observer → Full Checkout
Controller → giant business workflow
Policy → pricing calculation
Form Request → complex transaction
```

---

# 10. Request Lifecycle

Protected request lifecycle:

```text
HTTP Request
    ↓
Authentication
    ↓
Role / Permission
    ↓
Policy / Ownership
    ↓
Form Request Validation
    ↓
Business Rule
    ↓
Service
    ↓
Model / Database
    ↓
Response
```

Public Guest Checkout lifecycle:

```text
Public Request
    ↓
CheckoutRequest
    ↓
CheckoutService
    ↓
Cart Validation
    ↓
Product / Stock Validation
    ↓
Coupon Validation
    ↓
Server Pricing
    ↓
Buyer Validation
    ↓
Payment Method Validation
    ↓
Transaction ID Validation
    ↓
Database Transaction
    ↓
Thank You
```

---

# 11. Public Storefront Architecture

Public Storefront serves:

```text
Guest
Logged-in Customer
```

Public pages include:

```text
Home
Shop
Category
Product Details
Search Results
Cart
Checkout
Payment Instruction
Thank You
Login
Register
```

Storefront reads only publicly valid Product/Category data.

Normal Product visibility condition:

```text
Product = active
AND
Product is not soft deleted
```

---

# 12. Authentication Architecture

Authentication supports:

```text
Admin
Manager
Agent
Customer
```

Guest requires no authentication.

Authenticated account areas:

```text
Staff dashboards
Customer My Account
Customer My Orders
Own Order Details
```

Guest Checkout must remain separate from authentication requirement.

---

# 13. Authorization Architecture

Authorization combines:

```text
Laravel Authentication
+
Spatie Permission
+
Laravel Policies
+
Business Rules
```

## 13.1 Role / Permission Layer

Answers:

```text
Can this user perform this type of operation?
```

---

## 13.2 Policy Layer

Answers:

```text
Can this user perform the operation on this specific resource?
```

Examples:

```text
Customer owns Order?
Agent assigned to Order?
```

---

## 13.3 Business Rule Layer

Answers:

```text
Is this operation valid in the current business state?
```

Example:

```text
processing → shipped
= allowed

delivered → pending
= invalid
```

---

# 14. Route & Access Segmentation

Architecture should logically separate route surfaces:

```text
Public Storefront
Customer Account
Admin Backoffice
Manager Backoffice
Agent Backoffice
Authentication
```

Exact route filenames are deferred to `10-FOLDER-STRUCTURE.md`.

Logical access:

```text
Public
→ Guest + Customer

Customer Account
→ Customer

Admin
→ Admin

Manager
→ Manager + explicit permissions

Agent
→ Agent + assigned-resource Policy
```

---

# 15. Controller Architecture

Controllers must remain thin.

Controller responsibilities:

```text
Receive request
Authorize
Use validated data
Call Service
Return View / Redirect
```

Controller should not directly contain:

```text
Full checkout transaction
Stock orchestration
Coupon engine
Payment verification workflow
Order state machine
Large query/report logic
```

Example:

```text
CheckoutController
    ↓
CheckoutService
```

---

# 16. Form Request Architecture

Form Requests own request validation.

Suggested approved requests include:

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

Form Request responsibilities:

```text
Field presence
Field format
Basic validation
Authorization where appropriate
Custom Rule integration
```

Business workflow execution remains in Services.

---

# 17. Service Layer Architecture

Approved Service concepts:

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

## 17.1 Service Rule

Services should:

```text
Represent business capabilities
Remain focused
Be reusable by Controllers / Console
Own business orchestration
Coordinate Models
Coordinate transactions where appropriate
```

---

## 17.2 Service Non-Goal

Service classes must not become generic dumping grounds.

Avoid:

```text
GodService
CommonService
HelperService containing unrelated logic
```

---

# 18. Business Rule Architecture

Business rules from `05-BUSINESS-RULES.md` are architectural constraints.

Examples:

```text
Server-authoritative price
Server-authoritative discount
Stock revalidation
One coupon per Order
submitted != verified
Customer own Order only
Agent assigned Order only
Controlled Order transitions
```

Services and Rules enforce these behaviors.

---

# 19. Policy Architecture

Recommended Policies:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

## 19.1 OrderPolicy

Critical responsibilities:

```text
Customer own Order
Agent assigned Order
Staff Order authorization
```

---

## 19.2 PaymentSubmissionPolicy

Critical responsibilities:

```text
View submission
Verify submission
Reject submission
```

with permission checks.

---

# 20. Custom Rule Architecture

Suggested Rules:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

Rule responsibility:

```text
Validate a focused reusable condition
```

Rule must not execute large business transactions.

---

# 21. Enum & State Architecture

Recommended Enums:

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
Central status naming
```

Enums do not replace transition logic.

Transition logic remains in:

```text
OrderStatusService
Custom Rule
Business Rules
```

---

# 22. Eloquent Model Architecture

Core application entities currently identified:

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

Package-managed entities/tables:

```text
Role
Permission
Media
```

Final relationships/cardinality belong to ERD/Schema documents.

---

# 23. Persistence Architecture

Persistence strategy:

```text
Laravel Eloquent
+
MySQL
+
Database Transactions
+
SoftDeletes where approved
```

No separate persistence microservice.

No advanced event store.

No warehouse/inventory ledger.

---

# 24. Session Cart Architecture

Cart storage:

```text
Laravel Session
```

Conceptual Cart item:

```text
product_id
quantity
display metadata
```

Important:

Stored/displayed session price is not final financial authority.

Checkout must reload/recalculate authoritative Product price.

---

# 25. Checkout Architecture

`CheckoutService` is the orchestration boundary.

Conceptual orchestration:

```text
CheckoutController
       ↓
CheckoutRequest
       ↓
CheckoutService
       │
       ├── CartService
       ├── CouponService
       ├── StockService
       ├── OrderService
       └── PaymentService
```

Approved logical sequence:

```text
Resolve Buyer Context
    ↓
Validate Cart
    ↓
Validate Product
    ↓
Validate Stock
    ↓
Validate Coupon
    ↓
Calculate Server Price
    ↓
Validate Buyer Details
    ↓
Validate Payment Method
    ↓
Validate Transaction ID
    ↓
Create Order Transaction
```

---

# 26. Checkout Transaction Boundary

Critical Checkout writes should be atomic where applicable:

```text
BEGIN TRANSACTION

Create Order
Create Order Items
Create Payment Submission
Deduct Stock
Create Initial Order History

COMMIT
```

On failure:

```text
ROLLBACK
```

Then:

```text
Do not show successful Checkout
Do not clear Cart as successful purchase
```

After successful transaction:

```text
Clear Cart
Redirect to Thank You
```

---

# 27. Stock Architecture

P0 stock source:

```text
products.stock_quantity
```

Stock flow:

```text
Cart Quantity
    ↓
Checkout Revalidation
    ↓
SufficientStock
    ↓
Order Transaction
    ↓
Deduct Stock
```

Advanced inventory ledger is not part of this architecture.

---

# 28. Coupon Architecture

Coupon logic belongs in:

```text
CouponService
+
ValidCoupon Rule where useful
```

Rules:

```text
One Coupon Per Order
No Stacking
Active only
Not expired
Respect minimum Order
Respect date window
Server-calculated discount
```

Console integration:

```text
coupons:expire
    ↓
CouponService
```

where reusable logic applies.

---

# 29. Manual Payment Architecture

This project uses a **manual payment submission architecture**, not a real provider-gateway integration.

Conceptual entities:

```text
PaymentMethod
PaymentSubmission
Order
```

Flow:

```text
Admin configures PaymentMethod
        ↓
bKash / Nagad / Rocket active method
        ↓
Checkout displays number + instruction
        ↓
Customer pays outside ShopPilot
        ↓
Customer enters Transaction ID
        ↓
PaymentSubmission created
        ↓
status = submitted
        ↓
Thank You
        ↓
Authorized staff reviews
        ↓
verified OR rejected
```

---

# 30. Payment Verification Architecture

Payment verification belongs in:

```text
PaymentService
```

Authorization:

```text
Permission
+
PaymentSubmissionPolicy
+
Valid payment state
```

Default authority:

```text
Admin
→ verify + reject

Manager
→ verify by default permission template

Agent
→ none

Customer
→ none

Guest
→ none
```

Core invariant:

```text
submitted != verified
```

---

# 31. Order Architecture

Order is the primary purchase aggregate for the MVP.

Conceptual responsibilities:

```text
Order Number
Buyer Link nullable
Buyer Snapshot
Shipping Snapshot
Assigned Agent nullable
Totals
Payment Status
Order Status
Customer Note
Internal Note
```

Order owns historical purchase context through snapshots.

---

# 32. Order Assignment Architecture

Assignment orchestration belongs in:

```text
OrderAssignmentService
```

Request flow:

```text
Admin / Manager
    ↓
orders.assign
    ↓
Validate target Agent
    ↓
OrderAssignmentService
    ↓
Update assigned Agent
    ↓
Create trace/history
```

Agent cannot self-assign arbitrary Orders.

---

# 33. Order State Transition Architecture

Order status transitions belong in:

```text
OrderStatusService
+
OrderStatus Enum
+
ValidOrderStatusTransition
```

Approved state set:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Normal transition:

```text
pending
→ confirmed
→ processing
→ shipped
→ delivered
```

Cancellation:

```text
pending
confirmed
processing
→ cancelled
```

Invalid transition:

```text
Reject
```

---

# 34. Order History Architecture

`OrderHistory` preserves meaningful Order changes.

Examples:

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

History creation may be coordinated by Services.

Do not hide major business history writes inside unrelated Observers.

---

# 35. Historical Snapshot Architecture

Historical purchase facts must not rely entirely on mutable current data.

## 35.1 Order Buyer Snapshot

Preserve:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

---

## 35.2 Order Item Snapshot

Preserve:

```text
product_name
sku
unit_price
quantity
line_total
```

---

## 35.3 Snapshot Principle

Later edits to:

```text
User Profile
Product Name
Product SKU
Product Price
```

must not rewrite historical Order facts.

---

# 36. Media Architecture

Use:

```text
Spatie Laravel Media Library
```

Collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional
```

Media flow:

```text
Validated Request
    ↓
Authorized Staff
    ↓
Model
    ↓
Spatie Media Library
    ↓
Storage
```

---

# 37. Soft Delete Architecture

Recommended SoftDelete resources:

```text
users
categories
products
coupons
orders
payment_methods
```

Historical records:

```text
order_items
order_histories
payment_submissions
```

should be preserved according to business history requirements.

Force Delete is not required.

---

# 38. Observer Architecture

Possible Observers:

```text
ProductObserver
OrderObserver
UserObserver
```

Allowed responsibilities:

```text
Lightweight lifecycle bookkeeping
Lightweight slug preparation
Dispatch local event
```

Forbidden responsibilities:

```text
Full Checkout
Payment Verification
Complex Stock Mutation
Large workflow
```

Architecture rule:

> Observer must not become hidden business workflow orchestration.

---

# 39. Trait Architecture

Suggested reusable Traits:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

Trait rules:

```text
Use only for genuinely reusable behavior
Keep behavior focused
Do not hide large workflows
```

---

# 40. Console Command Architecture

Minimum P0 command:

```text
coupons:expire
```

Optional P1 commands:

```text
products:low-stock-report
orders:cleanup-pending
```

Preferred command architecture:

```text
Console Command
    ↓
Existing Service
    ↓
Models
```

where reusable business logic already exists.

---

# 41. Queue Architecture

Queue is optional P1.

Appropriate optional use:

```text
Email
Image Conversion
Non-Critical Notification
```

Queue must not be required for:

```text
Core Checkout
Order Creation
Payment Submission
Stock Deduction
```

No Redis/Horizon dependency is required by current approved source documents.

---

# 42. Notification Architecture

P0:

```text
Session Flash Messages
```

P1:

```text
Database Notification
Email Notification
```

Notifications must not become prerequisites for successful Checkout.

---

# 43. Dashboard Architecture

Dashboard data should be produced through:

```text
Controller
    ↓
DashboardService
    ↓
Scoped Eloquent Queries
    ↓
Blade
```

## Admin

```text
System-wide metrics
```

## Manager

```text
Operational metrics allowed by permissions
```

## Agent

```text
Assigned-order metrics only
```

## Customer

```text
Own Order/Profile metrics
```

## Guest

```text
No authenticated dashboard
```

---

# 44. Security Architecture

Mandatory security controls:

```text
Authentication
Spatie Permission
Policies
Ownership Checks
Form Request Validation
CSRF
Mass Assignment Protection
Server-Side Price
Server-Side Discount
Server-Side Stock
Protected Payment Status
Protected Internal Notes
```

Never trust:

```text
role from request
permission from request
assigned_agent_id from Customer
payment_status from Customer
order_status from Customer
price from browser
discount from browser
```

---

# 45. Validation Architecture

Validation layers:

```text
Form Request
+
Custom Rule
+
Service Business Rule
+
Database Constraint where appropriate
```

Example:

```text
CheckoutRequest
    ↓
ValidManualTransactionId
    ↓
CheckoutService
    ↓
StockService / CouponService
```

---

# 46. Error & Denial Architecture

## Authorization Failure

Return Laravel-appropriate denial.

Typical:

```text
403
```

where applicable.

---

## Validation Failure

Return:

```text
Validation errors
```

and preserve user-safe form experience.

---

## Business Rule Failure

Examples:

```text
Insufficient stock
Expired coupon
Invalid order transition
Inactive payment method
```

should return clear controlled application errors.

---

## Transaction Failure

Rollback critical Checkout writes.

Do not show success.

---

# 47. Operational Traceability

P0 traceability:

```text
Order History
Assigned Agent
Payment Verifier
Payment Verification Timestamp
Status Changes
```

P1 generic activity log may be added if time permits.

---

# 48. Testing Architecture

Testing should emphasize business behavior.

## Feature Test Areas

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
Order Transition
History Creation
SoftDelete Visibility
Internal Note Protection
```

## Unit Test Candidates

```text
CouponService
StockService
OrderStatusService
Custom Rules
Enum transition helpers if any
```

Do not chase test quantity at the expense of critical business coverage.

---

# 49. Module Dependency Map

Logical modules:

```text
Authentication
RBAC
Category
Product
Media
Stock
Storefront
Search
Cart
Coupon
Checkout
Payment Method
Payment Submission
Order
Order Assignment
Order History
Dashboard
Notification
```

Core dependency flow:

```text
Category
   ↓
Product
   ↓
Media / Stock
   ↓
Storefront
   ↓
Cart
   ↓
Coupon
   ↓
Checkout
   ↓
Order
   ├── Order Items
   ├── Payment Submission
   ├── Stock Mutation
   └── Order History
        ↓
Payment Verification
        ↓
Agent Assignment
        ↓
Order Processing
```

Cross-cutting:

```text
Auth
RBAC
Policies
Form Requests
Rules
Enums
SoftDeletes
Security
Testing
```

---

# 50. Main Flow Diagrams

## 50.1 Guest Checkout

```text
Guest
  ↓
Storefront
  ↓
Cart
  ↓
CheckoutRequest
  ↓
CheckoutService
  ↓
Stock + Coupon + Price Validation
  ↓
Manual Payment Method
  ↓
Transaction ID
  ↓
Database Transaction
  ↓
Order + Items + Payment Submission + History
  ↓
Stock Deduction
  ↓
Commit
  ↓
Clear Cart
  ↓
Thank You
```

---

## 50.2 Logged-in Customer Checkout

```text
Customer
  ↓
Authenticated Session
  ↓
Cart
  ↓
Checkout
  ↓
Profile Prefill
  ↓
Final Checkout Data
  ↓
CheckoutService
  ↓
Order user_id = Customer
  ↓
Buyer Snapshot preserved
  ↓
Thank You
```

---

## 50.3 Payment Verification

```text
Admin / Authorized Manager
       ↓
Permission
       ↓
PaymentSubmissionPolicy
       ↓
PaymentService
       ↓
Validate Current State
       ↓
verified OR rejected
       ↓
Verifier + Timestamp
       ↓
Trace / History where applicable
```

---

## 50.4 Agent Processing

```text
Admin / Manager
      ↓
orders.assign
      ↓
OrderAssignmentService
      ↓
Assigned Agent
      ↓
Agent Dashboard
      ↓
OrderPolicy
      ↓
OrderStatusService
      ↓
Confirmed
      ↓
Processing
      ↓
Shipped
      ↓
Delivered
```

---

# 51. Data Ownership Boundaries

## Customer

Owns access to:

```text
Own profile
Own authenticated Orders
Own Order details
```

Does not own:

```text
Staff workflow
Internal Notes
Payment verification
Agent assignment
```

---

## Guest

Owns no authenticated resource relationship.

Guest Checkout persists historical snapshot data.

Guest has no P0 account-based Order ownership dashboard.

---

## Agent

Operational access limited to:

```text
Assigned Orders
```

by default.

---

## Manager

Access is permission-driven.

---

## Admin

Broad application authority but still subject to business-state rules.

---

# 52. State Ownership Boundaries

## Product Status

Managed by authorized Product operations.

---

## Coupon Status

Managed by authorized staff and expiry command.

---

## Payment Status

Managed by server-side Payment workflow.

Customer/Guest cannot set it authoritatively.

---

## Order Status

Managed by authorized staff through controlled state transition architecture.

---

# 53. Transaction Boundaries

## 53.1 Required P0 Transaction

Checkout:

```text
Order
Order Items
Payment Submission
Stock Mutation
Initial History
```

---

## 53.2 Payment Verification

If verification updates multiple related records/history, implementation should keep the change internally consistent.

Exact transaction details may be finalized during schema/application-flow design.

---

## 53.3 Agent Assignment

Assignment + history should remain logically consistent.

Exact DB transaction usage may be finalized in Application Flow.

---

# 54. Read / Write Responsibility Matrix

| Concern | Read | Write / Command | Primary Layer |
|---|---|---|---|
| Product | Storefront/Admin | Authorized staff | ProductService / Model |
| Category | Storefront/Admin | Authorized staff | Service/Model |
| Stock | Storefront/Staff | Checkout/Authorized staff | StockService |
| Cart | Session | Customer/Guest | CartService |
| Coupon | Checkout/Admin | Authorized staff | CouponService |
| Checkout | Customer/Guest | Customer/Guest | CheckoutService |
| Order | Staff/Owner | Checkout/Staff | OrderService |
| Assignment | Staff | Admin/Manager | OrderAssignmentService |
| Order Status | Staff/Customer read | Authorized staff | OrderStatusService |
| Payment Method | Checkout/Staff | Admin by default | PaymentService/Model |
| Payment Submission | Staff/Order display | Checkout | PaymentService |
| Payment Verification | Staff | Authorized staff | PaymentService |
| Dashboard | Role-scoped | Read only | DashboardService |
| Media | Storefront/Staff | Authorized staff | Media Library |

---

# 55. Performance & Scope Principles

The project is medium-size and practice-oriented.

Use simple Laravel-native approaches first:

```text
Eloquent eager loading
Pagination
Query scopes
Database indexes later defined by Schema
Server-side filtering
Session Cart
Simple dashboard aggregates
```

Avoid premature:

```text
CQRS
Event Sourcing
Distributed Cache Architecture
Microservices
Elasticsearch
Complex Redis topology
Heavy domain framework
```

unless the project scope is intentionally changed.

---

# 56. Architecture Anti-Patterns

Do not use:

## 56.1 Fat Controllers

Bad:

```text
Controller handles
validation + pricing + stock + coupon + transaction + payment + history
```

Good:

```text
Controller
→ Service
```

---

## 56.2 Fat Blade

Do not calculate authoritative totals or permissions in Blade.

---

## 56.3 Hidden Authorization

Do not rely only on hidden buttons.

---

## 56.4 Fat Observer

Do not hide critical Checkout/payment/order workflow in Observers.

---

## 56.5 Client-Authoritative Financial Data

Do not trust browser price/discount/total.

---

## 56.6 Fake Payment Verification

Do not change:

```text
submitted
```

to:

```text
verified
```

merely because the Customer entered a Transaction ID.

---

## 56.7 Over-Engineering

Do not introduce:

```text
Microservices
Repository abstraction without real need
Complex provider adapter framework for manual MFS
Warehouse subsystem
Complex event bus
```

for this 13-day MVP.

---

# 57. Explicit TBD Architecture Decisions

The following remain intentionally unresolved because the approved source documents do not define them.

## TBD-ARCH-001 — Shipping Calculation

Exact shipping calculation architecture is not defined.

Do not create complex Shipping Engine.

---

## TBD-ARCH-002 — Stock Restore After Cancellation

Automatic stock restoration after cancellation is not defined.

Do not implement silently.

---

## TBD-ARCH-003 — Rejected Payment → Order Status

Do not automatically cancel Order solely because Payment is rejected unless later approved.

---

## TBD-ARCH-004 — Verification Before Fulfillment

Current documents do not require:

```text
payment_status = verified
```

before Agent processing.

Do not invent this dependency.

---

## TBD-ARCH-005 — Assignment Timing

Assignment before/after Payment verification remains undefined.

---

## TBD-ARCH-006 — PaymentSubmission Cardinality

Final choice:

```text
Order hasOne PaymentSubmission
OR
Order hasMany PaymentSubmissions
```

must be finalized in ERD / Schema.

---

## TBD-ARCH-007 — Transaction ID Uniqueness

Global/provider/order-scoped uniqueness is not yet defined.

---

## TBD-ARCH-008 — Coupon Case Sensitivity

Not defined.

---

## TBD-ARCH-009 — Sale Price Activation

Exact Sale Price activation/date logic is not defined.

---

## TBD-ARCH-010 — Guest Tracking

Guest My Orders / tracking architecture is not P0.

---

# 58. Out-of-Scope Architecture

Do not architect P0 systems for:

```text
Multi-Vendor
Marketplace
Real bKash API
Real Nagad API
Real Rocket API
Automatic Payment Verification
Courier API
Complex Product Variants
Multi-Warehouse
Advanced Inventory Ledger
Automatic Refund Gateway
Multi-Currency
Multi-Language
Native Mobile App
Microservices
AI
Accounting
Affiliate
Advanced Tax
```

---

# 59. Suggested Implementation Sequence

Architecture-aware build sequence:

## Phase 1 — Foundation

```text
Laravel Setup
Auth
Core Layout
Database Connection
```

## Phase 2 — RBAC

```text
Spatie Permission
Roles
Permissions
Policies foundation
```

## Phase 3 — Catalog

```text
Category
Product
Media
Stock
```

## Phase 4 — Storefront

```text
Home
Shop
Category
Product Details
Search
```

## Phase 5 — Purchase

```text
Session Cart
Coupon
Checkout
Buyer Snapshot
```

## Phase 6 — Payment

```text
Payment Methods
Manual MFS
Payment Submission
```

## Phase 7 — Order Operations

```text
Order Management
Payment Verification
Agent Assignment
Order Status
Order History
```

## Phase 8 — Reusable Components

```text
Service refinement
Observer
Traits
Rules
Console
Enums
SoftDeletes
```

## Phase 9 — Quality

```text
Security
Feature Tests
Bug Fixes
UI Polish
```

---

# 60. Architecture Acceptance Criteria

Architecture is acceptable when:

- [ ] Application remains one Laravel monolith.
- [ ] Business modules are logically separated.
- [ ] Blade is presentation-focused.
- [ ] Controllers remain thin.
- [ ] Form Requests handle write validation.
- [ ] Spatie Permission controls role/permission capability.
- [ ] Policies enforce resource ownership.
- [ ] Customer cannot access another Customer Order.
- [ ] Agent cannot process another Agent's Order.
- [ ] Services own reusable business workflows.
- [ ] Checkout is orchestrated through `CheckoutService`.
- [ ] Stock is revalidated server-side.
- [ ] Price/discount are server-authoritative.
- [ ] Checkout critical writes are transaction-safe.
- [ ] Guest Checkout works without authentication.
- [ ] Authenticated Orders link to User while preserving snapshots.
- [ ] Guest Orders allow nullable `user_id`.
- [ ] Payment submission creates `submitted`, not `verified`.
- [ ] Payment verification uses permission + Policy + Service.
- [ ] Order transition uses Enum + Rule/Service.
- [ ] Order History preserves important changes.
- [ ] Product/Order historical snapshots remain stable.
- [ ] Media uses Spatie Media Library.
- [ ] SoftDeletes are used only where approved.
- [ ] Observers stay lightweight.
- [ ] Traits stay genuinely reusable.
- [ ] Console command reuses Service logic where appropriate.
- [ ] Queue is not required for core Order creation.
- [ ] No real MFS integration is accidentally introduced.
- [ ] TBD behavior remains TBD instead of being guessed.
- [ ] Out-of-scope enterprise architecture is not introduced.

---

# 61. Definition of Architecture Complete

Before moving to ERD, the following architectural decisions must be clear:

```text
Application Style
Layer Responsibilities
Authorization Layers
Service Boundaries
Checkout Orchestration
Transaction Boundary
Manual Payment Flow
Order Workflow
State Transition Ownership
Historical Snapshot Strategy
SoftDelete Strategy
Observer Boundary
Trait Boundary
Console Boundary
Queue Boundary
Security Boundary
Testing Direction
TBD Decisions
```

This document intentionally leaves exact:

```text
Table columns
Foreign-key delete behavior
Indexes
PaymentSubmission cardinality
Physical folder paths
Exact route files
```

to later documents where appropriate.

---

# 62. Final Architecture Statement

ShopPilot is architected as:

> **A Service-Oriented Modular Laravel Monolith using Blade, MySQL, Eloquent, Spatie Laravel Permission, Spatie Laravel Media Library, Laravel Policies, Form Requests, Services, Custom Rules, Enums, SoftDeletes, lightweight Observers, reusable Traits, Console Commands, session-based Cart, and transaction-safe Checkout.**

The central architectural flow is:

```text
Request
→ Authentication / Permission / Policy
→ Form Request
→ Service
→ Rule / Enum / Model
→ Transaction-safe Persistence
→ Response
```

The central commerce flow is:

```text
Guest / Customer
→ Cart
→ CheckoutService
→ Server Validation
→ Manual MFS Submission
→ Order Transaction
→ Thank You
→ Admin / Authorized Manager Verification
→ Agent Assignment
→ Controlled Order Processing
```

The architecture is intentionally designed to demonstrate professional Laravel practices without turning a 13-day practice project into an enterprise platform.

---

# 63. Next Documentation

Next:

```text
07-DATABASE-ERD.md
```

Then:

```text
08-DATABASE-SCHEMA.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```
