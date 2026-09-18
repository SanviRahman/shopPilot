# ShopPilot E-commerce — Product Requirements Document (PRD)
# শপপাইলট ই-কমার্স — প্রোডাক্ট রিকোয়ারমেন্টস ডকুমেন্ট

> **Document:** Product Requirements Document (PRD)  
> **Project:** ShopPilot E-commerce  
> **Source:** `01-PROJECT-OVERVIEW-UPDATED.md` v1.1  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Primary Goal:** 13-Day Full-Stack Laravel Practice Project  
> **Backend:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **Roles:** Admin, Manager, Agent, User / Customer  
> **Public Buyer:** Guest Customer / Visitor  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Reusable Components:** Services, Observers, Traits, Console Commands, Custom Rules, Policies, Form Requests, Enums  
> **Payment:** Manual bKash / Nagad / Rocket submission, optional COD  
> **Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Implementation-Ready Product Requirements  
> **Next:** `03-FEATURES.md`

---

# 1. Document Purpose

This PRD converts the approved Project Overview into implementation-oriented product requirements.

এই document define করে:

- কে system ব্যবহার করবে
- Guest এবং logged-in Customer কীভাবে purchase করবে
- Admin / Manager / Agent কী access পাবে
- Product, Cart, Checkout, Payment এবং Order flow কী হবে
- কোন rule server-authoritative হবে
- কোন reusable Laravel components ব্যবহার হবে
- কোন feature MVP এবং কোনটা out of scope
- project complete বলতে কী বুঝাবে

This PRD does not define final SQL columns, exact ERD cardinality, final folder placement, or real payment-provider APIs. Those belong to later documents.

---

# 2. Product Summary

ShopPilot is a medium-size single-store Laravel e-commerce practice system.

Core flow:

```text
Guest / Logged-in Customer
        ↓
Storefront
        ↓
Product
        ↓
Cart
        ↓
Checkout
        ↓
bKash / Nagad / Rocket
        ↓
Transaction ID
        ↓
Order
        ↓
Thank You
        ↓
Admin / Manager Review
        ↓
Agent Assignment
        ↓
Order Processing
        ↓
Delivery
```

Both Guest and logged-in Customer can place Orders.

---

# 3. Product Vision

The project must be:

```text
Professional
Practice-Oriented
Finishable in 13 Days
Role-Based
Secure
Reusable
Testable
Laravel-Focused
```

It must demonstrate real Laravel application flow without becoming an enterprise e-commerce platform.

---

# 4. Product Goals

## 4.1 Functional Goals

1. Allow Guest checkout without login.
2. Allow logged-in Customer checkout.
3. Preserve buyer and shipping snapshots on every Order.
4. Allow Admin to manage store operations.
5. Allow Manager through permissions.
6. Allow Agent to process assigned Orders.
7. Support Product, Category, Media and Stock management.
8. Support session-based Cart.
9. Support Coupons.
10. Support manual bKash/Nagad/Rocket payment submission.
11. Keep `submitted` payment separate from `verified`.
12. Support Order history and controlled status transitions.
13. Provide role-specific dashboards.

## 4.2 Technical Practice Goals

```text
Spatie Laravel Permission
Spatie Laravel Media Library
Services
Observers
Traits
Console Commands
Custom Rules
Policies
Form Requests
Enums
SoftDeletes
Transactions
Feature Tests
Blade
```

---

# 5. Non-Goals

Not part of current MVP:

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

# 6. Actors

```text
Admin
Manager
Agent
Logged-in Customer
Guest Customer
System
```

Guest Customer is a public buyer, not an authenticated RBAC role.

---

# 7. Role Model

## 7.1 Admin

Admin can:

- manage staff
- manage roles
- manage permissions
- manage Categories
- manage Products
- manage Stock
- manage Product Media
- manage Coupons
- manage Orders
- assign Agents
- configure MFS numbers
- verify/reject Payment Submissions
- view analytics
- restore supported soft-deleted resources
- manage settings

## 7.2 Manager

Manager is permission-driven.

Possible permissions:

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

Manager does not automatically receive permission-management or sensitive system-settings access.

## 7.3 Agent

