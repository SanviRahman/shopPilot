# ShopPilot E-commerce — User Roles and Permissions
# শপপাইলট ই-কমার্স — ইউজার রোলস এবং পারমিশনস

> **Document:** User Roles and Permissions / ইউজার রোলস এবং পারমিশনস  
> **Project:** ShopPilot E-commerce  
> **Source Documents:** `01-PROJECT-OVERVIEW-UPDATED.md` v1.1, `ShopPilot-02-PRD.md` v1.0, `ShopPilot-03-FEATURES.md` v1.0  
> **Project Type:** Role-Based Single-Store E-commerce & Order Operations System  
> **Primary Goal:** 13-Day Full-Stack Laravel Practice Project  
> **RBAC Package:** Spatie Laravel Permission  
> **Authorization Layers:** Authentication + Role/Permission + Policy/Ownership + Business Rule  
> **Authenticated Roles:** Admin, Manager, Agent, Customer  
> **Public Actor:** Guest Customer / Visitor  
> **Document Version:** 1.0  
> **Language:** English + Bangla  
> **Status:** Role & Permission Definition  
> **Next Document:** `05-BUSINESS-RULES.md`

---

# Table of Contents

1. Document Purpose  
2. Authorization Principles  
3. Actor Model  
4. Role Summary  
5. Permission Naming Convention  
6. Permission Catalogue  
7. Permission Naming Normalization Note  
8. Default Role Templates  
9. Master Role-Permission Matrix  
10. Admin Role  
11. Manager Role  
12. Agent Role  
13. Customer Role  
14. Guest Customer  
15. System Actor  
16. Dashboard Access Rules  
17. Staff Management Rules  
18. Role & Permission Management Rules  
19. Category Permissions  
20. Product Permissions  
21. Stock Permissions  
22. Coupon Permissions  
23. Order Permissions  
24. Order Assignment Permissions  
25. Payment Permissions  
26. Payment Method Permissions  
27. Customer Data Permissions  
28. Reports Permissions  
29. Settings Permissions  
30. Soft Delete & Restore Permissions  
31. Resource Ownership Rules  
32. Customer Order Ownership  
33. Agent Assignment Ownership  
34. Guest Checkout Authorization  
35. Payment Verification Authorization  
36. Order Status Authorization  
37. Sensitive Permission Rules  
38. Route / Middleware / Policy Flow  
39. Blade UI Rules  
40. Policy Mapping  
41. Permission Seeding Strategy  
42. Role Assignment Rules  
43. Permission Change Rules  
44. Deny-by-Default Rules  
45. Security Requirements  
46. Test Matrix  
47. Acceptance Criteria  
48. Out-of-Scope Authorization  
49. Implementation Checklist  
50. Final Role & Permission Statement  
51. Next Documentation

---

# 1. Document Purpose

This document defines **who can do what** inside ShopPilot E-commerce.

এটি Project Overview, PRD এবং Features specification-এর approved behavior follow করে role, permission, ownership এবং authorization rules formalize করে।

Primary goals:

- Admin scope define করা
- Manager-এর default operational scope define করা
- Agent-এর assigned-order-only access define করা
- Customer-এর own-resource scope define করা
- Guest Customer-এর public checkout scope define করা
- Spatie Permission catalogue define করা
- Laravel Policy ownership rules define করা
- sensitive permissions আলাদা করা
- backend authorization order define করা
- testable access matrix তৈরি করা

This document does **not** define final database schema or route filenames.

---

# 2. Authorization Principles

ShopPilot authorization must follow:

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

Important principles:

1. **Frontend visibility is not authorization.**
2. **Role alone is not sufficient for operational access.**
3. **Manager and Agent use explicit permissions.**
4. **Policies protect resource ownership.**
5. **Customer can only access own Orders.**
6. **Agent can only process assigned Orders by default.**
7. **Guest Customer is not an authenticated RBAC role.**
8. **Sensitive configuration is Admin-controlled by default.**
9. **Payment submission does not grant payment-verification authority.**
10. **Backend authorization must be enforced even if a button is hidden in Blade.**

