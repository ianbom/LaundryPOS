# Implementation Plan — POS Laundry Website

## Scope

Dokumen ini berisi plan lengkap untuk mengerjakan:

```txt
Phase 2 — Core Settings
6. Business Settings CRUD
7. Integration Settings CRUD
8. Outlets CRUD
9. Users CRUD
10. User Outlet Assignment
11. Outlet Switcher

Phase 3 — Master Data
12. Customers CRUD
13. Service Categories CRUD
14. Services CRUD
15. Service Variants CRUD
16. Copy Services Between Outlets
17. WhatsApp Templates CRUD
```

## Project Context

Aplikasi ini adalah **POS Laundry Website** untuk satu bisnis laundry, bukan SaaS.

Model bisnis:

```txt
1 deployed website = 1 laundry business owner
1 laundry business = can have multiple outlets
1 owner/admin = can manage multiple outlets
cashier/staff = can be limited to specific outlet(s)
if another owner wants to use the system, the website/codebase will be cloned and configured separately
```

Karena bukan SaaS, tidak perlu tabel `companies`, `tenants`, `plans`, atau `subscriptions`.

Core model yang digunakan:

```txt
business_settings = global business configuration
outlets = laundry branches
users = system users
user_outlets = user access per outlet
customers = laundry customers
service_categories = laundry service categories
services = laundry services
service_variants = pricing/duration variants
whatsapp_templates = WhatsApp message templates
```

---

# General Development Rules

## Tech Stack Assumption

Gunakan stack berikut:

```txt
Backend: Laravel
Frontend: Inertia.js + React + TypeScript
Styling: Tailwind CSS + shadcn/ui
Database: MySQL / MariaDB
Authentication: Laravel auth / Breeze-style auth
```

## UI Standards

Semua halaman CRUD harus memiliki pola konsisten:

```txt
- Page header
- Search input
- Filter, jika dibutuhkan
- Data table
- Pagination
- Create button
- Edit action
- Detail action, jika diperlukan
- Delete/deactivate action
- Confirmation dialog for destructive actions
- Toast notification after success/failure
- Empty state
- Loading state
- Validation error message
```

## Backend Standards

Gunakan pola berikut:

```txt
- Controller for HTTP request handling
- FormRequest for validation
- Service class for business logic when needed
- Policy or middleware for permission/access control
- Resource/Transformer if response needs formatting
- Soft delete for important master data
- Activity log for critical updates where applicable
```

## Outlet Scoping Rule

Semua data operasional harus mengikuti outlet yang sedang aktif atau outlet yang bisa diakses user.

```txt
Owner:
- Can access all outlets.

Admin:
- Can access outlets assigned through user_outlets.

Cashier/staff:
- Can only access assigned outlet(s).
```

Gunakan helper:

```php
getAccessibleOutletIds(User $user): array
canAccessOutlet(User $user, int $outletId): bool
getActiveOutletId(): ?int
```

## Permission Rule

Permission utama berasal dari `user_outlets`:

```txt
can_manage_orders
can_manage_payments
can_manage_services
can_manage_reports
can_manage_users
can_manage_settings
```

Role umum:

```txt
owner  = full access
admin  = access based on permissions
cashier = mostly order/payment related
staff  = mostly order status related
```

---

# Phase 2 — Core Settings

---

# 6. Business Settings CRUD

## Goal

Membuat halaman untuk mengatur identitas utama bisnis laundry.

Karena satu website hanya digunakan oleh satu bisnis laundry, tabel `business_settings` hanya perlu memiliki **satu record utama**.

## Route

```txt
GET    /settings/business
PUT    /settings/business
```

## Page

```txt
resources/js/Pages/Settings/Business/Edit.tsx
```

## Backend Files

```txt
app/Http/Controllers/Settings/BusinessSettingController.php
app/Http/Requests/Settings/UpdateBusinessSettingRequest.php
app/Models/BusinessSetting.php
```

## Database Table

```txt
business_settings
```

## Fields

```txt
business_name
business_slug
logo_path
favicon_path
owner_name
owner_phone
owner_email
default_phone
default_whatsapp_number
default_email
default_address
default_google_maps_url
timezone
currency
receipt_footer_text
terms_and_conditions
qris_expiry_minutes
created_at
updated_at
```

## UI Sections

### 1. Business Identity

Fields:

```txt
business_name
business_slug
logo_path
favicon_path
```

### 2. Owner Information

Fields:

```txt
owner_name
owner_phone
owner_email
```

### 3. Default Contact Information

Fields:

```txt
default_phone
default_whatsapp_number
default_email
default_address
default_google_maps_url
```

### 4. Localization

Fields:

```txt
timezone
currency
```

Default:

```txt
timezone = Asia/Jakarta
currency = IDR
```

### 5. Receipt & Terms

Fields:

```txt
receipt_footer_text
terms_and_conditions
```

### 6. QRIS Default Setting

Field:

```txt
qris_expiry_minutes
```

Default:

```txt
30 minutes
```

## Validation Rules

```txt
business_name: required|string|max:150
business_slug: nullable|string|max:150
logo_path: nullable|image|max:2048
favicon_path: nullable|image|max:1024
owner_name: nullable|string|max:150
owner_phone: nullable|string|max:30
owner_email: nullable|email|max:150
default_phone: nullable|string|max:30
default_whatsapp_number: nullable|string|max:30
default_email: nullable|email|max:150
default_address: nullable|string
default_google_maps_url: nullable|url
timezone: required|string|max:100
currency: required|string|max:10
receipt_footer_text: nullable|string
terms_and_conditions: nullable|string
qris_expiry_minutes: required|integer|min:1|max:1440
```

## Behavior

