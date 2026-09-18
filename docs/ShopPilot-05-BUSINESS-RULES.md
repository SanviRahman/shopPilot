# ShopPilot E-commerce — Business Rules
# শপপাইলট ই-কমার্স — বিজনেস রুলস

> **Document:** Business Rules / বিজনেস রুলস  
> **Project:** ShopPilot E-commerce  
> **Source Documents:**  
> `01-PROJECT-OVERVIEW-UPDATED.md` v1.1  
> `ShopPilot-02-PRD.md` v1.0  
> `ShopPilot-03-FEATURES.md` v1.0  
> `ShopPilot-04-USER-ROLES-AND-PERMISSIONS.md` v1.0  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Primary Goal:** 13-Day Full-Stack Laravel Practice Project  
> **Backend:** PHP + Laravel  
> **Frontend:** Laravel Blade  
> **Database:** MySQL  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Approved Business Behavior Definition  
> **Next Document:** `06-ARCHITECTURE.md`

---

# Table of Contents

1. Document Purpose  
2. Business Rule Governance  
3. Rule Format  
4. Actor Model  
5. Global Authorization Rules  
6. Authentication Rules  
7. Guest Buyer Rules  
8. Logged-in Customer Rules  
9. Admin Rules  
10. Manager Rules  
11. Agent Rules  
12. Role & Permission Rules  
13. Category Rules  
14. Product Rules  
15. Media Rules  
16. Stock Rules  
17. Storefront Rules  
18. Search Rules  
19. Cart Rules  
20. Coupon Rules  
21. Checkout Rules  
22. Buyer Snapshot Rules  
23. Checkout Transaction Rules  
24. Manual Payment Method Rules  
25. Payment Submission Rules  
26. Payment Verification Rules  
27. Payment State Rules  
28. Order Rules  
29. Order Item Snapshot Rules  
30. Order Assignment Rules  
31. Order Status Transition Rules  
32. Cancellation Rules  
33. Order History Rules  
34. Customer Order Access Rules  
35. Guest Order Rules  
36. Dashboard Rules  
37. Soft Delete Rules  
38. Restore Rules  
39. Service Layer Rules  
40. Observer Rules  
41. Trait Rules  
42. Custom Rule Rules  
43. Console Command Rules  
44. Policy Rules  
45. Form Request Rules  
46. Enum Rules  
47. Queue Rules  
48. Validation Rules  
49. Security Rules  
50. Data Integrity Rules  
51. Historical Data Rules  
52. Error / Denial Behavior  
53. Rule Enforcement Map  
54. Business State Models  
55. Explicitly Undefined / TBD Rules  
56. Out-of-Scope Business Rules  
57. Critical Test Scenarios  
58. Business Rule Traceability  
59. Definition of Business-Rule Complete  
60. Final Business Rule Statement  
61. Next Documentation

---

# 1. Document Purpose

This document formalizes the approved business behavior of ShopPilot E-commerce.

এই file-এর কাজ হলো Project Overview, PRD, Features এবং Roles & Permissions document-এ already approved behavior-গুলোকে **clear, testable, implementation-ready business rules** হিসেবে define করা।

This document answers questions such as:

```text
Who may perform an action?
Under what conditions?
What must be validated?
What must be rejected?
What state may change?
What historical data must be preserved?
Which layer must enforce the rule?
```

This document must not silently introduce new modules or enterprise behavior.

---

# 2. Business Rule Governance

The following principles apply to all rules in this document.

## 2.1 Source Alignment

All rules must remain aligned with:

```text
01-PROJECT-OVERVIEW-UPDATED.md
02-PRD.md
03-FEATURES.md
04-USER-ROLES-AND-PERMISSIONS.md
```

## 2.2 No Silent Scope Expansion

If a behavior is not supported by the approved source documents:

```text
Do not invent it.
Do not silently implement it.
Mark it as TBD / Future / Out of Scope.
```

## 2.3 Business Rules Are Server-Authoritative

A browser or Blade UI is never the final authority for:

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

## 2.4 Later Technical Documents

The following later documents may define technical implementation details:

```text
06-ARCHITECTURE.md
07-DATABASE-ERD.md
08-DATABASE-SCHEMA.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
```

They must not contradict approved business behavior in this document.

---

# 3. Rule Format

Rules use IDs such as:

```text
BR-AUTH-001
BR-GUEST-001
BR-CART-001
BR-CHECKOUT-001
BR-PAY-001
BR-ORDER-001
BR-RBAC-001
```

Each rule may include:

```text
Rule
Actors
Condition
Allowed Behavior
Denied Behavior
Enforcement Layer
Priority
```

Unless stated otherwise:

```text
Priority = P0
```

---

# 4. Actor Model

Approved actors:

```text
Admin
Manager
Agent
Customer
Guest Customer / Visitor
System
```

## BR-ACTOR-001 — Authenticated Roles

Authenticated roles are:

```text
Admin
Manager
Agent
Customer
```

Enforcement:

```text
Spatie Laravel Permission
Authentication Middleware
Policies
```

---

## BR-ACTOR-002 — Guest Is Not an RBAC Role

Guest Customer is:

```text
Public Actor
```

Guest has:

```text
No authenticated User requirement
No Spatie Role requirement
No Spatie Permission requirement
```

Guest access is controlled through approved public routes and Checkout business rules.

---

## BR-ACTOR-003 — System Actor Must Follow Business Rules

Internal actors such as:

```text
Console Command
Observer
Scheduled Task
Optional Queue Job
```

must not bypass domain/business rules merely because they execute internally.

---

# 5. Global Authorization Rules

## BR-AUTHZ-001 — Authorization Order

Protected staff/customer resource actions must use the applicable sequence:

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

---

## BR-AUTHZ-002 — Frontend Visibility Is Not Authorization

Blade visibility such as:

```text
@can
@role
@if
```

may hide controls but must not be the security boundary.

Backend authorization is mandatory.

---

## BR-AUTHZ-003 — Deny By Default

If a protected capability is not explicitly granted:

```text
DENY
```

Examples:

```text
Manager without orders.assign
→ cannot assign Agent

Agent without payments.verify
→ cannot verify Payment

Customer accessing another Customer Order
→ deny
```

---

## BR-AUTHZ-004 — Permission + Ownership

Permission answers:

```text
Can this actor perform this class of action?
```

