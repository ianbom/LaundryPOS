import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { AppLayoutProps } from '@/types';

export default function AppHeaderLayout(props: AppLayoutProps) {
    return <AppSidebarLayout {...props} />;
}
