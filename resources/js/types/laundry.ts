export type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

export type BusinessSetting = {
    id: number;
    business_name: string;
    business_slug: string | null;
    logo_path: string | null;
    favicon_path: string | null;
    owner_name: string | null;
    owner_phone: string | null;
    owner_email: string | null;
    default_phone: string | null;
    default_whatsapp_number: string | null;
    default_email: string | null;
    default_address: string | null;
    default_google_maps_url: string | null;
    timezone: string;
    currency: string;
    receipt_footer_text: string | null;
    terms_and_conditions: string | null;
    qris_expiry_minutes: number;
};

export type IntegrationSettings = {
    whatsapp_provider: string | null;
    whatsapp_api_key_masked: string | null;
    whatsapp_sender_number: string | null;
    midtrans_server_key_masked: string | null;
    midtrans_client_key_masked: string | null;
    midtrans_is_production: boolean;
    qris_expiry_minutes: number;
};

export type Outlet = {
    id: number;
    name: string;
    code: string | null;
    slug: string;
    phone: string | null;
    whatsapp_number: string | null;
    email: string | null;
    address: string | null;
    google_maps_url: string | null;
    is_main: boolean;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
};

export type ManagedUser = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    global_role: 'owner' | 'admin' | 'staff';
    is_active: boolean;
    last_login_at: string | null;
    user_outlets_count?: number;
    user_outlets?: UserOutlet[];
};

export type UserOutlet = {
    id: number;
    outlet_id: number;
    role: 'owner' | 'admin' | 'cashier' | 'staff';
    can_manage_orders: boolean;
    can_manage_payments: boolean;
    can_manage_services: boolean;
    can_manage_reports: boolean;
    can_manage_users: boolean;
    can_manage_settings: boolean;
    is_primary: boolean;
    is_active: boolean;
    outlet?: Outlet;
};
