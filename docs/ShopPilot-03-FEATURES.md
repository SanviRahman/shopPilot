# ShopPilot E-commerce — Features Specification
# শপপাইলট ই-কমার্স — ফিচার স্পেসিফিকেশন

> **Document:** Features Specification / ফিচার ক্যাটালগ  
> **Project:** ShopPilot E-commerce  
> **Source Documents:** `01-PROJECT-OVERVIEW-UPDATED.md` v1.1, `ShopPilot-02-PRD.md` v1.0  
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
> **Payment:** Manual bKash / Nagad / Rocket submission; optional COD  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Feature Definition  
> **Next Document:** `04-USER-ROLES-AND-PERMISSIONS.md`

---

# Table of Contents

1. Document Purpose  
2. Feature Priority Legend  
3. Actor Legend  
4. Feature Catalogue Summary  
5. Authentication Features  
6. Guest Buyer Features  
7. Logged-in Customer Features  
8. Admin Features  
9. Manager Features  
10. Agent Features  
11. Role & Permission Features  
12. Category Features  
13. Product Features  
14. Media Features  
15. Stock Features  
16. Storefront Features  
17. Search & Filter Features  
18. Cart Features  
19. Coupon Features  
20. Checkout Features  
21. Buyer Snapshot Features  
22. Manual Payment Method Features  
23. Payment Submission Features  
24. Payment Verification Features  
25. Order Features  
26. Order Item Features  
27. Order Assignment Features  
28. Order Status Features  
29. Order History Features  
30. Customer Order History Features  
31. Dashboard Features  
32. Notification Features  
33. Soft Delete Features  
34. Operational Traceability Features  
35. Service Layer Features  
36. Observer Features  
37. Trait Features  
38. Custom Rule Features  
39. Console Command Features  
40. Policy Features  
41. Form Request Features  
42. Enum Features  
43. Queue Features  
44. Security Features  
45. Testing Features  
46. MVP Feature Matrix  
47. P1 Feature Matrix  
48. Explicitly Out-of-Scope Features  
49. 13-Day Delivery Mapping  
50. Feature Dependencies  
51. Definition of Feature Complete  
52. Next Documentation

---

# 1. Document Purpose

This document converts the approved Project Overview and PRD into an implementation-friendly feature catalogue.

এই file development-এর সময় feature checklist হিসেবে use করা যাবে।

It defines:

- feature name
- feature ID
- actor
- priority
- expected behavior
- dependency
- acceptance condition

This document must not silently expand the approved MVP.

---

# 2. Feature Priority Legend

| Priority | Meaning |
|---|---|
| **P0** | Must be completed for MVP |
| **P1** | Optional if time permits |
| **Future** | Not part of the 13-day MVP |

---

# 3. Actor Legend

```text
Admin
Manager
Agent
Logged-in Customer
Guest Customer
System
```

Important:

> **Guest Customer is a public buyer, not an authenticated Spatie role.**

---

# 4. Feature Catalogue Summary

Core feature groups:

```text
Authentication
Guest Checkout
Customer Account
Admin
Manager
Agent
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
Buyer Snapshot
Manual Payment
Payment Submission
Payment Verification
Order
Order Items
Order Assignment
Order Status
Order History
Dashboard
Reusable Laravel Components
Security
Testing
```

---

# 5. Authentication Features

## FEAT-AUTH-001 — Customer Registration
**Priority:** P0  
**Actor:** Visitor

Customer can create an account.

Acceptance:

- valid registration succeeds
- invalid data returns validation errors
- password is securely hashed

---

## FEAT-AUTH-002 — Login
**Priority:** P0  
**Actor:** Admin / Manager / Agent / Customer

Acceptance:

- valid credentials authenticate
- invalid credentials fail
- session is created securely

---

## FEAT-AUTH-003 — Logout
**Priority:** P0

Acceptance:

- current session is terminated
- protected pages are no longer accessible

---

## FEAT-AUTH-004 — Role-Aware Dashboard Access
**Priority:** P0

Expected access:

```text
Admin → Admin Dashboard
Manager → Manager Dashboard
Agent → Agent Dashboard
Customer → Customer Account
```

---

## FEAT-AUTH-005 — Forgot Password
**Priority:** P1

---

## FEAT-AUTH-006 — Email Verification
**Priority:** P1

---

# 6. Guest Buyer Features

## FEAT-GUEST-001 — Public Store Browsing
**Priority:** P0

Guest can access:

```text
Home
Shop
Category
Product Details
Search
```

---

## FEAT-GUEST-002 — Guest Session Cart
**Priority:** P0

Guest can:

- add Product
- update quantity
- remove Product
- clear Cart

---

## FEAT-GUEST-003 — Guest Coupon
**Priority:** P0

Guest can apply a valid Coupon.

---

## FEAT-GUEST-004 — Checkout Without Login
**Priority:** P0

