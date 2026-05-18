# Product Requirements Document (PRD)

# POS Laundry Website

**Version:** 1.0  
**Project Type:** Web-based Laundry Point of Sale  
**Target User:** Laundry business owner with one or more outlets  
**Business Model:** One website instance per laundry business owner, not SaaS  
**Payment Methods:** Cash and Dynamic QRIS via Midtrans  
**Notification Channel:** WhatsApp  
**Tracking:** Public customer tracking link without login  

---

## 1. Product Overview

POS Laundry Website adalah aplikasi kasir berbasis web untuk membantu pengusaha laundry mengelola transaksi harian, pelanggan, layanan laundry, pembayaran, bukti pembayaran, dan tracking status laundry secara digital.

Aplikasi ini dirancang untuk **satu bisnis laundry per website**. Jika ada owner laundry lain, aplikasi akan di-clone dan dikonfigurasi ulang, bukan menggunakan sistem SaaS multi-tenant.

Satu bisnis laundry tetap dapat memiliki beberapa outlet atau cabang. Karena itu, aplikasi mendukung multi-outlet di dalam satu website.

Contoh struktur bisnis:

```txt
Bersih Laundry
├── Outlet Pusat
├── Outlet Sidoarjo
└── Outlet Gresik
```

Owner dapat mengelola semua outlet, sedangkan kasir atau staff dapat dibatasi hanya untuk outlet tertentu.

---

## 2. Goals

Tujuan utama aplikasi ini adalah:

1. Memudahkan pencatatan pelanggan laundry.
2. Memudahkan pembuatan order laundry.
3. Membuat sistem layanan dan harga laundry yang fleksibel.
4. Mendukung pembayaran cash dan QRIS dinamis.
5. Menghindari kesalahan input nominal pembayaran QRIS.
6. Mengirim bukti pembayaran otomatis ke WhatsApp pelanggan.
7. Memberikan link tracking status laundry kepada pelanggan.
8. Mengirim WhatsApp otomatis ketika laundry selesai atau siap diambil.
9. Membantu owner melihat transaksi, pendapatan, dan performa outlet.
10. Membuat operasional laundry terlihat lebih profesional dan modern.

---

## 3. Non-Goals

Fitur berikut tidak menjadi prioritas pada versi awal:

1. Sistem SaaS multi-tenant dengan banyak owner dalam satu database.
2. Subscription billing untuk owner laundry.
3. Marketplace laundry.
4. Aplikasi mobile native.
5. Customer login app.
6. Payroll karyawan.
7. Inventory bahan laundry.
8. Accounting lengkap.
9. Integrasi pickup delivery driver secara kompleks.

Fitur-fitur tersebut dapat dipertimbangkan pada versi lanjutan.

---

## 4. Business Model Assumption

Model aplikasi:

```txt
1 website = 1 bisnis laundry
1 bisnis laundry = bisa punya banyak outlet
1 owner = bisa mengakses semua outlet
1 kasir/staff = bisa dibatasi ke outlet tertentu
jika ada owner lain = clone website + konfigurasi ulang
```

Karena modelnya bukan SaaS, database tidak membutuhkan tabel:

```txt
companies
tenants
subscriptions
plans
tenant_id
company_id
```

Sebagai gantinya, aplikasi menggunakan tabel `business_settings` untuk menyimpan identitas dan konfigurasi utama bisnis laundry.

---

## 5. Target Users

### 5.1 Owner

Owner adalah pemilik bisnis laundry.

Owner dapat:

- Mengakses semua outlet.
- Melihat dashboard semua outlet.
- Mengelola layanan dan harga laundry.
- Mengelola user, admin, kasir, dan staff.
- Melihat laporan transaksi.
- Mengatur konfigurasi bisnis.
- Mengatur Midtrans dan WhatsApp API.

### 5.2 Admin

Admin adalah pengelola operasional.

Admin dapat:

- Mengelola order.
- Mengelola pelanggan.
- Mengelola layanan.
- Mengelola pembayaran.
- Melihat laporan sesuai akses outlet.
- Mengirim ulang notifikasi WhatsApp.

### 5.3 Cashier / Kasir

Kasir adalah user outlet yang menangani transaksi pelanggan.

Kasir dapat:

- Membuat order baru.
- Mencatat pelanggan.
- Memilih layanan laundry.
- Menghitung total otomatis.
- Memproses pembayaran cash.
- Generate QRIS dinamis.
- Melihat status pembayaran.
- Mencetak atau mengirim bukti pembayaran.

### 5.4 Staff

Staff adalah user yang menangani proses laundry.

Staff dapat:

- Melihat daftar order.
- Mengubah status laundry.
- Menandai laundry siap diambil.
- Menandai laundry selesai.

### 5.5 Customer / Pelanggan

Pelanggan tidak perlu login.

Pelanggan dapat:

- Menerima bukti pembayaran via WhatsApp.
- Membuka link tracking laundry.
- Melihat status laundry.
- Menerima notifikasi ketika laundry selesai.

---

## 6. Main User Flow

### 6.1 Standard Laundry Order Flow

```txt
Pelanggan datang ke outlet
        ↓
Kasir input data pelanggan
        ↓
Kasir input layanan laundry
        ↓
Sistem hitung total otomatis
        ↓
Kasir pilih metode pembayaran: Cash atau QRIS
        ↓
Pembayaran berhasil
        ↓
Sistem kirim bukti pembayaran via WhatsApp
        ↓
Pelanggan menerima link tracking
        ↓
Laundry diproses
        ↓
Staff/Admin update status laundry
        ↓
Sistem kirim WhatsApp ketika laundry siap diambil / selesai
```

