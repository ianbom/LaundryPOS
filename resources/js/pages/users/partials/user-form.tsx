import { Form, Link } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/UserController';
import { CheckboxField, TextField } from '@/components/phase2/form-controls';
import { PagePanel } from '@/components/phase2/page-panel';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/users';
import type { ManagedUser } from '@/types';

export default function UserForm({
    managedUser,
}: {
    managedUser?: ManagedUser;
}) {
    const action = managedUser
        ? UserController.update.form(managedUser.id)
        : UserController.store.form();

    return (
        <Form {...action} options={{ preserveScroll: true }}>
            {({ processing, errors }) => (
                <PagePanel title="User Information">
                    <div className="grid gap-4 md:grid-cols-2">
                        <TextField
                            name="name"
                            label="Name"
                            defaultValue={managedUser?.name}
                            error={errors.name}
                            required
                        />
                        <TextField
                            name="email"
                            label="Email"
                            type="email"
                            defaultValue={managedUser?.email}
                            error={errors.email}
                            required
                        />
                        <TextField
                            name="phone"
                            label="Phone"
                            defaultValue={managedUser?.phone}
                            error={errors.phone}
                        />
                        <div className="grid gap-2">
                            <label
                                htmlFor="global_role"
                                className="text-sm font-medium"
                            >
                                Global role
                            </label>
                            <select
                                id="global_role"
                                name="global_role"
                                defaultValue={
                                    managedUser?.global_role ?? 'staff'
                                }
                                className="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm"
                            >
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                            {errors.global_role && (
                                <p className="text-sm text-red-600">
                                    {errors.global_role}
                                </p>
                            )}
                        </div>
                        {!managedUser && (
                            <>
                                <TextField
                                    name="password"
                                    label="Password"
                                    type="password"
                                    error={errors.password}
                                    required
                                />
                                <TextField
                                    name="password_confirmation"
                                    label="Confirm password"
                                    type="password"
                                    error={errors.password_confirmation}
                                    required
                                />
                            </>
                        )}
                        <CheckboxField
                            name="is_active"
                            label="Active"
                            defaultChecked={managedUser?.is_active ?? true}
                        />
                    </div>

                    <div className="mt-6 flex gap-3">
                        <Button disabled={processing}>
                            {managedUser ? 'Update user' : 'Create user'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={index()}>Cancel</Link>
                        </Button>
                    </div>
                </PagePanel>
            )}
        </Form>
    );
}