Guest can proceed to Checkout without registering or authenticating.

---

## FEAT-GUEST-005 — Guest Buyer Details
**Priority:** P0

Required checkout data:

```text
name
phone
email
address
city_or_area
order_note optional
```

---

## FEAT-GUEST-006 — Guest Manual Payment
**Priority:** P0

Guest can:

- select active MFS method
- view Admin-configured number
- view instruction
- enter Transaction ID
- submit Order

---

## FEAT-GUEST-007 — Guest Order With Nullable User
**Priority:** P0

Rule:

```text
user_id = null
```

is valid for Guest Order.

---

## FEAT-GUEST-008 — Guest Buyer Snapshot
**Priority:** P0

Order must preserve:

```text
buyer name
phone
email
shipping address
city / area
```

---

## FEAT-GUEST-009 — Guest Thank You Page
**Priority:** P0

After successful Checkout, Guest sees:

```text
Order Success
Order Number
Payment Submission Status
Basic Order Summary
```

---

## FEAT-GUEST-010 — Guest My Orders
**Priority:** Future / P1

Not required for MVP.

---

# 7. Logged-in Customer Features

## FEAT-CUSTOMER-001 — Customer Profile
**Priority:** P0

Customer can view/update basic profile information.

---

## FEAT-CUSTOMER-002 — Authenticated Checkout
**Priority:** P0

Logged-in Customer can complete Checkout.

---

## FEAT-CUSTOMER-003 — Checkout Prefill
**Priority:** P0

Profile information may prefill checkout fields.

Final checkout data remains editable before submission.

---

## FEAT-CUSTOMER-004 — User-Linked Order
**Priority:** P0

Authenticated purchase sets:

```text
orders.user_id = authenticated customer id
```

---

## FEAT-CUSTOMER-005 — Customer Snapshot
**Priority:** P0

Even authenticated purchases preserve checkout snapshots.

---

## FEAT-CUSTOMER-006 — My Orders
**Priority:** P0

Customer can list own Orders.

---

## FEAT-CUSTOMER-007 — Own Order Details
**Priority:** P0

Customer can see:

```text
Order Number
Date
Items
Total
Payment Method
Payment Status
Order Status
```

---

## FEAT-CUSTOMER-008 — Order Ownership Protection
**Priority:** P0

Rule:

```text
Customer A cannot access Customer B Order
```

---

## FEAT-CUSTOMER-009 — Internal Note Protection
**Priority:** P0

Customer must never see staff-only Internal Notes.

---

# 8. Admin Features

## FEAT-ADMIN-001 — Admin Dashboard
**Priority:** P0

Metrics:

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

---

## FEAT-ADMIN-002 — Staff Management
**Priority:** P0

Admin can:

- view staff
- create/assign Manager
- create/assign Agent
- update staff
- control role assignment

---

## FEAT-ADMIN-003 — Role Management
**Priority:** P0

---

## FEAT-ADMIN-004 — Permission Management
**Priority:** P0

---

## FEAT-ADMIN-005 — Category Management
**Priority:** P0

---

## FEAT-ADMIN-006 — Product Management
**Priority:** P0

---

## FEAT-ADMIN-007 — Product Media Management
**Priority:** P0

---

## FEAT-ADMIN-008 — Stock Management
**Priority:** P0

---

## FEAT-ADMIN-009 — Coupon Management
**Priority:** P0

---

## FEAT-ADMIN-010 — Order Management
**Priority:** P0

---

## FEAT-ADMIN-011 — Agent Assignment
**Priority:** P0

---

## FEAT-ADMIN-012 — Payment Method Configuration
**Priority:** P0

Admin manages:

```text
bKash
Nagad
Rocket
```

---

## FEAT-ADMIN-013 — Payment Verification
**Priority:** P0

Admin can:

```text
Verify
Reject
Add rejection note
```

---

## FEAT-ADMIN-014 — Restore Supported Resources
**Priority:** P0 where SoftDelete is used

---

# 9. Manager Features

Manager is permission-driven.

## FEAT-MANAGER-001 — Manager Dashboard
**Priority:** P0

Possible metrics:

```text
Today's Orders
Pending
Processing
Delivered
Unassigned Orders
Agents
Low Stock
```

---

## FEAT-MANAGER-002 — Product Operations
**Priority:** P0 if permission granted

Possible permissions:

```text
products.view
products.create
products.update
```

---

## FEAT-MANAGER-003 — Stock Operations
**Priority:** P0 if permission granted

---

## FEAT-MANAGER-004 — Order View
**Priority:** P0

---

## FEAT-MANAGER-005 — Order Update
**Priority:** P0 if authorized

---

## FEAT-MANAGER-006 — Assign Agent
**Priority:** P0 if `orders.assign`

---

## FEAT-MANAGER-007 — Payment Verification
**Priority:** P0 if `payments.verify`