---

## 7. Feature Requirements

---

# 7.1 Authentication & User Management

## Description

Aplikasi harus memiliki sistem login untuk owner, admin, kasir, dan staff.

## Functional Requirements

### FR-AUTH-001: Login

User dapat login menggunakan email dan password.

### FR-AUTH-002: Logout

User dapat logout dari aplikasi.

### FR-AUTH-003: User Role

User memiliki `global_role`:

```txt
owner
admin
staff
```

### FR-AUTH-004: Outlet Access

Akses user ke outlet diatur melalui tabel `user_outlets`.

Satu user dapat mengakses satu atau banyak outlet.

### FR-AUTH-005: Outlet-level Role

Di setiap outlet, user dapat memiliki role:

```txt
owner
admin
cashier
staff
```

### FR-AUTH-006: Permission per Outlet

Sistem mendukung permission per outlet:

```txt
can_manage_orders
can_manage_payments
can_manage_services
can_manage_reports
can_manage_users
can_manage_settings
```

## Acceptance Criteria

- User hanya dapat melihat data outlet yang diberikan kepadanya.
- Owner dapat mengakses semua outlet.
- Kasir outlet A tidak boleh melihat order outlet B jika tidak punya akses.
- Admin dapat mengelola user sesuai permission.

---

# 7.2 Business Settings

## Description

Business settings menyimpan konfigurasi utama website untuk satu bisnis laundry.

## Functional Requirements

### FR-BUS-001: Manage Business Identity

Owner dapat mengatur:

```txt
Nama bisnis
Slug bisnis
Logo
Favicon
Nama owner
Nomor owner
Email owner
Alamat default
Google Maps URL default
```

### FR-BUS-002: Manage Global Settings

Owner dapat mengatur:

```txt
Timezone
Currency
Receipt footer text
Terms and conditions
QRIS expiry minutes
```

### FR-BUS-003: Manage Midtrans Configuration

Owner dapat mengatur:

```txt
Midtrans server key
Midtrans client key
Midtrans environment: sandbox / production
```

### FR-BUS-004: Manage WhatsApp Configuration

Owner dapat mengatur:

```txt
WhatsApp provider
WhatsApp API key
WhatsApp sender number
```

## Acceptance Criteria

- Konfigurasi bisnis dapat diubah dari dashboard owner.
- API key sensitif harus disimpan secara aman atau encrypted.
- Sistem menggunakan konfigurasi Midtrans dan WhatsApp dari business settings.

---

# 7.3 Outlet Management

## Description

Aplikasi mendukung banyak outlet dalam satu website.

## Functional Requirements

### FR-OUTLET-001: Create Outlet

Owner/admin dapat membuat outlet baru.

Data outlet:

```txt
Name
Code
Slug
Phone
WhatsApp number
Email
Address
Google Maps URL
Is main outlet
Is active
```

### FR-OUTLET-002: Update Outlet

Owner/admin dapat mengubah informasi outlet.

### FR-OUTLET-003: Disable Outlet

Outlet dapat dinonaktifkan tanpa menghapus data historis.

### FR-OUTLET-004: Main Outlet

Sistem dapat menandai satu outlet sebagai outlet utama.

## Acceptance Criteria

- Outlet nonaktif tidak muncul di pilihan transaksi baru.
- Data order lama outlet nonaktif tetap dapat dilihat oleh owner/admin.
- Slug outlet harus unik.

---

# 7.4 Customer Management

## Description

Sistem menyimpan data pelanggan laundry agar order berikutnya lebih cepat dibuat.

## Functional Requirements

### FR-CUST-001: Create Customer

Kasir dapat membuat pelanggan baru saat membuat order.

Data pelanggan:

```txt
Outlet asal
Nama
Nomor telepon
Nomor WhatsApp
Alamat opsional
Catatan opsional
```

### FR-CUST-002: Search Customer

Kasir dapat mencari pelanggan berdasarkan:

```txt
Nama
Nomor telepon
Nomor WhatsApp
```

### FR-CUST-003: Customer History

Admin/kasir dapat melihat riwayat order pelanggan.

### FR-CUST-004: Customer Statistics

Sistem menyimpan:

```txt
Total orders
Total spent
```

## Acceptance Criteria

- Customer dapat dipilih kembali saat membuat order baru.
- Nomor WhatsApp digunakan untuk pengiriman bukti pembayaran dan tracking.
- Customer dapat dikaitkan ke outlet asal.

---

# 7.5 Laundry Service Management

## Description

Sistem layanan laundry harus fleksibel karena setiap laundry memiliki jenis layanan, satuan, harga, dan durasi berbeda.

## Functional Requirements

### FR-SVC-001: Manage Service Categories

Admin dapat membuat kategori layanan.

Contoh kategori:

```txt
Laundry Kiloan
Laundry Satuan
Dry Clean
Bedcover
Sprei
Boneka
Gorden
Sepatu
Karpet
Sofa
Extra Service
```

### FR-SVC-002: Manage Services

Admin dapat membuat layanan di dalam kategori.

Contoh:

```txt
Cuci Kering Setrika
Cuci Kering
Setrika Saja
Sepatu Premium
Boneka Sedang
```

### FR-SVC-003: Pricing Type

Setiap service memiliki tipe harga:

```txt
per_kg
per_item
per_set
per_m2
fixed
custom
```

### FR-SVC-004: Manage Service Variants

Admin dapat membuat varian layanan.

Contoh:

```txt
4x24 Jam
3x24 Jam
2x24 Jam
Express <24 Jam
Express <12 Jam
```

### FR-SVC-005: Service Variant Price

Setiap varian memiliki:

```txt
Price
Unit
Minimum quantity
Estimated duration hours
Is express
Sort order
```

### FR-SVC-006: Service per Outlet

Layanan dapat dibuat per outlet agar setiap cabang dapat memiliki harga sendiri.

### FR-SVC-007: Clone Services Between Outlets

Fitur opsional: admin dapat menyalin layanan dari satu outlet ke outlet lain.

## Acceptance Criteria

- Admin dapat membuat layanan kiloan dengan minimal order 3 kg.
- Admin dapat membuat layanan satuan seperti jas, boneka, sepatu, dan bedcover.
- Harga lama di invoice tidak berubah meskipun harga master service diperbarui.
- Layanan nonaktif tidak muncul saat membuat order baru.

---

# 7.6 Order Management

## Description

Order adalah transaksi laundry yang dibuat ketika pelanggan menyerahkan barang laundry.

## Functional Requirements

### FR-ORD-001: Create Order

Kasir dapat membuat order baru.

Data order:

```txt
Outlet
Customer
Invoice number
Order date
Estimated done date
Customer notes
Internal notes
```

### FR-ORD-002: Add Order Items

Kasir dapat menambahkan satu atau banyak item layanan ke order.

Item order menyimpan snapshot:

```txt
Service category
Service
Service variant
Service name
Variant name
Pricing type
Unit
Quantity
Charged quantity
Unit price
Subtotal
Notes
```

### FR-ORD-003: Automatic Calculation

Sistem menghitung:

```txt
Subtotal
Discount amount
Additional fee
Delivery fee
Grand total
```

### FR-ORD-004: Minimum Quantity Calculation

Jika layanan memiliki minimal order, sistem menggunakan `charged_quantity`.

Contoh:

```txt
Berat aktual: 2 kg
Minimal order: 3 kg
Harga: Rp7.000/kg
Charged quantity: 3 kg
Subtotal: Rp21.000
```

### FR-ORD-005: Order Status

Order memiliki status:

```txt
draft
waiting_payment
received
processing
washing
drying
ironing
ready_to_pickup
delivering
completed
cancelled
```

### FR-ORD-006: Payment Status

Order memiliki payment status:

```txt
unpaid
pending
paid
expired
failed
cancelled
refunded
conflict
```

### FR-ORD-007: Tracking Token

Setiap order memiliki `tracking_token` unik untuk halaman tracking pelanggan.

## Acceptance Criteria

- Satu order dapat memiliki banyak order items.
- Invoice number unik per outlet.
- Order dapat dibuat tanpa langsung dibayar.
- Grand total menjadi nilai pembayaran untuk cash atau QRIS.
- Tracking token tidak boleh mudah ditebak.

---

# 7.7 Payment Management

## Description

Sistem mendukung dua metode pembayaran:

```txt
Cash
QRIS Dinamis Midtrans
```

Payment disimpan sebagai riwayat. Satu order dapat memiliki beberapa payment attempt, tetapi hanya satu yang aktif.

---

## 7.7.1 Cash Payment

### Functional Requirements

#### FR-PAY-CASH-001: Pay with Cash

Kasir dapat memilih metode cash.

#### FR-PAY-CASH-002: Input Amount Paid

Kasir dapat input uang diterima.

#### FR-PAY-CASH-003: Calculate Change

Sistem menghitung kembalian.

```txt
change_amount = amount_paid - amount
```

#### FR-PAY-CASH-004: Manual Confirmation

Pembayaran cash dikonfirmasi manual oleh kasir.

#### FR-PAY-CASH-005: Update Order Payment Status

Setelah cash dikonfirmasi:

```txt
payments.status = paid
orders.payment_status = paid
orders.order_status = received / processing
orders.active_payment_id = payment.id
```

## Acceptance Criteria

- Cash dapat langsung menjadi paid setelah dikonfirmasi kasir.
- Sistem menyimpan user yang mengonfirmasi pembayaran.
- Jika uang diterima kurang dari total, sistem menolak konfirmasi.
- Bukti pembayaran WhatsApp dikirim setelah pembayaran cash berhasil.

---

## 7.7.2 QRIS Payment via Midtrans

### Functional Requirements

#### FR-PAY-QRIS-001: Generate Dynamic QRIS

Kasir dapat generate QRIS berdasarkan grand total order.

#### FR-PAY-QRIS-002: Midtrans Request

Sistem membuat transaksi ke Midtrans menggunakan nominal order.

#### FR-PAY-QRIS-003: Store QRIS Data

Sistem menyimpan:

```txt
provider_order_id
provider_transaction_id
provider_reference_id
qris_string
qris_url
payment_url
expired_at
raw_response
```

#### FR-PAY-QRIS-004: Pending Status

Setelah QRIS dibuat:

```txt
payments.status = pending
orders.payment_status = pending
orders.order_status = waiting_payment
```

#### FR-PAY-QRIS-005: Payment Expiry

QRIS memiliki waktu expired sesuai `qris_expiry_minutes`.

#### FR-PAY-QRIS-006: Regenerate QRIS

Kasir dapat generate ulang QRIS jika QRIS lama expired atau dibatalkan.

#### FR-PAY-QRIS-007: Change QRIS to Cash

Jika QRIS masih pending, kasir dapat membatalkan QRIS dan mengganti ke cash.

#### FR-PAY-QRIS-008: Webhook Confirmation

Status QRIS hanya menjadi paid setelah webhook Midtrans valid diterima.

## Acceptance Criteria

