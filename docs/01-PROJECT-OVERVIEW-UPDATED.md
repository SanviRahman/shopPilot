# ShopPilot E-commerce — Project Overview
# শপপাইলট ই-কমার্স — প্রজেক্ট ওভারভিউ

> **Document:** Project Overview / প্রজেক্ট ওভারভিউ  
> **Project Name:** ShopPilot E-commerce  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Primary Goal:** 13-Day Full-Stack Laravel Practice Project  
> **Backend Framework:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **Primary Roles:** Admin, Manager / Agent, User / Customer  
> **Public Buyer:** Guest Customer / Visitor  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Architecture Style:** Service-Oriented Modular Laravel Monolith  
> **Reusable Components:** Services, Observers, Traits, Console Commands, Custom Rules  
> **Payment Type:** Manual Mobile Financial Service Payment (bKash / Nagad / Rocket) + optional COD  
> **Document Version:** 1.1  
> **Language:** English + Bangla  
> **Status:** Project Definition / Practice Build Scope  
> **Target Completion Window:** 13 Days  

---

# 1. Project Introduction / প্রজেক্ট পরিচিতি

**ShopPilot E-commerce** is a medium-size single-store e-commerce application designed for professional Laravel full-stack practice.

এখানে **logged-in Customer এবং Guest Customer—দুইজনই** product browse করে cart/checkout-এর মাধ্যমে order place করতে পারবে। Admin, Manager এবং Agent backend থেকে product, stock, order, payment submission এবং delivery workflow manage করবে।

এই project-এর উদ্দেশ্য tutorial-level CRUD না; বরং একটি realistic but finishable Laravel project build করা।

Core practice areas:

```text
Authentication
Spatie Permission
Spatie Media Library
CRUD
Blade Frontend
Services
Observers
Traits
Console Commands
Custom Rules
Policies
Form Requests
Enums
Cart
Checkout
Manual Payment
Order Workflow
Agent Assignment
Dashboard
Testing
```

---

# 2. Project Vision / প্রজেক্ট ভিশন

The project vision is:

> **Customer shopping flow + Admin operations + Manager/Agent order processing + Manual MFS payment submission — all inside one clean Laravel project.**

১৩ দিনের মধ্যে project complete করার জন্য scope intentionally controlled থাকবে।

এটি হবে না:

```text
Marketplace
Multi-Vendor
Enterprise ERP
Real-Time Payment Gateway API Project
Courier Integration Platform
```

বরং এটি হবে একটি complete **single-store e-commerce practice application**।

---

# 3. Main User Roles / মূল Role

```text
Admin
Manager
Agent
User / Customer
Guest Customer / Visitor
```

Role এবং permission manage করতে ব্যবহার হবে:

```text
Spatie Laravel Permission
```

Authorization layers:

```text
Authentication
    ↓
Role / Permission
    ↓
Policy / Resource Ownership
    ↓
Business Rule
    ↓
Action / Service
```

---

# 4. Admin Role

Admin পুরো application control করবে।

Admin can:

- Manage Manager / Agent
- Manage customer users
- Manage roles
- Manage permissions
- Manage categories
- Manage products
- Manage stock
- Manage product media
- Manage coupons
- Manage orders
- Assign orders
- Update order status
- Configure bKash / Nagad / Rocket numbers
- Verify submitted transaction IDs
- Reject payment submissions
- View dashboard analytics
- View pending payment submissions
- Restore soft-deleted records
- Manage store settings

Sensitive permissions:

```text
staff.manage
roles.manage
permissions.manage
payment-methods.manage
settings.manage
payments.verify
payments.reject
```

---

# 5. Manager Role

Manager operational control করবে।

Manager may receive:

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

Manager defaultভাবে করতে পারবে না:

```text
Admin account management
Role architecture change
Permission architecture change
Sensitive system settings
```

---

# 6. Agent Role

Agent মূলত assigned order process করবে।

Agent dashboard:

```text
My Assigned Orders
Pending
Confirmed
Processing
Shipped
Delivered
Cancelled
```

Agent may:

- View assigned orders
- View customer delivery details
- Confirm assigned order
- Move order to Processing
- Mark Shipped
- Mark Delivered
- Cancel where allowed
- Add internal note

Agent defaultভাবে পারবে না:

```text
Manage products
Manage staff
Manage roles
Manage permissions
Configure payment numbers
Manage settings
```

Security rule:

```text
Agent A
opens Agent B assigned order
→ DENY
```

unless a broader permission is explicitly granted.

---

# 7. User / Customer & Guest Customer

