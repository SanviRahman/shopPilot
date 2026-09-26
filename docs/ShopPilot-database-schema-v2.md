# ShopPilot E-commerce — Database Schema
# শপপাইলট ই-কমার্স — ডেটাবেজ স্কিমা

> **Document:** Physical Database Schema / ফিজিক্যাল ডেটাবেজ স্কিমা  
> **Project:** ShopPilot E-commerce  
> **Database:** MySQL / InnoDB / utf8mb4  
> **ORM / Migration:** Laravel Eloquent + Laravel Schema Builder  
> **RBAC:** Spatie Laravel Permission  
> **Media:** Spatie Laravel Media Library  
> **Architecture:** Service-Oriented Modular Laravel Monolith  
> **Cart:** Laravel Session — no P0 `carts` / `cart_items` tables  
> **Payment:** Manual bKash / Nagad / Rocket Submission  
> **Document Version:** 2.0  
> **Status:** Canonical Physical Schema — Separate Admin Model + Spatie Role/Permission + Universal SoftDeletes + Trash/Restore  
> **Language:** English + Bangla

---

# 1. Purpose

এই document ShopPilot-এর final physical database structure define করে।

Latest project decision অনুযায়ী authentication/data ownership এখন দুই ভাগে:

```text
Admin-side actors
→ admins table
→ Admin model
→ Spatie guard: admin

Customer-side authenticated buyers
→ users table
→ User model
→ Spatie guard: web
```

Admin-side role examples:

```text
super_admin
admin
manager
agent
```

Customer role:

```text
customer
```

Guest:

```text
No database account required
No Spatie role required
orders.user_id = NULL
```

Critical rules:

```text
Every application-owned model uses SoftDeletes.
Every application management module has Trash + Restore.
Package pivot tables do not need SoftDeletes.
Roles and Permissions use custom application models extending Spatie.
Spatie's published permission migration stays package-owned.
Custom Role/Permission columns are added with a separate migration.
```

---

# 2. Final Application Models

Application-owned Eloquent models:

```text
Admin
User
Role
Permission
Category
Product
Coupon
PaymentMethod
Order
OrderItem
OrderHistory
PaymentSubmission
```

Every one of these models must use:

```php
use Illuminate\Database\Eloquent\SoftDeletes;
```

Example:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
}
```

Corresponding tables must contain:

```text
deleted_at TIMESTAMP NULL
```

Exception:

```text
Spatie pivot tables
Spatie media table
Framework tables
```

are not treated as ShopPilot application-owned SoftDelete models.

---

# 3. Final Table Inventory

## Application / Authentication

```text
admins
users
roles
permissions
```

## Spatie Permission Pivot Tables

```text
model_has_roles
model_has_permissions
role_has_permissions
```

## E-commerce Core

```text
categories
products
coupons
payment_methods
orders
order_items
order_histories
payment_submissions
```

## Package

```text
media
```

## Framework / Configuration Dependent

```text
sessions
password_reset_tokens
notifications
```

No P0 tables:

```text
customers
carts
cart_items
product_variants
warehouses
inventory_movements
payment_gateway_transactions
refunds
courier_shipments
```

---

# 4. Common Schema Conventions

```text
Primary Key       → BIGINT UNSIGNED AUTO_INCREMENT
Foreign Key       → BIGINT UNSIGNED
Money             → DECIMAL(12,2)
Boolean           → BOOLEAN / TINYINT(1)
Status            → VARCHAR + PHP backed Enum / validated string
Character Set     → utf8mb4
Collation         → utf8mb4_unicode_ci
Created/Updated   → Laravel timestamps()
Soft Delete       → deleted_at via softDeletes()
```

Never use:

```text
FLOAT
DOUBLE
```

for authoritative money.

---

# 5. Authentication / Guard Design

Recommended Laravel auth model mapping:

```text
web guard
→ App\Models\User
→ users table

admin guard
→ App\Models\Admin
→ admins table
```

Spatie guard mapping:

```text
Customer permissions/role
→ guard_name = web