Policy / ownership answers:

```text
Can this actor perform it on this exact resource?
```

Both may be required.

---

# 6. Authentication Rules

## BR-AUTH-001 — Customer Registration

A Visitor may create a Customer account using valid registration information.

Invalid registration data must be rejected.

---

## BR-AUTH-002 — Login

Only valid credentials may create an authenticated session.

---

## BR-AUTH-003 — Logout

Logout must terminate the current authenticated session.

---

## BR-AUTH-004 — Password Storage

Passwords must never be stored in plain text.

Laravel-supported secure password hashing must be used.

---

## BR-AUTH-005 — Staff Backoffice Protection

Admin / Manager / Agent dashboards and management routes require authentication.

---

## BR-AUTH-006 — Customer Account Protection

The following require authenticated Customer context:

```text
My Account
My Orders
Own Order Details
```

---

## BR-AUTH-007 — Guest Purchase Does Not Require Login

Authentication must not be required to complete approved Guest Checkout.

---

# 7. Guest Buyer Rules

## BR-GUEST-001 — Public Browsing

Guest may access public storefront pages.

Allowed:

```text
Home
Shop
Category
Product Details
Search
Cart
Checkout
Payment Instruction
Thank You after successful checkout
```

---

## BR-GUEST-002 — Guest Cart

Guest may use the session-based Cart.

---

## BR-GUEST-003 — Guest Coupon

Guest may apply one valid Coupon under the same Coupon rules as an authenticated Customer.

---

## BR-GUEST-004 — Guest Checkout

Guest may proceed to Checkout without registration or login.

---

## BR-GUEST-005 — Guest Buyer Information

Guest Checkout must collect the required buyer/contact/shipping data:

```text
name
phone
email
address
city_or_area
```

`order_note` may be optional.

---

## BR-GUEST-006 — Guest User Reference

A Guest Order may have:

```text
user_id = null
```

This is valid business behavior.

---

## BR-GUEST-007 — Guest Snapshot

Guest Order must preserve the final checkout buyer/contact/shipping information.

---

## BR-GUEST-008 — Guest Payment Submission

Guest may:

```text
Select active bKash/Nagad/Rocket method
View configured payment instruction
Enter Transaction ID
Place Order
```

---

## BR-GUEST-009 — Guest Thank You Page

A valid successful Guest Checkout redirects to Thank You.

Thank You must not falsely claim that payment was independently verified.

---

## BR-GUEST-010 — Guest Has No My Orders Dashboard

Guest does not receive an authenticated My Orders dashboard in the P0 MVP.

---

## BR-GUEST-011 — Guest Cannot Access Arbitrary Orders

A Guest must not be able to modify a URL/ID and expose arbitrary Order data.

---

# 8. Logged-in Customer Rules

## BR-CUSTOMER-001 — Authenticated Checkout

Logged-in Customer may complete Checkout.

---

## BR-CUSTOMER-002 — Profile Prefill

Authenticated profile data may prefill Checkout fields.

The Customer may modify final delivery details before submission.

---

## BR-CUSTOMER-003 — User-Linked Order

Authenticated Customer Order should link to the authenticated User.

---

## BR-CUSTOMER-004 — Snapshot Still Required

Authenticated Customer Orders must still preserve buyer/contact/shipping snapshots.

The Order history must not depend entirely on the current User profile.

---

## BR-CUSTOMER-005 — Own Orders Only

Customer may access only own Orders.

Ownership condition:

```text
order.user_id == auth()->id()
```

---

## BR-CUSTOMER-006 — Customer Internal Note Restriction

Customer must not see staff Internal Notes.

---

## BR-CUSTOMER-007 — Customer Cannot Control Staff Workflow

Customer must not:

```text
Assign Agent
Verify Payment
Reject Payment
Set staff Order status directly
Manage Product
Manage Stock
Manage RBAC
```

---

# 9. Admin Rules

## BR-ADMIN-001 — Admin Operational Scope

Admin may manage approved core modules:

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
Agent Assignment
Payment Methods
Payment Verification
Reports
Settings
Supported Restore Flows
```

---

## BR-ADMIN-002 — Admin Still Follows Business State Rules

Admin authority does not mean invalid state transitions may be silently accepted.

Admin must still follow:

```text
Validation
Order State Rules
Payment State Rules
Database Constraints
Business Rules
```

---

# 10. Manager Rules

## BR-MANAGER-001 — Manager Is Permission-Driven

Manager is an operational role, not a second Admin.

---

## BR-MANAGER-002 — Default Manager Operational Scope

Default Manager permissions may include:

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
dashboard.view
```

---

## BR-MANAGER-003 — Sensitive Capabilities Not Default

Manager does not automatically receive:

```text
staff.*
roles.*
permissions.*
payment-methods.manage
settings.update
payments.reject
products.delete
products.restore
orders.restore
```

unless explicitly granted where allowed by the approved permission model.

---

## BR-MANAGER-004 — Explicit Permission Required

Any additional Manager capability must be explicitly granted.

---

# 11. Agent Rules

## BR-AGENT-001 — Agent Is an Order-Processing Role

Agent's primary domain is assigned Order processing.

---

## BR-AGENT-002 — Assigned Order Scope

Default Agent resource condition:

```text
order.assigned_agent_id == auth()->id()
```

---

## BR-AGENT-003 — Agent May View Assigned Delivery Data

Agent may view buyer/contact/delivery information required to fulfill an assigned Order.

This does not grant global Customer directory access.

---

## BR-AGENT-004 — Agent Order Actions

For assigned Orders and valid transitions, Agent may:

```text
Confirm
Move to Processing
Mark Shipped
Mark Delivered
Cancel where allowed
Add Internal Note
```

subject to permission + Policy + state rules.

---

## BR-AGENT-005 — Cross-Agent Access Denied

Example:

```text
Agent A
opens Order assigned to Agent B
→ DENY
```

even if Agent A has `orders.view`, unless a later approved broader permission rule explicitly changes this behavior.

---

## BR-AGENT-006 — Agent Cannot Assign Orders

Agent does not receive `orders.assign` by default.

---

## BR-AGENT-007 — Agent Cannot Verify Payment

Agent cannot verify or reject Payment Submission in the MVP.

---

# 12. Role & Permission Rules

## BR-RBAC-001 — Spatie Permission

Authenticated role/permission management uses:

```text
Spatie Laravel Permission
```

---

## BR-RBAC-002 — Permission Naming

Permission naming format:

```text
module.action
```

---

## BR-RBAC-003 — Explicit Permission Records

Explicit granular permissions are authoritative.

---

## BR-RBAC-004 — Permission Normalization

Use granular implementation permissions such as:

```text
staff.view
staff.create
staff.update
staff.delete

settings.view
settings.update
```

The umbrella documentation labels:

```text
staff.manage
settings.manage
```

must not be seeded in parallel unless deliberately approved later.

---

## BR-RBAC-005 — Guest Has No Role

Do not create `Guest` as a Spatie role for the MVP.

---

## BR-RBAC-006 — Customer Has No Staff Permissions

Customer role must not receive staff operational permissions.

---

## BR-RBAC-007 — New Sensitive Permissions Do Not Auto-Grant

A new sensitive permission added later must not silently become available to existing Manager/Agent roles.

---

# 13. Category Rules

## BR-CAT-001 — Single-Level Category

MVP Category structure is single-level.

Nested Category hierarchy is not required.

---

## BR-CAT-002 — Category Management Authorization

Category management requires appropriate staff permission.

---

## BR-CAT-003 — Category Soft Delete

Category may use SoftDelete according to approved scope.

---

## BR-CAT-004 — Category Restore

Restore requires:

```text
categories.restore
```

or equivalent approved authority.

---

## BR-CAT-005 — Public Browsing Is Not Staff Permission

Customer/Guest may browse public Categories without receiving `categories.view`.

---

# 14. Product Rules

## BR-PROD-001 — Product Status

Product status values:

```text
active
inactive
```

---

## BR-PROD-002 — Public Product Visibility

Normal Storefront must not display:

```text
inactive Product
soft-deleted Product
```

---

## BR-PROD-003 — Product Price Authority

Server-side Product data is authoritative for pricing.

Client-provided Product Price must not be trusted.

---

## BR-PROD-004 — Product Variant Scope

Complex Size/Color variant pricing and variant stock are out of MVP scope.

---

## BR-PROD-005 — Product Historical Independence

Editing current Product data must not rewrite historical Order Item snapshots.

---

# 15. Media Rules

## BR-MEDIA-001 — Media Package

Product/category media uses:

```text
Spatie Laravel Media Library
```

---

## BR-MEDIA-002 — Media Collections

Approved / suggested collections:

```text
product_thumbnail
product_gallery
category_image
user_avatar optional
```

---

## BR-MEDIA-003 — Media Validation

Media upload must be validated server-side.

---

## BR-MEDIA-004 — Media Authorization

Only authorized staff may replace/delete managed Product or Category media.

---

# 16. Stock Rules

## BR-STOCK-001 — Stock Source

MVP uses simple Product stock:

```text
products.stock_quantity
```

---

## BR-STOCK-002 — Availability

```text
stock_quantity > 0
→ In Stock

stock_quantity = 0
→ Out of Stock
```

---

## BR-STOCK-003 — Checkout Revalidation

Stock must be revalidated during Checkout.

A prior Cart state is not sufficient proof of current availability.

---

## BR-STOCK-004 — Insufficient Stock

If requested quantity exceeds available stock:

```text
Reject Checkout / Order creation
```

---

## BR-STOCK-005 — Stock Deduction

Successful Order creation must deduct stock as part of the Order business operation.

---

## BR-STOCK-006 — Transaction-Safe Stock Mutation

Stock mutation and related Checkout Order writes must be handled safely within the Order transaction where applicable.

---

## BR-STOCK-007 — No Advanced Inventory Ledger

Advanced inventory ledger / warehouse movement system is out of MVP scope.

---

# 17. Storefront Rules

## BR-STORE-001 — Public Storefront

Both Guest and logged-in Customer may access the public Storefront.

---

## BR-STORE-002 — Active Product Rule

Storefront Product listing/details must respect Product active/deleted state.

---

## BR-STORE-003 — No Staff Permission Required for Public Shopping

Public browsing does not require staff RBAC permissions.

---

# 18. Search Rules

## BR-SEARCH-001 — Product Name Search

Product Name search is P0.

---

## BR-SEARCH-002 — Category Filter

Category filtering is P0.

---

## BR-SEARCH-003 — Optional Search Features

The following are P1:

```text
SKU search
Availability filter
Latest sorting
Price Low → High
Price High → Low
```

---

# 19. Cart Rules

## BR-CART-001 — Session-Based Cart

MVP Cart is session-based.

---

## BR-CART-002 — Guest and Authenticated Cart

Both Guest and logged-in Customer may use Cart.

---

## BR-CART-003 — Positive Valid Quantity

Cart quantity must pass server-side validation.

---

## BR-CART-004 — Cart Price Is Not Final Authority

Cart display values do not remove the need to recalculate price at Checkout.

---

## BR-CART-005 — Cart Operations

Allowed Cart operations:

```text
Add
Update Quantity
Remove
Clear
```

---

## BR-CART-006 — Clear After Successful Checkout

After a successful Checkout:

```text
Clear Cart
```

A failed Checkout should not be treated as a successful purchase.

---

# 20. Coupon Rules

## BR-COUPON-001 — Supported Discount Types

```text
fixed
percentage
```

---

## BR-COUPON-002 — One Coupon Per Order

Only one Coupon may apply to an Order.

---

## BR-COUPON-003 — No Coupon Stacking

Multiple Coupons cannot be stacked in the MVP.

---

## BR-COUPON-004 — Active Coupon

Inactive Coupon must be rejected.

---

## BR-COUPON-005 — Expired Coupon

Expired Coupon must be rejected.

---

## BR-COUPON-006 — Minimum Order Amount

If Coupon defines a minimum Order amount, the minimum must be satisfied before application.

---

## BR-COUPON-007 — Coupon Date Window

Coupon validity must respect configured start/end dates.

---

## BR-COUPON-008 — Server-Side Discount Calculation

Coupon discount must be calculated server-side.

Client-submitted discount amount is not authoritative.

---

## BR-COUPON-009 — Coupon Expiry Command

The P0 Console Command:

```text
coupons:expire
```

may identify expired active Coupons and mark them inactive/expired according to implementation.

---

# 21. Checkout Rules

## BR-CHECKOUT-001 — Supported Buyer Contexts

