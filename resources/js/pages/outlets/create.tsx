import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/phase2/page-panel';
import OutletForm from '@/pages/outlets/partials/outlet-form';

export default function CreateOutlet() {
    return (
        <>
            <Head title="Create Outlet" />
            <PageHeader
                title="Create Outlet"
                description="Add branch information used by POS operations."
            />
            <div className="mt-6">
                <OutletForm />
            </div>
        </>
    );
}