Super Admin/Admin/Manager/Agent
→ guard_name = admin
```

Do not add:

```text
admins.role_id
users.role_id
```

because Spatie manages role relations through:

```text
model_has_roles
```

---

# 6. `admins` Table

**Model:** `App\Models\Admin`

Purpose:

```text
Super Admin
Admin
Manager
Agent
```

Physical schema:

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `name` | VARCHAR(120) | No | — | — | Admin display name |
| `email` | VARCHAR(191) | No | — | UNIQUE | Admin login |
| `email_verified_at` | TIMESTAMP | Yes | NULL | — | Optional verification |
| `password` | VARCHAR(255) | No | — | — | Hashed |
| `status` | VARCHAR(20) | No | `active` | INDEX | active/inactive |
| `remember_token` | VARCHAR(100) | Yes | NULL | — | Laravel auth |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | INDEX optional | SoftDelete |

Migration:

```php
Schema::create('admins', function (Blueprint $table) {
    $table->id();

    $table->string('name', 120);
    $table->string('email', 191)->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');

    $table->string('status', 20)
        ->default('active')
        ->index();

    $table->rememberToken();

    $table->timestamps();
    $table->softDeletes();
});
```

### Screenshot Mapping

Admin list UI may show:

```text
Name
Email
Roles
Status
Actions
```

Database mapping:

```text
Name   → admins.name
Email  → admins.email
Status → admins.status
Roles  → Spatie relationship, NOT an admins column
```

Example:

```php
$admin->roles;
```

---

# 7. `users` Table

**Model:** `App\Models\User`

Purpose:

```text
Authenticated Customer accounts
```

Guest customers do not require a User record.

Physical schema:

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `name` | VARCHAR(120) | No | — | — | |
| `email` | VARCHAR(191) | No | — | UNIQUE | Customer login |
| `email_verified_at` | TIMESTAMP | Yes | NULL | — | |
| `password` | VARCHAR(255) | No | — | — | Hashed |
| `remember_token` | VARCHAR(100) | Yes | NULL | — | |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | SoftDelete |

Migration:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('name', 120);
    $table->string('email', 191)->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');

    $table->rememberToken();

    $table->timestamps();
    $table->softDeletes();
});
```

Do not add a `role` column.

If Customer uses Spatie role:

```text
role name  = customer
guard_name = web
```

---

# 8. Custom `Role` Model + `roles` Table

Spatie already creates the `roles` table.

Do **not** replace the package migration.

Create custom model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SoftDeletes;
}
```

Configure:

```php
// config/permission.php

'models' => [
    'permission' => App\Models\Permission::class,
    'role' => App\Models\Role::class,
],
```

Final physical columns:

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | Spatie |
| `team_id` | BIGINT UNSIGNED | Conditional | NULL | INDEX | Only if Spatie teams enabled |
| `name` | VARCHAR(255) | No | — | Composite UNIQUE | |
| `guard_name` | VARCHAR(255) | No | — | Composite UNIQUE | `admin` / `web` |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | Custom SoftDelete |

When teams are disabled:

```text
UNIQUE(name, guard_name)
```

Example rows:

```text
super_admin | admin
admin       | admin
manager     | admin
agent       | admin
customer    | web
```

### Screenshot Mapping

Roles List may show:

```text
Role Name
Guard Name
Permissions Count
Users Count
Actions
```

Only these are stored directly:

```text
name
guard_name
```

These are calculated:

```text
Permissions Count
Users/Admins Count
```

Use relationship counts such as:

```php
Role::withCount('permissions');
```

Admin/User count may be calculated from the morph relation according to the desired screen.

### Protected Super Admin

Recommended application rule:

```text
role name = super_admin
guard_name = admin
```

is protected from normal Trash/Delete/Rename unless explicitly allowed.

No `is_protected` DB column is required for P0.

---

# 9. Custom `Permission` Model + `permissions` Table

Spatie already creates the `permissions` table.

Use custom model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use SoftDeletes;
}
```

Final columns:

| Column | Type | Null | Default | Key / Index | Notes |
|---|---|---:|---|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK | |
| `name` | VARCHAR(255) | No | — | Composite UNIQUE | Permission code |
| `guard_name` | VARCHAR(255) | No | — | Composite UNIQUE | `admin` / `web` |
| `group_name` | VARCHAR(100) | Yes | NULL | INDEX | UI grouping |
| `created_at` | TIMESTAMP | Yes | NULL | — | |
| `updated_at` | TIMESTAMP | Yes | NULL | — | |
| `deleted_at` | TIMESTAMP | Yes | NULL | — | SoftDelete |

Unique rule remains:

```text
UNIQUE(name, guard_name)
```

Example permission groups:

```text
Admins
Roles
Permissions
Categories
Products
Stock
Coupons
Orders
Payments
Payment Methods
Customers
Reports
Settings
```

Example permission names:

```text
staff.view
staff.create
staff.update
staff.delete
staff.restore

roles.view
roles.manage

permissions.view
permissions.manage

products.view
products.create
products.update
products.delete
products.restore

orders.view
orders.update
orders.assign
orders.cancel
orders.restore

payments.view
payments.verify
payments.reject
```

### Screenshot Mapping

Permissions screen:

```text
ID              → permissions.id
Permission Name → permissions.name
Guard Name      → permissions.guard_name
Group Name      → permissions.group_name
Created At      → permissions.created_at
```