Agent primarily handles assigned Orders.

Agent can:

- view assigned Orders
- view delivery information
- confirm Order
- process Order
- mark Shipped
- mark Delivered
- cancel where allowed
- add internal note

Agent cannot manage Product, Role, Permission, payment-method configuration, or system settings by default.

## 7.4 Logged-in Customer

Logged-in Customer can:

- shop
- use Cart
- apply Coupon
- checkout
- submit Transaction ID
- place Order
- view Thank You page
- view My Orders
- view own Order details
- update profile

## 7.5 Guest Customer

Guest can:

- browse Products
- search
- use Cart
- apply Coupon
- checkout without login
- enter buyer/contact/shipping details
- select MFS method
- submit Transaction ID
- place Order
- view Thank You page

Guest does not have My Orders dashboard in MVP.

---

# 8. Authorization Principles

Use:

```text
Spatie Laravel Permission
+
Laravel Policies
+
Middleware
+
Business Rules
```

Permission naming:

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
payment-methods.manage
```

UI hiding is not security.

Every protected backend action must authorize independently.

---

# 9. Authentication Requirements

### AUTH-001 — Customer Registration
**Priority:** P0  
Customer can register.

### AUTH-002 — Login
**Priority:** P0  
Registered Customer and staff can login.

### AUTH-003 — Logout
**Priority:** P0

### AUTH-004 — Password Security
**Priority:** P0  
Passwords use Laravel-supported secure hashing.

### AUTH-005 — Staff Route Protection
**Priority:** P0  
Admin/Manager/Agent routes require authentication.

### AUTH-006 — Customer Account Protection
**Priority:** P0  
My Account and My Orders require authentication.

### AUTH-007 — Guest Checkout
**Priority:** P0  
Guest purchase must work without authentication.

### AUTH-008 — Forgot Password
**Priority:** P1

### AUTH-009 — Email Verification
**Priority:** P1

---

# 10. Guest Checkout Requirements

### GUEST-001
Guest can access public Storefront.

### GUEST-002
Guest can use session Cart.

### GUEST-003
Guest can apply valid Coupon.

### GUEST-004
Guest can proceed directly to Checkout.

### GUEST-005
Guest Checkout collects:

```text
name
phone
email
address
city_or_area
order_note optional
```

### GUEST-006
Guest can select active Payment Method.

### GUEST-007
Guest can submit Transaction ID.

### GUEST-008
Guest can create Order without User account.

### GUEST-009
Guest Order may have:

```text
user_id = null
```

### GUEST-010
Guest Order must preserve buyer/contact/address snapshots.

### GUEST-011
Guest receives Thank You page.

### GUEST-012
Guest has no authenticated My Orders dashboard in MVP.

---

# 11. Logged-in Customer Requirements

### CUSTOMER-001
Customer can view/update Profile.

### CUSTOMER-002
Customer can Checkout while authenticated.

### CUSTOMER-003
Profile details may prefill Checkout.

### CUSTOMER-004
Customer can modify delivery details before submission.

### CUSTOMER-005
Authenticated Order links to Customer User.

### CUSTOMER-006
Order still stores historical buyer/shipping snapshots.

### CUSTOMER-007
Customer can view own Orders.

### CUSTOMER-008
Customer cannot view another Customer's Order.

### CUSTOMER-009
Customer cannot see staff Internal Notes.

---

# 12. Staff & Permission Requirements

Use Spatie Laravel Permission.

Suggested permissions:

```text
dashboard.view

users.view
users.update

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

### RBAC-001
Admin controls roles/permissions.

### RBAC-002
Manager/Agent receives explicit permissions.

### RBAC-003
Permission checks are enforced server-side.

### RBAC-004
Blade may hide unauthorized controls but is not authoritative.

---

# 13. Category Requirements

Fields:

```text
name
slug
description
image
status
sort_order
```

### CAT-001
Authorized staff can list Categories.

### CAT-002
Authorized staff can create.

### CAT-003
Authorized staff can update.

### CAT-004
Authorized staff can soft-delete.

### CAT-005
Authorized staff can restore.

### CAT-006
MVP uses one-level Category only.

---