- Customer tidak perlu input nominal manual saat scan QRIS.
- QRIS yang dibuat harus sesuai grand total order.
- Order tidak boleh menjadi paid hanya karena kasir menekan tombol manual untuk QRIS.
- Webhook Midtrans harus divalidasi signature dan amount-nya.
- Jika order sudah paid via cash, webhook QRIS lama tidak boleh mengubah payment utama.

---

# 7.8 Payment Webhook Management

## Description

Webhook digunakan untuk menerima update status pembayaran dari Midtrans.

## Functional Requirements

### FR-WEBHOOK-001: Receive Webhook

Sistem menerima webhook dari Midtrans.

### FR-WEBHOOK-002: Store Raw Payload

Semua webhook disimpan di tabel `payment_webhooks`.

### FR-WEBHOOK-003: Validate Signature

Sistem memvalidasi signature key dari Midtrans.

### FR-WEBHOOK-004: Match Payment

Sistem mencari payment berdasarkan:

```txt
provider_order_id
provider_transaction_id
provider_reference_id
```

### FR-WEBHOOK-005: Validate Amount

Sistem memastikan `gross_amount` sama dengan payment amount.

### FR-WEBHOOK-006: Process Paid Payment

Jika webhook valid dan status transaksi berhasil, sistem update:

```txt
payments.status = paid
payments.paid_at = current timestamp
orders.payment_status = paid
orders.order_status = received / processing
```

### FR-WEBHOOK-007: Handle Duplicate Webhook

Jika webhook sudah pernah diproses, tandai sebagai duplicate.

### FR-WEBHOOK-008: Handle Conflict

Jika order sudah paid dengan metode lain, webhook ditandai conflict dan tidak mengubah order.

## Acceptance Criteria

- Semua webhook tersimpan untuk audit.
- Webhook invalid tidak mengubah status order.
- Duplicate webhook tidak menyebabkan pengiriman WhatsApp berulang.
- Conflict webhook tidak merusak payment yang sudah valid.

---

# 7.9 Order Status Tracking

## Description

Sistem mencatat riwayat perubahan status laundry dan menampilkannya pada halaman tracking pelanggan.

## Functional Requirements

### FR-TRACK-001: Update Order Status

Admin/staff dapat mengubah status order.

### FR-TRACK-002: Store Status History

Setiap perubahan status disimpan di `order_status_histories`.

Data yang disimpan:

```txt
Order
Old status
New status
Changed by
Notes
Created at
```

### FR-TRACK-003: Public Tracking Page

Pelanggan dapat membuka halaman tracking menggunakan tracking token.

### FR-TRACK-004: Tracking Information

Halaman tracking menampilkan:

```txt
Invoice number
Customer name
Order date
Estimated done date
Order status
Payment status
Order items
Status timeline
Outlet info
```

### FR-TRACK-005: No Customer Login Required

Pelanggan tidak perlu login untuk membuka tracking.

## Acceptance Criteria

- Tracking URL dikirim melalui WhatsApp.
- Tracking URL menggunakan token yang aman.
- Pelanggan hanya dapat melihat data order miliknya.
- Status timeline sesuai data di `order_status_histories`.

---

# 7.10 WhatsApp Notification

## Description

Sistem mengirim pesan WhatsApp otomatis untuk bukti pembayaran dan update status laundry.

## Functional Requirements

### FR-WA-001: Payment Receipt Message

Setelah payment paid, sistem mengirim bukti pembayaran ke WhatsApp pelanggan.

### FR-WA-002: Tracking Link Message

Pesan bukti pembayaran harus menyertakan link tracking.

### FR-WA-003: Order Ready Message

Saat status berubah menjadi `ready_to_pickup`, sistem mengirim WhatsApp ke pelanggan.

### FR-WA-004: Order Completed Message

Saat status berubah menjadi `completed`, sistem dapat mengirim WhatsApp ke pelanggan.

### FR-WA-005: WhatsApp Message Log

Semua pesan WhatsApp disimpan di tabel `whatsapp_messages`.

### FR-WA-006: WhatsApp Templates

Admin dapat mengatur template pesan.

Template tersedia:

```txt
payment_receipt
order_created
order_processing
order_ready
order_completed
payment_reminder
custom
```

### FR-WA-007: Resend WhatsApp

Admin/kasir dapat mengirim ulang pesan tertentu jika gagal.

## Acceptance Criteria

- Pesan tidak dikirim sebelum payment paid untuk receipt.
- Pesan menggunakan nomor WhatsApp pelanggan.
- Jika provider WhatsApp gagal, status pesan menjadi failed.
- Admin dapat melihat riwayat pesan WhatsApp per order.

---

# 7.11 Invoice / Receipt

## Description

Invoice atau nota digunakan sebagai bukti transaksi laundry.

## Functional Requirements

### FR-INV-001: Generate Invoice Number

Sistem membuat nomor invoice unik per outlet.

Contoh format:

```txt
LDR-20260518-0001
```

Atau dengan kode outlet:

```txt
SBY-20260518-0001
```

### FR-INV-002: Invoice Detail

Invoice menampilkan:

```txt
Business name
Outlet name
Invoice number
Customer name
Customer phone
Order date
Order items
Subtotal
Discount
Additional fee
Delivery fee
Grand total
Payment method
Payment status
Tracking URL
Receipt footer
```

### FR-INV-003: Send Invoice via WhatsApp

Invoice ringkas dikirim melalui WhatsApp setelah payment paid.

### FR-INV-004: Print Receipt

Opsional: sistem dapat menyediakan layout cetak untuk printer thermal.

## Acceptance Criteria

- Invoice lama tetap menampilkan harga lama meskipun harga layanan berubah.
- Invoice dapat dibuka dari halaman order detail.
- Invoice dapat dikirim ulang via WhatsApp.