---

## FEAT-MANAGER-008 — Reports
**Priority:** P0 if `reports.view`

---

## FEAT-MANAGER-009 — Sensitive Access Restriction
**Priority:** P0

Manager does not automatically receive:

```text
roles.manage
permissions.manage
payment-methods.manage
settings.update
```

---

# 10. Agent Features

## FEAT-AGENT-001 — Agent Dashboard
**Priority:** P0

Metrics:

```text
My Orders
Pending
Confirmed
Processing
Shipped
Delivered
Cancelled
```

---

## FEAT-AGENT-002 — My Assigned Orders
**Priority:** P0

Agent sees assigned Orders only by default.

---

## FEAT-AGENT-003 — View Delivery Information
**Priority:** P0

---

## FEAT-AGENT-004 — Confirm Order
**Priority:** P0

---

## FEAT-AGENT-005 — Move To Processing
**Priority:** P0

---

## FEAT-AGENT-006 — Mark Shipped
**Priority:** P0

---

## FEAT-AGENT-007 — Mark Delivered
**Priority:** P0

---

## FEAT-AGENT-008 — Cancel Where Allowed
**Priority:** P0

---

## FEAT-AGENT-009 — Internal Note
**Priority:** P0

---

## FEAT-AGENT-010 — Assignment Ownership Security
**Priority:** P0

Rule:

```text
Agent A cannot process Agent B Order
```

unless broader permission is explicitly granted.

---

# 11. Role & Permission Features

Use:

```text
Spatie Laravel Permission
```

## FEAT-RBAC-001 — Roles
**Priority:** P0

Authenticated roles:

```text
Admin
Manager
Agent
Customer
```

---

## FEAT-RBAC-002 — Granular Permissions
**Priority:** P0

Convention:

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

---

## FEAT-RBAC-003 — Permission Middleware
**Priority:** P0

---

## FEAT-RBAC-004 — Policy Authorization
**Priority:** P0

---

## FEAT-RBAC-005 — Blade Permission Visibility
**Priority:** P0

Important:

> Hiding a Blade button is not sufficient authorization.

---

## FEAT-RBAC-006 — Sensitive Permission Protection
**Priority:** P0

Sensitive examples:

```text
roles.manage
permissions.manage
payment-methods.manage
payments.verify
payments.reject
settings.update
```

---

# 12. Category Features

## FEAT-CAT-001 — Category List
**Priority:** P0

---

## FEAT-CAT-002 — Create Category
**Priority:** P0

Fields:

```text
name
slug
description
image
status
sort_order
```

---

## FEAT-CAT-003 — Update Category
**Priority:** P0

---

## FEAT-CAT-004 — Soft Delete Category
**Priority:** P0

---

## FEAT-CAT-005 — Restore Category
**Priority:** P0

---

## FEAT-CAT-006 — One-Level Category Structure
**Priority:** P0

Nested Categories are not required.

---

# 13. Product Features

## FEAT-PROD-001 — Product List
**Priority:** P0

---

## FEAT-PROD-002 — Create Product
**Priority:** P0

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

---

## FEAT-PROD-003 — Update Product
**Priority:** P0

---

## FEAT-PROD-004 — Soft Delete Product
**Priority:** P0

---

## FEAT-PROD-005 — Restore Product
**Priority:** P0

---

## FEAT-PROD-006 — Product Status
**Priority:** P0

```text
active
inactive
```

---

## FEAT-PROD-007 — Featured Product
**Priority:** P0

---

## FEAT-PROD-008 — Regular / Sale Price
**Priority:** P0

---

## FEAT-PROD-009 — Complex Variant System
**Priority:** Future

Not part of MVP.

---

# 14. Media Features

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

## FEAT-MEDIA-001 — Product Thumbnail
**Priority:** P0

## FEAT-MEDIA-002 — Product Gallery
**Priority:** P0

## FEAT-MEDIA-003 — Category Image
**Priority:** P0

## FEAT-MEDIA-004 — Media Validation
**Priority:** P0

## FEAT-MEDIA-005 — Replace Media
**Priority:** P0

## FEAT-MEDIA-006 — Delete Media
**Priority:** P0

## FEAT-MEDIA-007 — User Avatar
**Priority:** P1

---

# 15. Stock Features

MVP stock strategy:

```text
products.stock_quantity
```

## FEAT-STOCK-001 — Stock Quantity
**Priority:** P0

---

## FEAT-STOCK-002 — Stock Availability
**Priority:** P0

```text
stock > 0 → In Stock
stock = 0 → Out of Stock
```

---

## FEAT-STOCK-003 — Checkout Stock Revalidation
**Priority:** P0

---

## FEAT-STOCK-004 — Insufficient Stock Blocking
**Priority:** P0

---

## FEAT-STOCK-005 — Order Stock Deduction
**Priority:** P0