```txt
1. When the page is opened, check if business_settings record exists.
2. If not exists, create default record automatically.
3. Show current data in edit form.
4. When saved, update the existing record.
5. If logo/favicon uploaded, store file and update path.
6. Show success toast after update.
```

## Permission

Only users with one of these conditions can access:

```txt
- global_role = owner
- user has can_manage_settings = true in at least one active outlet
```

Recommended for MVP:

```txt
Only owner can access this page.
```

## Acceptance Criteria

```txt
- Owner can view business settings page.
- Owner can update business profile.
- System auto-creates default business_settings row if missing.
- Logo and favicon can be uploaded.
- Timezone defaults to Asia/Jakarta.
- Currency defaults to IDR.
- QRIS expiry setting can be changed.
```

---

# 7. Integration Settings CRUD

## Goal

Membuat halaman pengaturan integrasi pihak ketiga:

```txt
- Midtrans for QRIS dynamic payment
- WhatsApp provider for notification
```

Data integration tetap disimpan di `business_settings` karena website ini bukan SaaS.

## Route

```txt
GET    /settings/integrations
PUT    /settings/integrations
POST   /settings/integrations/test-whatsapp
POST   /settings/integrations/test-midtrans
```

## Page

```txt
resources/js/Pages/Settings/Integrations/Edit.tsx
```

## Backend Files

```txt
app/Http/Controllers/Settings/IntegrationSettingController.php
app/Http/Requests/Settings/UpdateIntegrationSettingRequest.php
app/Services/Payment/MidtransConfigTester.php
app/Services/WhatsApp/WhatsAppConfigTester.php
```

## Database Table

```txt
business_settings
```

## Fields

```txt
whatsapp_provider
whatsapp_api_key
whatsapp_sender_number
midtrans_server_key
midtrans_client_key
midtrans_is_production
qris_expiry_minutes
```

## UI Sections

### 1. Midtrans Settings

Fields:

```txt
midtrans_server_key
midtrans_client_key
midtrans_is_production
qris_expiry_minutes
```

Actions:

```txt
- Save settings
- Test Midtrans configuration
```

### 2. WhatsApp Settings

Fields:

```txt
whatsapp_provider
whatsapp_api_key
whatsapp_sender_number
```

Actions:

```txt
- Save settings
- Test send WhatsApp message
```

## WhatsApp Provider Options

For MVP, allow these options:

```txt
fonnte
wablas
whatsapp_cloud_api
custom
```

## Validation Rules

```txt
whatsapp_provider: nullable|string|max:50
whatsapp_api_key: nullable|string
whatsapp_sender_number: nullable|string|max:30
midtrans_server_key: nullable|string
midtrans_client_key: nullable|string
midtrans_is_production: boolean
qris_expiry_minutes: required|integer|min:1|max:1440
```

## Security Rules

```txt
- API keys must not be displayed fully after saved.
- Mask saved API keys in frontend.
- If possible, encrypt sensitive fields using Laravel encrypted cast.
- Do not log full API keys in activity_logs.
```

Example masked value:

```txt
SB-Mid-server-abc********xyz
```

## Behavior

```txt
1. User opens integration settings page.
2. System shows saved provider and masked key.
3. If user inputs new key, replace old key.
4. If key field is empty during update, keep old key.
5. User can toggle sandbox/production Midtrans mode.
6. User can send test WhatsApp message.
7. User can test Midtrans configuration.
```

## Permission

Recommended for MVP:

```txt
Only owner can access this page.
```

## Acceptance Criteria

```txt
- Owner can configure Midtrans sandbox/production.
- Owner can configure WhatsApp provider.
- API keys are masked after saving.
- Empty key input does not erase existing key unless user explicitly clears it.
- Test WhatsApp action gives clear success/failure response.
- Test Midtrans action gives clear success/failure response.
```

---

# 8. Outlets CRUD

## Goal

Membuat fitur untuk mengelola outlet/cabang laundry.

Satu bisnis laundry bisa memiliki banyak outlet.

## Routes

```txt
GET     /outlets
GET     /outlets/create
POST    /outlets
GET     /outlets/{outlet}
GET     /outlets/{outlet}/edit
PUT     /outlets/{outlet}
DELETE  /outlets/{outlet}
PATCH   /outlets/{outlet}/toggle-active
PATCH   /outlets/{outlet}/set-main
```

## Pages

```txt
resources/js/Pages/Outlets/Index.tsx
resources/js/Pages/Outlets/Create.tsx
resources/js/Pages/Outlets/Edit.tsx
resources/js/Pages/Outlets/Show.tsx
```

## Backend Files

```txt
app/Http/Controllers/OutletController.php
app/Http/Requests/StoreOutletRequest.php
app/Http/Requests/UpdateOutletRequest.php
app/Models/Outlet.php
```

## Database Table

```txt
outlets
```

## Fields

```txt
name
code
slug
phone
whatsapp_number
email
address
google_maps_url
is_main
is_active
created_at
updated_at
deleted_at
```

## List Page Features

```txt
- Search by name, code, phone, address.
- Filter active/inactive.
- Show main outlet badge.
- Pagination.
- Create outlet button.
- Edit action.
- Detail action.
- Toggle active action.
- Set main outlet action.
- Delete action with confirmation.
```

## Table Columns

```txt
Outlet Name
Code
Phone
WhatsApp
Address
Main Outlet
Status
Actions
```

## Create/Edit Form Fields

```txt
name
code
slug
phone
whatsapp_number
email
address
google_maps_url
is_main
is_active
```

## Validation Rules

```txt
name: required|string|max:150
code: nullable|string|max:50|unique:outlets,code
slug: required|string|max:150|unique:outlets,slug
phone: nullable|string|max:30
whatsapp_number: nullable|string|max:30
email: nullable|email|max:150
address: nullable|string
google_maps_url: nullable|url
is_main: boolean
is_active: boolean
```