Checkout must support:

```text
Guest Customer
Logged-in Customer
```

---

## BR-CHECKOUT-002 — Buyer Context Resolution

Checkout must determine whether the buyer is:

```text
Guest
OR
Authenticated Customer
```

without forcing Guest to register.

---

## BR-CHECKOUT-003 — Checkout Required Data

Checkout accepts the approved fields:

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

with required/optional behavior defined by the approved validation context.

---

## BR-CHECKOUT-004 — Checkout Validation Order

The approved logical flow is:

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

---

## BR-CHECKOUT-005 — Server Recalculates Totals

Checkout must recalculate authoritative totals server-side.

---

## BR-CHECKOUT-006 — Client Financial Values Are Not Authority

Never trust browser-submitted:

```text
Product Price
Subtotal
Discount
Grand Total
Payment Status
```

as authoritative business values.

---

## BR-CHECKOUT-007 — Active Payment Method Required

Selected manual Payment Method must be active.

---

## BR-CHECKOUT-008 — Transaction ID Required for Manual MFS

For manual bKash/Nagad/Rocket Checkout:

```text
Transaction ID is required
```

---

## BR-CHECKOUT-009 — Success Redirect

Only after successful Order operation:

```text
Redirect to Thank You
```

---

# 22. Buyer Snapshot Rules

## BR-SNAPSHOT-001 — Every Order Stores Buyer Snapshot

Every Order must preserve historical checkout data.

Approved snapshot concepts:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

---

## BR-SNAPSHOT-002 — Authenticated Customer Snapshot

Logged-in Customer Order must still preserve snapshot data.

---

## BR-SNAPSHOT-003 — Guest Snapshot

Guest Order must preserve the same required buyer/contact/shipping snapshot without requiring a User account.

---

## BR-SNAPSHOT-004 — Snapshot Immutability

Later User profile changes must not rewrite historical Order snapshot values.

---

# 23. Checkout Transaction Rules

## BR-TX-001 — Atomic Checkout Operation

Where applicable, the core Checkout business operation should create/update related records atomically:

```text
Order
Order Items
Payment Submission
Stock Mutation
Initial Order History
```

---

## BR-TX-002 — Failed Checkout Must Not Produce Partial Success

If the Order business operation fails:

```text
Do not present Checkout as successful.
```

Related critical writes should not remain partially committed where transaction protection applies.

---

## BR-TX-003 — Cart Clear After Commit-Level Success

Cart should be cleared only after successful Checkout/Order creation.

---

# 24. Manual Payment Method Rules

## BR-PAYMETHOD-001 — Supported Manual Methods

P0 manual Payment Methods:

```text
bKash
Nagad
Rocket
```

---

## BR-PAYMETHOD-002 — Admin Configuration

Admin-configured data may include:

```text
name
code
account_number
account_type
instruction
status
```

---

## BR-PAYMETHOD-003 — Active Methods Only

Only active manual Payment Methods may be selected at Checkout.

---

## BR-PAYMETHOD-004 — Sensitive Configuration

Only authorized staff may change MFS account configuration.

Default sensitive owner:

```text
Admin
```

---

## BR-PAYMETHOD-005 — Public Checkout Display

Guest/Customer may view the active Payment number/instruction necessary to pay.

This public display does not grant staff `payment-methods.view` or management authority.

---

## BR-PAYMETHOD-006 — No Real Provider API

MVP does not use real bKash/Nagad/Rocket payment APIs.

---

# 25. Payment Submission Rules

## BR-PAYSUB-001 — Submission Record

Manual MFS Checkout creates a Payment Submission associated with the Order and selected Payment Method.

---

## BR-PAYSUB-002 — Transaction ID

Transaction ID must be supplied for manual MFS payment.

---

## BR-PAYSUB-003 — Initial Status

Initial Payment Submission / Payment state after valid submission is:

```text
submitted
```

---

## BR-PAYSUB-004 — Format Validation Is Not Provider Verification

`ValidManualTransactionId` may validate format/basic requirement only.

It must not represent real verification against bKash/Nagad/Rocket provider servers.

---

## BR-PAYSUB-005 — Submission Actor

Both:

```text
Guest
Logged-in Customer
```

may submit manual payment information as part of Checkout.

---

# 26. Payment Verification Rules

## BR-PAYVERIFY-001 — Submission Is Not Verification

Core invariant:

```text
submitted != verified
```

---

## BR-PAYVERIFY-002 — Verify Permission

Payment verification requires:

```text
Authenticated Staff
+
payments.verify
+
Valid Payment Submission State
+
Business Rule
```

---

## BR-PAYVERIFY-003 — Reject Permission

Payment rejection requires:

```text
Authenticated Staff
+
payments.reject
+
Valid Payment Submission State
+
Business Rule
```

---

## BR-PAYVERIFY-004 — Default Verification Authorities

Default:

```text
Admin → verify + reject
Manager → verify
Agent → none
Customer → none
Guest → none
```

Manager rejection is not default unless explicitly granted `payments.reject`.

---

## BR-PAYVERIFY-005 — Verifier Identity

Verification should preserve the responsible verifier identity.

---

## BR-PAYVERIFY-006 — Verification Timestamp

Verification should preserve verification time.

---

## BR-PAYVERIFY-007 — Rejection Note

Rejected Payment may store a rejection note.

---

## BR-PAYVERIFY-008 — Customer/Guest Cannot Verify

Customer or Guest cannot change a submitted Payment into verified/rejected state.

---

# 27. Payment State Rules

Approved Payment states:

```text
unpaid
submitted
verified
rejected
```

## BR-PAYSTATE-001 — State Separation

Payment state must remain separate from Order fulfillment state.

---

## BR-PAYSTATE-002 — Thank You Does Not Mean Verified

Displaying Thank You means:

```text
Order + payment information submission succeeded
```

It does **not** mean:

```text
Provider-confirmed payment succeeded
```

Recommended customer-facing wording:

> Order placed successfully. Your payment information has been submitted for verification.

---

## BR-PAYSTATE-003 — Client Cannot Set Payment State

Client input must never be authoritative for:

```text
verified
rejected
unpaid
submitted
```

except the server itself assigning the correct state from the business operation.

---

# 28. Order Rules

## BR-ORDER-001 — Order Creation

A valid Checkout creates an Order.

---