## 7.1 Logged-in User / Customer

Logged-in Customer can:

- Register
- Login
- Logout
- Browse products
- Search products
- Browse category
- View product details
- Add product to cart
- Update cart quantity
- Remove item
- Apply coupon
- Checkout
- Select payment method
- Submit transaction ID
- Place order
- View Thank You page
- View own orders
- View own order details
- View order status
- Update profile

## 7.2 Guest Customer / Visitor

Guest Customer can purchase without creating an account or logging in.

Guest Customer can:

- Browse products
- Search products
- Browse category
- View product details
- Add product to cart
- Update cart quantity
- Remove item
- Apply coupon
- Checkout as Guest
- Enter name, phone, email and delivery address
- Select payment method
- Submit transaction ID
- Place order
- View Thank You page

Guest Customer will not have an authenticated **My Orders** dashboard in the core MVP. Guest order ownership will be preserved using order/customer snapshots while `user_id` may remain nullable.

Logged-in Customer and Guest Customer must not access:

```text
Admin Dashboard
Manager Dashboard
Agent Dashboard
Other Customer Orders
Internal Notes
Payment Verification Controls
```

---

# 8. Main Customer Flow

```text
Visitor / Logged-in Customer
   ↓
Home / Shop
   ↓
Category / Product
   ↓
Product Details
   ↓
Add To Cart
   ↓
Cart
   ↓
Checkout Choice
   ├── Continue as Guest
   └── Login / Register / Already Logged In
   ↓
Checkout
   ↓
Coupon / Total Calculation
   ↓
Choose Payment Method
   ↓
Manual Payment Instruction
   ↓
Submit Transaction ID
   ↓
Order Created
   ↓
Payment Submission Recorded
   ↓
Thank You Page
   ↓
Backoffice Processing
```

---

# 9. Product Module

Product fields:

```text
Name
Slug
SKU
Category
Short Description
Description
Regular Price
Sale Price
Stock Quantity
Status
Featured
Thumbnail
Gallery
Created At
Updated At
Deleted At
```

MVP product status:

```text
active
inactive
```

Complex variants:

```text
Size
Color
Variant Pricing
Variant Stock
```

will remain out of scope for the 13-day core build.

---

# 10. Category Module

Category fields:

```text
Name
Slug
Description
Image
Status
Sort Order
```

MVP category structure:

```text
Single-Level Category
```

Nested categories are optional future scope.

---

# 11. Media Module

Use:

```text
Spatie Laravel Media Library
```

Recommended collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar
```

Practice features:

```text
Upload
Replace
Delete
Display
Validation
```

---

# 12. Stock Module

Simple stock strategy:

```text
products.stock_quantity
```

Rules:

```text
stock_quantity > 0
→ In Stock

stock_quantity = 0
→ Out of Stock
```

Checkout must validate stock server-side.

Order placement should deduct stock inside the order transaction.

Advanced inventory ledger and warehouse management are out of scope.

---

# 13. Storefront Module

Public/customer pages:

```text
Home
Shop
Category
Product Details
Search Results
Cart
Checkout
Manual Payment Instructions
Thank You
Login
Register
My Account
My Orders
Order Details
```

Home may show:

```text
Featured Products
Latest Products
Categories
Sale Products
```

---

# 14. Cart Module

Recommended:

```text
Session-Based Cart
```

Cart features:

```text
Add To Cart
Update Quantity
Remove Item
Clear Cart
Subtotal
Coupon Discount
Grand Total
```

All authoritative price calculations must happen server-side.

---

# 15. Coupon Module

Coupon fields:

```text
Code
Discount Type
Discount Value
Minimum Order Amount
Start Date
End Date
Status
```

Discount types:

```text
fixed
percentage
```

MVP rules:

```text
One Coupon Per Order
No Coupon Stacking
Inactive Coupon = Reject
Expired Coupon = Reject
Server-Side Calculation
```

---

# 16. Checkout Module

Checkout fields:

```text
Name
Phone
Email
Address
City / Area
Order Note
Payment Method
Transaction ID when required
```

For authenticated Customers, profile data may prefill checkout fields, but the final order must preserve checkout snapshots. Guest Customers provide the same checkout data directly.

Checkout pipeline:

```text
Resolve Buyer Context
(Guest OR Authenticated Customer)
   ↓
Cart Validation
   ↓
Stock Validation
   ↓
Coupon Validation
   ↓
Server Price Calculation
   ↓
Customer Details Validation
   ↓
Payment Method Validation
   ↓
Transaction ID Validation
   ↓
