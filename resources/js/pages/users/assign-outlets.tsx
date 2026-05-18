import { Form, Head, Link } from '@inertiajs/react';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Button } from '@/components/ui/button';
import { show } from '@/routes/users';
import userOutlets from '@/routes/users/outlets';
import type { ManagedUser, Outlet } from '@/types';

const permissions = [
    'can_manage_orders',
    'can_manage_payments',
    'can_manage_services',
    'can_manage_reports',
    'can_manage_users',
    'can_manage_settings',
] as const;

export default function AssignOutlets({
    managedUser,
    outlets,
}: {
    managedUser: ManagedUser;
    outlets: Outlet[];
}) {
    const existing = new Map(
        (managedUser.user_outlets ?? []).map((assignment) => [
            assignment.outlet_id,
            assignment,
        ]),
    );

    return (
        <>
            <Head title={`Assign Outlets - ${managedUser.name}`} />
            <PageHeader
                title="Assign Outlets"
                description={`Configure outlet roles and permissions for ${managedUser.name}.`}
            />

            <Form
                {...userOutlets.update.form(managedUser.id)}
                options={{ preserveScroll: true }}
                className="mt-6"
            >
                {({ processing, errors }) => (
                    <PagePanel title="Outlet Access">
                        {errors.outlets && (
                            <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {errors.outlets}
                            </p>
                        )}

                        <div className="grid gap-4">
                            {outlets.map((outlet, index) => {
                                const assignment = existing.get(outlet.id);
                                const selected = assignment !== undefined;

                                return (
                                    <div
                                        key={outlet.id}
                                        className="rounded-xl border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <label className="flex items-center gap-3 font-semibold">
                                                <input
                                                    type="checkbox"
                                                    name={`outlets[${index}][outlet_id]`}
                                                    value={outlet.id}
                                                    defaultChecked={selected}
                                                    className="size-4"
                                                />
                                                {outlet.name}
                                            </label>
                                            <select
                                                name={`outlets[${index}][role]`}
                                                defaultValue={
                                                    assignment?.role ??
                                                    'cashier'
                                                }
                                                className="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm"
                                            >
                                                <option value="owner">
                                                    Owner
                                                </option>
                                                <option value="admin">
                                                    Admin
                                                </option>
                                                <option value="cashier">
                                                    Cashier
                                                </option>
                                                <option value="staff">
                                                    Staff
                                                </option>
                                            </select>
                                        </div>

                                        <div className="mt-4 grid gap-3 md:grid-cols-4">
                                            <label className="flex items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    name={`outlets[${index}][is_primary]`}
                                                    value="1"
                                                    defaultChecked={
                                                        assignment?.is_primary
                                                    }
                                                />
                                                Primary
                                            </label>
                                            <label className="flex items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    name={`outlets[${index}][is_active]`}
                                                    value="1"
                                                    defaultChecked={
                                                        assignment?.is_active ??
                                                        selected
                                                    }
                                                />
                                                Active assignment
                                            </label>
                                            {permissions.map((permission) => (
                                                <label
                                                    key={permission}
                                                    className="flex items-center gap-2 text-sm"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name={`outlets[${index}][${permission}]`}
                                                        value="1"
                                                        defaultChecked={
                                                            assignment?.[
                                                                permission
                                                            ] ?? false
                                                        }
                                                    />
                                                    {permission
                                                        .replace(
                                                            'can_manage_',
                                                            '',
                                                        )
                                                        .replace('_', ' ')}
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="mt-6 flex gap-3">
                            <Button disabled={processing}>
                                Save assignments
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={show(managedUser.id)}>Cancel</Link>
                            </Button>
                        </div>
                    </PagePanel>
                )}
            </Form>
        </>
    );
}