---

# 10. Additive Migration for Role / Permission SoftDeletes

Do not edit Spatie's published migration after installation.

Create a new application migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('group_name', 100)
                ->nullable()
                ->index()
                ->after('guard_name');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropIndex(['group_name']);
            $table->dropColumn('group_name');
            $table->dropSoftDeletes();
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

After Role/Permission mutation:

```php
app(\Spatie\Permission\PermissionRegistrar::class)
    ->forgetCachedPermissions();
```

Important:

Soft-deleting a Role/Permission does not hard-delete pivot rows.

Therefore restoration can preserve prior assignments.

Also note:

```text
UNIQUE(name, guard_name)
```

still sees a soft-deleted record at database level.

If the same role/permission name is needed again:

```text
Restore the trashed record
```

instead of creating a duplicate.

---

# 11. `model_has_roles` — Spatie Pivot

Use the package migration exactly according to installed Spatie version.

Core columns:

```text
role_id
model_type
model_id
```

If teams enabled:

```text
team_id
```

Examples:

```text
role_id    = manager role
model_type = App\Models\Admin
model_id   = 5
```

or:

```text
role_id    = customer role
model_type = App\Models\User
model_id   = 20
```

No application Model is required.

No `deleted_at` required.

---

# 12. `model_has_permissions` — Spatie Pivot

Core columns:

```text
permission_id
model_type
model_id
```

Optional teams column:

```text
team_id
```

No SoftDelete.

No custom Eloquent application model needed.

---

# 13. `role_has_permissions` — Spatie Pivot

Core columns:

```text
permission_id
role_id
```

Composite primary key from Spatie.

No SoftDelete.

No timestamps required unless package version explicitly provides them.

---

# 14. `categories` Table

**Model:** `App\Models\Category`

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `name` | VARCHAR(150) | No | — | |
| `slug` | VARCHAR(200) | No | — | UNIQUE |
| `description` | TEXT | Yes | NULL | |
| `status` | VARCHAR(20) | No | `active` | INDEX |
| `sort_order` | INT UNSIGNED | No | `0` | INDEX |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | |

Migration:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();

    $table->string('name', 150);
    $table->string('slug', 200)->unique();
    $table->text('description')->nullable();

    $table->string('status', 20)
        ->default('active')
        ->index();

    $table->unsignedInteger('sort_order')
        ->default(0)
        ->index();

    $table->timestamps();
    $table->softDeletes();
});
```

Media:

```text
category_image
```

through Spatie Media Library.

---

# 15. `products` Table

**Model:** `App\Models\Product`

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `category_id` | BIGINT UNSIGNED | No | — | FK + INDEX |
| `name` | VARCHAR(180) | No | — | |
| `slug` | VARCHAR(200) | No | — | UNIQUE |
| `sku` | VARCHAR(100) | No | — | UNIQUE |
| `short_description` | VARCHAR(500) | Yes | NULL | |
| `description` | LONGTEXT | Yes | NULL | |
| `regular_price` | DECIMAL(12,2) | No | — | |
| `sale_price` | DECIMAL(12,2) | Yes | NULL | |
| `stock_quantity` | INT UNSIGNED | No | `0` | INDEX |
| `status` | VARCHAR(20) | No | `active` | INDEX |
| `featured` | BOOLEAN | No | `false` | INDEX |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | |

FK:

```text
products.category_id
→ categories.id
ON DELETE RESTRICT
```

Migration:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();

    $table->foreignId('category_id')
        ->constrained('categories')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('name', 180);
    $table->string('slug', 200)->unique();
    $table->string('sku', 100)->unique();

    $table->string('short_description', 500)->nullable();
    $table->longText('description')->nullable();

    $table->decimal('regular_price', 12, 2);
    $table->decimal('sale_price', 12, 2)->nullable();

    $table->unsignedInteger('stock_quantity')
        ->default(0)
        ->index();

    $table->string('status', 20)
        ->default('active')
        ->index();

    $table->boolean('featured')
        ->default(false)
        ->index();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['category_id', 'status']);
    $table->index(['status', 'featured']);
});
```

Media collections:

```text
product_thumbnail
product_gallery
```

---

# 16. `coupons` Table

**Model:** `App\Models\Coupon`

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `code` | VARCHAR(80) | No | — | UNIQUE |
| `discount_type` | VARCHAR(20) | No | — | |
| `discount_value` | DECIMAL(12,2) | No | — | |
| `minimum_order_amount` | DECIMAL(12,2) | No | `0.00` | |
| `start_date` | DATETIME | No | — | INDEX |
| `end_date` | DATETIME | No | — | INDEX |
| `status` | VARCHAR(20) | No | `active` | INDEX |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | |

Values:

```text
discount_type:
fixed
percentage

status:
active
inactive
expired
```

Migration:

```php
Schema::create('coupons', function (Blueprint $table) {
    $table->id();

    $table->string('code', 80)->unique();
    $table->string('discount_type', 20);
    $table->decimal('discount_value', 12, 2);
    $table->decimal('minimum_order_amount', 12, 2)->default(0);

    $table->dateTime('start_date')->index();
    $table->dateTime('end_date')->index();

    $table->string('status', 20)
        ->default('active')
        ->index();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'end_date']);
});
```

---

# 17. `payment_methods` Table

**Model:** `App\Models\PaymentMethod`

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `name` | VARCHAR(100) | No | — | |
| `code` | VARCHAR(50) | No | — | UNIQUE |
| `account_number` | VARCHAR(50) | No | — | |
| `account_type` | VARCHAR(50) | No | — | |
| `instruction` | TEXT | No | — | |
| `status` | VARCHAR(20) | No | `active` | INDEX |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | |

Examples:

```text
bkash
nagad
rocket
```

Migration:

```php
Schema::create('payment_methods', function (Blueprint $table) {
    $table->id();

    $table->string('name', 100);
    $table->string('code', 50)->unique();

    $table->string('account_number', 50);
    $table->string('account_type', 50);
    $table->text('instruction');

    $table->string('status', 20)
        ->default('active')
        ->index();

    $table->timestamps();
    $table->softDeletes();
});
```

---

# 18. `orders` Table

**Model:** `App\Models\Order`

Important ownership decision:

```text
Customer relation
→ users.id

Assigned Agent relation
→ admins.id
```

Guest Order:

```text
user_id = NULL
```

Final columns:

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `order_number` | VARCHAR(40) | No | — | UNIQUE |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `assigned_agent_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `coupon_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `coupon_code` | VARCHAR(80) | Yes | NULL | INDEX optional |
| `buyer_name` | VARCHAR(150) | No | — | |
| `buyer_phone` | VARCHAR(30) | No | — | |
| `buyer_email` | VARCHAR(191) | No | — | |
| `shipping_address` | TEXT | No | — | |
| `city_or_area` | VARCHAR(150) | No | — | |
| `subtotal` | DECIMAL(12,2) | No | — | |
| `discount` | DECIMAL(12,2) | No | `0.00` | |
| `shipping` | DECIMAL(12,2) | No | `0.00` | |
| `grand_total` | DECIMAL(12,2) | No | — | |
| `payment_status` | VARCHAR(20) | No | `unpaid` | INDEX |
| `order_status` | VARCHAR(20) | No | `pending` | INDEX |
| `customer_note` | TEXT | Yes | NULL | |
| `internal_note` | TEXT | Yes | NULL | |
| `created_at` | TIMESTAMP | Yes | NULL | INDEX |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | |

FKs:

```text
orders.user_id
→ users.id
ON DELETE SET NULL

orders.assigned_agent_id
→ admins.id
ON DELETE SET NULL

orders.coupon_id
→ coupons.id
ON DELETE SET NULL
```

Migration:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();

    $table->string('order_number', 40)->unique();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('assigned_agent_id')
        ->nullable()
        ->constrained('admins')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('coupon_id')
        ->nullable()
        ->constrained('coupons')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->string('coupon_code', 80)->nullable();

    $table->string('buyer_name', 150);
    $table->string('buyer_phone', 30);
    $table->string('buyer_email', 191);

    $table->text('shipping_address');
    $table->string('city_or_area', 150);

    $table->decimal('subtotal', 12, 2);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('shipping', 12, 2)->default(0);
    $table->decimal('grand_total', 12, 2);

    $table->string('payment_status', 20)
        ->default('unpaid')
        ->index();

    $table->string('order_status', 20)
        ->default('pending')
        ->index();

    $table->text('customer_note')->nullable();
    $table->text('internal_note')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['user_id', 'created_at']);
    $table->index(['assigned_agent_id', 'order_status']);
    $table->index('coupon_code');
});
```

Order status:

```text
pending
confirmed
processing
shipped
delivered
cancelled
```

Payment status:

```text
unpaid
submitted
verified
rejected
```

---

# 19. `order_items` Table
id` | BIGINT UNSIGNED | No | auto | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK |
| `product_id` | BIGINT UNSIGNED | No | — | FK |
| `product_name` | VARCHAR(180) | No | — | Snapshot |
| `sku` | VARCHAR(100) | No | — | Snapshot |
| `unit_price` | DECIMAL(12,2) | No | — | Snapshot |
| `quantity` | INT UNSIGNED | No | — | |
| `line_total` | DECIMAL(12,2) | No | — | Snapshot |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | SoftDelete |