# 14. Product Requirements

Fields:

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
```

### PROD-001
Authorized staff can CRUD Product.

### PROD-002
Product uses SoftDelete.

### PROD-003
Inactive Product is hidden from normal Storefront.

### PROD-004
Deleted Product is hidden from normal Storefront.

### PROD-005
Server is authoritative for Product Price.

### PROD-006
Complex variants are out of scope.

---

# 15. Media Requirements

Use Spatie Laravel Media Library.

Collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional
```

### MEDIA-001
Product supports Thumbnail.

### MEDIA-002
Product supports Gallery.

### MEDIA-003
Category supports Image.

### MEDIA-004
Media Upload requires validation.

### MEDIA-005
Authorized staff can replace/delete Media.

---

# 16. Stock Requirements

Use simple:

```text
products.stock_quantity
```

### STOCK-001
`stock_quantity > 0` means available.

### STOCK-002
`stock_quantity = 0` means out of stock.

### STOCK-003
Checkout revalidates Stock.

### STOCK-004
Insufficient Stock blocks Order.

### STOCK-005
Order creation deducts Stock.

### STOCK-006
Stock mutation should be transaction-safe.

### STOCK-007
Advanced stock ledger is out of scope.

---

# 17. Storefront Requirements

Required public pages:

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

Authenticated Customer pages:

```text
My Account
My Orders
Order Details
```

---

# 18. Search Requirements

### SEARCH-001
Search by Product Name.

### SEARCH-002
SKU search optional.

### SEARCH-003
Filter by Category.

### SEARCH-004
Optional availability filter.

### SEARCH-005
Optional sorting:

```text
Latest
Price Low to High
Price High to Low
```

---

# 19. Cart Requirements

MVP Cart:

```text
Session-Based Cart
```

### CART-001
Add to Cart.

### CART-002
Update Quantity.

### CART-003
Remove Item.

### CART-004
Clear Cart.

### CART-005
Calculate Subtotal.

### CART-006
Apply Coupon Discount.

### CART-007
Calculate Grand Total.

### CART-008
Guest can use Cart.

### CART-009
Logged-in Customer can use Cart.

### CART-010
Client-provided Product Price is never authoritative.

---

# 20. Coupon Requirements

Fields:

```text
code
discount_type
discount_value
minimum_order_amount
start_date
end_date
status
```

Types:

```text
fixed
percentage
```

Rules:

### COUPON-001
One Coupon per Order.

### COUPON-002
No stacking.

### COUPON-003
Inactive Coupon rejected.

### COUPON-004
Expired Coupon rejected.

### COUPON-005
Discount calculated server-side.

---

# 21. Checkout Requirements

Checkout supports:

```text
Guest Customer
Logged-in Customer
```

Checkout fields:

```text
name
phone
email
address
city_or_area
order_note
payment_method
transaction_id
```

Pipeline:

```text
Resolve Buyer Context
   ↓
Cart Validation
   ↓
Product Validation
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

### CHECKOUT-001
Authentication is not mandatory for Guest.

### CHECKOUT-002
Authenticated profile may prefill data.

### CHECKOUT-003
Final submitted checkout data is preserved in Order.

### CHECKOUT-004
Totals are recalculated server-side.

### CHECKOUT-005
Order + Items + Payment Submission should be created atomically where applicable.

### CHECKOUT-006
Successful checkout clears Cart.

### CHECKOUT-007
Successful checkout redirects to Thank You page.

---

# 22. Buyer Snapshot Requirements

Every Order stores historical:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

### SNAP-001
Logged-in Order stores `user_id`.

### SNAP-002
Guest Order allows nullable `user_id`.

### SNAP-003
Both Guest and logged-in Order store buyer snapshots.

### SNAP-004
Later profile change must not rewrite old Order snapshots.

---

# 23. Manual Payment Method Requirements

Core methods:

```text
bKash
Nagad
Rocket
```

Admin configuration:

```text
name
code
account_number
account_type
instruction
status
```

### PAYMETHOD-001
Only active method appears at Checkout.

### PAYMETHOD-002
Only authorized staff can edit MFS numbers.

### PAYMETHOD-003
Checkout displays configured number and instruction.

### PAYMETHOD-004
No real provider API is used.

### PAYMETHOD-005
Optional COD may be added if time permits.

---

# 24. Payment Submission Requirements

Suggested entity:

```text
payment_submissions
```

Conceptual data:

```text
order_id
payment_method_id
transaction_id
amount
status
verified_by
verified_at
rejection_note
```

### PAYSUB-001
Transaction ID required for manual MFS.

### PAYSUB-002
Payment Submission belongs to Order.

### PAYSUB-003
Payment Submission belongs to Payment Method.

### PAYSUB-004
Initial status is:

```text
submitted
```

### PAYSUB-005
Transaction format validation is not real provider verification.

---

# 25. Payment Verification Requirements

Payment statuses:

```text
unpaid
submitted
verified
rejected
```

### PAYVERIFY-001
`submitted != verified`.

### PAYVERIFY-002
Admin can review Payment Submission.

### PAYVERIFY-003
Authorized Manager can review when permission granted.

### PAYVERIFY-004
Authorized staff can verify.

### PAYVERIFY-005
Authorized staff can reject.

### PAYVERIFY-006
Verification stores verifier identity.

### PAYVERIFY-007
Verification stores timestamp.

### PAYVERIFY-008
Rejection may store note.

### PAYVERIFY-009
Customer/Guest cannot verify Payment.

### PAYVERIFY-010
Thank You page is allowed before verification.

Recommended Customer message:

> **Order placed successfully. Your payment information has been submitted for verification.**

---

# 26. Order Requirements

Order fields conceptually include:

```text
order_number
user_id nullable
assigned_agent_id nullable
subtotal
discount
shipping
grand_total
payment_method
payment_status
order_status
buyer_snapshot
shipping_snapshot
customer_note
internal_note
```

### ORDER-001
Checkout creates Order.

### ORDER-002
Order has unique human-readable Order Number.

Example:

```text
ORD-2026-000001
```

### ORDER-003
Guest Order supports nullable `user_id`.

### ORDER-004
Logged-in Order links to Customer.

### ORDER-005
Order and Payment statuses are separate.

### ORDER-006
Order preserves historical buyer data.

### ORDER-007
Selected Order records may use SoftDelete.

---

# 27. Order Item Requirements

Order Item snapshot:

```text
product_id
product_name
sku
unit_price
quantity
line_total
```

### ORDERITEM-001
Order has one or more Items.

### ORDERITEM-002
Product Name snapshot is preserved.

### ORDERITEM-003
SKU snapshot is preserved.

### ORDERITEM-004
Unit Price snapshot is preserved.

### ORDERITEM-005
Product edits do not rewrite historical Order Item values.

---

# 28. Order Assignment Requirements

### ASSIGN-001
Admin can assign Agent.

### ASSIGN-002
Authorized Manager can assign Agent.

### ASSIGN-003
Assignment permission:

```text
orders.assign
```

### ASSIGN-004
Agent sees assigned Orders.

### ASSIGN-005
Agent cannot process another Agent's Order by default.

### ASSIGN-006
Authorized staff may reassign.

---

# 29. Order Status Requirements

Statuses:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Normal flow:

```text
pending
→ confirmed
→ processing
→ shipped
→ delivered
```

### STATUS-001
New Order begins `pending`.

### STATUS-002
Invalid transition rejected.

### STATUS-003
Delivered must not casually return to Pending.

### STATUS-004
Cancellation only from allowed states.

### STATUS-005
Use Service/Rule for transition validation.

### STATUS-006
Use PHP Enum for status values.

---

# 30. Order History Requirements

Suggested entity:

```text
order_histories
```

Possible data:

```text
order_id
user_id nullable
from_status
to_status
note
created_at
```

### HISTORY-001
Order creation may create initial timeline event.

### HISTORY-002
Agent assignment should be traceable.

### HISTORY-003
Payment verification should be traceable.

### HISTORY-004
Order status changes must be recorded.

### HISTORY-005
History is preserved.

---

# 31. Guest Order Behavior

### GUESTORDER-001
Guest Order is valid without User record.

### GUESTORDER-002
Backoffice processing is same after Order is created.

### GUESTORDER-003
Buyer information comes from Order snapshots.

### GUESTORDER-004
Guest does not receive My Orders dashboard.

### GUESTORDER-005
Guest Order tracking link is P1/Future.

---

# 32. Customer Order History Requirements

Logged-in Customer can view:

```text
Order Number
Date
Items
Total
Payment Method
Payment Status
Order Status
```

### CUSTOMERORDER-001
Only own Orders.

### CUSTOMERORDER-002
Internal Notes hidden.

### CUSTOMERORDER-003
Direct ID manipulation must not expose another Customer's Order.

---

# 33. Dashboard Requirements

## Admin

```text
Total Sales
Total Orders
Pending Orders
Delivered Orders
Cancelled Orders
Total Customers
Total Products
Low Stock
Pending Payment Verification
Recent Orders
```

## Manager

```text
Today's Orders
Pending
Processing
Delivered
Unassigned Orders
Agents
Low Stock
```

## Agent

```text
My Orders
Pending
Confirmed
Processing
Shipped
Delivered
Cancelled
```

## Customer

```text
My Orders
Pending
Delivered
Cancelled
Profile
```

Guest has no dashboard.

---

# 34. Service Layer Requirements

Required practice Services:

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

### SERVICE-001
Controllers stay thin.

### SERVICE-002
Checkout workflow belongs in CheckoutService.

### SERVICE-003
Payment verification belongs in PaymentService.

### SERVICE-004
Order transition belongs in OrderStatusService.

### SERVICE-005
Shared Stock logic belongs in StockService.

---

# 35. Observer Requirements

At least one meaningful Observer.

Possible:

```text
ProductObserver
OrderObserver
UserObserver
```

Allowed:

```text
Lightweight model lifecycle
Small local bookkeeping
Dispatch event
```

Not allowed:

```text
Full Checkout
Payment verification
Complex stock transaction
Large workflow
```

---

# 36. Trait Requirements

Suggested:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

### TRAIT-001
At least one meaningful Trait.

### TRAIT-002
Do not create a Trait only to satisfy checklist count.

---

# 37. Custom Rule Requirements

Suggested:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

### RULE-001
At least two meaningful Custom Rules.

### RULE-002
Transaction ID Rule validates format/basic requirement only.

### RULE-003
Status transition validation rejects invalid transition.

---

# 38. Console Command Requirements

Required minimum:

```text
coupons:expire
```

Optional:

```text
products:low-stock-report
orders:cleanup-pending
```

### CONSOLE-001
Console Command should reuse Service logic where appropriate.

---

# 39. Policy Requirements

Suggested:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

Core rules:

### POLICY-001
Customer can only view own Order.

### POLICY-002
Agent processes assigned Order only by default.

### POLICY-003
Payment verification requires permission.

### POLICY-004
Guest checkout uses public business validation, not authenticated ownership.

---

# 40. Form Request Requirements

Suggested:

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

### REQUEST-001
Write operations use Form Request.

### REQUEST-002
CheckoutRequest supports Guest and logged-in context.

---

# 41. Enum Requirements

Suggested:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

### ENUM-001
Avoid critical status magic strings.

### ENUM-002
Enums define values; Service/Rule defines allowed transitions.

---

# 42. Soft Delete Requirements

Recommended SoftDeletes:

```text
users
categories
products
coupons
orders
payment_methods
```

Historical:

```text
order_items
order_histories
payment_submissions
```

should be preserved as required.

### SOFT-001
Deleted Product hidden from Storefront.

### SOFT-002
Authorized restore supported where applicable.

### SOFT-003
Force Delete is not required for MVP.

---

# 43. Queue Requirements

Queue is optional for core completion.

Optional queue use:

```text
Email
Image Conversion
Non-Critical Notification
```

Core Order creation must not depend on unnecessary Queue complexity.

---

# 44. Validation Requirements

### VALID-001
All writes validated server-side.

### VALID-002
Price server-calculated.

### VALID-003
Discount server-calculated.

### VALID-004
Stock server-validated.

### VALID-005
Coupon server-validated.

### VALID-006
Payment Method server-validated.

### VALID-007
Transaction ID server-validated.

### VALID-008
Media validated.

### VALID-009
Agent assignment target validated.

### VALID-010
Guest buyer details required.

---

# 45. Security Requirements

### SEC-001
Passwords securely hashed.

### SEC-002
CSRF protection.

### SEC-003
Mass-assignment protection.

### SEC-004
Staff permission enforcement.

### SEC-005
Customer Order ownership.

### SEC-006
Agent assigned-Order ownership.

### SEC-007
Client Price not trusted.

### SEC-008
Client Discount not trusted.

### SEC-009
Client Payment Status not trusted.

### SEC-010
Transaction ID not considered verified proof.

### SEC-011
Internal Notes hidden from public/customer.

### SEC-012
Guest cannot expose arbitrary Order by ID.

### SEC-013
Payment-method configuration protected.

---

# 46. Data & Entity Requirements

Application-owned entities:

```text
users
categories
products
coupons
orders
order_items
order_histories
payment_methods
payment_submissions
```

Optional:

```text
addresses
activity_logs
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