## BR-ORDER-002 — Human-Readable Order Number

Order must have a unique human-readable Order Number concept.

Example pattern from approved documents:

```text
ORD-2026-000001
```

Exact generation implementation belongs to later technical documents.

---

## BR-ORDER-003 — Nullable Customer Relationship

Guest Order:

```text
user_id = null
```

is valid.

Logged-in Customer Order links to authenticated Customer where applicable.

---

## BR-ORDER-004 — Assigned Agent May Be Nullable

New Order may exist before Agent assignment.

---

## BR-ORDER-005 — Financial Components

Order concept includes:

```text
subtotal
discount
shipping
grand_total
```

Exact shipping calculation rule is not defined in current source documents.

---

## BR-ORDER-006 — Separate Status Columns/Concepts

Order must maintain independent concepts for:

```text
Order Status
Payment Status
```

---

## BR-ORDER-007 — Customer Note vs Internal Note

Customer Note may be visible to staff.

Internal Note is staff-only and must not be exposed to Customer/Guest.

---

# 29. Order Item Snapshot Rules

## BR-ITEM-001 — Required Commercial Snapshot

Order Item preserves:

```text
Product Name Snapshot
SKU Snapshot
Unit Price Snapshot
Quantity
Line Total
```

plus current Product reference where final schema permits.

---

## BR-ITEM-002 — Historical Stability

Later Product changes must not rewrite historical Order Item commercial values.

---

## BR-ITEM-003 — Current Product Is Not Sole Historical Source

Historical Order display must not depend entirely on current Product Name/Price/SKU.

---

# 30. Order Assignment Rules

## BR-ASSIGN-001 — Canonical Permission

Assignment permission:

```text
orders.assign
```

---

## BR-ASSIGN-002 — Default Assignment Actors

Allowed by default:

```text
Admin
Manager
```

Manager requires `orders.assign`.

---

## BR-ASSIGN-003 — Agent Cannot Self-Assign Arbitrary Orders

Agent cannot assign arbitrary Orders to self or others.

---

## BR-ASSIGN-004 — Valid Assignment Target

Assignment target must be a valid Agent.

---

## BR-ASSIGN-005 — Reassignment

Authorized Admin/Manager may reassign where permitted.

---

## BR-ASSIGN-006 — Assignment Traceability

Agent assignment/reassignment should be traceable through Order history or approved operational traceability.

---

# 31. Order Status Transition Rules

Approved Order states:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

## BR-STATUS-001 — Initial Status

New Order begins:

```text
pending
```

---

## BR-STATUS-002 — Normal Forward Flow

```text
pending
→ confirmed
→ processing
→ shipped
→ delivered
```

---

## BR-STATUS-003 — Controlled Transition

Status mutation must use controlled business logic.

Suggested enforcement:

```text
OrderStatusService
ValidOrderStatusTransition
OrderStatus Enum
```

---

## BR-STATUS-004 — Invalid Transition

Invalid Order state transition must be rejected.

---

## BR-STATUS-005 — Delivered Is Terminal in Normal Flow

Delivered must not casually return to Pending or earlier states.

No reverse transition is approved in current source documents.

---

## BR-STATUS-006 — Status Authorization

Changing Order status requires applicable:

```text
Authentication
+
orders.update
+
OrderPolicy
+
Valid Transition
```

---

# 32. Cancellation Rules

## BR-CANCEL-001 — Allowed Cancellation States

Approved cancellation flow identifies:

```text
pending
confirmed
processing
```

as cancellable states.

---

## BR-CANCEL-002 — Cancellation Permission

Staff cancellation requires:

```text
orders.cancel
```

plus applicable Policy and state rule.

---

## BR-CANCEL-003 — Agent Cancellation Scope

Agent may cancel only an assigned Order and only where cancellation state rules allow.

---

## BR-CANCEL-004 — Post-Cancellation Stock Restoration

Current source documents do **not** define whether cancellation automatically restores stock.

Status:

```text
TBD
```

Do not invent automatic stock restoration until later approved.

---

# 33. Order History Rules

## BR-HISTORY-001 — Important Changes Are Traceable

Important Order changes should create operational history.

Approved examples:

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

---

## BR-HISTORY-002 — Status Changes Must Be Recorded

Order status changes must create history.

---

## BR-HISTORY-003 — Actor Identity

History should identify the actor when one exists.

---

## BR-HISTORY-004 — Preserve History

Order history is historical data and should be preserved.

---

# 34. Customer Order Access Rules

## BR-CUSTORDER-001 — My Orders

Logged-in Customer may list own Orders.

---

## BR-CUSTORDER-002 — Own Order Detail

Customer may view:

```text
Order Number
Date
Items
Total
Payment Method
Payment Status
Order Status
```

for own Order.

---

## BR-CUSTORDER-003 — Direct ID Manipulation Protection

Changing URL / Order ID must not expose another Customer's Order.

---

# 35. Guest Order Rules

## BR-GUESTORDER-001 — Valid Without User Record

Guest Order remains valid without authenticated User relation.

---

## BR-GUESTORDER-002 — Same Backoffice Workflow

After creation, Guest Order may be processed by the same approved staff Order workflow as an authenticated Customer Order.

---

## BR-GUESTORDER-003 — No Guest My Orders in P0

Guest Order tracking/account history is not a P0 feature.

---

# 36. Dashboard Rules

## BR-DASH-001 — Admin Scope

Admin dashboard may use system-wide metrics.

---

## BR-DASH-002 — Manager Scope

Manager dashboard uses operational metrics allowed by approved permissions.

---

## BR-DASH-003 — Agent Scope

Agent dashboard must be scoped to assigned Orders.

---

## BR-DASH-004 — Customer Scope

Customer account/dashboard data must be scoped to own Orders/Profile.

---

## BR-DASH-005 — Guest Has No Dashboard

Guest has no authenticated dashboard.

---

# 37. Soft Delete Rules

Recommended SoftDeletes:

```text
users
categories
products
coupons
orders
payment_methods
```

## BR-SOFT-001 — Public Product Exclusion

Soft-deleted Product must not appear in normal Storefront.

---

## BR-SOFT-002 — Historical Children

Historical data such as:

```text
order_items
order_histories
payment_submissions
```

should be preserved according to approved history requirements.

---

## BR-SOFT-003 — Force Delete

Force Delete is not required for MVP.

---