---

# 7.12 Dashboard

## Description

Dashboard menampilkan ringkasan operasional dan keuangan laundry.

## Functional Requirements

### FR-DASH-001: Today Summary

Dashboard menampilkan:

```txt
Total order hari ini
Total pendapatan hari ini
Order pending payment
Order processing
Order ready to pickup
Order completed
```

### FR-DASH-002: Outlet Filter

Owner/admin dapat filter dashboard berdasarkan outlet.

### FR-DASH-003: Revenue Chart

Dashboard menampilkan grafik pendapatan harian/bulanan.

### FR-DASH-004: Popular Services

Dashboard menampilkan layanan paling sering digunakan.

### FR-DASH-005: Payment Method Summary

Dashboard menampilkan ringkasan pembayaran:

```txt
Cash total
QRIS total
Pending QRIS
Failed/expired payment
```

## Acceptance Criteria

- Kasir hanya melihat data outlet yang diizinkan.
- Owner dapat melihat semua outlet.
- Angka pendapatan hanya menghitung payment paid.

---

# 7.13 Reports

## Description

Laporan digunakan owner/admin untuk melihat performa bisnis.

## Functional Requirements

### FR-REP-001: Transaction Report

Laporan transaksi dapat difilter berdasarkan:

```txt
Tanggal
Outlet
Order status
Payment status
Payment method
Kasir
```

### FR-REP-002: Revenue Report

Laporan pendapatan berdasarkan tanggal dan outlet.

### FR-REP-003: Service Report

Laporan layanan paling laris.

### FR-REP-004: Customer Report

Laporan pelanggan dan total transaksi pelanggan.

### FR-REP-005: Export Report

Opsional: export ke CSV/Excel/PDF.

## Acceptance Criteria

- Report hanya menghitung pembayaran paid.
- Report dapat difilter per outlet.
- Owner dapat melihat gabungan semua outlet.

---

# 7.14 Activity Log

## Description

Activity log digunakan untuk audit perubahan penting di sistem.

## Functional Requirements

### FR-LOG-001: Store Important Activities

Sistem menyimpan aktivitas seperti:

```txt
User login
Create order
Update order
Confirm cash payment
Generate QRIS
Webhook payment received
Update laundry status
Send WhatsApp
Update service price
```

### FR-LOG-002: Store Old and New Values

Untuk perubahan data penting, sistem menyimpan old values dan new values.

## Acceptance Criteria

- Owner/admin dapat melihat log aktivitas.
- Log menyimpan user, outlet, action, subject, dan timestamp.

---

## 8. Database Design

Database final untuk model satu website per owner dengan multi-outlet:

```txt
business_settings
outlets
users
user_outlets
customers
service_categories
services
service_variants
orders
order_items
payments
payment_webhooks
order_status_histories
whatsapp_messages
whatsapp_templates
activity_logs
```

---

## 8.1 Database Table Summary

### business_settings

Menyimpan konfigurasi global bisnis laundry.

### outlets

Menyimpan daftar outlet/cabang laundry.

### users

Menyimpan akun owner, admin, kasir, dan staff.

### user_outlets

Mengatur user bisa mengakses outlet mana dan role-nya di outlet tersebut.

### customers

Menyimpan data pelanggan laundry.

### service_categories

Menyimpan kategori layanan laundry.

### services

Menyimpan layanan laundry.

### service_variants

Menyimpan varian harga/durasi dari layanan.

### orders

Menyimpan transaksi laundry utama.

### order_items

Menyimpan detail layanan dalam order.

### payments

Menyimpan pembayaran cash dan QRIS.

### payment_webhooks

Menyimpan webhook dari Midtrans.

### order_status_histories

Menyimpan riwayat perubahan status order.

### whatsapp_messages

Menyimpan riwayat pesan WhatsApp.

### whatsapp_templates

Menyimpan template pesan WhatsApp.

### activity_logs

Menyimpan log aktivitas sistem.

---

## 8.2 DBML