For update, unique rule must ignore current outlet ID.

## Business Rules

```txt
- At least one outlet must exist.
- At least one active outlet should exist.
- Only one outlet can be main outlet.
- If an outlet is set as main, all other outlets must be set is_main = false.
- Outlet with existing orders should not be hard deleted.
- Soft delete is recommended.
- Inactive outlet should not appear in new order form.
```

## Permission

```txt
owner:
- Full access.

admin with can_manage_settings:
- Can manage outlets if allowed.

cashier/staff:
- Cannot create/edit/delete outlets.
```

Recommended for MVP:

```txt
Only owner can manage outlets.
```

## Acceptance Criteria

```txt
- Owner can create multiple outlets.
- Owner can set one outlet as main outlet.
- Owner can deactivate outlet.
- Outlet with order history is not permanently deleted.
- Inactive outlet is not selectable for new POS transaction.
```

---

# 9. Users CRUD

## Goal

Membuat fitur untuk mengelola user aplikasi.

User dapat berupa:

```txt
owner
admin
cashier
staff
```

Akses outlet user tidak disimpan langsung di tabel `users`, tetapi diatur melalui `user_outlets`.

## Routes

```txt
GET     /users
GET     /users/create
POST    /users
GET     /users/{user}
GET     /users/{user}/edit
PUT     /users/{user}
DELETE  /users/{user}
PATCH   /users/{user}/toggle-active
PATCH   /users/{user}/reset-password
```

## Pages

```txt
resources/js/Pages/Users/Index.tsx
resources/js/Pages/Users/Create.tsx
resources/js/Pages/Users/Edit.tsx
resources/js/Pages/Users/Show.tsx
```

## Backend Files

```txt
app/Http/Controllers/UserController.php
app/Http/Requests/StoreUserRequest.php
app/Http/Requests/UpdateUserRequest.php
app/Http/Requests/ResetUserPasswordRequest.php
app/Models/User.php
```

## Database Table

```txt
users
```

## Fields

```txt
name
email
phone
password
global_role
is_active
last_login_at
created_at
updated_at
deleted_at
```

## List Page Features

```txt
- Search by name/email/phone.
- Filter by role.
- Filter by active/inactive.
- Show assigned outlet count.
- Pagination.
- Create user button.
- Edit action.
- Detail action.
- Assign outlets action.
- Toggle active action.
- Reset password action.
```

## Table Columns

```txt
Name
Email
Phone
Global Role
Assigned Outlets
Status
Last Login
Actions
```

## Create Form Fields

```txt
name
email
phone
password
password_confirmation
global_role
is_active
```

## Edit Form Fields

```txt
name
email
phone
global_role
is_active
```

Password should be handled separately through reset password action.

## Validation Rules

### Store

```txt
name: required|string|max:150
email: required|email|max:150|unique:users,email
phone: nullable|string|max:30
password: required|string|min:8|confirmed
global_role: required|in:owner,admin,staff
is_active: boolean
```

### Update

```txt
name: required|string|max:150
email: required|email|max:150|unique:users,email,{id}
phone: nullable|string|max:30
global_role: required|in:owner,admin,staff
is_active: boolean
```

### Reset Password

```txt
password: required|string|min:8|confirmed
```

## Business Rules

```txt
- Email must be unique.
- Owner user should not accidentally deactivate themselves.
- System should prevent deleting the last active owner.
- Soft delete is recommended.
- Password should be hashed.
```

## Permission

```txt
owner:
- Full access to user management.

admin with can_manage_users:
- Can manage users except owner users.

cashier/staff:
- No access to user management.
```

Recommended for MVP:

```txt
Only owner can manage users.
```

## Acceptance Criteria

```txt
- Owner can create admin/cashier/staff users.
- Owner can deactivate user.
- Owner can reset user password.
- Last active owner cannot be deleted or deactivated.
- Password is securely hashed.
```

---

# 10. User Outlet Assignment

## Goal

Membuat fitur untuk mengatur user bisa mengakses outlet mana dan permission apa yang dimiliki pada outlet tersebut.

## Routes

```txt
GET    /users/{user}/outlets
PUT    /users/{user}/outlets
```

Alternative route:

```txt
GET    /users/{user}/assign-outlets
PUT    /users/{user}/assign-outlets
```

## Page

```txt
resources/js/Pages/Users/AssignOutlets.tsx
```

## Backend Files

```txt
app/Http/Controllers/UserOutletController.php
app/Http/Requests/UpdateUserOutletAssignmentRequest.php
app/Models/UserOutlet.php
```

## Database Table

```txt
user_outlets
```

## Fields

```txt
user_id
outlet_id
role
can_manage_orders
can_manage_payments
can_manage_services
can_manage_reports
can_manage_users
can_manage_settings
is_primary
is_active
created_at
updated_at
```

## UI Design

Show a list of outlets with checkbox.

For each selected outlet, show:

```txt
- outlet name
- role dropdown
- is_primary checkbox/radio
- is_active toggle
- permissions checklist
```

## Role Options

```txt
owner
admin
cashier
staff
```

## Default Permission Presets

### Owner

```txt
can_manage_orders = true
can_manage_payments = true
can_manage_services = true
can_manage_reports = true
can_manage_users = true
can_manage_settings = true
```

### Admin

```txt
can_manage_orders = true
can_manage_payments = true
can_manage_services = true
can_manage_reports = true
can_manage_users = false
can_manage_settings = false
```

### Cashier

```txt
can_manage_orders = true
can_manage_payments = true
can_manage_services = false
can_manage_reports = false
can_manage_users = false
can_manage_settings = false
```