FKs:

```text
order_items.order_id
→ orders.id
ON DELETE RESTRICT

order_items.product_id
→ products.id
ON DELETE RESTRICT
```

Migration:

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('product_id')
        ->constrained('products')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('product_name', 180);
    $table->string('sku', 100);

    $table->decimal('unit_price', 12, 2);
    $table->unsignedInteger('quantity');
    $table->decimal('line_total', 12, 2);

    $table->timestamps();
    $table->softDeletes();
});
```

Normal Checkout/Order workflow must not delete historical OrderItems.

---

# 20. `order_histories` Table

**Model:** `App\Models\OrderHistory`

Because staff now uses `Admin` model and customers use `User` model, history actor is stored with two nullable FKs.

Final columns:

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK + INDEX |
| `admin_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `from_status` | VARCHAR(20) | Yes | NULL | |
| `to_status` | VARCHAR(20) | Yes | NULL | |
| `note` | TEXT | Yes | NULL | |
| `created_at` | TIMESTAMP | No | CURRENT | INDEX |
| `deleted_at` | TIMESTAMP | Yes | NULL | SoftDelete |

Actor rules:

```text
Admin/Manager/Agent event
→ admin_id populated
→ user_id NULL

Customer event
→ user_id populated
→ admin_id NULL

Guest/System event
→ admin_id NULL
→ user_id NULL
```

Application must not populate both actor columns for the same event.

FKs:

```text
order_histories.order_id
→ orders.id
ON DELETE RESTRICT

order_histories.admin_id
→ admins.id
ON DELETE SET NULL

order_histories.user_id
→ users.id
ON DELETE SET NULL
```

Migration:

```php
Schema::create('order_histories', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('admin_id')
        ->nullable()
        ->constrained('admins')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained('users')
        ->nul
**Model:** `App\Models\OrderItem`

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `lOnDelete()
        ->cascadeOnUpdate();

    $table->string('from_status', 20)->nullable();
    $table->string('to_status', 20)->nullable();

    $table->text('note')->nullable();

    $table->timestamp('created_at')->useCurrent();
    $table->softDeletes();

    $table->index(['order_id', 'created_at']);
});
```

No `updated_at` is required.

History remains append-oriented in normal workflow.

---

# 21. `payment_submissions` Table

**Model:** `App\Models\PaymentSubmission`

Only authorized Admin-side staff verifies/rejects payment.

Therefore verifier FK points to:

```text
admins.id
```

Final columns:

| Column | Type | Null | Default | Key / Index |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | auto | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK + UNIQUE |
| `payment_method_id` | BIGINT UNSIGNED | No | — | FK |
| `transaction_id` | VARCHAR(100) | No | — | INDEX, NOT UNIQUE |
| `amount` | DECIMAL(12,2) | No | — | |
| `status` | VARCHAR(20) | No | `submitted` | INDEX |
| `verified_by_admin_id` | BIGINT UNSIGNED | Yes | NULL | FK + INDEX |
| `verified_at` | TIMESTAMP | Yes | NULL | |
| `rejection_note` | TEXT | Yes | NULL | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |
| `deleted_at` | TIMESTAMP | Yes | NULL | SoftDelete |

P0 cardinality:

```text
Order hasOne PaymentSubmission
```

Physical enforcement:

```text
UNIQUE(order_id)
```

Transaction ID rule:

```text
INDEX(transaction_id)
NOT UNIQUE
```

Migration:

```php
Schema::create('payment_submissions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')
        ->unique()
        ->constrained('orders')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->foreignId('payment_method_id')
        ->constrained('payment_methods')
        ->restrictOnDelete()
        ->cascadeOnUpdate();

    $table->string('transaction_id', 100)->index();
    $table->decimal('amount', 12, 2);

    $table->string('status', 20)
        ->default('submitted')
        ->index();

    $table->foreignId('verified_by_admin_id')
        ->nullable()
        ->constrained('admins')
        ->nullOnDelete()
        ->cascadeOnUpdate();

    $table->timestamp('verified_at')->nullable();
    $table->text('rejection_note')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

Payment submission statuses:

```text
submitted
verified
rejected
```

Critical invariant:

```text
submitted != verified
```

---

# 22. Spatie Media Library `media` Table

Use Spatie's published migration.

Do not redesign package columns.

ShopPilot collections:

```text
category_image
product_thumbnail
product_gallery
admin_avatar     optional
user_avatar      optional
```

No custom:

```text
product_images
category_images
```

tables are required.

---

# 23. Final Relationship Map