Order Transaction
```

---

# 17. Manual Payment Module

Supported MVP manual payment methods:

```text
bKash
Nagad
Rocket
```

Admin panel will configure:

```text
Method Name
Account Number
Account Type
Instruction
Status
```

Example:

```text
Method: bKash
Number: 01XXXXXXXXX
Account Type: Personal
Instruction:
"Send Money and enter the Transaction ID below."
```

Checkout flow:

```text
Customer selects bKash
        ↓
System displays Admin-configured number
        ↓
Customer sends payment externally
        ↓
Customer enters Transaction ID
        ↓
Server validates required format
        ↓
Order is created
        ↓
Payment Submission saved
        ↓
Thank You Page
```

The same pattern applies to:

```text
Nagad
Rocket
```

---

# 18. Payment Status Model

Important professional rule:

> **Transaction ID submission is not automatic payment verification.**

Recommended statuses:

```text
unpaid
submitted
verified
rejected
```

Flow:

```text
Customer enters Transaction ID
        ↓
payment_status = submitted
        ↓
Thank You Page
        ↓
Admin / Authorized Manager Review
        ↓
Verified
   OR
Rejected
```

Customer-facing success message:

> **Order placed successfully. Your payment information has been submitted for verification.**

This keeps the user experience successful while keeping backend payment state correct.

---

# 19. Payment Method Management

Suggested `payment_methods` concept:

```text
id
name
code
account_number
account_type
instruction
status
created_at
updated_at
deleted_at
```

Examples:

```text
bkash
nagad
rocket
```

Only authorized staff may edit these numbers.

---

# 20. Payment Submission

Suggested concept:

```text
payment_submissions
```

Possible fields:

```text
id
order_id
payment_method_id
transaction_id
amount
status
verified_by
verified_at
rejection_note
created_at
updated_at
```

This separates:

```text
Order
```

from:

```text
Customer Payment Proof Submission
```

---

# 21. Order Module

Order concept:

```text
Order Number
Customer / Guest Snapshot
Authenticated User ID nullable
Assigned Agent
Subtotal
Discount
Shipping
Grand Total
Payment Method
Payment Status
Order Status
Customer Snapshot
Shipping Address Snapshot
Customer Note
Internal Note
Created At
Updated At
Deleted At
```

Order items:

```text
Product ID
Product Name Snapshot
SKU Snapshot
Unit Price
Quantity
Line Total
```

Historical product information must not depend entirely on current Product data.

---

# 22. Order Assignment

Flow:

```text
New Order
   ↓
Admin / Manager
   ↓
Assign Agent
   ↓
Assigned Agent Dashboard
   ↓
Agent Processes Order
```

Possible permissions:

```text
orders.assign
orders.view
orders.update
```

---

# 23. Order Status Workflow

Recommended statuses:

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
Pending
   ↓
Confirmed
   ↓
Processing
   ↓
Shipped
   ↓
Delivered
```

Cancellation flow:

```text
Pending / Confirmed / Processing
            ↓
        Cancelled
```

State changes should be controlled through Service/Rule logic.

---

# 24. Order History

Important order changes create history.

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

Suggested:

```text
order_histories
```

Possible fields:

```text
order_id
user_id
from_status
to_status
note
created_at
```

---

# 25. Dashboard

## Admin Dashboard

```text
Total Sales
Total Orders
Pending Orders
Delivered Orders
Cancelled Orders
Total Customers
Total Products
Low Stock Products
Pending Payment Verification
Recent Orders
```

## Manager Dashboard

```text
Today's Orders
Pending
Processing
Delivered
Unassigned Orders
Agents
Low Stock
```

## Agent Dashboard

```text
My Orders
Pending
Confirmed
Processing
Shipped
Delivered
Cancelled
```

## Customer Dashboard

```text
My Orders
Pending
Delivered
Cancelled
Profile
```

---

# 26. Service Layer

Controllers should remain thin.

Suggested services:

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

Example:

```text
CheckoutController
      ↓
CheckoutService
      ↓
CouponService
StockService
OrderService
PaymentService
```

Service layer is a required practice component.

---

# 27. Observer Usage

Use Observers for lightweight lifecycle behavior.

Possible:

```text
ProductObserver
OrderObserver
UserObserver
```

Good Observer responsibilities:

```text
Lightweight slug preparation
Dispatch a local event
Simple lifecycle bookkeeping
```

Do not place:

```text
Checkout transaction
Payment verification
Complex stock deduction
Large workflow
```

inside Observer.

---

# 28. Trait Usage

Use reusable Traits only when logic is genuinely reusable.

