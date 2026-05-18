import { Form, Head, Link } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/UserController';
import { TextField } from '@/components/phase2/form-controls';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit, index } from '@/routes/users';
import userOutlets from '@/routes/users/outlets';
import type { ManagedUser } from '@/types';

export default function ShowUser({
    managedUser,
}: {
    managedUser: ManagedUser;
}) {
    return (
        <>
            <Head title={managedUser.name} />
            <PageHeader
                title={managedUser.name}
                description="User detail, outlet access, and password reset."
                actions={
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back</Link>
                        </Button>
                        <Button asChild>
                            <Link href={edit(managedUser.id)}>Edit</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={userOutlets.edit(managedUser.id)}>
                                Assign Outlets
                            </Link>
                        </Button>
                    </div>
                }
            />

            <div className="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
                <PagePanel title="Account">
                    <dl className="grid gap-4 text-sm md:grid-cols-2">
                        <div>
                            <dt className="font-semibold text-slate-500">
                                Email
                            </dt>
                            <dd className="mt-1">{managedUser.email}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-slate-500">
                                Phone
                            </dt>
                            <dd className="mt-1">{managedUser.phone ?? '-'}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-slate-500">
                                Global role
                            </dt>
                            <dd className="mt-1 capitalize">
                                {managedUser.global_role}
                            </dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-slate-500">
                                Status
                            </dt>
                            <dd className="mt-1">
                                <Badge
                                    variant={
                                        managedUser.is_active
                                            ? 'default'
                                            : 'secondary'
                                    }
                                >
                                    {managedUser.is_active
                                        ? 'Active'
                                        : 'Inactive'}
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </PagePanel>

                <PagePanel title="Reset Password">
                    <Form
                        {...UserController.resetPassword.form(managedUser.id)}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing, errors }) => (
                            <div className="space-y-4">
                                <TextField
                                    name="password"
                                    label="New password"
                                    type="password"
                                    error={errors.password}
                                />
                                <TextField
                                    name="password_confirmation"
                                    label="Confirm password"
                                    type="password"
                                    error={errors.password_confirmation}
                                />
                                <Button disabled={processing}>
                                    Reset password
                                </Button>
                            </div>
                        )}
                    </Form>
                </PagePanel>
            </div>

            <div className="mt-6">
                <PagePanel title="Outlet Assignments">
                    <div className="grid gap-3">
                        {(managedUser.user_outlets ?? []).map((assignment) => (
                            <div
                                key={assignment.id}
                                className="flex items-center justify-between rounded-lg border border-slate-200 p-3"
                            >
                                <div>
                                    <div className="font-semibold">
                                        {assignment.outlet?.name}
                                    </div>
                                    <div className="text-sm text-slate-500">
                                        {assignment.role}
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    {assignment.is_primary && (
                                        <Badge>Main</Badge>
                                    )}
                                    <Badge
                                        variant={
                                            assignment.is_active
                                                ? 'default'
                                                : 'secondary'
                                        }
                                    >
                                        {assignment.is_active
                                            ? 'Active'
                                            : 'Inactive'}
                                    </Badge>
                                </div>
                            </div>
                        ))}
                    </div>
                </PagePanel>
            </div>
        </>
    );
}