```text
ADMIN
├── morphToMany ROLE via model_has_roles
├── morphToMany PERMISSION via model_has_permissions
├── hasMany ORDERS as assigned agent
├── hasMany ORDER_HISTORIES as staff actor
└── hasMany PAYMENT_SUBMISSIONS as verifier

USER
├── morphToMany ROLE via model_has_roles
├── morphToMany PERMISSION via model_has_permissions
├── hasMany ORDERS as authenticated customer
└── hasMany ORDER_HISTORIES as customer actor

ROLE
├── belongsToMany PERMISSION via role_has_permissions
└── morphToMany ADMIN / USER via model_has_roles

PERMISSION
├── belongsToMany ROLE via role_has_permissions
└── morphToMany ADMIN / USER via model_has_permissions

CATEGORY
└── hasMany PRODUCTS

PRODUCT
└── belongsTo CATEGORY

ORDER
├── belongsTo USER nullable
├── belongsTo ADMIN as assigned agent nullable
├── belongsTo COUPON nullable
├── hasMany ORDER_ITEMS
├── hasMany ORDER_HISTORIES
└── hasOne PAYMENT_SUBMISSION

ORDER_ITEM
├── belongsTo ORDER
└── belongsTo PRODUCT

ORDER_HISTORY
├── belongsTo ORDER
├── belongsTo ADMIN nullable
└── belongsTo USER nullable

PAYMENT_SUBMISSION
├── belongsTo ORDER
├── belongsTo PAYMENT_METHOD
└── belongsTo ADMIN as verifier nullable
```

---

# 24. Foreign-Key Matrix

| Child Column | Parent | Nullable | On Delete |
|---|---|---:|---|
| `products.category_id` | `categories.id` | No | RESTRICT |
| `orders.user_id` | `users.id` | Yes | SET NULL |
| `orders.assigned_agent_id` | `admins.id` | Yes | SET NULL |
| `orders.coupon_id` | `coupons.id` | Yes | SET NULL |
| `order_items.order_id` | `orders.id` | No | RESTRICT |
| `order_items.product_id` | `products.id` | No | RESTRICT |
| `order_histories.order_id` | `orders.id` | No | RESTRICT |
| `order_histories.admin_id` | `admins.id` | Yes | SET NULL |
| `order_histories.user_id` | `users.id` | Yes | SET NULL |
| `payment_submissions.order_id` | `orders.id` | No | RESTRICT |
| `payment_submissions.payment_method_id` | `payment_methods.id` | No | RESTRICT |
| `payment_submissions.verified_by_admin_id` | `admins.id` | Yes | SET NULL |

Spatie pivot FK behavior remains package-defined.

---

# 25. Unique Constraints

```text
admins.email
users.email
roles(name, guard_name)              // Spatie
permissions(name, guard_name)        // Spatie
categories.slug
products.slug
products.sku
coupons.code
payment_methods.code
orders.order_number
payment_submissions.order_id
```

Not unique:

```text
payment_submissions.transaction_id
```

until duplicate transaction-ID policy is explicitly approved.

---

# 26. Universal SoftDelete Contract

These models must use SoftDeletes:

```text
Admin
User
Role
Permission
Category
Product
Coupon
PaymentMethod
Order
OrderItem
OrderHistory
PaymentSubmission
```

Normal query:

```php
Model::query();
```

Trash:

```php
Model::onlyTrashed();
```

Include all:

```php
Model::withTrashed();
```

Restore:

```php
$model = Model::onlyTrashed()->findOrFail($id);
$model->restore();
```

Force Delete:

```text
Not enabled by default for P0.
```

Historical models:

```text
OrderItem
OrderHistory
PaymentSubmission
```

use SoftDeletes because this project requires universal Trash/Restore, but normal Order/Payment workflow must never delete them automatically.

---

# 27. Trash Blade View Contract

Each application management module must have a Trash page.

Recommended views:

```text
resources/views/backoffice/admins/trash.blade.php
resources/views/backoffice/customers/trash.blade.php
resources/views/backoffice/roles/trash.blade.php
resources/views/backoffice/permissions/trash.blade.php

resources/views/backoffice/categories/trash.blade.php
resources/views/backoffice/products/trash.blade.php
resources/views/backoffice/coupons/trash.blade.php
resources/views/backoffice/payment-methods/trash.blade.php

resources/views/backoffice/orders/trash.blade.php
resources/views/backoffice/order-items/trash.blade.php
resources/views/backoffice/order-histories/trash.blade.php
resources/views/backoffice/payments/trash.blade.php
```

Every Trash screen should support:

```text
Title / Trash Bin
Search
Filter where useful
Deleted At
Relevant name / identifier
Restore action
Bulk restore where useful
Back to active list
Pagination
Empty state
```

Suggested active list toolbar inspired by the provided Admin UI:

```text
+ Add New
Trash Bin
Bulk Actions
Search / Filter
Reset
```