Suggested:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

Example:

```text
HasOrderNumber
→ ORD-2026-000001
```

Avoid creating Traits solely to increase component count.

---

# 29. Custom Rules

Use:

```text
app/Rules/
```

Suggested:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

Important:

`ValidManualTransactionId` validates input format/basic requirement only.

It does **not** verify against bKash/Nagad/Rocket provider servers.

---

# 30. Console Commands

Use:

```text
app/Console/Commands/
```

Good practice commands:

```text
coupons:expire
products:low-stock-report
orders:cleanup-pending
```

Recommended minimum:

```text
coupons:expire
```

Flow:

```text
Find expired active coupons
   ↓
Mark inactive / expired
```

---

# 31. Policies

Suggested:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

Important rules:

```text
Customer can only view own orders.
Agent can only process assigned orders unless broader permission exists.
Only authorized staff can verify/reject payments.
```

---

# 32. Form Requests

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

Validation should not be duplicated inside Controllers.

---

# 33. Enums

Recommended:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

Avoid critical status magic strings.

---

# 34. Soft Delete

Use `SoftDeletes` where appropriate.

Recommended:

```text
users
categories
products
coupons
orders
payment_methods
```

Historical records such as:

```text
order_items
order_histories
payment_submissions
```

should be preserved according to history requirements.

---

# 35. Core Database Entities

Application-owned initial entities:

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

Optional if time permits:

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

Framework tables may include:

```text
sessions
password_reset_tokens
notifications
```

depending on configuration.

---

# 36. Main Relationships

```text
User
├── hasMany Orders (authenticated purchases only)
├── hasMany Assigned Orders
└── Roles / Permissions

Category
└── hasMany Products

Product
└── belongsTo Category

Order
├── belongsTo Customer/User nullable (guest checkout supported)
├── stores Customer + Shipping snapshots
├── belongsTo Assigned Agent/User nullable
├── hasMany OrderItems
├── hasMany OrderHistories
└── hasMany / hasOne PaymentSubmission depending final schema

PaymentSubmission
├── belongsTo Order
├── belongsTo PaymentMethod
└── belongsTo Verifier/User nullable
```

---

# 37. Business Rules

## BR-001 — Customer / Guest Checkout Ownership

Authenticated Customer can only view own account orders. Guest checkout is allowed without login; guest orders preserve buyer/contact/address snapshots and do not require an authenticated `user_id`.

## BR-002 — Agent Assignment

Agent can process assigned orders only unless additional permission explicitly allows broader access.

## BR-003 — Server Price Authority

Server recalculates cart and checkout totals.

## BR-004 — Stock Validation

Requested quantity cannot exceed available stock.

## BR-005 — Coupon Validation

Inactive/expired/invalid coupon is rejected.

## BR-006 — Payment Number Management

Only authorized staff can configure bKash/Nagad/Rocket account numbers.

## BR-007 — Transaction ID Requirement

Manual MFS checkout requires transaction ID.

## BR-008 — Submission Is Not Verification

Transaction ID submission creates `submitted`, not `verified`.

## BR-009 — Payment Verification

Only authorized staff can verify/reject payment submission.

## BR-010 — Order History

Important order status changes must create history.

## BR-011 — Staff Permission

Manager/Agent needs required permission for every protected backend action.

## BR-012 — Soft Delete

Selected recoverable business records use SoftDeletes.

## BR-013 — Guest Checkout Allowed

A visitor may place an order without registering or logging in. Guest checkout must collect and preserve the required buyer/contact/shipping information on the Order.

## BR-014 — Logged-in Checkout

An authenticated Customer may also place an order. The Order links to the authenticated User while still preserving checkout snapshots for historical accuracy.

---

# 38. Security Rules

Mandatory:

```text
Password hashing
CSRF
Form Request validation
Permission checks
Policies
Customer order ownership
Agent assignment ownership
Server-side price calculation
Server-side stock validation
Mass-assignment protection
Media validation
Sensitive configuration protection
```

Never trust:

```text
Hidden input price
Client total
Client discount
Client payment state
Transaction ID as verified proof
```

---

# 39. Queue Scope

Queue is optional for the core 13-day completion.

If implemented:

```text
Email
Image conversion
Non-critical notification
```

may use Queue.

Order creation must not become dependent on unnecessary queue complexity.

---

# 40. Frontend Scope

Use:

```text
Laravel Blade
```

Recommended styling:

```text
Bootstrap 5
```

or Tailwind if already familiar.

Main frontend:

```text
Store Home
Shop
Product Details
Category
Search
Cart
Checkout
Payment Instruction
Thank You
Customer Account
Admin Dashboard
Manager Dashboard
Agent Dashboard
CRUD Screens
Order Management
Payment Verification
```

---

# 41. MVP Scope

Must-have:

```text
Authentication
Admin / Manager / Agent / Customer
Guest Checkout
Spatie Permission
Category CRUD
Product CRUD
Spatie Media Library
Stock
Storefront
Search
Cart
Coupon
Checkout
bKash / Nagad / Rocket Configuration
Transaction ID Submission
Thank You Page
Order + Order Items
Order History
Admin Order Management
Manager → Agent Assignment
Agent Order Processing
Customer My Orders
Payment Verification
Dashboard Summary
Services
Observer
Trait
Custom Rules
Console Command
Policies
Form Requests
Enums
Soft Delete
Critical Tests
```

---

# 42. Out of Scope

Do not include in the core 13-day build:

```text
Multi-Vendor
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
Complex Shipping Engine
Microservices
AI
Accounting
Affiliate
Advanced Tax
```

---

# 43. 13-Day Development Plan

```text
Day 1
Laravel Setup
Authentication
Database Planning

Day 2
Spatie Permission
Roles / Permissions
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
Cart
Coupon

Day 7
Guest + Logged-in Checkout
Manual Payment Display

Day 8
Guest + Logged-in Order Creation
Order Items
Payment Submission
Thank You Page

Day 9
Admin Orders
Payment Verification

Day 10
Manager → Agent Assignment

Day 11
Agent Workflow
Customer My Orders
Guest Checkout

Day 12
Services
Observer
Traits
Rules
Console
Policies
Security
Dashboard Stats

Day 13
Tests
Bug Fix
UI Polish
README
Git Cleanup
```

Reusable architecture should be introduced progressively instead of waiting until Day 12.

---

# 44. Testing Scope

Critical tests:

```text
Authenticated Customer cannot view another customer's order
Guest can place an order without login
Agent cannot access another agent's order
Missing permission denies staff action
Checkout rejects insufficient stock
Expired coupon rejected
Server recalculates product price
Manual MFS payment requires transaction ID
Transaction submission produces submitted status
Unauthorized user cannot verify payment
Valid staff can verify payment
Order status transition is controlled
Soft deleted product does not appear in storefront
```

---

# 45. Definition of Done

Project is complete when:

- [ ] Authentication works.
- [ ] Admin works.
- [ ] Manager/Agent permission flow works.
- [ ] Customer storefront works.
- [ ] Guest checkout works without login.
- [ ] Logged-in checkout works.
- [ ] Category CRUD works.
- [ ] Product CRUD works.
- [ ] Media Library works.
- [ ] Stock validation works.
- [ ] Cart works.
- [ ] Coupon works.
- [ ] Checkout works.
- [ ] bKash/Nagad/Rocket numbers are configurable from Admin.
- [ ] Transaction ID submission works.
- [ ] Thank You page works.
- [ ] Payment remains `submitted` until verification.
- [ ] Admin/authorized Manager can verify/reject payment.
- [ ] Order/OrderItem storage works.
- [ ] Order history works.
- [ ] Manager can assign Agent.
- [ ] Agent can process assigned orders.
- [ ] Logged-in Customer can view own orders.
- [ ] Guest order can be placed with nullable authenticated user reference and preserved buyer snapshots.
- [ ] Services are meaningfully used.
- [ ] Observer is meaningfully used.
- [ ] Trait is meaningfully used.
- [ ] Custom Rules are used.
- [ ] Console Command exists.
- [ ] Policies protect resources.
- [ ] Enums control statuses.
- [ ] Soft Delete works where selected.
- [ ] Critical tests pass.

---

# 46. Final Project Statement

**ShopPilot E-commerce** will be a medium-size role-based Laravel practice project where:

> **Both Guest Customers and logged-in Customers can browse products, add items to cart and complete checkout. They can select an Admin-configured bKash/Nagad/Rocket payment method, submit a transaction ID and receive an order-success Thank You page. Logged-in Customers can also access their account order history, while guest orders preserve buyer/contact/address snapshots without requiring login. Admin and authorized staff verify payment, manage orders, assign Agents and complete fulfillment.**

The project will deliberately practice:

> **Spatie Permission + Spatie Media Library + Services + Observers + Traits + Console Commands + Custom Rules + Policies + Form Requests + Enums + SoftDeletes**

without expanding into an oversized enterprise system.

---

# 47. Next Documentation

Next recommended document:

```text
02-PRD.md
```

Then:

```text
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