---

## FEAT-STOCK-006 — Transaction-Safe Stock Mutation
**Priority:** P0

---

## FEAT-STOCK-007 — Low Stock Visibility
**Priority:** P0

---

## FEAT-STOCK-008 — Advanced Inventory Ledger
**Priority:** Future

---

# 16. Storefront Features

## FEAT-STORE-001 — Home Page
**Priority:** P0

Possible sections:

```text
Featured Products
Latest Products
Categories
Sale Products
```

---

## FEAT-STORE-002 — Shop Page
**Priority:** P0

---

## FEAT-STORE-003 — Category Page
**Priority:** P0

---

## FEAT-STORE-004 — Product Details
**Priority:** P0

---

## FEAT-STORE-005 — Product Availability Display
**Priority:** P0

---

## FEAT-STORE-006 — Guest + Customer Public Access
**Priority:** P0

---

# 17. Search & Filter Features

## FEAT-SEARCH-001 — Product Name Search
**Priority:** P0

## FEAT-SEARCH-002 — Category Filter
**Priority:** P0

## FEAT-SEARCH-003 — SKU Search
**Priority:** P1

## FEAT-SEARCH-004 — Availability Filter
**Priority:** P1

## FEAT-SEARCH-005 — Latest Sorting
**Priority:** P1

## FEAT-SEARCH-006 — Price Sorting
**Priority:** P1

```text
Low → High
High → Low
```

---

# 18. Cart Features

Use:

```text
Session-Based Cart
```

## FEAT-CART-001 — Add To Cart
**Priority:** P0

## FEAT-CART-002 — Update Quantity
**Priority:** P0

## FEAT-CART-003 — Remove Item
**Priority:** P0

## FEAT-CART-004 — Clear Cart
**Priority:** P0

## FEAT-CART-005 — Subtotal
**Priority:** P0

## FEAT-CART-006 — Coupon Discount
**Priority:** P0

## FEAT-CART-007 — Grand Total
**Priority:** P0

## FEAT-CART-008 — Guest Cart
**Priority:** P0

## FEAT-CART-009 — Logged-in Cart
**Priority:** P0

## FEAT-CART-010 — Server-Authoritative Price
**Priority:** P0

Client-provided price must not be trusted.

---

# 19. Coupon Features

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

## FEAT-COUPON-001 — Coupon CRUD
**Priority:** P0

## FEAT-COUPON-002 — Fixed Discount
**Priority:** P0

## FEAT-COUPON-003 — Percentage Discount
**Priority:** P0

## FEAT-COUPON-004 — Minimum Order Validation
**Priority:** P0

## FEAT-COUPON-005 — Start/End Date Validation
**Priority:** P0

## FEAT-COUPON-006 — Active Status Validation
**Priority:** P0

## FEAT-COUPON-007 — One Coupon Per Order
**Priority:** P0

## FEAT-COUPON-008 — No Stacking
**Priority:** P0

---

# 20. Checkout Features

## FEAT-CHECKOUT-001 — Guest Checkout
**Priority:** P0

## FEAT-CHECKOUT-002 — Logged-in Checkout
**Priority:** P0

## FEAT-CHECKOUT-003 — Profile Prefill
**Priority:** P0

For authenticated Customer only.

---

## FEAT-CHECKOUT-004 — Checkout Form
**Priority:** P0

Fields:

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

---

## FEAT-CHECKOUT-005 — Cart Validation
**Priority:** P0

## FEAT-CHECKOUT-006 — Product Validation
**Priority:** P0

## FEAT-CHECKOUT-007 — Stock Validation
**Priority:** P0

## FEAT-CHECKOUT-008 — Coupon Validation
**Priority:** P0

## FEAT-CHECKOUT-009 — Server Total Recalculation
**Priority:** P0

## FEAT-CHECKOUT-010 — Payment Method Validation
**Priority:** P0

## FEAT-CHECKOUT-011 — Transaction ID Validation
**Priority:** P0

## FEAT-CHECKOUT-012 — Atomic Checkout Operation
**Priority:** P0

Related writes:

```text
Order
Order Items
Payment Submission
Stock Mutation
Order History
```

must be handled safely.

---

## FEAT-CHECKOUT-013 — Clear Cart After Success
**Priority:** P0

## FEAT-CHECKOUT-014 — Thank You Redirect
**Priority:** P0

---

# 21. Buyer Snapshot Features

Every Order preserves historical buyer data.

## FEAT-SNAPSHOT-001 — Buyer Name
**Priority:** P0

## FEAT-SNAPSHOT-002 — Buyer Phone
**Priority:** P0

## FEAT-SNAPSHOT-003 — Buyer Email
**Priority:** P0

## FEAT-SNAPSHOT-004 — Shipping Address
**Priority:** P0