Framework:

```text
sessions
password_reset_tokens
notifications
```

Final physical schema belongs in:

```text
08-DATABASE-SCHEMA.md
```

---

# 47. Logical Relationships

```text
User
├── hasMany Orders
├── hasMany Assigned Orders
└── Roles / Permissions

Category
└── hasMany Products

Product
└── belongsTo Category

Order
├── belongsTo Customer/User nullable
├── belongsTo Assigned Agent/User nullable
├── stores Buyer/Shipping Snapshot
├── hasMany OrderItems
├── hasMany OrderHistories
└── hasOne / hasMany PaymentSubmission

PaymentSubmission
├── belongsTo Order
├── belongsTo PaymentMethod
└── belongsTo Verifier/User nullable
```

---

# 48. Business Rules

## BR-001 — Guest Checkout
Guest can place Order without login.

## BR-002 — Logged-in Checkout
Authenticated Customer can place Order.

## BR-003 — Nullable User Reference
Guest Order can have `user_id = null`.

## BR-004 — Buyer Snapshot
Every Order preserves buyer/contact/shipping snapshots.

## BR-005 — Customer Ownership
Authenticated Customer can access own account Orders only.

## BR-006 — Agent Ownership
Agent processes assigned Orders only unless broader permission exists.

## BR-007 — Server Price Authority
Server recalculates Price, Discount, and Total.