---

# 3. Actor Model

System actors:

```text
Admin
Manager
Agent
Customer
Guest Customer / Visitor
System
```

## 3.1 Authenticated Actors

```text
Admin
Manager
Agent
Customer
```

These users exist in the application `users` model and may use Spatie roles.

## 3.2 Public Actor

```text
Guest Customer / Visitor
```

Guest:

- has no authenticated User record requirement
- has no Spatie role requirement
- can use public shopping and Guest Checkout
- may create an Order with `user_id = null`

## 3.3 System Actor

Internal application process:

```text
Console Command
Observer
Scheduled Task
Optional Queue Job
```

System actions do not bypass business rules merely because they are internal.

---

# 4. Role Summary

| Role / Actor | Type | Main Responsibility |
|---|---|---|
| Admin | Authenticated Role | Full store administration and sensitive control |
| Manager | Authenticated Role | Permission-driven store operations |
| Agent | Authenticated Role | Assigned Order processing |
| Customer | Authenticated Role | Shopping, account, own Orders |
| Guest Customer | Public Actor | Shopping and checkout without login |
| System | Internal Actor | Automated application operations |

---

# 5. Permission Naming Convention

Implementation permission convention:

```text
module.action
```

Examples:

```text
products.view
products.create
products.update
orders.view
orders.update
orders.assign
payments.verify
payments.reject
```

Rules:

- lowercase
- dot-separated
- action-oriented
- explicit
- no ambiguous names where granular permission exists

---

# 6. Permission Catalogue

The implementation permission catalogue derived from the approved PRD / Features is:

## Dashboard

```text
dashboard.view
```

## Users

```text
users.view
users.update
```

## Customers

```text
customers.view
```

## Staff

```text
staff.view
staff.create
staff.update
staff.delete
```

## Roles

```text
roles.view
roles.manage
```

## Permissions

```text
permissions.view
permissions.manage
```

## Categories

```text
categories.view
categories.create
categories.update
categories.delete
categories.restore
```

## Products

```text
products.view
products.create
products.update
products.delete
products.restore
```

## Stock

```text
stock.view
stock.update
```

## Coupons

```text
coupons.view
coupons.create
coupons.update
coupons.delete
```

## Orders

```text
orders.view
orders.update
orders.assign
orders.cancel
orders.restore
```

## Payments

```text
payments.view
payments.verify
payments.reject
```

## Payment Methods

```text
payment-methods.view
payment-methods.manage
```

## Reports

```text
reports.view
```

## Settings

```text
settings.view
settings.update
```

---

# 7. Permission Naming Normalization Note

The Project Overview uses the sensitive umbrella labels:

```text
staff.manage
settings.manage
```

The later PRD / Features define more granular implementation permissions:

```text
staff.view
staff.create
staff.update
staff.delete

settings.view
settings.update
```

For implementation, this document treats the **granular PRD / Features catalogue as the seedable permission set**.

Therefore:

```text
staff.manage
settings.manage
```

should be treated as high-level documentation labels unless a later approved document explicitly chooses to seed them.

Do not seed both umbrella and granular versions without a deliberate decision.

---

# 8. Default Role Templates

The following are default role templates for the MVP.

They do not remove Policy or ownership checks.

## 8.1 Admin Default

Admin receives all approved explicit permissions.

Admin should not rely on an unbounded wildcard permission for the practice project.

---

## 8.2 Manager Default

Default Manager permissions:

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

Any extra Manager capability must be explicitly granted.

---

## 8.3 Agent Default

Recommended operational permissions:

```text
dashboard.view
orders.view
orders.update
orders.cancel
```

Important:

`orders.view`, `orders.update`, and `orders.cancel` do **not** mean Agent can act on every Order.

`OrderPolicy` must scope Agent access to:

```text
order.assigned_agent_id == authenticated_agent.id
```

unless a broader permission/business decision is explicitly introduced later.

