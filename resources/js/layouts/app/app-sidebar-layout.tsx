import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import {
    Bell,
    BarChart3,
    Building2,
    ChevronDown,
    ChevronLeft,
    ClipboardList,
    CreditCard,
    FileText,
    History,
    LayoutDashboard,
    ListChecks,
    Menu,
    MessageCircle,
    Plus,
    ReceiptText,
    Search,
    Settings,
    ShieldCheck,
    Shirt,
    Store,
    User,
    UserCog,
    Users,
    WashingMachine,
    X,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import ActivityLogController from '@/actions/App/Http/Controllers/ActivityLogController';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import DashboardController from '@/actions/App/Http/Controllers/DashboardController';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import OutletController from '@/actions/App/Http/Controllers/OutletController';
import POSOrderController from '@/actions/App/Http/Controllers/POSOrderController';
import ReportController from '@/actions/App/Http/Controllers/ReportController';
import ServiceCategoryController from '@/actions/App/Http/Controllers/ServiceCategoryController';
import ServiceController from '@/actions/App/Http/Controllers/ServiceController';
import ServiceCopyController from '@/actions/App/Http/Controllers/ServiceCopyController';
import UserController from '@/actions/App/Http/Controllers/UserController';
import OutletSwitcher from '@/components/outlet-switcher';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { edit as appearanceEdit } from '@/routes/appearance';
import { edit as profileEdit } from '@/routes/profile';
import { edit as securityEdit } from '@/routes/security';
import { edit as businessSettingsEdit } from '@/routes/settings/business';
import { edit as integrationsSettingsEdit } from '@/routes/settings/integrations';
import { index as whatsappTemplatesIndex } from '@/routes/settings/whatsapp-templates';
import type { AppLayoutProps } from '@/types';

type NavGroup = {
    label: string;
    items: {
        title: string;
        href: NonNullable<InertiaLinkProps['href']>;
        icon: LucideIcon;
    }[];
};

const navGroups: NavGroup[] = [
    {
        label: 'Main Menu',
        items: [
            {
                title: 'Dashboard',
                href: DashboardController.index.url(),
                icon: LayoutDashboard,
            },
            {
                title: 'Create Order',
                href: POSOrderController.index.url(),
                icon: Plus,
            },
            {
                title: 'Orders',
                href: OrderController.index.url(),
                icon: ClipboardList,
            },
            {
                title: 'Customers',
                href: CustomerController.index.url(),
                icon: Users,
            },
        ],
    },
    {
        label: 'Management',
        items: [
            {
                title: 'Outlets',
                href: OutletController.index.url(),
                icon: Building2,
            },
            {
                title: 'Users',
                href: UserController.index.url(),
                icon: UserCog,
            },
            {
                title: 'Service Categories',
                href: ServiceCategoryController.index.url(),
                icon: ListChecks,
            },
            {
                title: 'Services',
                href: ServiceController.index.url(),
                icon: Shirt,
            },
            {
                title: 'Copy Services',
                href: ServiceCopyController.create.url(),
                icon: FileText,
            },
        ],
    },
    {
        label: 'Reports',
        items: [
            {
                title: 'Transactions',
                href: ReportController.transactions.url(),
                icon: ReceiptText,
            },
            {
                title: 'Revenue',
                href: ReportController.revenue.url(),
                icon: BarChart3,
            },
            {
                title: 'Services',
                href: ReportController.services.url(),
                icon: Shirt,
            },
            {
                title: 'Customers',
                href: ReportController.customers.url(),
                icon: Users,
            },
            {
                title: 'Activity Logs',
                href: ActivityLogController.index.url(),
                icon: History,
            },
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
            {
                title: 'Payment Gateway',
                href: integrationsSettingsEdit.url(),
                icon: CreditCard,
            },
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
            {
                title: 'My Profile',
                href: profileEdit.url(),
                icon: User,
            },
            {
                title: 'Security',
                href: securityEdit.url(),
                icon: ShieldCheck,
            },
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

function SidebarNav({
    mobile = false,
    onNavigate,
}: {
    mobile?: boolean;
    onNavigate?: () => void;
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <aside
            className={
                mobile
                    ? 'flex h-full min-h-0 w-full flex-col bg-white p-3 sm:p-4'
                    : 'dashboard-sidebar'
            }
        >
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
                                const isActive = isCurrentUrl(item.href);

                                return (
                                    <Link
                                        key={item.title}
                                        href={item.href}
                                        prefetch
                                        onClick={onNavigate}
                                        className={[
                                            'flex h-6 items-center gap-2 rounded-md px-2.5 text-[13px] leading-[18px] transition',
                                            isActive
                                                ? 'bg-[#dbeafe] font-bold text-[#2563eb]'
                                                : 'font-medium text-[#334155] hover:bg-[#f1f5f9] hover:text-[#0f172a]',
                                        ].join(' ')}
                                    >
                                        <Icon
                                            className={[
                                                'size-3.5 shrink-0',
                                                isActive
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
                onClick={onNavigate}
                className="mt-3 flex h-11 items-center gap-3 rounded-xl border border-[#e2e8f0] bg-white px-3 text-[13px] font-semibold text-[#334155] transition hover:bg-[#f8fafc] lg:hidden"
            >
                <span className="grid size-7 place-items-center rounded-full border border-[#e2e8f0]">
                    <ChevronLeft className="size-4" strokeWidth={1.8} />
                </span>
                Close Menu
            </button>
        </aside>
    );
}

function MobileSidebar({
    open,
    onClose,
}: {
    open: boolean;
    onClose: () => void;
}) {
    useEffect(() => {
        if (!open) {
            return;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = '';
        };
    }, [open, onClose]);

    return (
        <div
            className={[
                'fixed inset-0 z-50 lg:hidden',
                open ? 'pointer-events-auto' : 'pointer-events-none',
            ].join(' ')}
            aria-hidden={!open}
        >
            <button
                type="button"
                aria-label="Close menu"
                onClick={onClose}
                className={[
                    'absolute inset-0 bg-slate-950/40 transition-opacity',
                    open ? 'opacity-100' : 'opacity-0',
                ].join(' ')}
            />
            <div
                className={[
                    'absolute top-0 bottom-0 left-0 w-[min(86vw,320px)] overflow-hidden border-r border-[#e2e8f0] bg-white shadow-[0_20px_60px_rgba(15,23,42,0.24)] transition-transform duration-200 ease-out',
                    open ? 'translate-x-0' : '-translate-x-full',
                ].join(' ')}
                role="dialog"
                aria-modal="true"
                aria-label="Main navigation"
            >
                <div className="absolute top-3 right-3 z-10">
                    <button
                        type="button"
                        onClick={onClose}
                        className="grid size-9 place-items-center rounded-lg border border-[#e2e8f0] bg-white text-[#0f172a] shadow-sm transition hover:bg-[#f8fafc]"
                        aria-label="Close menu"
                    >
                        <X className="size-5" strokeWidth={2} />
                    </button>
                </div>
                <SidebarNav mobile onNavigate={onClose} />
            </div>
        </div>
    );
}

function Topbar({ onMenuClick }: { onMenuClick: () => void }) {
    return (
        <header className="dashboard-topbar">
            <div className="flex min-w-0 flex-1 items-center gap-2 sm:gap-3 lg:gap-5">
                <button
                    type="button"
                    onClick={onMenuClick}
                    className="grid size-9 shrink-0 place-items-center rounded-lg text-[#0f172a] transition hover:bg-[#f1f5f9] lg:hidden"
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

            <div className="flex min-w-0 shrink-0 items-center gap-2 sm:gap-3 lg:gap-5">
                <div className="hidden min-w-0 sm:block">
                    <OutletSwitcher />
                </div>

                <Link
                    href={POSOrderController.index.url()}
                    prefetch
                    className="flex h-10 items-center gap-2 rounded-[10px] bg-[#2563eb] px-3 text-sm font-bold text-white shadow-[0_4px_10px_rgba(37,99,235,0.25)] transition hover:bg-[#1d4ed8] active:translate-y-px sm:h-[46px] sm:px-[22px] sm:text-[15px]"
                >
                    <Plus className="size-[18px]" strokeWidth={2} />
                    <span className="hidden sm:inline">Create Order</span>
                </Link>

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
    const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

    return (
        <div className="min-h-[100dvh] bg-[#f8fafc] font-sans text-[#0f172a]">
            <Link
                href="#main-content"
                className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-[#2563eb] focus:shadow"
            >
                Skip to content
            </Link>
            <SidebarNav />
            <MobileSidebar
                open={mobileSidebarOpen}
                onClose={() => setMobileSidebarOpen(false)}
            />
            <div className="dashboard-main-shell">
                <Topbar onMenuClick={() => setMobileSidebarOpen(true)} />
                <main id="main-content" className="dashboard-main-content">
                    {children}
                </main>
            </div>
        </div>
    );
}
