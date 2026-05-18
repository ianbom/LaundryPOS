import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/phase2/page-panel';
import UserForm from '@/pages/users/partials/user-form';

export default function CreateUser() {
    return (
        <>
            <Head title="Create User" />
            <PageHeader
                title="Create User"
                description="Add owner, admin, cashier, or staff accounts."
            />
            <div className="mt-6">
                <UserForm />
            </div>
        </>
    );
}