### Staff

```txt
can_manage_orders = true
can_manage_payments = false
can_manage_services = false
can_manage_reports = false
can_manage_users = false
can_manage_settings = false
```

## Validation Rules

```txt
outlets: required|array
outlets.*.outlet_id: required|exists:outlets,id
outlets.*.role: required|in:owner,admin,cashier,staff
outlets.*.can_manage_orders: boolean
outlets.*.can_manage_payments: boolean
outlets.*.can_manage_services: boolean
outlets.*.can_manage_reports: boolean
outlets.*.can_manage_users: boolean
outlets.*.can_manage_settings: boolean
outlets.*.is_primary: boolean
outlets.*.is_active: boolean
```

## Business Rules

```txt
- One user can access multiple outlets.
- One user should have only one primary outlet.
- A user must have at least one active outlet assignment unless global_role is owner.
- If role preset is selected, default permissions should be applied but still editable by owner.
- Owner global_role may automatically access all outlets, but storing user_outlets rows is still useful for UI consistency.
```

## Behavior

```txt
1. Owner opens user outlet assignment page.
2. System displays all active outlets.
3. Owner selects outlets for the user.
4. Owner chooses role per outlet.
5. System applies default permission preset.
6. Owner can modify permissions manually.
7. On save, sync user_outlets table.
```

## Acceptance Criteria

```txt
- Owner can assign a cashier to one outlet only.
- Owner can assign admin to multiple outlets.
- User cannot access unassigned outlet.
- Only one primary outlet is allowed per user.
- Permission flags are saved correctly.
```

---

# 11. Outlet Switcher

## Goal

Membuat komponen untuk memilih outlet aktif di dashboard/POS.

Outlet aktif digunakan untuk:

```txt
- Customer list
- Service category list
- Service list
- Service variant list
- POS order creation
- Order list
- Dashboard metrics
- Reports
```

## Component

```txt
resources/js/Components/OutletSwitcher.tsx
```

## Backend Support

```txt
GET  /current-outlet
POST /current-outlet
```

Recommended controller:

```txt
app/Http/Controllers/CurrentOutletController.php
```

## Data Source

Outlet options should come from:

```txt
user_outlets where user_id = current user and is_active = true
```

For owner:

```txt
Show all active outlets.
```

## UI Behavior

```txt
- Show dropdown in dashboard header/sidebar.
- If user has one outlet only, auto-select it.
- If user has multiple outlets, allow switching.
- Store selected outlet in session.
- Optionally persist selected outlet in localStorage for frontend display.
```

## Backend Session Key

```txt
active_outlet_id
```

## Validation

```txt
outlet_id: required|exists:outlets,id
```

Then check:

```txt
current user can access outlet_id
```

## Business Rules

```txt
- User cannot select outlet they cannot access.
- Inactive outlets cannot be selected.
- All outlet-scoped pages must use active_outlet_id.
- If active_outlet_id becomes invalid, reset it to user's primary outlet or first accessible outlet.
```

## Acceptance Criteria

```txt
- User can switch between assigned outlets.
- Cashier with one outlet does not need to switch.
- Data tables follow selected outlet.
- POS order creation uses selected outlet automatically.
- User cannot force access another outlet through URL/request manipulation.
```

---

# Phase 3 — Master Data

---

# 12. Customers CRUD

## Goal

Membuat fitur untuk mengelola pelanggan laundry.

Customer digunakan saat membuat order laundry dan untuk mengirim WhatsApp receipt/tracking.

## Routes

```txt
GET     /customers
GET     /customers/create
POST    /customers
GET     /customers/{customer}
GET     /customers/{customer}/edit
PUT     /customers/{customer}
DELETE  /customers/{customer}
GET     /customers/search
```

## Pages

```txt
resources/js/Pages/Customers/Index.tsx
resources/js/Pages/Customers/Create.tsx
resources/js/Pages/Customers/Edit.tsx
resources/js/Pages/Customers/Show.tsx
```

## Backend Files

```txt
app/Http/Controllers/CustomerController.php
app/Http/Requests/StoreCustomerRequest.php
app/Http/Requests/UpdateCustomerRequest.php
app/Models/Customer.php
```

## Database Table

```txt
customers
```

## Fields

```txt
outlet_id
name
phone
whatsapp_number
address
notes
total_orders
total_spent
created_at
updated_at
deleted_at
```

## List Page Features

```txt
- List customers by active outlet.
- Search by name/phone/WhatsApp.
- Filter by outlet for owner/admin with multi-outlet access.
- Pagination.
- Create customer button.
- Edit action.
- Detail action.
- Delete action with confirmation.
```

## Table Columns

```txt
Name
Phone
WhatsApp
Outlet
Total Orders
Total Spent
Created At
Actions
```

## Create/Edit Form Fields

```txt
name
phone
whatsapp_number
address
notes
```

`outlet_id` should be auto-filled from active outlet.

## Validation Rules

```txt
outlet_id: required|exists:outlets,id
name: required|string|max:150
phone: required|string|max:30
whatsapp_number: nullable|string|max:30
address: nullable|string
notes: nullable|string
```

## Business Rules

```txt
- Customer belongs to active outlet.
- If whatsapp_number is empty, use phone as WhatsApp fallback.
- Same phone number can exist in different outlets if needed.
- Within same outlet, duplicate phone should be warned or prevented.
- Customer with orders should not be hard deleted.
```

Recommended unique behavior for MVP:

```txt
Prevent duplicate phone per outlet.
```

## Quick Create Support

This will be used later in POS page.

Endpoint:

```txt
POST /customers/quick-create
```

Required fields:

```txt
name
phone
whatsapp_number optional
address optional
```