## FEAT-SNAPSHOT-005 — City / Area
**Priority:** P0

## FEAT-SNAPSHOT-006 — Nullable User Reference
**Priority:** P0

```text
Guest → user_id = null
Logged-in Customer → user_id = authenticated user
```

## FEAT-SNAPSHOT-007 — Snapshot Stability
**Priority:** P0

Later profile changes must not rewrite historical Order data.

---

# 22. Manual Payment Method Features

Supported:

```text
bKash
Nagad
Rocket
```

## FEAT-PAYMETHOD-001 — Payment Method List
**Priority:** P0

## FEAT-PAYMETHOD-002 — Account Number
**Priority:** P0

## FEAT-PAYMETHOD-003 — Account Type
**Priority:** P0

## FEAT-PAYMETHOD-004 — Payment Instruction
**Priority:** P0

## FEAT-PAYMETHOD-005 — Active / Inactive
**Priority:** P0

## FEAT-PAYMETHOD-006 — Checkout Display
**Priority:** P0

## FEAT-PAYMETHOD-007 — Optional COD
**Priority:** P1

No real provider API is required.

---

# 23. Payment Submission Features

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

## FEAT-PAYSUB-001 — Transaction ID Input
**Priority:** P0

## FEAT-PAYSUB-002 — Payment Submission Record
**Priority:** P0

## FEAT-PAYSUB-003 — Initial Submitted Status
**Priority:** P0

```text
submitted
```

## FEAT-PAYSUB-004 — Order Relationship
**Priority:** P0

## FEAT-PAYSUB-005 — Payment Method Relationship
**Priority:** P0

## FEAT-PAYSUB-006 — Basic Transaction ID Rule
**Priority:** P0

Important:

> Validation checks the input format/requirement only; it does not verify the transaction with bKash/Nagad/Rocket servers.

---

# 24. Payment Verification Features

Statuses:

```text
unpaid
submitted
verified
rejected
```

## FEAT-PAYVERIFY-001 — Pending Submission List
**Priority:** P0

## FEAT-PAYVERIFY-002 — Verify
**Priority:** P0

## FEAT-PAYVERIFY-003 — Reject
**Priority:** P0

## FEAT-PAYVERIFY-004 — Rejection Note
**Priority:** P0

## FEAT-PAYVERIFY-005 — Verifier Identity
**Priority:** P0

## FEAT-PAYVERIFY-006 — Verification Timestamp
**Priority:** P0

## FEAT-PAYVERIFY-007 — Unauthorized Verification Block
**Priority:** P0

Customer/Guest cannot verify.

---

## FEAT-PAYVERIFY-008 — Submission/Verification Separation
**Priority:** P0

Invariant:

```text
submitted != verified
```

---

# 25. Order Features

## FEAT-ORDER-001 — Order Creation
**Priority:** P0

## FEAT-ORDER-002 — Human-Readable Order Number
**Priority:** P0

Example:

```text
ORD-2026-000001
```

## FEAT-ORDER-003 — Nullable Customer User
**Priority:** P0

## FEAT-ORDER-004 — Assigned Agent
**Priority:** P0

## FEAT-ORDER-005 — Subtotal
**Priority:** P0

## FEAT-ORDER-006 — Discount
**Priority:** P0

## FEAT-ORDER-007 — Shipping Amount
**Priority:** P0

## FEAT-ORDER-008 — Grand Total
**Priority:** P0

## FEAT-ORDER-009 — Payment Status
**Priority:** P0

## FEAT-ORDER-010 — Order Status
**Priority:** P0

## FEAT-ORDER-011 — Customer Note
**Priority:** P0

## FEAT-ORDER-012 — Internal Note
**Priority:** P0

## FEAT-ORDER-013 — Order SoftDelete
**Priority:** P0 if retained in final schema

---

# 26. Order Item Features

Order Item snapshot fields:

```text
product_id
product_name
sku
unit_price
quantity
line_total
```

## FEAT-ORDERITEM-001 — Product Reference
**Priority:** P0

## FEAT-ORDERITEM-002 — Product Name Snapshot
**Priority:** P0

## FEAT-ORDERITEM-003 — SKU Snapshot
**Priority:** P0

## FEAT-ORDERITEM-004 — Unit Price Snapshot
**Priority:** P0

## FEAT-ORDERITEM-005 — Quantity
**Priority:** P0

## FEAT-ORDERITEM-006 — Line Total
**Priority:** P0

## FEAT-ORDERITEM-007 — Historical Stability
**Priority:** P0

Product updates must not rewrite old Order Item commercial data.

---

# 27. Order Assignment Features

## FEAT-ASSIGN-001 — Assign Agent
**Priority:** P0

Actors:

```text
Admin
Manager with orders.assign
```

## FEAT-ASSIGN-002 — Reassign Agent
**Priority:** P0

