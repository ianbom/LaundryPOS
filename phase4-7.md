# Implementation Plan — POS Laundry Website

## Scope

Dokumen ini berisi plan implementasi lengkap untuk:

- **Phase 4 — POS Transaction**
- **Phase 5 — Payment**
- **Phase 6 — WhatsApp & Tracking**
- **Phase 7 — Operations**

Aplikasi ini bukan SaaS. Satu website digunakan oleh satu bisnis laundry, tetapi bisnis tersebut dapat memiliki banyak outlet/cabang. Semua data operasional harus mengikuti `outlet_id` aktif yang dipilih user melalui outlet switcher.

WhatsApp notification menggunakan **Fonnte**.

---

# Global Development Rules

## 1. Tech Stack Assumption

Gunakan stack berikut jika tidak ada instruksi lain:

- Laravel
- Inertia.js
- React
- TypeScript
- Tailwind CSS
- shadcn/ui
- MySQL atau PostgreSQL
- Midtrans QRIS Dynamic Payment
- Fonnte WhatsApp API

## 2. Outlet Scope Rule

Semua data operasional wajib terikat ke outlet.

Data yang wajib memakai `outlet_id`:

- customers
- orders
- payments
- whatsapp_messages
- service_categories
- services
- service_variants
- activity_logs
- reports
- dashboard metrics

Rule akses:

- Owner dapat melihat semua outlet.
- Admin dapat melihat outlet yang diberikan akses.
- Cashier hanya melihat outlet yang diassign.
- Staff hanya melihat outlet yang diassign.

Setiap query order/customer/payment/report wajib difilter berdasarkan outlet yang dapat diakses user.

## 3. Payment Rule

Satu order dapat memiliki banyak payment history, tetapi hanya boleh ada satu payment aktif.

Rules:

- Cash payment dikonfirmasi manual oleh kasir.
- QRIS payment dibuat melalui Midtrans.
- QRIS hanya boleh dianggap `paid` dari webhook Midtrans yang valid.
- QRIS pending dapat diganti menjadi cash.
- QRIS expired dapat digenerate ulang.
- Jika webhook QRIS lama datang setelah order dibayar cash, webhook disimpan sebagai `conflict` dan tidak boleh mengubah status order.

## 4. WhatsApp Rule

WhatsApp dikirim menggunakan Fonnte.

Semua pesan WhatsApp wajib disimpan ke tabel `whatsapp_messages`.

Message status:

- `pending`
- `sent`
- `failed`
- `cancelled`

WhatsApp tidak boleh menghambat transaksi utama. Jika Fonnte gagal, transaksi tetap berhasil tetapi log pesan menjadi `failed`.

## 5. Public Page Rule

Halaman publik tidak membutuhkan login.

Halaman publik wajib menggunakan `tracking_token`, bukan `order_id`.

Public page tidak boleh menampilkan:

- internal notes
- raw response Midtrans
- payment webhook payload
- user/staff internal data
- activity logs internal

---

# Phase 4 — POS Transaction

---

# 18. POS Create Order Page

## Objective

Membuat halaman POS utama untuk kasir mencatat transaksi laundry pelanggan dengan cepat, memilih layanan, menghitung total, lalu menentukan metode pembayaran.

## Route

```txt
GET  /pos/orders/create
POST /pos/orders
```

## Page Name

```txt
resources/js/Pages/POS/Orders/Create.tsx
```

## Backend Controller

```txt
POSOrderController@index
POSOrderController@store
```

## Required Data

Halaman ini membutuhkan data:

- outlet aktif
- customer list/search endpoint
- active service categories
- active services
- active service variants
- business settings

## UI Sections

### A. Outlet Context

Tampilkan outlet aktif di bagian atas halaman.

Jika user punya lebih dari satu outlet, outlet dipilih dari outlet switcher global.

Data yang ditampilkan:

- nama outlet
- alamat outlet
- nomor WhatsApp outlet

### B. Customer Section

Fitur:

- Search customer berdasarkan nama/no HP/no WhatsApp.
- Pilih customer existing.
- Quick create customer jika belum ada.
- Input nama pelanggan.
- Input nomor WhatsApp.
- Input alamat opsional.
- Input catatan pelanggan opsional.

Field quick create:

```txt
name
phone
whatsapp_number
address
notes
```

Rule:

- `name` wajib.
- `phone` wajib.
- Jika `whatsapp_number` kosong, gunakan `phone` sebagai fallback.
- Customer baru otomatis tersimpan ke outlet aktif.

### C. Order Items Section

Fitur:

- Add item layanan.
- Pilih kategori layanan.
- Pilih service.
- Pilih service variant.
- Input quantity/berat/jumlah.
- Tampilkan unit.
- Tampilkan price.
- Tampilkan min quantity.
- Tampilkan charged quantity.
- Tampilkan subtotal.
- Tambahkan catatan per item.
- Hapus item.

Item dapat lebih dari satu dalam satu order.

Contoh:

```txt
1. Cuci Kering Setrika - 3x24 Jam - 5 kg
2. Bedcover Queen - 1 item
3. Extra Parfum - 1 item
```