## Customer Detail Page

Show:

```txt
- Customer profile
- Total orders
- Total spent
- Last order date
- Order history table
```

## Permission

```txt
owner/admin/cashier:
- Can create and edit customers if assigned to outlet.

staff:
- Read-only or limited depending permission.
```

## Acceptance Criteria

```txt
- User can create customer for active outlet.
- User can search customer by name or phone.
- Customer detail shows order history.
- POS page can reuse customer search and quick create.
- User cannot access customer from unauthorized outlet.
```

---

# 13. Service Categories CRUD

## Goal

Membuat fitur untuk mengelola kategori layanan laundry per outlet.

Examples:

```txt
Laundry Kiloan
Laundry Satuan
Dry Clean
Bedcover
Sepatu
Karpet
Boneka
Gorden
Extra Service
```

## Routes

```txt
GET     /service-categories
GET     /service-categories/create
POST    /service-categories
GET     /service-categories/{category}/edit
PUT     /service-categories/{category}
DELETE  /service-categories/{category}
PATCH   /service-categories/{category}/toggle-active
PATCH   /service-categories/reorder
```

## Pages

```txt
resources/js/Pages/ServiceCategories/Index.tsx
resources/js/Pages/ServiceCategories/Create.tsx
resources/js/Pages/ServiceCategories/Edit.tsx
```

## Backend Files

```txt
app/Http/Controllers/ServiceCategoryController.php
app/Http/Requests/StoreServiceCategoryRequest.php
app/Http/Requests/UpdateServiceCategoryRequest.php
app/Models/ServiceCategory.php
```

## Database Table

```txt
service_categories
```

## Fields

```txt
outlet_id
name
description
sort_order
is_active
created_at
updated_at
deleted_at
```

## List Page Features

```txt
- List categories by active outlet.
- Search by name.
- Filter active/inactive.
- Sort by sort_order.
- Create category button.
- Edit action.
- Toggle active action.
- Delete action with confirmation.
```

## Table Columns

```txt
Name
Description
Services Count
Sort Order
Status
Actions
```

## Create/Edit Form Fields

```txt
name
description
sort_order
is_active
```

`outlet_id` should be auto-filled from active outlet.

## Validation Rules

```txt
outlet_id: required|exists:outlets,id
name: required|string|max:150
description: nullable|string
sort_order: nullable|integer|min:0
is_active: boolean
```

## Business Rules

```txt
- Category belongs to active outlet.
- Category inactive should not appear in POS order form.
- Category with active services should not be hard deleted.
- Soft delete is recommended.
- Category name should be unique per outlet.
```

## Permission

```txt
User must have can_manage_services = true for selected outlet.
```

For MVP:

```txt
owner/admin can manage categories.
cashier/staff cannot manage categories.
```

## Acceptance Criteria

```txt
- Admin can create category for active outlet.
- Category list is scoped by outlet.
- Inactive category is hidden from POS service selection.
- User cannot manage category from unauthorized outlet.
```

---

# 14. Services CRUD

## Goal

Membuat fitur untuk mengelola layanan laundry utama per outlet.

A service belongs to a service category and has a pricing type.

Examples:

```txt
Cuci Kering Setrika
Cuci Kering
Setrika Saja
Boneka Sedang
Bedcover Queen
Karpet
```

## Routes

```txt
GET     /services
GET     /services/create
POST    /services
GET     /services/{service}
GET     /services/{service}/edit
PUT     /services/{service}
DELETE  /services/{service}
PATCH   /services/{service}/toggle-active
```

## Pages

```txt
resources/js/Pages/Services/Index.tsx
resources/js/Pages/Services/Create.tsx
resources/js/Pages/Services/Edit.tsx
resources/js/Pages/Services/Show.tsx
```

## Backend Files

```txt
app/Http/Controllers/ServiceController.php
app/Http/Requests/StoreServiceRequest.php
app/Http/Requests/UpdateServiceRequest.php
app/Models/Service.php
```

## Database Table

```txt
services
```

## Fields

```txt
outlet_id
service_category_id
name
description
pricing_type
is_active
sort_order
created_at
updated_at
deleted_at
```

## Pricing Type Options

```txt
per_kg
per_item
per_set
per_m2
fixed
custom
```

## List Page Features

```txt
- List services by active outlet.
- Search by service name.
- Filter by category.
- Filter by pricing type.
- Filter active/inactive.
- Show variants count.
- Pagination.
- Create service button.
- Edit action.
- Detail action.
- Manage variants action.
- Toggle active action.
- Delete action with confirmation.
```

## Table Columns

```txt
Service Name
Category
Pricing Type
Variants Count
Sort Order
Status
Actions
```

## Create/Edit Form Fields

```txt
service_category_id
name
description
pricing_type
sort_order
is_active
```

`outlet_id` should be auto-filled from active outlet.

## Validation Rules

```txt
outlet_id: required|exists:outlets,id
service_category_id: required|exists:service_categories,id
name: required|string|max:150
description: nullable|string
pricing_type: required|in:per_kg,per_item,per_set,per_m2,fixed,custom
sort_order: nullable|integer|min:0
is_active: boolean
```

Extra validation:

```txt
service_category_id must belong to the same outlet as active outlet.
```

## Business Rules

```txt
- Service belongs to active outlet.
- Service category must belong to the same outlet.
- Service inactive should not appear in POS order form.
- Service with historical order_items should not be hard deleted.
- Soft delete is recommended.
- Service name should be unique within category and outlet.
```

## Service Detail Page

Show:

```txt
- Service info
- Category
- Pricing type
- Variants list
- Active/inactive status
```

## Permission

```txt
User must have can_manage_services = true for selected outlet.
```