# 38. Restore Rules

## BR-RESTORE-001 — Supported Restore Permissions

Currently defined restore permissions:

```text
categories.restore
products.restore
orders.restore
```

---

## BR-RESTORE-002 — Default Restore Authority

Admin has default restore authority where restore is implemented.

Manager requires explicit restore permission.

Agent/Customer/Guest have no restore authority.

---

# 39. Service Layer Rules

## BR-SERVICE-001 — Thin Controllers

Controllers should remain thin.

---

## BR-SERVICE-002 — Business Logic Location

Reusable/core business logic should live in Services where appropriate.

Approved suggested Services:

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

## BR-SERVICE-003 — Checkout Orchestration

Checkout orchestration belongs in `CheckoutService` rather than a large Controller method.

---

## BR-SERVICE-004 — Payment Verification

Payment verification business logic belongs in `PaymentService`.

---

## BR-SERVICE-005 — Status Transition

Order transition logic belongs in `OrderStatusService` and/or approved Rule.

---

# 40. Observer Rules

## BR-OBSERVER-001 — Lightweight Only

Observer may handle lightweight lifecycle behavior.

Approved examples:

```text
Lightweight slug preparation
Small bookkeeping
Dispatch local event
```

---

## BR-OBSERVER-002 — No Checkout Workflow in Observer

Do not place full Checkout transaction in Observer.

---

## BR-OBSERVER-003 — No Payment Verification Workflow in Observer

Do not place Payment verification workflow in Observer.

---

## BR-OBSERVER-004 — No Complex Stock Workflow in Observer

Complex stock mutation/order transaction logic must not be hidden inside Observer.

---

# 41. Trait Rules

## BR-TRAIT-001 — Reusable Behavior Only

Trait must represent genuinely reusable behavior.

Suggested:

```text
HasSlug
HasOrderNumber
HasActiveScope
```

---

## BR-TRAIT-002 — No Artificial Trait

Do not create a Trait only to increase component count.

---

# 42. Custom Rule Rules

## BR-RULE-001 — Meaningful Custom Rules

At least two meaningful Custom Rules should be used in the practice project.

Suggested:

```text
ValidCoupon
SufficientStock
ValidManualTransactionId
ValidOrderStatusTransition
```

---

## BR-RULE-002 — Manual Transaction Validation Boundary

`ValidManualTransactionId` validates input requirements/format only.

It does not perform provider verification.

---

# 43. Console Command Rules

## BR-CONSOLE-001 — Minimum Command

P0 minimum:

```text
coupons:expire
```

---

## BR-CONSOLE-002 — Service Reuse

Console Command should reuse an existing Service when the business logic already belongs there.

---

## BR-CONSOLE-003 — Optional Commands

P1:

```text
products:low-stock-report
orders:cleanup-pending
```

---

# 44. Policy Rules

Recommended Policies:

```text
ProductPolicy
OrderPolicy
StaffPolicy
CouponPolicy
PaymentMethodPolicy
PaymentSubmissionPolicy
```

## BR-POLICY-001 — OrderPolicy Customer Rule

Customer can view own Order only.

---

## BR-POLICY-002 — OrderPolicy Agent Rule

Agent can operate on assigned Order only by default.

---

## BR-POLICY-003 — Payment Submission Policy

Payment verify/reject actions require appropriate permissions.

---

## BR-POLICY-004 — Guest Checkout

Guest Checkout uses public validation/business rules rather than authenticated resource ownership.

---

# 45. Form Request Rules

## BR-REQUEST-001 — Write Validation

Write operations should use Form Requests where applicable.

---

## BR-REQUEST-002 — Checkout Context

`CheckoutRequest` must support both:

```text
Guest
Authenticated Customer
```

---

## BR-REQUEST-003 — Do Not Duplicate Validation

Controllers should not unnecessarily duplicate Form Request validation.

---

# 46. Enum Rules

## BR-ENUM-001 — Important Status Values Use Enums

Recommended:

```text
ProductStatus
OrderStatus
PaymentStatus
CouponStatus
PaymentMethodStatus
```

---

## BR-ENUM-002 — Enum Does Not Define Workflow Alone

Enum defines allowed values.

Service/Rule defines allowed transitions.

---

# 47. Queue Rules

## BR-QUEUE-001 — Queue Is Optional for Core MVP

Queue is P1 for the 13-day core project.

---

## BR-QUEUE-002 — Appropriate Optional Uses

Possible:

```text
Email
Image Conversion
Non-Critical Notification
```

---

## BR-QUEUE-003 — Core Order Creation Must Not Depend on Optional Queue

Checkout/Order creation must remain functional without unnecessary Queue complexity.

---

# 48. Validation Rules

## BR-VALID-001 — All Writes Server-Validated

All write operations require server-side validation.

---

## BR-VALID-002 — Price

Price is server-calculated.

---

## BR-VALID-003 — Discount

Discount is server-calculated.

---

## BR-VALID-004 — Stock

Stock is server-validated.

---

## BR-VALID-005 — Coupon

Coupon is server-validated.

---

## BR-VALID-006 — Payment Method

Payment Method is server-validated.

---

## BR-VALID-007 — Transaction ID

Transaction ID is server-validated.

---

## BR-VALID-008 — Media

Media is validated.

---

## BR-VALID-009 — Agent Assignment

Assignment target must be validated as an eligible Agent.

---

## BR-VALID-010 — Guest Details

Required Guest buyer details must be validated.

---

# 49. Security Rules

## BR-SEC-001 — CSRF

Web form writes must use Laravel CSRF protection.

---

## BR-SEC-002 — Mass Assignment

Mass assignment must be controlled.

---

## BR-SEC-003 — Client Cannot Set Role/Permission Authority

Never trust request payload for role/permission authority.

---

## BR-SEC-004 — Client Cannot Set Agent Authority

Customer/Guest must not be able to set authoritative internal `assigned_agent_id`.

---

## BR-SEC-005 — Client Cannot Set Payment Verified

Customer/Guest cannot submit authoritative `verified` payment state.

---

## BR-SEC-006 — Client Cannot Set Order Workflow State

Customer/Guest cannot set staff Order workflow state.

---

## BR-SEC-007 — Internal Notes Protected

Internal Notes must be hidden from Customer/Guest.

---

## BR-SEC-008 — Guest Order Exposure Protection