---

# 28. Trash Routes / Restore Pattern

Recommended pattern:

```text
GET   /admin/<module>/trash
PATCH /admin/<module>/{id}/restore
```

Example:

```php
Route::get('/products/trash', [ProductController::class, 'trash'])
    ->name('products.trash');

Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])
    ->name('products.restore');
```

Controller:

```php
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

    return back()->with('success', 'Product restored successfully.');
}
```

Role/Permission restore must also clear Spatie cache.

Example:

```php
$role->restore();

app(\Spatie\Permission\PermissionRegistrar::class)
    ->forgetCachedPermissions();
```

---

# 29. Admin / Role / Permission Trash Notes

## Admin

```text
SoftDelete Admin
→ admin cannot authenticate normally
→ role pivot records remain
→ restoring Admin preserves role assignments
```

## Role

```text
SoftDelete Role
→ model_has_roles rows remain
→ normal Spatie Role queries exclude trashed Role
→ restoring Role can reactivate old assignments
```

Protect:

```text
super_admin
```

from accidental trash by application rule.

## Permission

```text
SoftDelete Permission
→ role_has_permissions/model_has_permissions rows remain
→ normal Permission queries exclude trashed Permission
→ restore + clear cache
```

Because DB uniqueness remains:

```text
(name, guard_name)
```

restore a trashed Role/Permission instead of creating the same name again.

---

# 30. Checkout / Order Ownership

Guest:

```text
orders.user_id = NULL
```

Authenticated Customer:

```text
orders.user_id = users.id
```

Assigned Agent:

```text
orders.assigned_agent_id = admins.id
```

Buyer snapshot always stored:

```text
buyer_name
buyer_phone
buyer_email
shipping_address
city_or_area
```

Agent assignment does not change buyer ownership.

---

# 31. Order Item Snapshot

Every `order_items` row preserves:

```text
product_name
sku
unit_price
quantity
line_total
```

Historical Order display must not depend only on current Product values.

---

# 32. Payment Verification Ownership

Payment Submission begins:

```text
status = submitted
verified_by_admin_id = NULL
verified_at = NULL
```

Authorized staff verification:

```text
verified_by_admin_id = auth('admin')->id()
status = verified
verified_at = now()
```

Rejection:

```text
verified_by_admin_id = auth('admin')->id()
status = rejected
verified_at = now()
rejection_note = ...
```

Order:

```text
orders.payment_status
```

must be synchronized by `PaymentService`.

Do not automatically change `order_status` on rejection until that business rule is explicitly approved.

---

# 33. Migration Order

Recommended:

```text
01 users
02 admins
03 Spatie create_permission_tables
04 alter roles/permissions: group_name + deleted_at
05 Spatie media table
06 categories
07 products
08 coupons
09 payment_methods
10 orders
11 order_items
12 order_histories
13 payment_submissions
14 optional framework tables
```

Exact migration timestamps may vary.

FK parent tables must exist before child tables.

---

# 34. Seeder Requirements

## Roles

Admin guard:

```text
super_admin
admin
manager
agent
```

Web guard:

```text
customer
```

## Super Admin

Seed one bootstrap Admin.

Example concept:

```text
admins row
+
super_admin role
```

Do not store the role ID directly on `admins`.

## Payment Methods

Seed:

```text
bkash
nagad
rocket
```

with safe development values.

## Permissions

Use grouped permissions.

Example:

```text
Group: Admins
staff.view
staff.create
staff.update
staff.delete
staff.restore

Group: Products
products.view
products.create
products.update
products.delete
products.restore

Group: Orders
orders.view
orders.update
orders.assign
orders.cancel
orders.restore

Group: Payments
payments.view
payments.verify
payments.reject
```

Exact permission catalogue remains synchronized with:

```text
04-USER-ROLES-AND-PERMISSIONS.md
```

---

# 35. Model Skeletons

## Admin

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles, SoftDeletes;

    protected string $guard_name = 'admin';
}
```

## User

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, SoftDeletes;

    protected string $guard_name = 'web';
}
```

## Role

```php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SoftDeletes;
}
```

## Permission

```php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use SoftDeletes;
}
```

All remaining application models also use:

```php
use SoftDeletes;
```

---

# 36. Important Spatie Rules

Do not modify these package pivot structures without need:

```text
model_has_roles
model_has_permissions
role_has_permissions
```

Spatie's morph relation supports both:

```text
App\Models\Admin
App\Models\User
```

Guard must match:

```text
Admin model
→ admin guard

User model
→ web guard
```

After Role/Permission create/update/delete/restore:

```php
app(\Spatie\Permission\PermissionRegistrar::class)
    ->forgetCachedPermissions();
```