## Acceptance Criteria

```txt
- Admin can create service under selected category.
- Service list is scoped by outlet.
- Service cannot use category from another outlet.
- Inactive service is hidden from POS service selection.
- Service detail shows its variants.
```

---

# 15. Service Variants CRUD

## Goal

Membuat fitur untuk mengelola variasi harga dan durasi dari setiap service.

Examples for `Cuci Kering Setrika`:

```txt
4x24 Jam — Rp7.000/kg — min 3 kg
3x24 Jam — Rp8.000/kg — min 3 kg
2x24 Jam — Rp10.000/kg — min 3 kg
Express <24 Jam — Rp18.000/kg — min 3 kg
```

## Routes

```txt
GET     /services/{service}/variants
GET     /services/{service}/variants/create
POST    /services/{service}/variants
GET     /services/{service}/variants/{variant}/edit
PUT     /services/{service}/variants/{variant}
DELETE  /services/{service}/variants/{variant}
PATCH   /services/{service}/variants/{variant}/toggle-active
PATCH   /services/{service}/variants/reorder
```

Alternative simplified route:

```txt
Manage variants inside /services/{service} detail page.
```

## Pages

```txt
resources/js/Pages/ServiceVariants/Index.tsx
resources/js/Pages/ServiceVariants/Create.tsx
resources/js/Pages/ServiceVariants/Edit.tsx
```

Or embedded component:

```txt
resources/js/Pages/Services/Partials/VariantTable.tsx
resources/js/Pages/Services/Partials/VariantFormModal.tsx
```

## Backend Files

```txt
app/Http/Controllers/ServiceVariantController.php
app/Http/Requests/StoreServiceVariantRequest.php
app/Http/Requests/UpdateServiceVariantRequest.php
app/Models/ServiceVariant.php
```

## Database Table

```txt
service_variants
```

## Fields

```txt
outlet_id
service_id
name
description
price
unit
min_quantity
estimated_duration_hours
is_express
is_active
sort_order
created_at
updated_at
deleted_at
```

## Unit Options

Suggested options:

```txt
kg
item
set
m2
unit
custom
```

## List Page Features

```txt
- List variants for selected service.
- Search by variant name.
- Filter active/inactive.
- Show price formatted as Rupiah.
- Show unit.
- Show minimum quantity.
- Show estimated duration.
- Show express badge.
- Create variant button.
- Edit action.
- Toggle active action.
- Delete action with confirmation.
```

## Table Columns

```txt
Variant Name
Price
Unit
Minimum Quantity
Estimated Duration
Express
Status
Actions
```

## Create/Edit Form Fields

```txt
name
description
price
unit
min_quantity
estimated_duration_hours
is_express
sort_order
is_active
```

`outlet_id` and `service_id` should be auto-filled.

## Validation Rules

```txt
outlet_id: required|exists:outlets,id
service_id: required|exists:services,id
name: required|string|max:150
description: nullable|string
price: required|numeric|min:0
unit: required|string|max:50
min_quantity: required|numeric|min:0.01
estimated_duration_hours: nullable|integer|min:1
is_express: boolean
sort_order: nullable|integer|min:0
is_active: boolean
```

Extra validation:

```txt
service_id must belong to selected active outlet.
```

## Business Rules

```txt
- Variant belongs to a service.
- Variant outlet_id must match service outlet_id.
- Variant inactive should not appear in POS order form.
- Variant with historical order_items should not be hard deleted.
- Price changes must not affect old invoices because order_items store snapshot data.
- min_quantity is used to calculate charged_quantity in POS order.
```

## Calculation Rule Preview

Show helper text in UI:

```txt
If customer quantity is lower than minimum quantity, system will charge using minimum quantity.
```

Example:

```txt
Quantity: 2 kg
Minimum: 3 kg
Price: Rp7.000/kg
Charged Quantity: 3 kg
Subtotal: Rp21.000
```

## Permission

```txt
User must have can_manage_services = true for selected outlet.
```

## Acceptance Criteria

```txt
- Admin can add multiple variants to one service.
- Variant price is formatted in Rupiah.
- Variant min_quantity is saved correctly.
- Variant inactive is hidden from POS order selection.
- User cannot create variant for service from unauthorized outlet.
```

---

# 16. Copy Services Between Outlets

## Goal

Membuat fitur untuk menyalin master data layanan dari satu outlet ke outlet lain.

Fitur ini penting karena bisnis multi-outlet sering memiliki daftar layanan dan harga yang sama.

## Route

```txt
GET  /services/copy
POST /services/copy
```

## Page

```txt
resources/js/Pages/Services/Copy.tsx
```

## Backend Files

```txt
app/Http/Controllers/ServiceCopyController.php
app/Http/Requests/CopyServicesRequest.php
app/Services/ServiceCatalogCopyService.php
```

## Source Tables

```txt
service_categories
services
service_variants
```

## UI Fields

```txt
source_outlet_id
target_outlet_id
copy_mode
include_inactive
```

## Copy Mode Options

```txt
skip_existing
duplicate_all
overwrite_existing
```

## UI Behavior

```txt
1. User selects source outlet.
2. User selects target outlet.
3. User chooses copy mode.
4. User optionally chooses include inactive data.
5. System shows copy preview:
   - total categories
   - total services
   - total variants
6. User confirms copy.
7. System copies service catalog.
8. System shows copy result summary.
```

## Validation Rules

```txt
source_outlet_id: required|exists:outlets,id
target_outlet_id: required|exists:outlets,id|different:source_outlet_id
copy_mode: required|in:skip_existing,duplicate_all,overwrite_existing
include_inactive: boolean
```

Extra validation:

```txt
current user must be able to access both source and target outlets.
current user must have can_manage_services permission for target outlet.
```