```dbml
Table business_settings {
  id bigint [pk, increment]

  business_name varchar(150) [not null]
  business_slug varchar(150)

  logo_path varchar(255)
  favicon_path varchar(255)

  owner_name varchar(150)
  owner_phone varchar(30)
  owner_email varchar(150)

  default_phone varchar(30)
  default_whatsapp_number varchar(30)
  default_email varchar(150)
  default_address text
  default_google_maps_url text

  timezone varchar(100) [default: 'Asia/Jakarta']
  currency varchar(10) [default: 'IDR']

  receipt_footer_text text
  terms_and_conditions text

  qris_expiry_minutes int [default: 30]

  whatsapp_provider varchar(50)
  whatsapp_api_key text
  whatsapp_sender_number varchar(30)

  midtrans_server_key text
  midtrans_client_key text
  midtrans_is_production boolean [default: false]

  created_at timestamp
  updated_at timestamp
}

Table outlets {
  id bigint [pk, increment]

  name varchar(150) [not null]
  code varchar(50) [unique]
  slug varchar(150) [unique, not null]

  phone varchar(30)
  whatsapp_number varchar(30)
  email varchar(150)
  address text
  google_maps_url text

  is_main boolean [default: false]
  is_active boolean [default: true]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table users {
  id bigint [pk, increment]

  name varchar(150) [not null]
  email varchar(150) [unique, not null]
  phone varchar(30)
  password varchar(255) [not null]

  global_role varchar(50) [default: 'staff']

  is_active boolean [default: true]
  last_login_at timestamp

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table user_outlets {
  id bigint [pk, increment]

  user_id bigint [ref: > users.id, not null]
  outlet_id bigint [ref: > outlets.id, not null]

  role varchar(50) [default: 'staff']

  can_manage_orders boolean [default: true]
  can_manage_payments boolean [default: true]
  can_manage_services boolean [default: false]
  can_manage_reports boolean [default: false]
  can_manage_users boolean [default: false]
  can_manage_settings boolean [default: false]

  is_primary boolean [default: false]
  is_active boolean [default: true]

  created_at timestamp
  updated_at timestamp

  indexes {
    (user_id, outlet_id) [unique]
    user_id
    outlet_id
  }
}

Table customers {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]

  name varchar(150) [not null]
  phone varchar(30) [not null]
  whatsapp_number varchar(30)
  address text
  notes text

  total_orders int [default: 0]
  total_spent decimal(14,2) [default: 0]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    phone
    (outlet_id, phone)
  }
}

Table service_categories {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]

  name varchar(150) [not null]
  description text
  sort_order int [default: 0]
  is_active boolean [default: true]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    outlet_id
  }
}

Table services {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]
  service_category_id bigint [ref: > service_categories.id, not null]

  name varchar(150) [not null]
  description text

  pricing_type varchar(50) [not null]

  is_active boolean [default: true]
  sort_order int [default: 0]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    outlet_id
    service_category_id
  }
}

Table service_variants {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]
  service_id bigint [ref: > services.id, not null]

  name varchar(150) [not null]
  description text

  price decimal(14,2) [not null, default: 0]
  unit varchar(50) [not null]

  min_quantity decimal(10,2) [default: 1]
  estimated_duration_hours int

  is_express boolean [default: false]
  is_active boolean [default: true]
  sort_order int [default: 0]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    outlet_id
    service_id
  }
}

Table orders {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id, not null]
  customer_id bigint [ref: > customers.id, not null]
  created_by bigint [ref: > users.id]

  invoice_number varchar(100) [not null]

  order_status varchar(100) [default: 'waiting_payment']
  payment_status varchar(100) [default: 'unpaid']

  active_payment_id bigint [ref: > payments.id]

  order_date timestamp
  estimated_done_at timestamp
  completed_at timestamp
  cancelled_at timestamp

  subtotal decimal(14,2) [default: 0]
  discount_amount decimal(14,2) [default: 0]
  additional_fee decimal(14,2) [default: 0]
  delivery_fee decimal(14,2) [default: 0]
  grand_total decimal(14,2) [default: 0]

  customer_notes text
  internal_notes text

  tracking_token varchar(150) [unique, not null]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    (outlet_id, invoice_number) [unique]
    (outlet_id, order_status)
    (outlet_id, payment_status)
    tracking_token
  }
}

Table order_items {
  id bigint [pk, increment]

  order_id bigint [ref: > orders.id, not null]

  service_category_id bigint [ref: > service_categories.id]
  service_id bigint [ref: > services.id]
  service_variant_id bigint [ref: > service_variants.id]

  service_name varchar(150) [not null]
  variant_name varchar(150)

  pricing_type varchar(50) [not null]
  unit varchar(50) [not null]

  quantity decimal(10,2) [not null, default: 1]
  charged_quantity decimal(10,2) [not null, default: 1]

  unit_price decimal(14,2) [not null, default: 0]
  subtotal decimal(14,2) [not null, default: 0]

  notes text

  created_at timestamp
  updated_at timestamp

  indexes {
    order_id
  }
}

Table payments {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id, not null]
  order_id bigint [ref: > orders.id, not null]

  provider varchar(50) [not null]
  method varchar(50) [not null]

  status varchar(50) [default: 'pending']
  is_active boolean [default: true]

  amount decimal(14,2) [not null, default: 0]
  amount_paid decimal(14,2)
  change_amount decimal(14,2)

  provider_order_id varchar(150)
  provider_transaction_id varchar(150)
  provider_reference_id varchar(150)

  qris_string text
  qris_url text
  payment_url text

  expired_at timestamp
  paid_at timestamp
  cancelled_at timestamp

  confirmed_by bigint [ref: > users.id]

  raw_response json

  created_at timestamp
  updated_at timestamp

  indexes {
    outlet_id
    order_id
    status
    provider_order_id
    provider_transaction_id
  }
}

Table payment_webhooks {
  id bigint [pk, increment]

  payment_id bigint [ref: > payments.id]
  order_id bigint [ref: > orders.id]

  provider varchar(50) [not null]
  provider_order_id varchar(150)
  provider_transaction_id varchar(150)

  event_type varchar(100)
  transaction_status varchar(100)
  fraud_status varchar(100)
  payment_type varchar(100)

  gross_amount decimal(14,2)
  signature_key text

  is_valid_signature boolean [default: false]
  is_processed boolean [default: false]
  processed_at timestamp

  process_status varchar(50) [default: 'pending']
  process_message text

  raw_payload json [not null]

  created_at timestamp

  indexes {
    provider_order_id
    provider_transaction_id
  }
}

Table order_status_histories {
  id bigint [pk, increment]

  order_id bigint [ref: > orders.id, not null]

  old_status varchar(100)
  new_status varchar(100) [not null]

  changed_by bigint [ref: > users.id]
  notes text

  created_at timestamp

  indexes {
    order_id
  }
}

Table whatsapp_messages {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id, not null]
  order_id bigint [ref: > orders.id]
  customer_id bigint [ref: > customers.id]

  provider varchar(50)

  phone varchar(30) [not null]
  message_type varchar(100) [not null]

  message_body text [not null]

  status varchar(50) [default: 'pending']

  provider_message_id varchar(150)
  error_message text
  raw_response json

  sent_at timestamp

  created_at timestamp
  updated_at timestamp

  indexes {
    order_id
    status
  }
}

Table whatsapp_templates {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]

  type varchar(100) [not null]
  title varchar(150) [not null]
  body text [not null]

  is_active boolean [default: true]

  created_at timestamp
  updated_at timestamp

  indexes {
    (outlet_id, type) [unique]
  }
}

Table activity_logs {
  id bigint [pk, increment]

  outlet_id bigint [ref: > outlets.id]
  user_id bigint [ref: > users.id]

  subject_type varchar(150)
  subject_id bigint

  action varchar(100) [not null]
  description text

  old_values json
  new_values json

  ip_address varchar(100)
  user_agent text

  created_at timestamp

  indexes {
    (subject_type, subject_id)
  }
}
```