## FEAT-ASSIGN-003 — My Assigned Orders
**Priority:** P0

## FEAT-ASSIGN-004 — Assignment Authorization
**Priority:** P0

## FEAT-ASSIGN-005 — Assignment Traceability
**Priority:** P0

---

# 28. Order Status Features

Approved states:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

## FEAT-STATUS-001 — Pending
**Priority:** P0

New Order begins here.

## FEAT-STATUS-002 — Confirmed
**Priority:** P0

## FEAT-STATUS-003 — Processing
**Priority:** P0

## FEAT-STATUS-004 — Shipped
**Priority:** P0

## FEAT-STATUS-005 — Delivered
**Priority:** P0

## FEAT-STATUS-006 — Cancelled
**Priority:** P0

## FEAT-STATUS-007 — Controlled Transition
**Priority:** P0

Normal path:

```text
pending
→ confirmed
→ processing
→ shipped
→ delivered
```

## FEAT-STATUS-008 — Invalid Transition Block
**Priority:** P0

---

# 29. Order History Features

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

## FEAT-HISTORY-001 — Order Created Entry
**Priority:** P0

## FEAT-HISTORY-002 — Agent Assignment Entry
**Priority:** P0

## FEAT-HISTORY-003 — Payment Submission Trace
**Priority:** P0 where timeline displays it

## FEAT-HISTORY-004 — Payment Verification Trace
**Priority:** P0 where timeline displays it

## FEAT-HISTORY-005 — Status Change Entry
**Priority:** P0

## FEAT-HISTORY-006 — Actor Recording
**Priority:** P0 where actor exists

---

# 30. Customer Order History Features

## FEAT-CUSTORDER-001 — My Orders List
**Priority:** P0

## FEAT-CUSTORDER-002 — Order Details
**Priority:** P0

## FEAT-CUSTORDER-003 — Payment Status
**Priority:** P0

## FEAT-CUSTORDER-004 — Order Status
**Priority:** P0

## FEAT-CUSTORDER-005 — Ownership Enforcement
**Priority:** P0

---

# 31. Dashboard Features

## FEAT-DASH-001 — Admin Dashboard
**Priority:** P0

## FEAT-DASH-002 — Manager Dashboard
**Priority:** P0

## FEAT-DASH-003 — Agent Dashboard
**Priority:** P0

## FEAT-DASH-004 — Customer Dashboard
**Priority:** P0

## FEAT-DASH-005 — Role-Scoped Metrics
**Priority:** P0

Guest has no authenticated dashboard.

---

# 32. Notification Features

## FEAT-NOTIFY-001 — Session Flash Messages
**Priority:** P0

Examples:

```text
Product Created
Order Placed
Payment Verified
Order Updated
```

## FEAT-NOTIFY-002 — Database Notification
**Priority:** P1

## FEAT-NOTIFY-003 — Email Notification
**Priority:** P1

---

# 33. Soft Delete Features

Recommended resources:

```text
users
categories
products
coupons
orders
payment_methods
```

## FEAT-SOFT-001 — Soft Delete
**Priority:** P0 where selected

## FEAT-SOFT-002 — Restore
**Priority:** P0 where selected

## FEAT-SOFT-003 — Storefront Exclusion
**Priority:** P0

Soft-deleted Product must not appear publicly.

## FEAT-SOFT-004 — Force Delete
**Priority:** Future

Not required.

---

# 34. Operational Traceability Features

Full enterprise audit system is not required.

P0 traceability:

```text
Order History
Payment Verifier
Payment Verification Timestamp
Assigned Agent
Status Changes
```

P1:

```text
Generic activity_logs
Permission Change Log
```

---

# 35. Service Layer Features

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

## FEAT-SERVICE-001 — Thin Controllers
**Priority:** P0

## FEAT-SERVICE-002 — CheckoutService
**Priority:** P0

Handles checkout orchestration.

## FEAT-SERVICE-003 — StockService
**Priority:** P0

## FEAT-SERVICE-004 — CouponService
**Priority:** P0

## FEAT-SERVICE-005 — OrderService
**Priority:** P0

## FEAT-SERVICE-006 — OrderAssignmentService
**Priority:** P0

## FEAT-SERVICE-007 — OrderStatusService
**Priority:** P0

## FEAT-SERVICE-008 — PaymentService
**Priority:** P0

## FEAT-SERVICE-009 — DashboardService
**Priority:** P0 where useful

---

# 36. Observer Features

Possible Observers:

```text
ProductObserver
OrderObserver
UserObserver
```

## FEAT-OBSERVER-001 — Meaningful Observer
**Priority:** P0

At least one Observer must be used for appropriate lightweight model lifecycle behavior.

Allowed:

```text
Small bookkeeping
Lightweight model lifecycle
Dispatch local event
```

Do not place:

```text
Full Checkout
Payment Verification
Complex Stock Mutation
Large Business Workflow
```