### D. Order Summary Section

Tampilkan:

- subtotal
- discount amount
- additional fee
- delivery fee
- grand total
- customer notes
- internal notes

Field:

```txt
discount_amount
additional_fee
delivery_fee
customer_notes
internal_notes
```

### E. Payment Option Section

Setelah total dihitung, kasir dapat memilih:

```txt
1. Save as unpaid / belum bayar
2. Pay with cash
3. Generate QRIS
```

Tombol:

```txt
[Simpan Order]
[Bayar Cash]
[Generate QRIS]
```

## Store Order Flow

Ketika order disimpan:

1. Validate outlet access.
2. Validate customer.
3. Validate at least one order item.
4. Calculate item subtotal server-side.
5. Calculate order total server-side.
6. Generate invoice number.
7. Generate tracking token.
8. Insert row into `orders`.
9. Insert rows into `order_items`.
10. Insert initial `order_status_histories`.
11. Return order detail route.

## Invoice Number Format

Recommended format:

```txt
LDR-{OUTLET_CODE}-{YYYYMMDD}-{SEQUENCE}
```

Example:

```txt
LDR-SBY-20260518-0001
```

Rules:

- Invoice number unique per outlet.
- Sequence resets daily per outlet, or can be continuous per outlet.

## Tracking Token Format

Use secure random token.

Example:

```txt
trk_8f7a9c2d4b1e9a0f
```

Do not use sequential order ID.

## Validation

```txt
outlet_id: required, exists, accessible_by_current_user
customer_id: required or quick_create_customer_data required
items: required array min 1
items.*.service_variant_id: required exists active
items.*.quantity: required numeric min > 0
discount_amount: numeric min 0
additional_fee: numeric min 0
delivery_fee: numeric min 0
```

## Acceptance Criteria

- Kasir dapat membuat order dari halaman POS.
- Kasir dapat membuat customer baru secara cepat.
- Satu order dapat memiliki banyak item layanan.
- Harga item tersimpan sebagai snapshot di `order_items`.
- Total dihitung server-side, bukan hanya frontend.
- Order memiliki invoice number unik.
- Order memiliki tracking token unik.
- Order tersimpan dengan `payment_status = unpaid` jika belum dibayar.

---

# 19. Order Item Calculation

## Objective

Membuat logic perhitungan item order yang akurat untuk berbagai tipe layanan laundry.

## Service Class

```txt
OrderPricingService
```

## Main Methods

```txt
calculateItem(serviceVariant, quantity)
calculateOrderTotals(items, discountAmount, additionalFee, deliveryFee)
```

## Calculation Rule

Setiap `service_variant` memiliki:

```txt
price
unit
min_quantity
```

Perhitungan:

```txt
charged_quantity = max(quantity, min_quantity)
subtotal = charged_quantity * unit_price
```

Untuk `pricing_type = fixed`, quantity bisa tetap 1, tetapi tetap simpan quantity input.

## Example 1 — Kiloan Minimum Order

```txt
Service: Cuci Kering Setrika
Variant: 3x24 Jam
Price: Rp8.000/kg
Min Quantity: 3 kg
Quantity input: 2 kg

charged_quantity = 3
subtotal = 3 x 8000 = 24000
```

## Example 2 — Normal Kiloan

```txt
Price: Rp8.000/kg
Min Quantity: 3 kg
Quantity input: 5 kg

charged_quantity = 5
subtotal = 5 x 8000 = 40000
```

## Example 3 — Per Item

```txt
Service: Bedcover Queen
Price: Rp45.000/item
Quantity: 2

charged_quantity = 2
subtotal = 2 x 45000 = 90000
```

## Snapshot Fields

Saat menyimpan order item, wajib simpan snapshot:

```txt
service_name
variant_name
pricing_type
unit
unit_price
quantity
charged_quantity
subtotal
```

Tujuan:

- Invoice lama tidak berubah jika harga layanan berubah.
- Riwayat transaksi tetap valid.

## Grand Total Calculation

```txt
subtotal = sum(order_items.subtotal)
grand_total = subtotal - discount_amount + additional_fee + delivery_fee
```

Rule:

- `grand_total` tidak boleh kurang dari 0.
- Semua perhitungan dilakukan server-side.
- Frontend calculation hanya untuk preview.

## Acceptance Criteria

- Minimum order dihitung dengan benar.
- Subtotal item benar.
- Grand total benar.
- Harga order lama tidak berubah jika harga service diubah.

---

# 20. Order List

## Objective

Membuat halaman daftar order untuk monitoring transaksi laundry.

## Route

```txt
GET /orders
```

## Page Name

```txt
resources/js/Pages/Orders/Index.tsx
```

## Controller

```txt
OrderController@index
```

## Table Columns

```txt
Invoice Number
Customer Name
Customer Phone
Outlet
Order Date
Grand Total
Payment Method
Payment Status
Order Status
Created By
Actions
```

## Filters

```txt
search
outlet_id
order_status
payment_status
payment_method
date_from
date_to
created_by
```

