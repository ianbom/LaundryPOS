import { Form, Head, Link } from '@inertiajs/react';
import {
    CheckCircle2,
    Eye,
    Pencil,
    Plus,
    Power,
    Star,
    Trash2,
} from 'lucide-react';
import OutletController from '@/actions/App/Http/Controllers/OutletController';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { create, edit, show } from '@/routes/outlets';
import type { Outlet, Paginated } from '@/types';

export default function OutletsIndex({
    outlets,
    filters,
}: {
    outlets: Paginated<Outlet>;
    filters: { search?: string; status?: string };
}) {
    return (
        <>
            <Head title="Outlets" />
            <PageHeader
                title="Outlets"
                description="Manage laundry branches and active operational outlets."
                actions={
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="size-4" />
                            Create Outlet
                        </Link>
                    </Button>
                }
            />

            <div className="mt-6">
                <PagePanel
                    title="Outlet List"
                    actions={
                        <Form
                            {...OutletController.index.form()}
                            className="flex gap-2"
                        >
                            <Input
                                name="search"
                                placeholder="Search outlets"
                                defaultValue={filters.search ?? ''}
                                className="h-10 w-64"
                            />
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
                                    <th className="py-3">Outlet</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th className="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {outlets.data.map((outlet) => (
                                    <tr
                                        key={outlet.id}
                                        className="border-b hover:bg-slate-50"
                                    >
                                        <td className="py-3">
                                            <Link
                                                href={show(outlet.id)}
                                                className="font-semibold text-blue-600"
                                            >
                                                {outlet.name}
                                            </Link>
                                            <div className="text-xs text-slate-500">
                                                {outlet.code ?? '-'} /{' '}
                                                {outlet.slug}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{outlet.phone ?? '-'}</div>
                                            <div className="text-xs text-slate-500">
                                                {outlet.whatsapp_number ?? '-'}
                                            </div>
                                        </td>
                                        <td className="max-w-xs truncate">
                                            {outlet.address ?? '-'}
                                        </td>
                                        <td>
                                            <div className="flex gap-2">
                                                {outlet.is_main && (
                                                    <Badge>Main</Badge>
                                                )}
                                                <Badge
                                                    variant={
                                                        outlet.is_active
                                                            ? 'default'
                                                            : 'secondary'
                                                    }
                                                >
                                                    {outlet.is_active
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </Badge>
                                            </div>
                                        </td>
                                        <td>
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={show(outlet.id)}
                                                    >
                                                        <Eye className="size-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit(outlet.id)}
                                                    >
                                                        <Pencil className="size-4" />
                                                    </Link>
                                                </Button>
                                                <Form
                                                    {...OutletController.setMain.form(
                                                        outlet.id,
                                                    )}
                                                >
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        disabled={
                                                            outlet.is_main
                                                        }
                                                    >
                                                        <Star className="size-4" />
                                                    </Button>
                                                </Form>
                                                <Form
                                                    {...OutletController.toggleActive.form(
                                                        outlet.id,
                                                    )}
                                                >
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                    >
                                                        {outlet.is_active ? (
                                                            <Power className="size-4" />
                                                        ) : (
                                                            <CheckCircle2 className="size-4" />
                                                        )}
                                                    </Button>
                                                </Form>
                                                <Form
                                                    {...OutletController.destroy.form(
                                                        outlet.id,
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