Agent does not receive:

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

## 8.4 Customer Default

Customer is an authenticated role used to identify buyer account context.

Customer does not receive staff operational permissions.

Customer capabilities are primarily controlled through:

```text
Authenticated Customer Routes
+
OrderPolicy Own-Order Rule
+
Business Rules
```

Customer may access:

```text
Profile
Cart
Checkout
My Orders
Own Order Details
```

Customer cannot access staff dashboards or management screens.

---

## 8.5 Guest Default

Guest has:

```text
No Spatie Role
No Spatie Permissions
```

Guest access is provided only by approved public routes and public business rules.

---

# 9. Master Role-Permission Matrix

Legend:

```text
✓  Default Allowed
△  Can be granted explicitly / conditional
—  Not allowed by default
P  Policy / ownership scope additionally required
```

| Permission | Admin | Manager | Agent | Customer | Guest |
|---|---:|---:|---:|---:|---:|
| dashboard.view | ✓ | ✓ | ✓ | — | — |
| users.view | ✓ | — | — | — | — |
| users.update | ✓ | — | — | — | — |
| customers.view | ✓ | ✓ | — | — | — |
| staff.view | ✓ | — | — | — | — |
| staff.create | ✓ | — | — | — | — |
| staff.update | ✓ | — | — | — | — |
| staff.delete | ✓ | — | — | — | — |
| roles.view | ✓ | — | — | — | — |
| roles.manage | ✓ | — | — | — | — |
| permissions.view | ✓ | — | — | — | — |
| permissions.manage | ✓ | — | — | — | — |
| categories.view | ✓ | △ | — | — | — |
| categories.create | ✓ | △ | — | — | — |
| categories.update | ✓ | △ | — | — | — |
| categories.delete | ✓ | △ | — | — | — |
| categories.restore | ✓ | △ | — | — | — |
| products.view | ✓ | ✓ | — | — | — |
| products.create | ✓ | ✓ | — | — | — |
| products.update | ✓ | ✓ | — | — | — |
| products.delete | ✓ | △ | — | — | — |
| products.restore | ✓ | △ | — | — | — |
| stock.view | ✓ | ✓ | — | — | — |
| stock.update | ✓ | ✓ | — | — | — |
| coupons.view | ✓ | △ | — | — | — |
| coupons.create | ✓ | △ | — | — | — |
| coupons.update | ✓ | △ | — | — | — |
| coupons.delete | ✓ | △ | — | — | — |
| orders.view | ✓ | ✓ | ✓ + P | own via P | — |
| orders.update | ✓ | ✓ | ✓ + P | — | — |
| orders.assign | ✓ | ✓ | — | — | — |
| orders.cancel | ✓ | △ | ✓ + P | — | — |
| orders.restore | ✓ | △ | — | — | — |
| payments.view | ✓ | ✓ | — | own display only | — |
| payments.verify | ✓ | ✓ | — | — | — |
| payments.reject | ✓ | △ | — | — | — |
| payment-methods.view | ✓ | △ | — | public active only | public active only |
| payment-methods.manage | ✓ | — | — | — | — |
| reports.view | ✓ | ✓ | — | — | — |
| settings.view | ✓ | — | — | — | — |
| settings.update | ✓ | — | — | — | — |

Notes:

- Public checkout showing an active MFS number is not equivalent to granting `payment-methods.view`.
- Customer own Order access is controlled by Policy rather than staff permission.
- Agent Order access always requires assignment ownership.
- Manager `△` permissions are not part of the default Manager template unless explicitly granted.

---

# 10. Admin Role

Admin is the highest application-level authenticated role.

Admin can:

```text
Manage Staff
Manage Roles
Manage Permissions
Manage Customers
Manage Categories
Manage Products
Manage Media
Manage Stock
Manage Coupons
Manage Orders
Assign Agents
Update Order Status
Configure bKash / Nagad / Rocket
Review Payment Submissions
Verify Payment
Reject Payment
View Reports
View Dashboard Analytics
Restore Supported Resources
Manage Settings
```