## Copy Logic

### A. skip_existing

```txt
- If category with same name exists in target outlet, use existing category.
- If service with same name exists in same target category, skip service.
- If variant with same name exists in same target service, skip variant.
- Copy only missing data.
```

### B. duplicate_all

```txt
- Always create new category/service/variant records.
- If name already exists, append suffix like "Copy" or outlet-specific suffix.
```

Example:

```txt
Cuci Kering Setrika Copy
```

### C. overwrite_existing

```txt
- If matching category/service/variant exists by name, update target data with source values.
- If not exists, create new data.
```

## Data Mapping

### Copy service_categories

Copy:

```txt
name
description
sort_order
is_active
```

Set:

```txt
outlet_id = target_outlet_id
```

### Copy services

Copy:

```txt
name
description
pricing_type
is_active
sort_order
```

Set:

```txt
outlet_id = target_outlet_id
service_category_id = mapped target category id
```

### Copy service_variants

Copy:

```txt
name
description
price
unit
min_quantity
estimated_duration_hours
is_express
is_active
sort_order
```

Set:

```txt
outlet_id = target_outlet_id
service_id = mapped target service id
```

## Transaction Rule

Run copy process inside database transaction.

```txt
If one copy step fails, rollback all changes.
```

## Activity Log

Log:

```txt
source_outlet_id
target_outlet_id
copy_mode
total_categories_created
total_services_created
total_variants_created
```

## Acceptance Criteria

```txt
- Owner/admin can copy services from one outlet to another.
- Source and target outlet cannot be the same.
- Copy process preserves category > service > variant hierarchy.
- Copy process does not break existing order history.
- Copy result summary is shown after success.
- User cannot copy from/to unauthorized outlet.
```

---

# 17. WhatsApp Templates CRUD

## Goal

Membuat fitur untuk mengelola template pesan WhatsApp yang digunakan sistem.

Templates digunakan untuk:

```txt
- payment receipt
- order created
- order processing
- order ready
- order completed
- payment reminder
- custom message
```

## Routes

```txt
GET     /settings/whatsapp-templates
GET     /settings/whatsapp-templates/create
POST    /settings/whatsapp-templates
GET     /settings/whatsapp-templates/{template}/edit
PUT     /settings/whatsapp-templates/{template}
DELETE  /settings/whatsapp-templates/{template}
PATCH   /settings/whatsapp-templates/{template}/toggle-active
POST    /settings/whatsapp-templates/{template}/preview
```

## Pages

```txt
resources/js/Pages/Settings/WhatsAppTemplates/Index.tsx
resources/js/Pages/Settings/WhatsAppTemplates/Create.tsx
resources/js/Pages/Settings/WhatsAppTemplates/Edit.tsx
```

## Backend Files

```txt
app/Http/Controllers/Settings/WhatsAppTemplateController.php
app/Http/Requests/Settings/StoreWhatsAppTemplateRequest.php
app/Http/Requests/Settings/UpdateWhatsAppTemplateRequest.php
app/Services/WhatsApp/WhatsAppTemplateRenderer.php
app/Models/WhatsAppTemplate.php
```

## Database Table

```txt
whatsapp_templates
```

## Fields

```txt
outlet_id
type
title
body
is_active
created_at
updated_at
```

## Template Types

```txt
payment_receipt
order_created
order_processing
order_ready
order_completed
payment_reminder
custom
```

## Template Scope

`outlet_id` is nullable.

```txt
outlet_id = null
- Global template.
- Used by all outlets unless outlet-specific template exists.

outlet_id = specific outlet id
- Outlet-specific template.
- Overrides global template for that outlet.
```

## List Page Features

```txt
- List templates.
- Filter by type.
- Filter by outlet/global.
- Filter active/inactive.
- Search by title/body.
- Create template button.
- Edit action.
- Preview action.
- Toggle active action.
- Delete action with confirmation.
```

## Table Columns

```txt
Title
Type
Scope
Status
Updated At
Actions
```

## Create/Edit Form Fields

```txt
outlet_id
type
title
body
is_active
```

## Available Variables

Show helper panel in UI:

```txt
{customer_name}
{customer_phone}
{invoice_number}
{grand_total}
{payment_method}
{payment_status}
{order_status}
{tracking_url}
{invoice_url}
{outlet_name}
{outlet_phone}
{outlet_whatsapp}
{outlet_address}
{business_name}
{estimated_done_at}
{paid_at}
```

## Default Templates

### payment_receipt

```txt
Halo {customer_name}, pembayaran laundry kamu berhasil.

Invoice: {invoice_number}
Total: {grand_total}
Metode Pembayaran: {payment_method}

Tracking laundry:
{tracking_url}

Terima kasih sudah menggunakan layanan {business_name}.
```

### order_ready

```txt
Halo {customer_name}, laundry kamu sudah selesai dan siap diambil.

Invoice: {invoice_number}
Status: {order_status}

Tracking laundry:
{tracking_url}

Terima kasih.
```

### order_completed

```txt
Halo {customer_name}, pesanan laundry kamu telah selesai.

Invoice: {invoice_number}
Terima kasih sudah menggunakan layanan {business_name}.
```

### payment_reminder

```txt
Halo {customer_name}, pembayaran untuk invoice {invoice_number} masih menunggu.

Total: {grand_total}
Tracking:
{tracking_url}
```

## Validation Rules

```txt
outlet_id: nullable|exists:outlets,id
type: required|in:payment_receipt,order_created,order_processing,order_ready,order_completed,payment_reminder,custom
title: required|string|max:150
body: required|string
is_active: boolean
```

## Uniqueness Rule

For non-custom templates:

```txt
Only one active template per outlet_id + type.
```