Guest routes must not expose arbitrary Order data.

---

## BR-SEC-009 — Sensitive MFS Configuration

Payment account numbers/configuration management is protected.

---

# 50. Data Integrity Rules

## BR-DATA-001 — Product Relationship

Product belongs to Category.

---

## BR-DATA-002 — Order Customer Relationship

Order may belong to Customer/User:

```text
nullable
```

to support Guest Checkout.

---

## BR-DATA-003 — Order Agent Relationship

Assigned Agent relation may be nullable until assignment.

---

## BR-DATA-004 — Order Items

Order has one or more Order Items for purchased Products.

---

## BR-DATA-005 — Order History

Order may have multiple Order History records.

---

## BR-DATA-006 — Payment Submission Relationship

Payment Submission belongs to:

```text
Order
Payment Method
Verifier/User nullable
```

Exact `Order → PaymentSubmission` cardinality remains deferred to final schema because source documents allow `hasOne / hasMany`.

---

# 51. Historical Data Rules

## BR-HISTDATA-001 — Snapshot Over Live Data

Historical Order display must prefer stored Order/Order Item snapshots for purchase facts.

---

## BR-HISTDATA-002 — Product Edit Independence

Changing Product:

```text
name
sku
price
```

later must not rewrite historical purchase snapshots.

---

## BR-HISTDATA-003 — User Profile Edit Independence

Changing Customer profile later must not rewrite historical buyer/shipping snapshots.

---

## BR-HISTDATA-004 — Staff Record Changes

Staff account deletion/deactivation must not casually break historical Order actor references.

Exact foreign-key behavior belongs to Database Schema documentation.

---

# 52. Error / Denial Behavior

## BR-ERROR-001 — Invalid Permission

Protected action without required permission:

```text
DENY
```

with Laravel-appropriate authorization response.

---

## BR-ERROR-002 — Ownership Failure

Permission exists but resource ownership fails:

```text
DENY
```

---

## BR-ERROR-003 — Invalid Stock

Insufficient Stock:

```text
Reject Checkout
```

---

## BR-ERROR-004 — Invalid Coupon

Inactive/expired/invalid Coupon:

```text
Reject Coupon application
```

---

## BR-ERROR-005 — Invalid Payment Method

Inactive/invalid Payment Method:

```text
Reject Checkout selection
```

---

## BR-ERROR-006 — Missing Transaction ID

Manual MFS Checkout without required Transaction ID:

```text
Validation Failure
```

---

## BR-ERROR-007 — Invalid Order Transition

Invalid state transition:

```text
Reject transition
```

---

# 53. Rule Enforcement Map

| Rule Area | Primary Enforcement |
|---|---|
| Authentication | Middleware / Laravel Auth |
| Role / Permission | Spatie Permission Middleware / `can` |
| Customer Ownership | `OrderPolicy` |
| Agent Assignment Ownership | `OrderPolicy` |
| Product Authorization | `ProductPolicy` |
| Payment Verification | Permission + `PaymentSubmissionPolicy` + Service |
| Form Validation | Form Requests |
| Coupon Validation | Form Request / Rule / `CouponService` |
| Stock Validation | Rule / `StockService` / `CheckoutService` |
| Checkout Orchestration | `CheckoutService` |
| Order Creation | `OrderService` / Checkout transaction |
| Order Transition | `OrderStatusService` / Custom Rule / Enum |
| Payment State | `PaymentService` / Enum |
| Media Validation | Form Request / Media Library rules |
| SoftDelete | Eloquent model + permissions/policies |
| Coupon Expiry | Console Command + Service |
| Historical Snapshots | Checkout/Order Service + Database |

---

# 54. Business State Models

## 54.1 Product State

```text
active
inactive
```

Public Storefront:

```text
active + not soft deleted
→ display normally
```

---

## 54.2 Coupon State

```text
active
inactive
expired
```

Use only valid active Coupon within its configured validity rules.

---

## 54.3 Payment State

```text
unpaid
submitted
verified
rejected
```

Core invariant:

```text
submitted != verified
```

---

## 54.4 Order State

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Normal path:

```text
pending
→ confirmed
→ processing
→ shipped
→ delivered
```

Cancellation path approved from:

```text
pending
confirmed
processing
→ cancelled
```

---

# 55. Explicitly Undefined / TBD Rules

The current source documents do not define the following behavior precisely.

These must **not** be invented silently.

## TBD-001 — Shipping Calculation

Order contains a `shipping` amount, but exact shipping calculation method is not defined.

Status:

```text
TBD
```

Complex Shipping Engine is out of MVP scope.

---

## TBD-002 — Stock Restoration After Cancellation

Order creation deducts Stock, but automatic Stock restoration after cancellation is not defined.

Status:

```text
TBD
```

---

## TBD-003 — Payment Rejection Effect on Order Status

Payment and Order statuses are separate.

The source documents do not define whether:

```text
payment = rejected
```

must automatically cancel an Order.

Status:

```text
TBD
```

Do not auto-cancel unless later approved.

---

## TBD-004 — Payment Verification Requirement Before Fulfillment

The source documents do not define whether an Order must be `payment = verified` before Agent processing begins.

Status:

```text
TBD
```

Do not impose this dependency without later approval.

---

## TBD-005 — Order Assignment Timing

Sources define Admin/Manager → Agent assignment but do not require assignment before or after Payment verification.

Status:

```text
TBD
```

---

## TBD-006 — Payment Submission Cardinality

Current source allows:

```text
Order hasOne / hasMany PaymentSubmission
```

Final decision belongs to ERD / Database Schema.

---

## TBD-007 — Duplicate Transaction ID Policy

No explicit rule defines whether Transaction ID must be globally unique or unique per payment method/order.

Status:

```text
TBD
```

---

## TBD-008 — Coupon Code Case Sensitivity

Not defined.

Status:

```text
TBD
```

---

## TBD-009 — Sale Price Activation Rule

Product has regular and sale price concepts, but exact temporal/activation rules for Sale Price are not defined.

Status:

```text
TBD
```

---

## TBD-010 — Guest Order Tracking

Guest My Orders / tracking link is P1/Future.

No P0 Guest tracking business rule should be invented.

---

# 56. Out-of-Scope Business Rules

Do not create core MVP business rules for:

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

Optional P1 features also must not become P0 dependencies.

---

# 57. Critical Test Scenarios

