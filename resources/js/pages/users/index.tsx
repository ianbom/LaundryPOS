import { Form, Head, Link } from '@inertiajs/react';
import { Eye, Pencil, Plus, Power, Shield, Trash2 } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/UserController';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { create, edit, show } from '@/routes/users';
import userOutlets from '@/routes/users/outlets';
import type { ManagedUser, Paginated } from '@/types';

export default function UsersIndex({
    users,
    filters,
}: {
    users: Paginated<ManagedUser>;
    filters: { search?: string; role?: string; status?: string };
}) {
    return (
        <>
            <Head title="Users" />
            <PageHeader
                title="Users"
                description="Manage application users and outlet-level access."
                actions={
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="size-4" />
                            Create User
                        </Link>
                    </Button>
                }
            />

            <div className="mt-6">
                <PagePanel
                    title="User List"
                    actions={
                        <Form
                            {...UserController.index.form()}
                            className="flex gap-2"
                        >
                            <Input
                                name="search"
                                placeholder="Search users"
                                defaultValue={filters.search ?? ''}
                                className="h-10 w-64"
                            />
                            <select
                                name="role"
                                defaultValue={filters.role ?? ''}
                                className="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm"
                            >
                                <option value="">All role</option>
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                            <select
                                name="status"
                                defaultValue={filters.status ?? ''}
                                className="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm"
                            >
                                <option value="">All status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <Button variant="outline">Filter</Button>
                        </Form>
                    }
                >
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead>
                                <tr className="border-b text-xs font-bold text-slate-900 uppercase">
                                    <th className="py-3">Name</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Outlets</th>
                                    <th>Status</th>
                                    <th className="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.map((user) => (
                                    <tr
                                        key={user.id}
                                        className="border-b hover:bg-slate-50"
                                    >
                                        <td className="py-3 font-semibold">
                                            {user.name}
                                        </td>
                                        <td>
                                            <div>{user.email}</div>
                                            <div className="text-xs text-slate-500">
                                                {user.phone ?? '-'}
                                            </div>
                                        </td>
                                        <td className="capitalize">
                                            {user.global_role}
                                        </td>
                                        <td>{user.user_outlets_count ?? 0}</td>
                                        <td>
                                            <Badge
                                                variant={
                                                    user.is_active
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {user.is_active
                                                    ? 'Active'
                                                    : 'Inactive'}
                                            </Badge>
                                        </td>
                                        <td>
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link href={show(user.id)}>
                                                        <Eye className="size-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link href={edit(user.id)}>
                                                        <Pencil className="size-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={userOutlets.edit(
                                                            user.id,
                                                        )}
                                                    >
                                                        <Shield className="size-4" />
                                                    </Link>
                                                </Button>
                                                <Form
                                                    {...UserController.toggleActive.form(
                                                        user.id,
                                                    )}
                                                >
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                    >
                                                        <Power className="size-4" />
                                                    </Button>
                                                </Form>
                                                <Form
                                                    {...UserController.destroy.form(
                                                        user.id,
                                                    )}
                                                >
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-red-600"
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </Form>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </PagePanel>
            </div>
        </>
    );
}