inside Observers.

---

# 37. Trait Features

Suggested:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

## FEAT-TRAIT-001 — Meaningful Reusable Trait
**Priority:** P0

At least one Trait must represent real reusable behavior.

---

# 38. Custom Rule Features

Suggested:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

## FEAT-RULE-001 — At Least Two Custom Rules
**Priority:** P0

## FEAT-RULE-002 — Manual Transaction ID Rule
**Priority:** P0

## FEAT-RULE-003 — Status Transition Rule
**Priority:** P0

---

# 39. Console Command Features

## FEAT-CONSOLE-001 — coupons:expire
**Priority:** P0

Behavior:

```text
Find expired active Coupons
→ mark inactive / expired
```

## FEAT-CONSOLE-002 — products:low-stock-report
**Priority:** P1

## FEAT-CONSOLE-003 — orders:cleanup-pending
**Priority:** P1

---

# 40. Policy Features

Suggested:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

## FEAT-POLICY-001 — Customer Order Ownership
**Priority:** P0

## FEAT-POLICY-002 — Agent Assignment Ownership
**Priority:** P0

## FEAT-POLICY-003 — Payment Verification Authorization
**Priority:** P0

## FEAT-POLICY-004 — Product Authorization
**Priority:** P0

## FEAT-POLICY-005 — Guest Checkout Public Rule
**Priority:** P0

Guest checkout uses public business validation rather than authenticated ownership.

---

# 41. Form Request Features

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

## FEAT-REQUEST-001 — Write Validation Separation
**Priority:** P0

## FEAT-REQUEST-002 — Guest/Auth Checkout Support
**Priority:** P0

---

# 42. Enum Features

Suggested:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

## FEAT-ENUM-001 — Status Enums
**Priority:** P0

Avoid critical magic strings.

Important:

> Enum defines allowed values; Service/Rule defines valid state transitions.

---

# 43. Queue Features

## FEAT-QUEUE-001 — Queue Support
**Priority:** P1

Optional uses:

```text
Email
Image Conversion
Non-Critical Notification
```

Core Checkout and Order creation must not depend on unnecessary Queue complexity.

---

# 44. Security Features

## FEAT-SEC-001 — Password Hashing
**Priority:** P0

## FEAT-SEC-002 — CSRF Protection
**Priority:** P0

## FEAT-SEC-003 — Form Request Validation
**Priority:** P0

## FEAT-SEC-004 — Mass Assignment Protection
**Priority:** P0

## FEAT-SEC-005 — Permission Enforcement
**Priority:** P0

## FEAT-SEC-006 — Policy Enforcement
**Priority:** P0

## FEAT-SEC-007 — Server-Side Price
**Priority:** P0

## FEAT-SEC-008 — Server-Side Discount
**Priority:** P0

## FEAT-SEC-009 — Server-Side Stock
**Priority:** P0

## FEAT-SEC-010 — Protected Payment Status
**Priority:** P0

Customer/Guest cannot decide:

```text
verified
paid
```

## FEAT-SEC-011 — Internal Note Protection
**Priority:** P0

## FEAT-SEC-012 — Guest Order Exposure Protection
**Priority:** P0

## FEAT-SEC-013 — Payment Configuration Protection
**Priority:** P0

---

# 45. Testing Features

## FEAT-TEST-001 — Guest Checkout Test
**Priority:** P0

```text
Guest can place Order without login
Guest Order user_id = null
Guest snapshot saved
```

## FEAT-TEST-002 — Logged-in Checkout Test
**Priority:** P0

```text
Order linked to Customer
Snapshot saved
```

## FEAT-TEST-003 — Customer Ownership Test
**Priority:** P0

## FEAT-TEST-004 — Agent Ownership Test
**Priority:** P0

## FEAT-TEST-005 — Permission Denial Test
**Priority:** P0

## FEAT-TEST-006 — Stock Validation Test
**Priority:** P0

## FEAT-TEST-007 — Expired Coupon Test
**Priority:** P0

## FEAT-TEST-008 — Price Tampering Test
**Priority:** P0

## FEAT-TEST-009 — Missing Transaction ID Test
**Priority:** P0

## FEAT-TEST-010 — Submitted Payment State Test
**Priority:** P0

## FEAT-TEST-011 — Payment Verification Authorization Test
**Priority:** P0

## FEAT-TEST-012 — Invalid Order Transition Test
**Priority:** P0

## FEAT-TEST-013 — Order History Test
**Priority:** P0

## FEAT-TEST-014 — Soft-Deleted Product Test
**Priority:** P0

---

# 46. MVP Feature Matrix

