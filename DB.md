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