---

## 9. Payment Behavior Rules

### 9.1 General Payment Rules

1. One order can have multiple payment records.
2. Only one payment can be active at one time.
3. `orders.active_payment_id` points to active payment.
4. Payment history must not be deleted.
5. Payment amount must match `orders.grand_total`.
6. Paid order should not be paid again.

---

### 9.2 Cash Rules

1. Cash payment is manual.
2. Cash can be confirmed by cashier.
3. Cash requires `amount_paid`.
4. `change_amount` is calculated automatically.
5. Cash payment can become paid immediately after confirmation.
6. Cash receipt WhatsApp is sent after payment is confirmed.

---

### 9.3 QRIS Rules

1. QRIS payment is generated through Midtrans.
2. QRIS starts as pending.
3. QRIS can only become paid through valid webhook.
4. QRIS can expire.
5. Expired QRIS can be regenerated.
6. Pending QRIS can be changed to cash.
7. Late webhook from old QRIS must be handled as conflict if order is already paid.

---

## 10. Order Status Rules

### 10.1 Status Flow

Recommended flow:

```txt
waiting_payment
    ↓
received
    ↓
processing
    ↓
washing
    ↓
drying
    ↓
ironing
    ↓
ready_to_pickup
    ↓
completed
```

Alternative simplified MVP flow:

```txt
waiting_payment
    ↓
processing
    ↓
ready_to_pickup
    ↓
completed
```

### 10.2 Cancel Rules

Order can be cancelled if:

- Payment is unpaid.
- Payment is pending.
- Payment is failed.
- Payment is expired.

If payment is already paid, cancellation should require admin/owner confirmation and may need refund handling.

---

## 11. WhatsApp Message Examples

### 11.1 Cash Payment Receipt

```txt
Halo {customer_name}, pembayaran laundry kamu berhasil.

Invoice: {invoice_number}
Metode Pembayaran: Cash
Total: {grand_total}
Dibayar: {amount_paid}
Kembalian: {change_amount}

Tracking laundry:
{tracking_url}

Terima kasih sudah menggunakan layanan {business_name}.
```

### 11.2 QRIS Payment Receipt

```txt
Halo {customer_name}, pembayaran laundry kamu berhasil.

Invoice: {invoice_number}
Metode Pembayaran: QRIS
Total: {grand_total}

Tracking laundry:
{tracking_url}

Terima kasih sudah menggunakan layanan {business_name}.
```

### 11.3 Order Ready Message

```txt
Halo {customer_name}, laundry kamu sudah selesai dan siap diambil.

Invoice: {invoice_number}
Status: Siap Diambil

Tracking:
{tracking_url}

Silakan ambil laundry kamu di outlet kami. Terima kasih.
```

### 11.4 Order Completed Message

```txt
Halo {customer_name}, laundry kamu sudah selesai.

Invoice: {invoice_number}
Status: Selesai

Terima kasih sudah menggunakan layanan {business_name}.
```

---

## 12. UI Pages

## 12.1 Admin / Staff Pages

### Authentication

- Login page
- Forgot password page, optional

### Dashboard

- Summary cards
- Revenue chart
- Order status summary
- Payment method summary
- Outlet filter

### Outlet Management

- Outlet list
- Create outlet
- Edit outlet
- Outlet detail

### User Management

- User list
- Create user
- Edit user
- Assign user to outlet
- Manage user permissions

### Customer Management

- Customer list
- Customer detail
- Customer order history

### Service Management

- Service category list
- Service list
- Service variant list
- Create/edit category
- Create/edit service
- Create/edit variant
- Clone service to outlet, optional

### Order Management

- Order list
- Create order
- Order detail
- Update order status
- Print invoice
- Send WhatsApp

### Payment

- Cash payment modal
- QRIS payment modal
- QRIS display page/modal
- Payment history
- Webhook log

### WhatsApp

- WhatsApp message logs
- WhatsApp template settings
- Resend failed message

### Reports

- Transaction report
- Revenue report
- Service report
- Customer report

### Settings

- Business profile
- Midtrans settings
- WhatsApp settings
- Receipt settings
- Terms and conditions

---

## 12.2 Public Customer Pages

### Tracking Page

URL example:

```txt
/track/{tracking_token}
```

Page content:

```txt
Business logo
Outlet name
Invoice number
Customer name
Order date
Estimated done date
Payment status
Order status
Order item list
Status timeline
Outlet WhatsApp contact
```

---

## 13. Security Requirements

### SEC-001: Authentication

Admin/staff pages require authentication.

### SEC-002: Authorization

Data must be filtered by outlet access.

### SEC-003: Secure Tracking Token

Tracking token must be random and hard to guess.

### SEC-004: Webhook Signature Validation

Midtrans webhook must validate signature before processing payment.

### SEC-005: Sensitive API Keys

Midtrans and WhatsApp API keys should be encrypted in database.