The following business rules require critical automated Feature Tests.

## TEST-BR-001 — Guest Checkout

```text
Guest can place Order without login
Guest Order user_id = null
Buyer snapshot stored
```

Expected:

```text
PASS
```

---

## TEST-BR-002 — Logged-in Checkout

```text
Authenticated Customer places Order
```

Expected:

```text
Order user_id = Customer ID
Buyer snapshot stored
```

---

## TEST-BR-003 — Customer Ownership

```text
Customer A opens Customer B Order
```

Expected:

```text
DENY
```

---

## TEST-BR-004 — Agent Ownership

```text
Agent A opens Agent B assigned Order
```

Expected:

```text
DENY
```

---

## TEST-BR-005 — Permission Denial

```text
Manager without orders.assign
attempts assignment
```

Expected:

```text
DENY
```

---

## TEST-BR-006 — Stock

```text
Requested quantity > available stock
```

Expected:

```text
Checkout rejected
```

---

## TEST-BR-007 — Coupon Expiry

```text
Expired Coupon applied
```

Expected:

```text
Rejected
```

---

## TEST-BR-008 — Price Tampering

```text
Browser sends modified price
```

Expected:

```text
Server recalculates authoritative price
```

---

## TEST-BR-009 — Missing Transaction ID

```text
Manual MFS checkout
transaction_id missing
```

Expected:

```text
Validation failure
```

---

## TEST-BR-010 — Payment Submission State

After valid Transaction ID submission:

```text
payment = submitted
```

not:

```text
verified
```

---

## TEST-BR-011 — Unauthorized Verification

```text
Customer / Agent attempts Payment verification
```

Expected:

```text
DENY
```

---

## TEST-BR-012 — Authorized Verification

```text
Admin
OR
Manager with payments.verify
```

may verify valid Payment Submission.

---

## TEST-BR-013 — Rejection Permission

Manager without:

```text
payments.reject
```

cannot reject.

Admin can reject by default.

---

## TEST-BR-014 — Invalid Transition

Example:

```text
delivered → pending
```

Expected:

```text
Rejected
```

---

## TEST-BR-015 — Valid Transition History

Example:

```text
processing → shipped
```

Expected:

```text
Status updated
History record created
```

---

## TEST-BR-016 — Soft-Deleted Product

Soft-deleted Product:

```text
must not appear in normal Storefront
```

---

## TEST-BR-017 — Internal Note

Customer Order detail:

```text
must not expose internal_note
```

---

# 58. Business Rule Traceability

| Source Area | Business Rule Sections |
|---|---|
| Actor / Roles | 4, 7–12 |
| Authorization | 5, 44, 49 |
| Guest Checkout | 7, 21–23, 35 |
| Logged-in Customer | 8, 22, 34 |
| Category | 13 |
| Product | 14 |
| Media | 15 |
| Stock | 16 |
| Storefront | 17 |
| Search | 18 |
| Cart | 19 |
| Coupon | 20 |
| Checkout | 21–23 |
| Buyer Snapshot | 22, 51 |
| Manual Payment | 24–27 |
| Orders | 28–35 |
| Assignment | 30 |
| Status | 31–32 |
| History | 33, 51 |
| Dashboard | 36 |
| SoftDelete | 37–38 |
| Services | 39 |
| Observers | 40 |
| Traits | 41 |
| Rules | 42 |
| Console | 43 |
| Policies | 44 |
| Requests | 45 |
| Enums | 46 |
| Queue | 47 |
| Validation | 48 |
| Security | 49 |
| Data Integrity | 50 |
| TBD Decisions | 55 |
| Critical Tests | 57 |

---

# 59. Definition of Business-Rule Complete

Business rules are considered implementation-ready when:

- [ ] Guest Checkout works without login.
- [ ] Guest Order supports nullable `user_id`.
- [ ] Guest and authenticated Orders preserve buyer snapshots.
- [ ] Customer can only access own Orders.
- [ ] Agent can only process assigned Orders.
- [ ] Manager behavior is explicit permission-driven.
- [ ] Admin still follows validation/state rules.
- [ ] Product price is server-authoritative.
- [ ] Coupon discount is server-authoritative.
- [ ] Stock is revalidated during Checkout.
- [ ] Insufficient Stock blocks Order.
- [ ] Order creation deducts Stock.
- [ ] Checkout critical writes are transaction-safe where applicable.
- [ ] Manual MFS requires Transaction ID.
- [ ] Payment Submission begins as `submitted`.
- [ ] `submitted != verified`.
- [ ] Customer/Guest/Agent cannot verify Payment.
- [ ] Payment verify/reject permissions are enforced.
- [ ] Order Status and Payment Status remain separate.
- [ ] Order transitions are controlled.
- [ ] Invalid transitions are rejected.
- [ ] Important status changes create history.
- [ ] Order Items preserve commercial snapshots.
- [ ] Soft-deleted Product is hidden from Storefront.
- [ ] Internal Notes are staff-only.
- [ ] Form Requests validate writes.
- [ ] Services contain reusable business logic.
- [ ] Observers remain lightweight.
- [ ] Traits are genuinely reusable.
- [ ] Custom Rules are meaningful.
- [ ] `coupons:expire` exists.
- [ ] Critical authorization/security tests pass.
- [ ] Undefined behavior remains explicitly TBD instead of being invented.
- [ ] Out-of-scope modules are not introduced into MVP.

---

# 60. Final Business Rule Statement

ShopPilot business behavior is governed by the following core principles:

> **Both Guest and logged-in Customers may buy products; Checkout is server-authoritative; Guest Orders may have nullable `user_id` while preserving buyer snapshots; Stock and Coupon rules are revalidated at Checkout; manual bKash/Nagad/Rocket Transaction ID submission creates `submitted`, not `verified`; Admin/authorized Manager review payments; Agent processes assigned Orders only; Customer accesses own Orders only; Order state transitions are controlled and historical purchase facts remain preserved.**

The implementation must combine:

> **Spatie Permission + Policies + Form Requests + Services + Custom Rules + Enums + Transactions + SoftDeletes + Historical Snapshots**

without adding unapproved enterprise complexity.

---

# 61. Next Documentation

Next:

```text
06-ARCHITECTURE.md
```

Then:

```text
07-DATABASE-ERD.md
08-DATABASE-SCHEMA.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```