Search should match:

- invoice number
- customer name
- customer phone
- customer WhatsApp

## Actions

```txt
View Detail
Update Status
Confirm Cash Payment
Generate QRIS
Send WhatsApp Receipt
Print Invoice
Cancel Order
```

Actions must depend on status.

### If `payment_status = unpaid`

Show:

```txt
[Bayar Cash]
[Generate QRIS]
[Detail]
[Cancel]
```

### If `payment_status = pending`

Show:

```txt
[Detail]
[Cek Pembayaran]
[Ganti ke Cash]
[Generate Ulang QRIS]
```

### If `payment_status = paid`

Show:

```txt
[Detail]
[Print Invoice]
[Kirim Ulang WA]
[Update Status]
```

## Query Rule

- Owner: can view all outlets or selected outlet.
- Admin/cashier/staff: only accessible outlet IDs.

## Acceptance Criteria

- Order list supports search and filters.
- User only sees orders from accessible outlets.
- Payment status and order status displayed with badges.
- Actions are hidden/disabled based on status and permission.

---

# 21. Order Detail

## Objective

Membuat halaman detail order lengkap sebagai pusat operasional transaksi.

## Route

```txt
GET /orders/{order}
```

## Page Name

```txt
resources/js/Pages/Orders/Show.tsx
```

## Controller

```txt
OrderController@show
```

## Sections

### A. Header Summary

Display:

```txt
invoice_number
order_status badge
payment_status badge
grand_total
order_date
estimated_done_at
```

### B. Customer Information

Display:

```txt
customer name
phone
whatsapp_number
address
notes
```

### C. Outlet Information

Display:

```txt
outlet name
outlet phone
outlet address
```

### D. Order Items

Display:

```txt
service_name
variant_name
pricing_type
quantity
charged_quantity
unit
unit_price
subtotal
notes
```

### E. Payment Information

Display active payment:

```txt
method
provider
status
amount
amount_paid
change_amount
paid_at
expired_at
```

If QRIS pending, display:

```txt
QRIS image / QRIS URL
amount
countdown expired
provider_order_id
```

### F. Payment History

Display all payments for order.

### G. Status Timeline

Display order status histories.

### H. WhatsApp Log

Display all WhatsApp messages related to order.

### I. Internal Notes

Only visible to authenticated dashboard users.

## Actions

```txt
Confirm Cash Payment
Generate QRIS
Regenerate QRIS
Change QRIS to Cash
Update Order Status
Send Payment Receipt WhatsApp
Send Ready Notification WhatsApp
Print Invoice
Open Public Tracking Page
Open Public Invoice Page
Cancel Order
```

## Acceptance Criteria

- Order detail displays complete operational data.
- QRIS pending section appears only when active payment is QRIS pending.
- Payment history appears clearly.
- Timeline status appears chronologically.
- WhatsApp logs appear with status sent/failed.

---

# 22. Invoice Page

## Objective

Membuat halaman invoice/receipt untuk dicetak atau dikirim ke pelanggan.

## Authenticated Route

```txt
GET /orders/{order}/invoice
```

## Page Name

```txt
resources/js/Pages/Orders/Invoice.tsx
```

## Invoice Content

```txt
business logo
business name
outlet name
outlet address
outlet phone
invoice number
order date
customer name
customer phone
customer address
order items
subtotal
discount amount
additional fee
delivery fee
grand total
payment method
payment status
paid at
tracking URL
receipt footer text
terms and conditions
```

## Actions

```txt
Print
Download PDF optional
Send WhatsApp Receipt
Back to Order Detail
```

## Design Requirement

Invoice should be clean, printable, and suitable for thermal or A4 print.

## Acceptance Criteria

- Invoice can be opened from order detail.
- Invoice displays snapshot item price.
- Invoice displays payment method cash/QRIS.
- Invoice can be printed by browser.

---

# Phase 5 — Payment

---

# 23. Cash Payment Flow

## Objective

Menghandle pembayaran cash/tunai yang dikonfirmasi manual oleh kasir.

## Route

```txt
POST /orders/{order}/payments/cash
```

## Controller

```txt
PaymentController@payCash
```

## Request Fields

```txt
amount_paid
notes optional
```

## UI

Use modal from order detail or POS create page.

Display:

```txt
Grand total
Input amount paid
Calculated change amount
Confirm button
```

## Backend Flow

1. Validate user has payment permission for order outlet.
2. Lock order row to prevent race condition.
3. Check order is not already paid.
4. Validate `amount_paid >= order.grand_total`.
5. If order has active QRIS pending payment:
   - update old payment status to `cancelled`
   - set `is_active = false`
   - set `cancelled_at = now()`
6. Create new payment:

```txt
provider = manual
method = cash
status = paid
is_active = true
amount = order.grand_total
amount_paid = request.amount_paid
change_amount = amount_paid - amount
paid_at = now()
confirmed_by = current_user.id
```

7. Update order:

```txt
active_payment_id = payment.id
payment_status = paid
order_status = received or processing
```

