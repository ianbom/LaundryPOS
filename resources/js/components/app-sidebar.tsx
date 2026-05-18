import { Link } from '@inertiajs/react';
import {
    BarChart3,
    Building2,
    ClipboardList,
    Copy,
    CreditCard,
    FileText,
    History,
    LayoutGrid,
    MessageSquareText,
    PlusCircle,
    ReceiptText,
    ShieldCheck,
    Shirt,
    Store,
    Tags,
    UserCog,
    UsersRound,
} from 'lucide-react';
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
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { edit as appearanceEdit } from '@/routes/appearance';
import { edit as profileEdit } from '@/routes/profile';
import { edit as securityEdit } from '@/routes/security';
import { edit as businessSettingsEdit } from '@/routes/settings/business';
import { edit as integrationsSettingsEdit } from '@/routes/settings/integrations';
import { index as whatsappTemplatesIndex } from '@/routes/settings/whatsapp-templates';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: DashboardController.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Create Order',
        href: POSOrderController.index.url(),
        icon: PlusCircle,
    },
    {
        title: 'Orders',
        href: OrderController.index.url(),
        icon: ClipboardList,
    },
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
        title: 'Customers',
        href: CustomerController.index.url(),
        icon: UsersRound,
    },
    {
        title: 'Service Categories',
        href: ServiceCategoryController.index.url(),
        icon: Tags,
    },
    {
        title: 'Services',
        href: ServiceController.index.url(),
        icon: Shirt,
    },
    {
        title: 'Copy Services',
        href: ServiceCopyController.create.url(),
        icon: Copy,
    },
    {
        title: 'Transaction Report',
        href: ReportController.transactions.url(),
        icon: ReceiptText,
    },
    {
        title: 'Revenue Report',
        href: ReportController.revenue.url(),
        icon: BarChart3,
    },
    {
        title: 'Service Report',
        href: ReportController.services.url(),
        icon: FileText,
    },
    {
        title: 'Customer Report',
        href: ReportController.customers.url(),
        icon: UsersRound,
    },
    {
        title: 'Activity Logs',
        href: ActivityLogController.index.url(),
        icon: History,
    },
    {
        title: 'Business Settings',
        href: businessSettingsEdit.url(),
        icon: Store,
    },
    {
        title: 'Integration Settings',
        href: integrationsSettingsEdit.url(),
        icon: CreditCard,
    },
    {
        title: 'WhatsApp Templates',
        href: whatsappTemplatesIndex.url(),
        icon: MessageSquareText,
    },
    {
        title: 'Appearance',
        href: appearanceEdit.url(),
        icon: ShieldCheck,
    },
    {
        title: 'Profile',
        href: profileEdit.url(),
        icon: UserCog,
    },
    {
        title: 'Security',
        href: securityEdit.url(),
        icon: ShieldCheck,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={DashboardController.index.url()}
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
