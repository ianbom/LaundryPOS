import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Building2,
    Copy,
    CreditCard,
    FolderGit2,
    LayoutGrid,
    MessageSquareText,
    Settings,
    Shirt,
    Store,
    Tags,
    UserCog,
    UsersRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
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
import { dashboard } from '@/routes';
import { edit as appearanceEdit } from '@/routes/appearance';
import { index as customersIndex } from '@/routes/customers';
import { index as outletsIndex } from '@/routes/outlets';
import { index as serviceCategoriesIndex } from '@/routes/service-categories';
import { index as servicesIndex } from '@/routes/services';
import { create as serviceCopyCreate } from '@/routes/services/copy';
import { edit as businessSettingsEdit } from '@/routes/settings/business';
import { edit as integrationsSettingsEdit } from '@/routes/settings/integrations';
import { index as whatsappTemplatesIndex } from '@/routes/settings/whatsapp-templates';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Outlets',
        href: outletsIndex.url(),
        icon: Building2,
    },
    {
        title: 'Users',
        href: usersIndex.url(),
        icon: UserCog,
    },
    {
        title: 'Customers',
        href: customersIndex.url(),
        icon: UsersRound,
    },
    {
        title: 'Service Categories',
        href: serviceCategoriesIndex.url(),
        icon: Tags,
    },
    {
        title: 'Services',
        href: servicesIndex.url(),
        icon: Shirt,
    },
    {
        title: 'Copy Services',
        href: serviceCopyCreate.url(),
        icon: Copy,
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
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard.url()} prefetch>
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
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