### SEC-006: Payment Amount Validation

Webhook amount must match payment amount.

### SEC-007: Activity Logging

Important actions should be logged.

### SEC-008: Prevent Double Payment

Paid order cannot be paid again unless explicitly handled by admin.

---

## 14. Data Access Rules

### Owner

- Can access all outlets.
- Can manage all users.
- Can manage all settings.
- Can view all reports.

### Admin

- Can access assigned outlets.
- Can manage operational data according to permission.

### Cashier

- Can access assigned outlet.
- Can create orders.
- Can process cash.
- Can generate QRIS.
- Can print/send receipt.

### Staff

- Can access assigned outlet.
- Can update laundry status.

---

## 15. MVP Scope

## MVP 1

Required for first production version:

```txt
Login
Business settings
Outlet management
User and outlet access
Customer management
Service category management
Service management
Service variant management
Create order
Order item calculation
Cash payment
QRIS Midtrans payment
Midtrans webhook
Order status update
Customer tracking page
WhatsApp payment receipt
WhatsApp order ready notification
Dashboard basic
Transaction list
```

## MVP 2

Next version:

```txt
Report export
WhatsApp templates management
Thermal print receipt
Payment reminder
Clone services between outlets
Advanced dashboard charts
Activity logs UI
```

## MVP 3

Future enhancements:

```txt
Pickup and delivery management
Membership customer
Discount voucher
Expense tracking
Inventory detergent/perfume
Customer booking online
Advanced role permission system
```

---

## 16. Recommended Tech Stack

Recommended stack based on project needs:

```txt
Backend: Laravel
Frontend: React + Inertia.js + TypeScript
Styling: Tailwind CSS + shadcn/ui
Database: MySQL or PostgreSQL
Payment: Midtrans Core API / Snap QRIS
WhatsApp: Fonnte / WhatsApp Cloud API
Queue: Redis Queue
Scheduler: Laravel Scheduler
Deployment: VPS with Docker or non-Docker setup
```

Queue is recommended for:

```txt
Sending WhatsApp messages
Processing webhook safely
Generating reports
Retrying failed notifications
```

---

## 17. Important Implementation Notes

### 17.1 Service Price Snapshot

Order items must save service name, variant name, pricing type, unit, and unit price at transaction time.

Reason:

```txt
Harga layanan bisa berubah di masa depan.
Invoice lama tidak boleh ikut berubah.
```

### 17.2 One Active Payment

A single order can have multiple payment attempts, but only one active payment.

Example:

```txt
Payment 1: QRIS pending → cancelled
Payment 2: QRIS expired
Payment 3: Cash paid → active
```

### 17.3 Webhook Conflict Handling

If order already paid via cash, but late QRIS webhook arrives:

```txt
Do not update order.
Store webhook as conflict.
Do not send duplicate WhatsApp receipt.
```

### 17.4 Outlet-based Query

Most operational queries must filter by outlet.

Example:

```sql
SELECT *
FROM orders
WHERE outlet_id IN (
  SELECT outlet_id
  FROM user_outlets
  WHERE user_id = ?
  AND is_active = true
);
```

### 17.5 Tracking Page Privacy

Tracking page should not expose sensitive data such as:

```txt
Full internal notes
Payment raw response
Webhook payload
Admin/staff data
```

---

## 18. Success Metrics

The product is successful if:

1. Kasir can create an order in under 1 minute.
2. Customer receives WhatsApp receipt automatically after payment.
3. QRIS payment status updates automatically via webhook.
4. Customer can track laundry status without contacting admin.
5. Owner can see daily revenue per outlet.
6. Staff can update laundry status easily.
7. Payment conflicts are handled safely.
8. Invoice data remains correct even after service price changes.

---

## 19. Open Questions

Questions to decide during implementation:

1. Should services be global then copied to outlet, or always outlet-specific?
2. Should customer data be global or outlet-specific?
3. Should order be allowed before payment?
4. Should unpaid orders still be processed?
5. Should WhatsApp be sent using Fonnte first or WhatsApp Cloud API?
6. Should QRIS use Midtrans Snap or Core API?
7. Should thermal printer support be included in MVP?
8. Should cancellation after paid require refund handling?

Recommended MVP decisions:

```txt
Services: outlet-specific
Customers: outlet-linked but searchable globally
Order before payment: allowed
Unpaid order processing: configurable, default not allowed
WhatsApp provider: Fonnte for MVP
QRIS: Midtrans Core API if custom POS UI is needed
Thermal printer: MVP 2
Paid cancellation: owner/admin only
```

---

## 20. Final Summary

POS Laundry Website ini dirancang untuk satu bisnis laundry yang memiliki satu atau banyak outlet. Aplikasi tidak menggunakan model SaaS. Jika ada owner lain, website akan di-clone dan dikonfigurasi ulang.

Core features:

```txt
Multi-outlet dalam satu website
Role owner/admin/cashier/staff
Customer management
Flexible laundry services
Order management
Cash payment
Dynamic QRIS Midtrans payment
Midtrans webhook
WhatsApp receipt
Public tracking page
Laundry status notification
Dashboard and reports
```

Database dirancang agar aman untuk kasus nyata:

```txt
Cash bisa dikonfirmasi kasir
QRIS hanya paid via webhook
QRIS pending bisa diganti cash
Webhook terlambat tidak merusak order
Satu order bisa punya payment history
Harga invoice tetap aman walau harga layanan berubah
User bisa dibatasi per outlet
```

Dengan PRD ini, aplikasi dapat langsung dijadikan acuan untuk membuat sistem POS Laundry berbasis Laravel, Inertia, React, dan Midtrans.