8. Insert order status history if order status changes.
9. Write activity log.
10. Dispatch WhatsApp payment receipt job.
11. Return updated order.

## Race Condition Protection

Use database transaction and row lock.

Pseudo:

```php
DB::transaction(function () use ($orderId) {
    $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
    // payment logic
});
```

## Acceptance Criteria

- Cash payment can mark order as paid.
- Change amount is calculated correctly.
- Existing pending QRIS is cancelled when cash is confirmed.
- Payment receipt is sent through Fonnte.
- Payment history records cash payment.

---

# 24. Midtrans QRIS Generate Flow

## Objective

Generate QRIS dinamis Midtrans sesuai nominal order.

## Route

```txt
POST /orders/{order}/payments/qris
```

## Controller

```txt
PaymentController@generateQris
```

## Service Class

```txt
MidtransPaymentService
```

## Backend Flow

1. Validate user has payment permission for order outlet.
2. Lock order row.
3. Check order is not already paid.
4. Check business settings has Midtrans server key.
5. If there is active pending payment:
   - cancel it or mark as expired depending on status.
   - set `is_active = false`.
6. Generate unique `provider_order_id`.
7. Create QRIS transaction to Midtrans.
8. Store payment:

```txt
outlet_id = order.outlet_id
order_id = order.id
provider = midtrans
method = qris
status = pending
is_active = true
amount = order.grand_total
provider_order_id = generated_provider_order_id
provider_transaction_id = response.transaction_id if available
qris_url = QR image/action URL from response
qris_string = QR content if available
payment_url = payment link if available
expired_at = now + qris_expiry_minutes
raw_response = response JSON
```

9. Update order:

```txt
active_payment_id = payment.id
payment_status = pending
order_status = waiting_payment
```

10. Write activity log.
11. Return QRIS data to frontend.

## Midtrans Configuration

Use settings from `business_settings`:

```txt
midtrans_server_key
midtrans_client_key
midtrans_is_production
qris_expiry_minutes
```

## Frontend QRIS Section

Display:

```txt
QR Code image
Amount
Invoice number
Expired countdown
Payment status pending
Button: Check Payment Status
Button: Regenerate QRIS
Button: Change to Cash
```

## Acceptance Criteria

- QRIS is generated with exact order grand total.
- QRIS payment is saved as pending.
- Order payment status becomes pending.
- Previous pending QRIS is deactivated before new QRIS is created.
- QRIS expired time follows business settings.

---

# 25. Midtrans Webhook Handler

## Objective

Menerima notifikasi pembayaran dari Midtrans dan mengubah status QRIS menjadi paid secara otomatis.

## Route

```txt
POST /webhooks/midtrans
```

## Controller

```txt
MidtransWebhookController@handle
```

## Security

Webhook handler must validate Midtrans signature key.

Signature validation should compare payload signature with calculated hash based on Midtrans rule.

## Backend Flow

1. Receive raw payload.
2. Extract:

```txt
order_id / provider_order_id
transaction_id
transaction_status
fraud_status
payment_type
gross_amount
signature_key
```

3. Find payment by:

```txt
provider_order_id
or provider_transaction_id
```

4. Create row in `payment_webhooks`:

```txt
payment_id
order_id
provider = midtrans
provider_order_id
provider_transaction_id
event_type
transaction_status
fraud_status
payment_type
gross_amount
signature_key
is_valid_signature
is_processed
process_status
process_message
raw_payload
created_at
```

5. If signature invalid:
   - mark webhook `failed`
   - do not update payment/order

6. If payment not found:
   - mark webhook `ignored`
   - save message

7. If duplicate webhook:
   - mark webhook `duplicate`
   - do not process again

8. Lock related order.

9. Validate amount:

```txt
gross_amount == payment.amount
```

10. Validate active payment:

```txt
payment.is_active == true
payment.status == pending
order.active_payment_id == payment.id
```

11. If order already paid via another method:
    - mark webhook `conflict`
    - do not update order

12. If transaction status is successful:

Midtrans successful status usually includes:

```txt
settlement
capture with acceptable fraud status
```

Then update:

```txt
payments.status = paid
payments.paid_at = now()
orders.payment_status = paid
orders.order_status = received or processing
orders.active_payment_id = payment.id
```

13. Insert order status history if order status changes.
14. Mark webhook processed.
15. Write activity log.
16. Dispatch WhatsApp payment receipt job.

## Failed/Expired Handling

If Midtrans status indicates expire/cancel/deny/failure:

Update payment accordingly:

```txt
expire -> expired
cancel -> cancelled
deny -> failed
failure -> failed
```

Update order payment_status if the failed payment is active.

## Acceptance Criteria

- Valid Midtrans webhook marks QRIS payment as paid.
- Invalid signature is rejected and logged.
- Duplicate webhook does not duplicate process.
- Webhook after cash payment is marked as conflict.
- Payment receipt is sent after successful QRIS payment.

---

# 26. QRIS Expiry Scheduler

## Objective

Mengubah status QRIS pending menjadi expired jika melewati waktu expired.

## Artisan Command