Admin must still follow:

```text
Validation
Business Rules
Order State Rules
Payment State Rules
Database Constraints
```

Admin status does not mean invalid state transitions should be silently accepted.

---

# 11. Manager Role

Manager is **permission-driven**, not a second Admin.

Default Manager scope:

```text
Operational Products
Stock
Orders
Agent Assignment
Customer View
Payment View / Verification
Reports
```

Manager does not automatically control:

```text
Admin Accounts
Staff Administration
Role Architecture
Permission Architecture
MFS Account Configuration
Sensitive Settings
```

Manager permissions can be adjusted by Admin.

However, every additional capability must remain explicit.

---

# 12. Agent Role

Agent is an Order-processing role.

Default Agent workflow:

```text
Agent Dashboard
    ↓
My Assigned Orders
    ↓
View Buyer / Delivery Information
    ↓
Confirm
    ↓
Processing
    ↓
Shipped
    ↓
Delivered
```

Agent may cancel only:

```text
if orders.cancel is granted
AND
OrderPolicy permits the Order
AND
OrderStatus rule allows cancellation
```

Agent cannot:

```text
Assign Orders
Configure Payments
Verify Payments
Manage Products
Manage Stock
Manage Staff
Manage Roles
Manage Permissions
Manage Settings
View Global Reports
```

---

# 13. Customer Role

Customer is an authenticated buyer role.

Customer capabilities:

```text
Browse
Search
Cart
Coupon
Checkout
Manual Payment Submission
Thank You
Profile
My Orders
Own Order Details
```

Customer authorization is based on:

```text
Authenticated Customer Context
+
Resource Ownership
```

Customer cannot:

```text
Access another Customer's Order
See Internal Notes
Verify Payment
Assign Agent
Update Staff Order Workflow
Access Staff Dashboards
```

---

# 14. Guest Customer

Guest Customer is a public actor.

Guest can:

```text
Browse
Search
Cart
Coupon
Guest Checkout
Submit Buyer Information
Select Active MFS Method
Submit Transaction ID
Place Order
View Thank You Page
```

Guest cannot:

```text
Access My Orders Dashboard
Access Staff Dashboard
Access Arbitrary Order Details
Verify Payment
Manage Any Resource
```

Guest Order:

```text
user_id = null
```

is valid.

The Order must preserve buyer/contact/address snapshots.

---

# 15. System Actor

Internal application operations may include:

```text
Coupon Expiry Console Command
Observer Lifecycle Action
Optional Queue Job
```

System operations must use approved Services / business rules where appropriate.

Example:

```text
coupons:expire
```

may update expired Coupons without a human role, but it should not bypass the Coupon rules defined by the application.

---

# 16. Dashboard Access Rules

## Admin Dashboard

Actor:

```text
Admin
```

Default permission:

```text
dashboard.view
```

Data scope:

```text
System-wide
```

---

## Manager Dashboard

Actor:

```text
Manager
```

Default permission:

```text
dashboard.view
```

Data:

```text
Operational metrics
```

---

## Agent Dashboard

Actor:

```text
Agent
```

Default permission:

```text
dashboard.view
```

Data must be scoped to:

```text
Assigned Orders
```

---

## Customer Dashboard

Actor:

```text
Customer
```

Customer account access is authenticated and ownership-based.

Customer does not gain staff dashboard access from generic dashboard permissions.

---

## Guest

Guest has no authenticated Dashboard.

---

# 17. Staff Management Rules

Admin only by default.

Permissions:

```text
staff.view
staff.create
staff.update
staff.delete
```

Rules:

- Manager cannot create another Manager by default.
- Agent cannot create staff.
- Customer cannot create staff.
- Guest cannot create staff.
- Staff role assignment must be authorized.
- Staff account deletion must not break historical Order actor references.

---

# 18. Role & Permission Management Rules

Sensitive permissions:

```text
roles.view
roles.manage
permissions.view
permissions.manage
```

Default owner:

```text
Admin
```

Rules:

1. Manager has no default permission-management capability.
2. Agent never manages roles/permissions in MVP.
3. Customer never manages roles/permissions.
4. Guest has no role/permission access.
5. Permission assignment must be server-authorized.
6. UI controls must not be the only protection.
7. Role templates may be seeded, but explicit permission records remain authoritative.

---

# 19. Category Permissions

Permissions:

```text
categories.view
categories.create
categories.update
categories.delete
categories.restore
```

Default:

```text
Admin = Full
Manager = Not default; explicit optional grant
Agent = None
Customer = Public storefront category browsing only
Guest = Public storefront category browsing only
```

Public storefront Category browsing does not require staff `categories.view`.

---

# 20. Product Permissions

Permissions:

```text
products.view
products.create
products.update
products.delete
products.restore
```

Default:

```text
Admin
→ full

Manager
→ view/create/update

Agent
→ none

Customer/Guest
→ public active Product browsing only
```

Customer/Guest cannot use staff Product CRUD permissions.

---

# 21. Stock Permissions

Permissions:

```text
stock.view
stock.update
```

Default:

```text
Admin = view/update
Manager = view/update
Agent = none
Customer = none
Guest = none
```

Storefront availability is public read behavior and is not equivalent to `stock.view`.

---

# 22. Coupon Permissions

Permissions:

```text
coupons.view
coupons.create
coupons.update
coupons.delete
```

Default:

```text
Admin = full
Manager = optional explicit grant
Agent = none
Customer/Guest = can apply valid public Coupon only
```

Using a Coupon does not grant Coupon management permission.

---

# 23. Order Permissions

Staff permissions:

```text
orders.view
orders.update
orders.assign
orders.cancel
orders.restore
```

## Admin

```text
Full Order operational access
```

## Manager

Default:

```text
orders.view
orders.update
orders.assign
```

Optional:

```text
orders.cancel
orders.restore
```

## Agent

Default:

```text
orders.view
orders.update
orders.cancel
```

plus mandatory assigned-order Policy.

## Customer

No staff Order permission.

Own Order read access through:

```text
OrderPolicy
```

## Guest

No authenticated Order read permission after checkout in MVP.

---

# 24. Order Assignment Permissions

Canonical permission:

```text
orders.assign
```

Allowed by default:

```text
Admin
Manager
```

Not allowed:

```text
Agent
Customer
Guest
```

Assignment rules:

1. Assignment target must be an Agent.
2. Manager requires `orders.assign`.
3. Reassignment requires same authority.
4. Assignment should be traceable.
5. Agent cannot self-assign arbitrary Orders.
6. Customer/Guest can never select internal Agent assignment.

---

# 25. Payment Permissions

Permissions:

```text
payments.view
payments.verify
payments.reject
```

## Admin

```text
view
verify
reject
```

## Manager Default

```text
view
verify
```

`payments.reject` is not part of default Manager template.

Admin may explicitly grant it if later required.

## Agent

No Payment verification permission.

## Customer / Guest

May submit Transaction ID through Checkout but cannot verify or reject Payment.

Important:

```text
Payment Submission Capability
≠
Payment Verification Permission
```

---

# 26. Payment Method Permissions

Permissions:

```text
payment-methods.view
payment-methods.manage
```

Sensitive configuration:

```text
Account Number
Account Type
Instruction
Status
```

Admin:

```text
view
manage
```

Manager:

```text
not granted by default
```

Agent:

```text
none
```

Customer / Guest:

Public Checkout may display only active configured payment instructions needed to pay.

This public display does not expose staff management screens.

---

# 27. Customer Data Permissions

Permission:

```text
customers.view
```

Default:

```text
Admin = allowed
Manager = allowed
Agent = no global customer list
```

Agent may view customer/contact information **inside an assigned Order** because it is necessary for fulfillment.

This does not grant global `customers.view`.

Customer:

```text
own profile only
```

