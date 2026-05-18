import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/phase2/page-panel';
import OutletForm from '@/pages/outlets/partials/outlet-form';
import type { Outlet } from '@/types';

export default function EditOutlet({ outlet }: { outlet: Outlet }) {
    return (
        <>
            <Head title={`Edit ${outlet.name}`} />
            <PageHeader
                title="Edit Outlet"
                description="Update branch profile and operational status."
            />
            <div className="mt-6">
                <OutletForm outlet={outlet} />
            </div>
        </>
    );
}