## BR-008 — Stock Validation
Requested quantity cannot exceed available Stock.

## BR-009 — Coupon Validation
Inactive/expired/invalid Coupon rejected.

## BR-010 — MFS Configuration
Only authorized staff can configure bKash/Nagad/Rocket numbers.

## BR-011 — Transaction ID Required
Manual MFS requires Transaction ID.

## BR-012 — Submission Is Not Verification
Payment Submission starts as `submitted`.

## BR-013 — Payment Verification
Only authorized staff can verify/reject.

## BR-014 — Separate Statuses
Order Status and Payment Status are separate.

## BR-015 — Historical Product Snapshot
Product edits do not rewrite Order Item history.

## BR-016 — Order History
Important Order changes are traceable.

## BR-017 — Soft Delete
Selected recoverable business records use SoftDeletes.

## BR-018 — Thank You Meaning
Thank You confirms successful Order/payment-information submission, not independent provider verification.

---

# 49. Status Model

## Product

```text
active
inactive
```

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

# 50. Manual Payment Flow

```text
Guest / Logged-in Customer
        ↓
Select bKash / Nagad / Rocket
        ↓
System Shows Admin Number
        ↓
Customer Pays Externally
        ↓
Enter Transaction ID
        ↓
Server Validation
        ↓
Create Order
        ↓
Create Payment Submission
        ↓
Payment = submitted
        ↓
Thank You
        ↓
Admin / Authorized Manager Review
        ↓
verified / rejected
```

Invariant:

```text
submitted != verified
```

---

# 51. Non-Functional Requirements

### NFR-001 — Maintainability
Use clean Laravel structure.

### NFR-002 — Readability
Consistent naming.

### NFR-003 — Security
Backend authorization and validation.

