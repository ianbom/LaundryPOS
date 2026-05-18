import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/phase2/page-panel';
import UserForm from '@/pages/users/partials/user-form';
import type { ManagedUser } from '@/types';

export default function EditUser({
    managedUser,
}: {
    managedUser: ManagedUser;
}) {
    return (
        <>
            <Head title={`Edit ${managedUser.name}`} />
            <PageHeader
                title="Edit User"
                description="Update account profile and global role."
            />
            <div className="mt-6">
                <UserForm managedUser={managedUser} />
            </div>
        </>
    );
}