For global templates:

```txt
Only one active global template per type.
```

## Template Rendering Logic

Create service:

```txt
WhatsAppTemplateRenderer
```

Method:

```php
render(string $body, array $data): string
```

Behavior:

```txt
1. Receive template body.
2. Receive data map.
3. Replace variables with values.
4. Unknown variables should remain unchanged or be replaced with empty string based on config.
5. Return final message.
```

Recommended:

```txt
Unknown variables should remain unchanged to make debugging easier.
```

## Template Selection Logic

When sending a message for an order:

```txt
1. Check active outlet-specific template by outlet_id and type.
2. If not found, use active global template by type.
3. If not found, use hardcoded fallback template.
```

## Preview Feature

Preview endpoint should use sample data:

```txt
customer_name = Budi Santoso
invoice_number = LDR-20260518-0001
grand_total = Rp50.000
payment_method = QRIS
tracking_url = https://example.com/track/sample-token
business_name = Bersih Laundry
outlet_name = Bersih Laundry Pusat
```

## Permission

```txt
owner:
- Can manage all templates.

admin with can_manage_settings:
- Can manage templates.
```

Recommended for MVP:

```txt
Only owner can manage WhatsApp templates.
```

## Acceptance Criteria

```txt
- Owner can create and edit WhatsApp templates.
- Template supports variables.
- User can preview rendered message before saving.
- Outlet-specific template overrides global template.
- Active template uniqueness is maintained.
- System has fallback template if no template exists.
```

---

# Cross-Module Requirements

## 1. Activity Logs

For Phase 2 and 3, log important actions:

```txt
- business settings updated
- integration settings updated
- outlet created/updated/deactivated/deleted
- user created/updated/deactivated/deleted
- user outlet assignment updated
- customer created/updated/deleted
- service category created/updated/deleted
- service created/updated/deleted
- service variant created/updated/deleted
- services copied between outlets
- WhatsApp template created/updated/deleted
```

## 2. Soft Delete Rules

Use soft delete for:

```txt
outlets
users
customers
service_categories
services
service_variants
```

Do not hard delete data that may be referenced by historical transactions.

## 3. Data Isolation Rules

All outlet-related queries must be scoped.

```php
$query->whereIn('outlet_id', getAccessibleOutletIds($user));
```

For active outlet pages:

```php
$query->where('outlet_id', getActiveOutletId());
```

## 4. Form UX Rules

All forms should:

```txt
- Show required marker.
- Show validation error below fields.
- Disable submit button while processing.
- Show success toast after save.
- Show error toast on failure.
- Redirect to list/detail page after success depending context.
```

## 5. Empty State Rules

Each master data page should have a helpful empty state.

Example for service categories:

```txt
No service categories yet.
Create your first laundry service category such as Laundry Kiloan, Laundry Satuan, or Dry Clean.
```

## 6. Currency Format

All prices should use Indonesian Rupiah format.

Example:

```txt
Rp7.000
Rp50.000
Rp125.000
```

## 7. Date Format

Use Indonesian readable format.

Example:

```txt
18 Mei 2026, 14:30
```

---

# Recommended Development Order

Use this exact sequence for AI Agent execution.

## Phase 2 — Core Settings Order

```txt
1. Create/verify migrations for business_settings, outlets, users, user_outlets.
2. Create models and relationships.
3. Implement permission helpers for outlet access.
4. Build Business Settings CRUD.
5. Build Integration Settings CRUD.
6. Build Outlets CRUD.
7. Build Users CRUD.
8. Build User Outlet Assignment page.
9. Build Outlet Switcher component.
10. Apply outlet scoping middleware/helper to layout and queries.
```

## Phase 3 — Master Data Order

```txt
1. Create/verify migrations for customers, service_categories, services, service_variants, whatsapp_templates.
2. Create models and relationships.
3. Build Customers CRUD.
4. Build Service Categories CRUD.
5. Build Services CRUD.
6. Build Service Variants CRUD.
7. Build Copy Services Between Outlets.
8. Build WhatsApp Templates CRUD.
9. Add preview renderer for WhatsApp templates.
10. Add permission and outlet scoping tests for all master data modules.
```

---

# Testing Checklist

## Phase 2 Tests

```txt
- Owner can update business settings.
- Non-owner cannot access business settings.
- Owner can update integration settings.
- API keys are masked after save.
- Owner can create outlet.
- Only one outlet can be main outlet.
- Owner can create user.
- Last owner cannot be deactivated.
- Owner can assign user to outlet.
- Cashier only sees assigned outlet.
- Outlet switcher only shows accessible outlets.
- User cannot switch to unauthorized outlet.
```

## Phase 3 Tests

```txt
- User can create customer in active outlet.
- Duplicate customer phone in same outlet is handled.
- User cannot access customer from unauthorized outlet.
- Admin can create service category in active outlet.
- Service category is scoped by outlet.
- Admin can create service under category from same outlet.
- Service cannot use category from another outlet.
- Admin can create service variant.
- Variant cannot be attached to service from another outlet.
- Copy services between outlets works.
- Copy services cannot use same outlet as source and target.
- WhatsApp template variables render correctly.
- Outlet-specific WhatsApp template overrides global template.
```

---

# Deliverables

At the end of Phase 2 and Phase 3, the application should have:

```txt
- Business settings page
- Integration settings page
- Outlets management
- Users management
- User outlet assignment
- Outlet switcher
- Customers management
- Service categories management
- Services management
- Service variants management
- Copy services between outlets feature
- WhatsApp templates management
- Basic permission and outlet scoping
- Activity logs for important changes
```

These deliverables are required before implementing POS order creation, payments, WhatsApp sending, invoice, and tracking pages.