### NFR-004 — Performance
Pagination for large lists.

### NFR-005 — Query Efficiency
Avoid obvious N+1 problems.

### NFR-006 — Reliability
Order creation should be atomic.

### NFR-007 — Recoverability
SoftDelete where selected.

### NFR-008 — Testability
Critical flows require automated tests.

### NFR-009 — Scope Discipline
Do not overbuild beyond the 13-day target.

### NFR-010 — Reusability
Use Services/Rules/Traits where justified.

---

# 52. MVP Scope

Must-have:

```text
Authentication
Admin
Manager
Agent
Logged-in Customer
Guest Checkout
Spatie Permission
Category CRUD
Product CRUD
Spatie Media Library
Stock
Storefront
Search
Session Cart
Coupon
Guest + Logged-in Checkout
bKash / Nagad / Rocket Configuration
Transaction ID Submission
Thank You Page
Order
Order Items
Buyer Snapshots
Order History
Payment Submission
Payment Verification
Admin Order Management
Manager → Agent Assignment
Agent Order Processing
Customer My Orders
Dashboard
Services
Observer
Trait
Custom Rules
Console Command
Policies
Form Requests
Enums
SoftDeletes
Critical Tests
```

---

# 53. Optional / P1 Scope

```text
Forgot Password
Email Verification
User Avatar
Database Notifications
Email Notifications
Guest Order Tracking Link
Generic Activity Log
COD
Advanced Filters
```

---

# 54. 13-Day Delivery Plan

```text
Day 1
Laravel Setup
Authentication
Database Planning

Day 2
Spatie Permission
Roles
Permissions
Dashboards

Day 3
Category CRUD
Media Setup

Day 4
Product CRUD
Stock
Product Media

Day 5
Storefront
Search
Product Details

Day 6
Session Cart
Coupon

Day 7
Guest + Logged-in Checkout
Manual MFS Display

Day 8
Order
Order Items
Buyer Snapshots
Payment Submission
Thank You

Day 9
Admin Orders
Payment Verification

Day 10
Manager → Agent Assignment

Day 11
Agent Workflow
Customer My Orders

Day 12
Services
Observer
Trait
Rules
Console
Policies
Security
Dashboard

Day 13
Tests
Bug Fix
UI Polish
README
Git Cleanup
```

---

# 55. Acceptance Criteria

## Guest

- Guest can purchase without login.
- Guest Order has nullable User reference.
- Buyer snapshots are stored.
- Guest receives Thank You page.

## Logged-in Customer

- Customer can purchase.
- Order links to Customer.
- Order snapshots are stored.
- Customer sees Order in My Orders.

## RBAC

- Agent cannot access Admin-only feature.
- Missing permission denies protected action.
- Blade hiding is not the only authorization.

## Product

- CRUD works.
- Media works.
- Deleted/inactive Product hidden.

## Cart

- Add/update/remove works.
- Guest and authenticated Customer supported.

## Coupon

- Valid Coupon applies.
- Invalid/expired Coupon fails.
- No stacking.

## Checkout

- Server recalculates Totals.
- Stock is revalidated.
- Transaction ID required for MFS.

## Payment

- New submission is `submitted`.
- Thank You page appears.
- Customer/Guest cannot verify.
- Authorized staff can verify/reject.

## Order

- Order Items preserve snapshots.
- Agent assignment works.
- Agent ownership works.
- Status transition is controlled.
- History is recorded.

---

# 56. Testing Requirements

Critical tests:

```text
Guest can place Order without login
Guest Order user_id is null
Guest snapshot stored

Logged-in Customer can place Order
Logged-in Order user_id is set
Customer sees own Order

Customer A cannot see Customer B Order

Agent A cannot process Agent B Order

Missing permission blocks staff action

Soft-deleted Product not shown

Insufficient Stock blocks Checkout

Expired Coupon rejected

Modified browser Price ignored / recalculated

MFS without Transaction ID fails

Payment Submission starts submitted

Customer cannot verify Payment

Authorized staff can verify Payment

Authorized staff can reject Payment

Invalid Order transition rejected

Valid transition creates Order History
```

---