Guest:

```text
no customer directory access
```

---

# 28. Reports Permissions

Permission:

```text
reports.view
```

Default:

```text
Admin = allowed
Manager = allowed
Agent = denied
Customer = denied
Guest = denied
```

Agent dashboard counts are operational assigned-order metrics, not global Reports access.

---

# 29. Settings Permissions

Implementation permissions:

```text
settings.view
settings.update
```

Default:

```text
Admin = allowed
Manager = denied
Agent = denied
Customer = denied
Guest = denied
```

Project Overview's `settings.manage` should not be separately seeded unless later documentation intentionally adopts that alias.

---

# 30. Soft Delete & Restore Permissions

Selected resources may use SoftDeletes.

Restore permissions currently defined:

```text
categories.restore
products.restore
orders.restore
```

Admin:

```text
default restore authority
```

Manager:

```text
only if explicitly granted
```

Agent:

```text
no restore authority
```

Customer/Guest:

```text
no restore authority
```

Force Delete:

```text
Not required for MVP
```

---

# 31. Resource Ownership Rules

Permission answers:

> **Can this role perform this kind of action?**

Policy / ownership answers:

> **Can this user perform it on this exact resource?**

Examples:

```text
Agent has orders.update
BUT
Order assigned to another Agent
→ DENY
```

```text
Customer authenticated
BUT
Order belongs to another Customer
→ DENY
```

Permission and Policy must work together.

---

# 32. Customer Order Ownership

Rule:

```text
order.user_id == auth()->id()
```

for authenticated Customer Order access.

Customer may:

```text
View own Order
View own Order Items
View own Payment Status
View own Order Status
```

Customer may not:

```text
View another Customer Order
View Internal Note
Change Staff Workflow State
Verify Payment
```

Guest Orders are not matched through authenticated Customer ownership because:

```text
user_id = null
```

---

# 33. Agent Assignment Ownership

Default Agent resource rule:

```text
order.assigned_agent_id == auth()->id()
```

Required for:

```text
View assigned Order
Update assigned Order
Cancel assigned Order where state allows
Add Internal Note
```

Agent with `orders.view` still receives:

```text
DENY
```

for another Agent's Order under default policy.

---

# 34. Guest Checkout Authorization

Guest Checkout does not use authenticated role permission.

Public checkout authorization flow:

```text
Public Route
    ↓
Valid Cart
    ↓
Active Product
    ↓
Stock Available
    ↓
Valid Coupon
    ↓
Valid Buyer Details
    ↓
Active Payment Method
    ↓
Valid Transaction ID
    ↓
Create Order
```

Guest cannot use public checkout to gain staff access.

---

# 35. Payment Verification Authorization

Verification requires:

```text
Authenticated Staff
+
payments.verify
+
Valid Payment Submission State
+
Business Rule
```

Rejection requires:

```text
Authenticated Staff
+
payments.reject
+
Valid Payment Submission State
+
Business Rule
```

Default:

```text
Admin → verify + reject
Manager → verify
Agent → none
Customer → none
Guest → none
```

---

# 36. Order Status Authorization

Changing Order status requires all applicable checks:

```text
Authentication
+
orders.update
+
OrderPolicy
+
ValidOrderStatusTransition
```

Cancellation additionally requires:

```text
orders.cancel
```

Example:

```text
Agent
has orders.update
Order assigned to Agent
Current status = processing
Requested status = shipped
Valid transition
→ ALLOW
```

Example:

```text
Agent
has orders.update
Order belongs to Agent B
→ DENY
```

---

# 37. Sensitive Permission Rules

Sensitive permissions include:

```text
roles.manage
permissions.manage
staff.create
staff.update
staff.delete
payment-methods.manage
payments.verify
payments.reject
settings.update
```

Default principle:

```text
Sensitive capability is NOT inherited merely because user is staff.
```

Manager receives only documented operational permissions.

Agent receives only assigned-order operational permissions.

---

# 38. Route / Middleware / Policy Flow