```txt
php artisan payments:expire-pending-qris
```

## Scheduler

Run every minute:

```php
$schedule->command('payments:expire-pending-qris')->everyMinute();
```

## Backend Flow

1. Find payments:

```txt
provider = midtrans
method = qris
status = pending
expired_at < now()
is_active = true
```

2. For each payment:
   - lock payment/order
   - update payment status = expired
   - set is_active = false

3. If order active payment is this payment:

```txt
orders.payment_status = expired
orders.active_payment_id = null
```

4. Write activity log.

## Acceptance Criteria

- Expired QRIS becomes expired automatically.
- Order no longer has active payment after QRIS expired.
- Kasir can generate new QRIS after expiry.

---

# 27. Change QRIS to Cash Flow

## Objective

Menghandle kondisi pelanggan awalnya memilih QRIS tetapi ingin membayar cash.

## Route

```txt
POST /orders/{order}/payments/change-qris-to-cash
```

Alternative implementation:

- Use Cash Payment Flow directly.
- Cash flow detects active QRIS pending and cancels it.

## Recommended Implementation

Use Cash Payment Flow as the main implementation.

When kasir confirms cash payment:

1. Detect active QRIS pending.
2. Cancel QRIS.
3. Create cash payment.
4. Mark order paid.

## UI

On order detail QRIS section:

```txt
Button: Ganti ke Cash
```

When clicked:

- open cash payment modal
- show warning:

```txt
QRIS pending saat ini akan dibatalkan dan order akan dibayar menggunakan cash.
```

## Acceptance Criteria

- Pending QRIS can be replaced by cash.
- Old QRIS payment remains in payment history as cancelled.
- New cash payment becomes active payment.
- If old QRIS webhook arrives later, it is marked conflict.

---

# 28. Payment History Display

## Objective

Menampilkan semua riwayat pembayaran pada detail order.

## Location

```txt
/orders/{order}
```

## Data Displayed

```txt
payment id
provider
method
status
is_active
amount
amount_paid
change_amount
provider_order_id
provider_transaction_id
expired_at
paid_at
cancelled_at
confirmed_by
created_at
```

## UI Rules

- Active payment gets highlighted.
- Paid payment uses green badge.
- Pending payment uses yellow badge.
- Expired payment uses orange badge.
- Cancelled payment uses red/gray badge.
- Conflict payment uses purple badge.

## Acceptance Criteria

- Admin can see full payment history.
- Kasir can understand which payment is active.
- QRIS cancelled/expired history is not deleted.

---

# Phase 6 — WhatsApp & Tracking

---

# 29. WhatsApp Service Using Fonnte

## Objective

Membuat service untuk mengirim WhatsApp melalui Fonnte dan mencatat semua log pesan.

## Service Class

```txt
FonnteWhatsAppService
```

## Config Source

Read from `business_settings`:

```txt
whatsapp_provider = fonnte
whatsapp_api_key
whatsapp_sender_number optional
```

## Fonnte Endpoint

Use Fonnte send message endpoint based on Fonnte API documentation used in the project.

Implementation should support:

```txt
target phone number
message body
optional delay/schedule if needed later
```

## Main Methods

```txt
sendMessage(phone, message, metadata = [])
sendPaymentReceipt(order)
sendOrderReady(order)
sendOrderCompleted(order)
sendCustomOrderMessage(order, message)
```

## Standard Flow

1. Create `whatsapp_messages` row with status `pending`.
2. Send request to Fonnte.
3. If success:
   - update status `sent`
   - set `sent_at`
   - save provider response
4. If failed:
   - update status `failed`
   - save error message
   - save raw response if available

## Phone Normalization

Create helper:

```txt
normalizeIndonesianPhoneNumber(phone)
```

Rules:

```txt
08123456789 -> 628123456789
+628123456789 -> 628123456789
628123456789 -> 628123456789
```

## Error Handling

WhatsApp failure must not rollback successful payment or order status update.

Use queued job if possible:

```txt
SendWhatsAppMessageJob
```

## Acceptance Criteria

- WhatsApp messages are sent through Fonnte.
- All messages are logged.
- Failed messages are logged with error message.
- Payment/order process does not fail when WhatsApp fails.

---

# 30. Send Payment Receipt

## Objective

Mengirim bukti pembayaran otomatis setelah order lunas, baik cash maupun QRIS.

## Trigger

Send receipt after:

```txt
cash payment status paid
qris payment webhook status paid
```

## Message Type

```txt
payment_receipt
```

## Template Variables

```txt
{customer_name}
{invoice_number}
{grand_total}
{payment_method}
{payment_status}
{paid_at}
{tracking_url}
{outlet_name}
{outlet_phone}
{business_name}
```

## Default Template

```txt
Halo {customer_name}, pembayaran laundry kamu berhasil.

Invoice: {invoice_number}
Total: {grand_total}
Metode Pembayaran: {payment_method}
Status: Lunas

Tracking laundry:
{tracking_url}

Terima kasih sudah menggunakan layanan {business_name}.
```

## Flow