# 57. Risks & Mitigations

## Scope Creep

Mitigation:

```text
P0 First
No Real Payment API
No Courier API
No Complex Variants
No Enterprise Modules
```

## Guest/Auth Flow Conflict

Mitigation:

```text
Explicit Buyer Context
Nullable user_id
Mandatory Order Snapshot
Shared CheckoutService
```

## Fake Payment Assumption

Mitigation:

```text
submitted != verified
Manual Staff Verification
Clear Thank You Message
```

## Unauthorized Access

Mitigation:

```text
Permission
Policy
Ownership
Feature Tests
```

## Stock Errors

Mitigation:

```text
Checkout Revalidation
Transaction
StockService
Tests
```

---

# 58. Definition of Done

Project MVP is complete when:

- [ ] Authentication works.
- [ ] Admin works.
- [ ] Manager works.
- [ ] Agent works.
- [ ] Customer works.
- [ ] Guest Checkout works.
- [ ] Logged-in Checkout works.
- [ ] Spatie Permission works.
- [ ] Category CRUD works.
- [ ] Product CRUD works.
- [ ] Media Library works.
- [ ] Stock works.
- [ ] Storefront works.
- [ ] Search works.
- [ ] Cart works.
- [ ] Coupon works.
- [ ] MFS configuration works.
- [ ] Transaction ID submission works.
- [ ] Thank You works.
- [ ] Payment remains submitted before verification.
- [ ] Verify/reject works.
- [ ] Guest Order supports nullable User reference.
- [ ] Logged-in Order links to Customer.
- [ ] Buyer snapshots work.
- [ ] Order Items preserve snapshots.
- [ ] Order History works.
- [ ] Manager can assign Agent.
- [ ] Agent processes assigned Orders.
- [ ] Customer sees own Orders only.
- [ ] Service layer is meaningful.
- [ ] Observer is meaningful.
- [ ] Trait is meaningful.
- [ ] Custom Rules are meaningful.
- [ ] Console Command works.
- [ ] Policies are implemented.
- [ ] Form Requests are used.
- [ ] Enums control statuses.
- [ ] SoftDeletes work.
- [ ] Critical tests pass.
- [ ] Project remains inside the 13-day scope.

---

# 59. Requirement Traceability

| Project Overview Area | PRD Section |
|---|---|
| Guest Checkout | 10, 21, 22, 31 |
| Logged-in Customer | 11, 21, 32 |
| RBAC | 7, 8, 12 |
| Category | 13 |
| Product | 14 |
| Media | 15 |
| Stock | 16 |
| Storefront | 17 |
| Search | 18 |
| Cart | 19 |
| Coupon | 20 |
| Checkout | 21 |
| Buyer Snapshot | 22 |
| Manual Payment | 23–25, 50 |
| Order | 26–32 |
| Dashboard | 33 |
| Services | 34 |
| Observer | 35 |
| Trait | 36 |
| Rules | 37 |
| Console | 38 |
| Policies | 39 |
| Requests | 40 |
| Enums | 41 |
| SoftDelete | 42 |
| Security | 45 |
| Entities | 46 |
| Relationships | 47 |
| Business Rules | 48 |
| MVP | 52 |
| Delivery Plan | 54 |
| Tests | 56 |

---

# 60. Next Documentation

Next document:

```text
03-FEATURES.md
```

Then:

```text
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

---

# 61. Final PRD Statement

**ShopPilot E-commerce** is a medium-size role-based Laravel e-commerce practice application where **both Guest Customers and logged-in Customers can purchase products**.

Guest checkout does not require login and may create an Order with a nullable authenticated User reference. Logged-in purchases link to the Customer account. In both cases, the Order preserves buyer/contact/shipping snapshots for historical accuracy.

Customers can select an Admin-configured bKash/Nagad/Rocket payment method, submit a Transaction ID, receive a Thank You page, and have the payment submission reviewed separately by authorized staff.

The project intentionally practices:

> **Spatie Permission + Spatie Media Library + Services + Observers + Traits + Console Commands + Custom Rules + Policies + Form Requests + Enums + SoftDeletes + Feature Tests**

while preserving the 13-day completion target.