| Module | Core P0 Feature |
|---|---|
| Authentication | Register, Login, Logout |
| Guest | Browse, Cart, Checkout, Order |
| Customer | Profile, Checkout, My Orders |
| RBAC | Spatie Roles & Permissions |
| Category | CRUD + Restore |
| Product | CRUD + Status + Featured |
| Media | Thumbnail, Gallery, Category Image |
| Stock | Quantity, Validation, Deduction |
| Storefront | Home, Shop, Category, Product |
| Search | Product Name + Category |
| Cart | Session Cart |
| Coupon | Fixed + Percentage |
| Checkout | Guest + Logged-in |
| Snapshot | Buyer + Shipping |
| Payment | bKash/Nagad/Rocket |
| Payment Submission | Transaction ID |
| Verification | Verify / Reject |
| Order | Order + Items |
| Assignment | Manager/Admin → Agent |
| Status | Controlled Workflow |
| History | Status / Assignment Trace |
| Dashboard | Admin/Manager/Agent/Customer |
| Service | Business Services |
| Observer | At least one meaningful Observer |
| Trait | At least one meaningful Trait |
| Rule | At least two meaningful Rules |
| Console | `coupons:expire` |
| Policy | Ownership / Authorization |
| Request | Form Requests |
| Enum | Status Enums |
| SoftDelete | Selected recoverable resources |
| Testing | Critical Feature Tests |

---

# 47. P1 Feature Matrix

```text
Forgot Password
Email Verification
User Avatar
SKU Search
Availability Filter
Advanced Sorting
COD
Database Notification
Email Notification
Guest Order Tracking Link
Generic Activity Log
products:low-stock-report
orders:cleanup-pending
Queue Usage
```

---

# 48. Explicitly Out-of-Scope Features

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

These must not be introduced into the core 13-day MVP unless the product scope is formally changed.

---

# 49. 13-Day Delivery Mapping

## Day 1

```text
Laravel Setup
Authentication
Database Planning
```

## Day 2

```text
Spatie Permission
Roles
Permissions
Dashboards
```

## Day 3

```text
Category CRUD
Media Library Setup
```

## Day 4

```text
Product CRUD
Stock
Product Media
```

## Day 5

```text
Home
Shop
Category
Product Details
Search
```

## Day 6

```text
Session Cart
Coupon
```

## Day 7

```text
Guest Checkout
Logged-in Checkout
MFS Payment Method Display
```

## Day 8

```text
Buyer Snapshot
Order
Order Items
Payment Submission
Stock Deduction
Thank You
```

## Day 9

```text
Admin Orders
Payment Verification
```

## Day 10

```text
Manager → Agent Assignment
Reassignment
```

## Day 11

```text
Agent Workflow
Customer My Orders
Order Details
```

## Day 12

```text
Services
Observer
Trait
Rules
Console
Policies
Form Requests
Enums
Security
Dashboard Stats
```

## Day 13

```text
Critical Tests
Bug Fix
UI Polish
README
Git Cleanup
```

---

# 50. Feature Dependencies

```text
Authentication
   ↓
RBAC
   ↓
Staff Dashboards

Category
   ↓
Product
   ↓
Media
   ↓
Stock
   ↓
Storefront
   ↓
Search
   ↓
Cart
   ↓
Coupon
   ↓
Checkout
   ↓
Buyer Snapshot
   ↓
Payment Method
   ↓
Payment Submission
   ↓
Order
   ↓
Order Items
   ↓
Order History
   ↓
Payment Verification
   ↓
Order Assignment
   ↓
Agent Workflow
   ↓
Customer My Orders
```

Cross-cutting dependencies:

```text
Services
Policies
Form Requests
Rules
Enums
SoftDeletes
Security
Tests
```

---

# 51. Definition of Feature Complete

A P0 feature is considered complete only when:

```text
UI exists where required
        +
Route exists
        +
Validation exists
        +
Authorization exists
        +
Business rule is implemented
        +
Database behavior is correct
        +
Error state is handled
        +
Feature is manually verified
        +
Critical automated test exists where applicable
```

Project-level completion additionally requires:

```text
Guest Checkout works
Logged-in Checkout works
submitted != verified
Customer ownership works
Agent ownership works
Stock validation works
Server-authoritative pricing works
Order snapshots remain historical
```

---

# 52. Next Documentation

After `03-FEATURES.md`, create:

```text
04-USER-ROLES-AND-PERMISSIONS.md
```

Then:

```text
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

# Final Feature Statement

ShopPilot E-commerce MVP feature scope is organized around:

> **Guest + Logged-in Shopping, RBAC, Product & Media, Stock, Storefront, Search, Session Cart, Coupon, Checkout, Buyer Snapshot, Manual bKash/Nagad/Rocket Payment Submission, Payment Verification, Order Management, Agent Assignment, Controlled Order Status, Dashboards, Reusable Laravel Components, Security, and Critical Tests.**

The feature catalogue must remain aligned with the approved Project Overview and PRD. Any new major capability should first be approved in those higher-level documents before being implemented.