---

# 37. Important Indexes

Recommended:

```text
admins.email UNIQUE
admins.status INDEX

users.email UNIQUE

permissions.group_name INDEX

categories.slug UNIQUE
categories.status INDEX

products.slug UNIQUE
products.sku UNIQUE
products(category_id, status)
products(status, featured)

coupons.code UNIQUE
coupons(status, end_date)

payment_methods.code UNIQUE

orders.order_number UNIQUE
orders.user_id INDEX
orders.assigned_agent_id INDEX
orders.order_status INDEX
orders.payment_status INDEX
orders(user_id, created_at)
orders(assigned_agent_id, order_status)

order_items.order_id INDEX
order_items.product_id INDEX

order_histories.order_id INDEX
order_histories.admin_id INDEX
order_histories.user_id INDEX
order_histories(order_id, created_at)

payment_submissions.order_id UNIQUE
payment_submissions.payment_method_id INDEX
payment_submissions.transaction_id INDEX
payment_submissions.status INDEX
payment_submissions.verified_by_admin_id INDEX
```

---

# 38. SoftDelete / Unique Constraint Note

SoftDelete does not remove a row physically.

Therefore these unique values remain reserved while trashed:

```text
admins.email
users.email
roles(name, guard_name)
permissions(name, guard_name)
categories.slug
products.slug
products.sku
coupons.code
payment_methods.code
orders.order_number
```

Recommended behavior:

```text
Restore existing trashed record
```

rather than creating a duplicate with the same unique value.

---

# 39. Critical Schema Tests

Must test:

```text
Admin can receive admin-guard role through Spatie.
User can receive web-guard customer role.
Guard mismatch is rejected.

Admin soft delete hides Admin from normal query.
Admin restore works and preserves role pivot.

Role soft delete hides Role.
Role restore works and Spatie cache is cleared.

Permission soft delete hides Permission.
Permission restore works and cache is cleared.

Guest Order accepts user_id = NULL.
Customer Order links users.id.

Agent assignment references admins.id.
Non-existing Admin agent FK fails.

OrderHistory can store Admin actor.
OrderHistory can store User actor.
Guest/System history can have both actor IDs NULL.

Payment verifier references admins.id.
Payment submission starts submitted.
submitted != verified.

Second PaymentSubmission for same Order fails UNIQUE(order_id).
Duplicate transaction_id is not blocked globally by DB.

Every application model supports onlyTrashed().
Every management module has Trash view.
Restore returns record to normal query.
```

---

# 40. Final Canonical Schema Summary

```text
admins
├── Super Admin
├── Admin
├── Manager
└── Agent
    ↓
Spatie Role / Permission
guard = admin

users
└── Customer
    ↓
Spatie Role / Permission
guard = web

Guest
└── No account required
```

Core relations:

```text
admins
├── assigned Orders
├── OrderHistory staff actor
└── Payment verifier

users
├── Customer Orders
└── OrderHistory customer actor

orders
├── user_id → users.id nullable
├── assigned_agent_id → admins.id nullable
├── coupon_id → coupons.id nullable
├── hasMany order_items
├── hasMany order_histories
└── hasOne payment_submission
```

Spatie:

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

Custom Spatie extensions:

```text
roles.deleted_at
permissions.group_name
permissions.deleted_at
```

Universal project rule:

```text
Admin
User
Role
Permission
Category
Product
Coupon
PaymentMethod
Order
OrderItem
OrderHistory
PaymentSubmission

→ all use SoftDeletes
→ all have Trash/Restore management
```

Package pivots/media/framework tables:

```text
No forced SoftDelete requirement.
```

Critical invariants:

```text
Guest checkout does not require login.
Guest Order user_id may be NULL.
Customer Order user_id references users.id.
Agent references admins.id.
Payment verifier references admins.id.
Buyer snapshots are stored.
Product snapshots are stored.
Cart is Session-based.
Payment submitted != verified.
Order status and Payment status remain separate.
Order hasOne PaymentSubmission.
transaction_id is indexed but not globally unique.
Historical rows are never cascade-destroyed.
```

---

# 41. Documentation Sync Required

Because this schema changes the earlier single-`users` staff/customer design, these documents should be synchronized next:

```text
04-USER-ROLES-AND-PERMISSIONS.md
06-ARCHITECTURE.md
07-DATABASE-ERD.md
09-APPLICATION-FLOW.md
10-FOLDER-STRUCTURE.md
AGENTS.md
README.md
```

Main sync change:

```text
Old:
Admin / Manager / Agent / Customer → users

New:
Admin / Manager / Agent → admins
Customer → users
```

This `08-DATABASE-SCHEMA.md` is now the canonical physical database design for that new model separation.