Protected backend request:

```text
Request
   ↓
Authenticate User
   ↓
Check Role Context
   ↓
Check Required Permission
   ↓
Load Resource
   ↓
Policy / Ownership Check
   ↓
Form Request Validation
   ↓
Business Rule
   ↓
Service / Action
   ↓
Response
```

Public Guest Checkout:

```text
Request
   ↓
Public Route
   ↓
Form Request Validation
   ↓
Checkout Business Rules
   ↓
CheckoutService
   ↓
Order Transaction
   ↓
Thank You
```

---

# 39. Blade UI Rules

Blade may use:

```text
@role
@can
@canany
```

to hide unavailable controls.

Examples:

```text
Assign Agent button
→ show only when orders.assign allowed

Verify Payment button
→ show only when payments.verify allowed
```

But:

> Backend Controller / Service / Policy must still enforce the same authorization.

Never rely only on:

```text
@if
hidden button
disabled input
```

for security.

---

# 40. Policy Mapping

Recommended Policy mapping:

| Policy | Primary Responsibility |
|---|---|
| ProductPolicy | Product staff authorization |
| OrderPolicy | Customer ownership + Agent assignment scope + staff Order access |
| StaffPolicy | Staff-management access |
| CouponPolicy | Coupon management |
| PaymentMethodPolicy | MFS configuration access |
| PaymentSubmissionPolicy | Payment view/verify/reject access |

Important OrderPolicy decisions:

```text
Admin
→ broad access

Manager
→ permission-controlled operational access

Agent
→ assigned Order only

Customer
→ own Order only

Guest
→ no authenticated Order Policy access
```

---

# 41. Permission Seeding Strategy

Recommended seed order:

```text
1. Create Permission records
2. Create Roles
3. Assign default Permissions to Roles
4. Create initial Admin
5. Assign Admin role
```

Recommended roles:

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

as a database role for MVP.

Guest is a public actor.

---

# 42. Role Assignment Rules

## Admin

Only authorized Admin should assign:

```text
Admin
Manager
Agent
Customer
```

roles to application users.

## Manager

Manager does not assign staff roles by default.

## Agent

Agent cannot assign roles.

## Customer

Customer cannot assign roles.

---

# 43. Permission Change Rules

Permission changes are sensitive.

Rules:

1. Admin controls permission assignment.
2. Manager cannot change own permissions.
3. Agent cannot change own permissions.
4. Customer cannot change own permissions.
5. No permission should be trusted from client-submitted hidden fields.
6. Permission changes should be server-authorized.
7. Generic wildcard access is not required for MVP.
8. New sensitive permission added later should not silently become available to existing non-Admin roles.

---

# 44. Deny-by-Default Rules

If access is not explicitly granted, deny.

Examples:

```text
Manager asks for Role Management
No roles.manage
→ DENY
```

```text
Agent asks for Payment Verification
No payments.verify
→ DENY
```

```text
Customer asks for another Customer Order
Ownership false
→ DENY
```

```text
Guest asks for Admin route
Not authenticated
→ DENY
```

---

# 45. Security Requirements

Mandatory:

```text
Authentication
Spatie Permission
Policies
Ownership Checks
Form Request Validation
CSRF
Mass Assignment Protection
Server-Side Pricing
Server-Side Discount
Server-Side Stock
Protected Payment Status
Protected Internal Notes
```

Never trust:

```text
Role from request payload
Permission from hidden field
assigned_agent_id from Customer
payment_status from Customer
order_status from Customer
price from browser
discount from browser
```

---

# 46. Test Matrix

Critical authorization tests:

| Test | Expected |
|---|---|
| Guest opens Admin route | DENY |
| Customer opens Admin route | DENY |
| Agent opens Role Management | DENY |
| Manager without permission opens Roles | DENY |
| Manager with `orders.assign` assigns Agent | ALLOW |
| Manager without `orders.assign` assigns Agent | DENY |
| Agent opens own assigned Order | ALLOW |
| Agent opens another Agent Order | DENY |
| Customer opens own Order | ALLOW |
| Customer opens another Customer Order | DENY |
| Guest places Order | ALLOW |
| Guest Order has null user_id | PASS |
| Customer verifies Payment | DENY |
| Agent verifies Payment | DENY |
| Manager with `payments.verify` verifies | ALLOW |
| Manager without `payments.verify` verifies | DENY |
| Admin verifies Payment | ALLOW |
| Admin rejects Payment | ALLOW |
| Manager without `payments.reject` rejects | DENY |
| Customer sees Internal Note | DENY |
| Soft-deleted Product public access | DENY / Hidden |

---

# 47. Acceptance Criteria

Role/permission system is acceptable when:

## Admin

- can manage approved platform/store operations
- can manage roles/permissions
- can manage MFS configuration
- can verify/reject payments

## Manager

- receives only explicit operational permissions
- cannot manage sensitive RBAC/settings by default
- can assign Agent when `orders.assign` exists
- can verify Payment when `payments.verify` exists

## Agent

- sees assigned Orders
- cannot access another Agent's Order
- cannot assign Orders
- cannot verify Payments
- cannot manage Products/Settings/RBAC

## Customer

- can access own account
- can purchase
- can view own Orders
- cannot view another Customer's Order
- cannot access Internal Notes/staff controls

## Guest

- can purchase without login
- has no RBAC role
- cannot access authenticated dashboards
- cannot access arbitrary Order data

## Backend

- permission checks are server-side
- Policy ownership works
- Blade hiding is not the only authorization
- sensitive actions are deny-by-default

---

# 48. Out-of-Scope Authorization

The current permission model does not need permissions for:

```text
Multi-Vendor
Warehouse
Courier API
Refund Gateway
Affiliate
Accounting
AI
Multi-Currency
Multi-Language
Real MFS Provider API
```

Do not create speculative permissions for out-of-scope modules.

---

# 49. Implementation Checklist

- [ ] Install/configure Spatie Laravel Permission.
- [ ] Add `HasRoles` to User model.
- [ ] Seed Admin, Manager, Agent, Customer roles.
- [ ] Seed explicit permission catalogue.
- [ ] Assign full explicit permissions to Admin.
- [ ] Assign default operational permissions to Manager.
- [ ] Assign assigned-order operational permissions to Agent.
- [ ] Keep Customer free of staff permissions.
- [ ] Do not create Guest role.
- [ ] Protect backend routes with authentication.
- [ ] Protect sensitive routes with permission middleware.
- [ ] Implement OrderPolicy.
- [ ] Enforce Customer own-Order access.
- [ ] Enforce Agent assigned-Order access.
- [ ] Implement PaymentSubmissionPolicy.
- [ ] Protect payment verification.
- [ ] Implement Product/Coupon/Staff/PaymentMethod Policies where used.
- [ ] Use Blade permission directives only as UI layer.
- [ ] Add authorization Feature Tests.
- [ ] Verify missing permission returns 403 / appropriate denial.
- [ ] Verify Guest Checkout works without authentication.
- [ ] Verify no hidden client field can grant privilege.

---

# 50. Final Role & Permission Statement

ShopPilot uses a layered authorization model:

> **Admin has controlled full store administration; Manager has explicit operational permissions; Agent is restricted to assigned-order operations; authenticated Customer is restricted to own account and own Orders; Guest Customer is a public buyer with checkout access but no RBAC role.**

The implementation must combine:

> **Spatie Laravel Permission + Middleware + Laravel Policies + Resource Ownership + Business Rules**

so that role or UI visibility alone never becomes the security boundary.

Default sensitive control remains with Admin, while Manager and Agent capabilities are explicit, minimal, and deny-by-default.

---

# 51. Next Documentation

Next:

```text
05-BUSINESS-RULES.md
```

Then:

```text
06-ARCHITECTURE.md
07-DATABASE-ERD.md
08-DATABASE-SCHEMA.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```