1. Ensure order payment_status is paid.
2. Get customer WhatsApp number or phone.
3. Get active payment.
4. Get template from `whatsapp_templates`.
5. Replace variables.
6. Send via Fonnte.
7. Save log in `whatsapp_messages`.

## Manual Resend

Provide button on order detail:

```txt
Kirim Ulang Bukti Pembayaran
```

## Acceptance Criteria

- Cash payment sends receipt.
- QRIS webhook paid sends receipt.
- Receipt contains tracking URL.
- Admin can resend receipt manually.

---

# 31. Send Order Ready Notification

## Objective

Mengirim WhatsApp ketika laundry siap diambil.

## Trigger

When `order_status` changes to:

```txt
ready_to_pickup
```

Optional trigger:

```txt
completed
```

## Message Type

```txt
order_ready
```

## Template Variables

```txt
{customer_name}
{invoice_number}
{order_status}
{tracking_url}
{outlet_name}
{outlet_phone}
{business_name}
```

## Default Template

```txt
Halo {customer_name}, laundry kamu sudah selesai dan siap diambil.

Invoice: {invoice_number}
Status: Siap Diambil

Tracking laundry:
{tracking_url}

Silakan ambil di outlet {outlet_name}.
Terima kasih.
```

## Flow

1. Admin/staff updates order status.
2. System inserts order_status_histories.
3. If new status = ready_to_pickup:
   - Dispatch WhatsApp order ready job.
4. Save message log.

## Prevent Duplicate Message

Recommended rule:

- Do not automatically send duplicate `order_ready` message more than once for the same order unless user clicks resend manually.

Check `whatsapp_messages`:

```txt
order_id = current order
message_type = order_ready
status = sent
```

If exists, skip auto send.

## Acceptance Criteria

- Customer gets WA when laundry is ready.
- Message contains tracking URL.
- Duplicate automatic notification is prevented.
- Admin can manually resend notification.

---

# 32. Public Tracking Page

## Objective

Membuat halaman publik agar pelanggan dapat melacak status laundry tanpa login.

## Route

```txt
GET /track/{tracking_token}
```

## Controller

```txt
PublicTrackingController@show
```

## Page Name

```txt
resources/js/Pages/Public/Tracking.tsx
```

## Display Data

```txt
business name
business logo
outlet name
outlet address
outlet WhatsApp
invoice number
customer name
order date
estimated done date
order status
payment status
order items
grand total
status timeline
```

## Status Timeline

Timeline comes from `order_status_histories`.

Display example:

```txt
Order diterima
Sedang diproses
Sedang dicuci
Sedang disetrika
Siap diambil
Selesai
```

## Public Safety Rules

Do not display:

```txt
internal_notes
activity_logs
raw payment response
payment webhook payload
staff name if sensitive
customer full address optional, depending on privacy preference
```

## UI Requirement

Public page should be mobile-first because customers will open from WhatsApp.

Sections:

```txt
Hero status card
Invoice summary
Order progress timeline
Order items
Payment summary
Outlet contact card
CTA WhatsApp outlet
```

## Acceptance Criteria

- Public tracking can be opened without login.
- Tracking uses secure token.
- Page is mobile-friendly.
- Customer can see current laundry status.
- Customer can contact outlet through WhatsApp CTA.

---

# 33. Public Invoice Page

## Objective

Membuat invoice publik yang bisa dibuka pelanggan dari WhatsApp.

## Route

```txt
GET /public/invoice/{tracking_token}
```

## Controller

```txt
PublicInvoiceController@show
```

## Page Name

```txt
resources/js/Pages/Public/Invoice.tsx
```

## Display Data

```txt
business logo
business name
outlet name
outlet address
outlet phone
invoice number
customer name
order date
order items
subtotal
discount amount
additional fee
delivery fee
grand total
payment method
payment status
paid at
tracking URL
receipt footer
terms and conditions
```

## Actions

```txt
Print invoice
Open tracking page
Contact outlet via WhatsApp
```

## Acceptance Criteria

- Public invoice can be opened without login.
- Public invoice uses tracking token.
- Invoice displays payment method cash/QRIS.
- Invoice is printable.

---

# Phase 7 — Operations

---

# 34. Order Status Update

## Objective

Membuat fitur update progress laundry dari dashboard.

## Route

```txt
PATCH /orders/{order}/status
```

## Controller

```txt
OrderStatusController@update
```

## Allowed Statuses

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

## Recommended MVP Statuses

```txt
waiting_payment
received
processing
ready_to_pickup
completed
cancelled
```

## Request Fields

```txt
status
notes optional
```

## Flow

1. Validate user can manage order for outlet.
2. Validate status transition.
3. Lock order.
4. Store old status.
5. Update new status.
6. If new status = completed, set `completed_at`.
7. If new status = cancelled, set `cancelled_at`.
8. Insert row into `order_status_histories`.
9. Write activity log.
10. If new status = ready_to_pickup, send Fonnte notification.

## Status Transition Rule

Basic allowed flow:

```txt
waiting_payment -> received
received -> processing
processing -> washing
washing -> drying
drying -> ironing
ironing -> ready_to_pickup
ready_to_pickup -> completed
```

Simplified flow:

```txt
waiting_payment -> received -> processing -> ready_to_pickup -> completed
```

Owner/admin can override status if needed.

Cashier/staff should follow allowed forward transitions.

## Acceptance Criteria

- Staff can update laundry progress.
- Status history is created every time status changes.
- Ready to pickup sends WhatsApp notification via Fonnte.
- Completed order gets completed_at timestamp.

---

# 35. Status History Timeline

## Objective

Menampilkan timeline perubahan status pada order detail dan public tracking page.

## Data Source

```txt
order_status_histories
```

## Dashboard Timeline Display

Show:

```txt
new_status
old_status
changed_by
notes
created_at
```

## Public Timeline Display

Show:

```txt
new_status
created_at
notes if public-safe
```

Do not show internal staff information if not needed.

## Timeline Sorting

Sort ascending by `created_at`.

## Acceptance Criteria

- Order detail shows full internal timeline.
- Public tracking shows customer-friendly timeline.
- Timeline updates after every status change.

---

# 36. Dashboard

## Objective

Membuat dashboard ringkasan operasional dan performa bisnis laundry.

## Route

```txt
GET /dashboard
```

## Controller

```txt
DashboardController@index
```

## Filters

```txt
outlet_id
date_range
```

Default:

```txt
today
selected outlet
```

Owner can choose all outlets.

## KPI Cards

```txt
Revenue Today
Orders Today
Pending Payment Orders
Processing Orders
Ready to Pickup Orders
Completed Orders Today
Cash Revenue Today
QRIS Revenue Today
```

## Charts

### A. Revenue Chart

Show revenue for last 7 days or last 30 days.

Data source:

```txt
orders with payment_status = paid
```

### B. Order Status Distribution

Show count by order status.

### C. Payment Method Distribution

Show cash vs QRIS.

### D. Top Services

Show top service by quantity/revenue.

Data source:

```txt
order_items
orders.payment_status = paid
```

## Recent Orders

Show latest 10 orders:

```txt
invoice
customer
grand_total
payment_status
order_status
created_at
```

## Acceptance Criteria

- Dashboard metrics are filtered by outlet access.
- Owner can see all outlets or specific outlet.
- Cashier only sees assigned outlet data.
- Revenue only counts paid orders.

---

# 37. Reports

## Objective

Membuat laporan transaksi, pendapatan, layanan, dan pelanggan.

## Routes

```txt
GET /reports/transactions
GET /reports/revenue
GET /reports/services
GET /reports/customers
```

## A. Transaction Report

### Filters

```txt
outlet_id
date_from
date_to
payment_method
payment_status
order_status
created_by
```

### Columns

```txt
invoice number
outlet
customer
phone
order date
payment method
payment status
order status
subtotal
discount
additional fee
delivery fee
grand total
paid at
created by
```

### Export

```txt
CSV
Excel optional
```

## B. Revenue Report

### Metrics

```txt
total revenue
total cash revenue
total qris revenue
total orders paid
average order value
```

### Grouping

```txt
daily
weekly
monthly
```

## C. Service Report

### Metrics

```txt
service_name
variant_name
total_quantity
total_charged_quantity
total_orders
total_revenue
```

### Use Cases

- Know most popular services.
- Know top revenue service.

## D. Customer Report

### Metrics

```txt
customer name
phone
total orders
total spent
last order date
```

## Permission

Only users with `can_manage_reports` or owner/admin can access reports.

## Acceptance Criteria

- Reports can be filtered by date and outlet.
- Reports respect outlet access permission.
- Transaction report can be exported.
- Revenue only counts paid orders.

---

# 38. Activity Logs

## Objective

Mencatat aktivitas penting untuk audit dan debugging.

## Table

```txt
activity_logs
```

## Service Class

```txt
ActivityLogService
```

## Events to Log

```txt
user_login
business_settings_updated
integration_settings_updated
outlet_created
outlet_updated
outlet_deleted
user_created
user_updated
user_outlet_assigned
customer_created
customer_updated
service_category_created
service_created
service_variant_created
order_created
order_updated
order_cancelled
cash_payment_confirmed
qris_generated
qris_expired
qris_changed_to_cash
midtrans_webhook_received
midtrans_webhook_processed
midtrans_webhook_conflict
order_status_updated
whatsapp_sent
whatsapp_failed
invoice_printed optional
report_exported optional
```

## Log Fields

```txt
outlet_id
user_id
subject_type
subject_id
action
description
old_values
new_values
ip_address
user_agent
created_at
```

## UI Page

```txt
GET /activity-logs
```

## Filters

```txt
outlet_id
user_id
action
subject_type
date_from
date_to
```

## Acceptance Criteria

- Important business actions are logged.
- Owner can see logs.
- Logs can be filtered.
- Logs include old and new values where relevant.

---

# Recommended Implementation Order for Phase 4–7

Use this exact order for AI Agent execution.

## Phase 4 — POS Transaction

