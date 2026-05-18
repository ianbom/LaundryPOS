import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import {
    Bell,
    Building2,
    CalendarClock,
    ChevronDown,
    ChevronLeft,
    CircleDollarSign,
    ClipboardList,
    CreditCard,
    FileBarChart,
    FileText,
    HandCoins,
    History,
    LayoutDashboard,
    ListChecks,
    LogOut,
    Menu,
    MessageCircle,
    PackageCheck,
    Percent,
    Plus,
    Printer,
    ReceiptText,
    Search,
    Settings,
    ShieldCheck,
    Shirt,
    Store,
    Tags,
    Truck,
    User,
    UserCog,
    Users,
    WalletCards,
    WashingMachine,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import OutletSwitcher from '@/components/outlet-switcher';
import { dashboard } from '@/routes';
import { edit as appearanceEdit } from '@/routes/appearance';
import { index as customersIndex } from '@/routes/customers';
import { index as outletsIndex } from '@/routes/outlets';
import { index as serviceCategoriesIndex } from '@/routes/service-categories';
import { index as servicesIndex } from '@/routes/services';
import { edit as businessSettingsEdit } from '@/routes/settings/business';
import { edit as integrationsSettingsEdit } from '@/routes/settings/integrations';
import { index as whatsappTemplatesIndex } from '@/routes/settings/whatsapp-templates';
import { index as usersIndex } from '@/routes/users';
import type { AppLayoutProps } from '@/types';

type NavGroup = {
    label: string;
    items: {
        title: string;
        href?: NonNullable<InertiaLinkProps['href']>;
        icon: LucideIcon;
        active?: boolean;
    }[];
};

const navGroups: NavGroup[] = [
    {
        label: 'Main Menu',
        items: [
            {
                title: 'Dashboard',
                href: dashboard.url(),
                icon: LayoutDashboard,
                active: true,
            },
            { title: 'Create Order', icon: Plus },
            { title: 'Orders', icon: ClipboardList },
            { title: 'Customers', href: customersIndex.url(), icon: Users },
        ],
    },
    {
        label: 'Operations',
        items: [
            { title: 'Payments', icon: CreditCard },
            { title: 'Laundry Status', icon: WashingMachine },
            { title: 'Pickup Management', icon: PackageCheck },
            { title: 'Delivery Management', icon: Truck },
            { title: 'Order Timeline', icon: History },
            { title: 'Notifications', icon: Bell },
        ],
    },
    {
        label: 'Management',
        items: [
            { title: 'Services', href: servicesIndex.url(), icon: Shirt },
            {
                title: 'Service Categories',
                href: serviceCategoriesIndex.url(),
                icon: ListChecks,
            },
            { title: 'Price List', icon: Tags },
            { title: 'Discounts / Promotions', icon: Percent },
            {
                title: 'Outlets / Branches',
                href: outletsIndex.url(),
                icon: Building2,
            },
            { title: 'Staff / Users', href: usersIndex.url(), icon: UserCog },
            { title: 'Roles & Permissions', icon: ShieldCheck },
            {
                title: 'Customers Database',
                href: customersIndex.url(),
                icon: Users,
            },
        ],
    },
    {
        label: 'Reports',
        items: [
            { title: 'Sales Report', icon: FileBarChart },
            { title: 'Payment Report', icon: ReceiptText },
            { title: 'Order Report', icon: ClipboardList },
            { title: 'Customer Report', icon: Users },
            { title: 'Staff Performance', icon: UserCog },
            { title: 'Service Performance', icon: Shirt },
            { title: 'Expense Report', icon: CircleDollarSign },
            { title: 'Export Reports', icon: FileText },
        ],
    },
    {
        label: 'Finance',
        items: [
            { title: 'Transactions', icon: ReceiptText },
            { title: 'Cashier Session', icon: WalletCards },
            { title: 'Cash Flow', icon: HandCoins },
            { title: 'Daily Closing', icon: CalendarClock },
            { title: 'Refunds', icon: CreditCard },
        ],
    },
    {
        label: 'System',
        items: [
            {
                title: 'WhatsApp Templates',
                href: whatsappTemplatesIndex.url(),
                icon: MessageCircle,
            },
            { title: 'Invoice Settings', icon: Printer },
            {
                title: 'Payment Gateway Settings',
                href: integrationsSettingsEdit.url(),
                icon: CreditCard,
            },
            { title: 'Laundry Status Settings', icon: WashingMachine },
            {
                title: 'Business Profile',
                href: businessSettingsEdit.url(),
                icon: Store,
            },
            {
                title: 'General Settings',
                href: appearanceEdit.url(),
                icon: Settings,
            },
        ],
    },
    {
        label: 'Account',
        items: [
            { title: 'My Profile', icon: User },
            { title: 'Logout', icon: LogOut },
        ],
    },
];

function LogoMark() {
    return (
        <div className="grid size-9 shrink-0 place-items-center rounded-lg bg-[#2563eb] text-white shadow-[0_4px_10px_rgba(37,99,235,0.25)]">
            <WashingMachine className="size-5" strokeWidth={2} />
        </div>
    );
}

function SidebarNav() {
    return (
        <aside className="dashboard-sidebar">
            <div className="flex h-[42px] items-center gap-3 px-2">
                <LogoMark />
                <span className="text-[20px] leading-7 font-bold text-[#0f172a]">
                    Laundry POS
                </span>
            </div>

            <button className="mt-3 flex h-[54px] w-full items-center gap-2 rounded-[10px] border border-[#93c5fd] bg-[#f8fbff] px-2.5 text-left">
                <span className="grid size-8 shrink-0 place-items-center rounded-lg bg-[#2563eb] text-white">
                    <Store className="size-4" strokeWidth={2} />
                </span>
                <span className="min-w-0 flex-1">
                    <span className="block text-[11px] leading-4 font-medium text-[#64748b]">
                        Outlet
                    </span>
                    <span className="block truncate text-[13px] leading-[18px] font-bold text-[#0f172a]">
                        Central Surabaya
                    </span>
                </span>
                <ChevronDown
                    className="size-4 text-[#0f172a]"
                    strokeWidth={2}
                />
            </button>

            <nav className="mt-2 min-h-0 flex-1 overflow-y-auto pr-1">
                {navGroups.map((group) => (
                    <div key={group.label}>
                        <div className="mx-2 mt-[14px] mb-1.5 text-[10px] leading-[14px] font-bold tracking-[0.04em] text-[#64748b] uppercase">
                            {group.label}
                        </div>
                        <div className="grid gap-0.5">
                            {group.items.map((item) => {
                                const Icon = item.icon;

                                return (
                                    <Link
                                        key={item.title}
                                        href={item.href ?? dashboard.url()}
                                        prefetch={Boolean(item.href)}
                                        className={[
                                            'flex h-6 items-center gap-2 rounded-md px-2.5 text-[13px] leading-[18px] transition',
                                            item.active
                                                ? 'bg-[#dbeafe] font-bold text-[#2563eb]'
                                                : 'font-medium text-[#334155] hover:bg-[#f1f5f9] hover:text-[#0f172a]',
                                        ].join(' ')}
                                    >
                                        <Icon
                                            className={[
                                                'size-3.5 shrink-0',
                                                item.active
                                                    ? 'text-[#2563eb]'
                                                    : 'text-[#64748b]',
                                            ].join(' ')}
                                            strokeWidth={1.8}
                                        />
                                        <span className="truncate">
                                            {item.title}
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </nav>

            <button
                type="button"
                className="mt-3 flex h-11 items-center gap-3 rounded-xl border border-[#e2e8f0] bg-white px-3 text-[13px] font-semibold text-[#334155] transition hover:bg-[#f8fafc]"
            >
                <span className="grid size-7 place-items-center rounded-full border border-[#e2e8f0]">
                    <ChevronLeft className="size-4" strokeWidth={1.8} />
                </span>
                Collapse Menu
            </button>
        </aside>
    );
}

function Topbar() {
    return (
        <header className="dashboard-topbar">
            <div className="flex min-w-0 flex-1 items-center gap-5">
                <button
                    type="button"
                    className="grid size-9 shrink-0 place-items-center rounded-lg text-[#0f172a] transition hover:bg-[#f1f5f9] lg:hidden xl:grid"
                    aria-label="Open menu"
                >
                    <Menu className="size-[22px]" strokeWidth={2} />
                </button>

                <label className="relative hidden h-11 w-full max-w-[680px] items-center lg:flex">
                    <Search
                        className="pointer-events-none absolute left-4 size-[18px] text-[#64748b]"
                        strokeWidth={2}
                    />
                    <input
                        className="h-11 w-full rounded-xl border border-[#e2e8f0] bg-white pr-[78px] pl-11 text-sm text-[#0f172a] transition outline-none placeholder:text-[#94a3b8] focus:border-[#2563eb] focus:ring-3 focus:ring-[#2563eb]/12"
                        placeholder="Search invoice, customer name, or phone number..."
                        type="search"
                    />
                    <span className="absolute right-3 rounded-md border border-[#e2e8f0] bg-[#f1f5f9] px-2 py-0.5 text-[11px] font-semibold text-[#64748b]">
                        Ctrl + K
                    </span>
                </label>
            </div>

            <div className="flex shrink-0 items-center gap-5">
                <OutletSwitcher />

                <button
                    type="button"
                    className="flex h-[46px] items-center gap-2.5 rounded-[10px] bg-[#2563eb] px-[22px] text-[15px] font-bold text-white shadow-[0_4px_10px_rgba(37,99,235,0.25)] transition hover:bg-[#1d4ed8] active:translate-y-px"
                >
                    <Plus className="size-[18px]" strokeWidth={2} />
                    Create Order
                </button>

                <div className="hidden h-7 w-px bg-[#e2e8f0] md:block" />

                <button
                    type="button"
                    className="relative grid size-10 place-items-center rounded-lg text-[#0f172a] transition hover:bg-[#f1f5f9]"
                    aria-label="Notifications"
                >
                    <Bell className="size-5" strokeWidth={2} />
                    <span className="absolute top-0.5 right-0.5 grid size-[18px] place-items-center rounded-full bg-[#ef4444] text-[10px] leading-none font-bold text-white">
                        8
                    </span>
                </button>

                <button
                    type="button"
                    className="flex items-center gap-2 rounded-xl px-1.5 py-1 transition hover:bg-[#f8fafc]"
                >
                    <img
                        src="https://api.dicebear.com/9.x/adventurer/svg?seed=Admin%20Laundry&backgroundColor=b6e3f4"
                        alt="Admin Laundry"
                        className="size-10 rounded-full"
                    />
                    <span className="hidden text-left md:block">
                        <span className="block text-[13px] leading-[18px] font-bold text-[#0f172a]">
                            Admin Laundry
                        </span>
                        <span className="block text-xs leading-4 text-[#64748b]">
                            Super Admin
                        </span>
                    </span>
                    <ChevronDown
                        className="hidden size-4 text-[#0f172a] md:block"
                        strokeWidth={1.8}
                    />
                </button>
            </div>
        </header>
    );
}

export default function AppSidebarLayout({ children }: AppLayoutProps) {
    return (
        <div className="min-h-[100dvh] bg-[#f8fafc] font-sans text-[#0f172a]">
            <Link
                href="#main-content"
                className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-[#2563eb] focus:shadow"
            >
                Skip to content
            </Link>
            <SidebarNav />
            <div className="dashboard-main-shell">
                <Topbar />
                <main id="main-content" className="dashboard-main-content">
                    {children}
                </main>
            </div>
        </div>
    );
}