```txt
1. Build OrderPricingService.
2. Build invoice number generator.
3. Build tracking token generator.
4. Build POS create order page.
5. Build quick customer creation inside POS page.
6. Build order item selection and calculation preview.
7. Build order store endpoint with server-side calculation.
8. Build order list page.
9. Build order detail page.
10. Build invoice page.
```

## Phase 5 — Payment

```txt
11. Build cash payment modal and endpoint.
12. Build PaymentService for cash payment.
13. Build MidtransPaymentService.
14. Build QRIS generate endpoint.
15. Build QRIS display section on order detail.
16. Build Midtrans webhook endpoint.
17. Build payment_webhooks logging.
18. Build webhook signature validation.
19. Build duplicate/conflict webhook handling.
20. Build QRIS expiry artisan command.
21. Register QRIS expiry scheduler.
22. Build change QRIS to cash flow.
23. Build payment history display.
```

## Phase 6 — WhatsApp & Tracking

```txt
24. Build phone number normalization helper.
25. Build FonnteWhatsAppService.
26. Build SendWhatsAppMessageJob.
27. Build template variable replacement service.
28. Build send payment receipt flow.
29. Build resend receipt button.
30. Build send order ready notification flow.
31. Build public tracking page.
32. Build public invoice page.
```

## Phase 7 — Operations

```txt
33. Build order status update endpoint.
34. Build status update modal/select UI.
35. Build status transition validation.
36. Build status history timeline on order detail.
37. Build public customer-friendly timeline.
38. Build dashboard metrics service.
39. Build dashboard page.
40. Build transaction report.
41. Build revenue report.
42. Build service report.
43. Build customer report.
44. Build export CSV for transaction report.
45. Build ActivityLogService.
46. Add activity logs to all important actions.
47. Build activity logs page.
```

---

# AI Agent Prompt

Use this prompt for implementation.

```txt
Continue building the POS Laundry web application based on the existing PRD and database design.

Implement Phase 4 to Phase 7 only:

Phase 4 — POS Transaction:
18. POS create order page
19. Order item calculation
20. Order list
21. Order detail
22. Invoice page

Phase 5 — Payment:
23. Cash payment flow
24. Midtrans QRIS generate flow
25. Midtrans webhook handler
26. QRIS expiry scheduler
27. Change QRIS to cash flow
28. Payment history display

Phase 6 — WhatsApp & Tracking:
29. WhatsApp service using Fonnte
30. Send payment receipt
31. Send order ready notification
32. Public tracking page
33. Public invoice page

Phase 7 — Operations:
34. Order status update
35. Status history timeline
36. Dashboard
37. Reports
38. Activity logs

Important rules:
- This is not SaaS.
- One website is used by one laundry business.
- One business can have multiple outlets.
- Operational data must be scoped by outlet_id.
- User access must respect user_outlets permissions.
- Cash payment is confirmed manually by cashier.
- QRIS payment is generated dynamically by Midtrans.
- QRIS payment can only become paid from a valid Midtrans webhook.
- One order can have many payment histories but only one active payment.
- If QRIS pending is changed to cash, cancel the QRIS payment and create a new cash payment.
- If Midtrans webhook arrives after the order is already paid by cash, store it as conflict and do not update the order.
- WhatsApp messages must be sent using Fonnte.
- All WhatsApp messages must be logged in whatsapp_messages.
- WhatsApp failure must not rollback payment or order updates.
- Public tracking and invoice pages must use tracking_token, not order id.
- Public pages must not expose internal notes, raw payment responses, webhook payloads, or activity logs.

Build everything incrementally and make sure each module has validation, permission checks, activity logs, and clear UI states.
```

---

# Final Acceptance Checklist

## POS Transaction

```txt
[ ] Kasir can create order.
[ ] Kasir can quick create customer.
[ ] Kasir can add multiple laundry items.
[ ] Minimum quantity is calculated correctly.
[ ] Order totals are calculated server-side.
[ ] Order list supports filters.
[ ] Order detail shows items, payment, timeline, WhatsApp logs.
[ ] Invoice page is printable.
```

## Payment

```txt
[ ] Cash payment works.
[ ] Change amount is calculated.
[ ] QRIS dynamic payment can be generated.
[ ] QRIS pending can expire.
[ ] QRIS pending can be changed to cash.
[ ] Midtrans webhook can mark payment as paid.
[ ] Duplicate webhook is handled.
[ ] Conflict webhook is handled.
[ ] Payment history is visible.
```

## WhatsApp & Tracking

```txt
[ ] Fonnte service is implemented.
[ ] Payment receipt is sent after cash paid.
[ ] Payment receipt is sent after QRIS webhook paid.
[ ] Order ready notification is sent.
[ ] WhatsApp logs are stored.
[ ] Public tracking page works.
[ ] Public invoice page works.
```

## Operations

```txt
[ ] Order status can be updated.
[ ] Status timeline is shown.
[ ] Dashboard metrics are shown.
[ ] Reports are available.
[ ] Activity logs are recorded.
[ ] Outlet permission is enforced.
```
