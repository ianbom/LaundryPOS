import { Form, Head, Link } from '@inertiajs/react';
import ServiceController from '@/actions/App/Http/Controllers/ServiceController';
import {
    DeleteButton,
    EditButton,
    PageHeader,
    Paginated,
    Pagination,
    StatusBadge,
    TextFilter,
} from '@/pages/master-data/shared';

type Service = {
    id: number;
    name: string;
    pricing_type: string;
    sort_order: number;
    is_active: boolean;
    variants_count: number;
    outlet?: { name: string } | null;
    service_category?: { name: string } | null;
};

export default function ServicesIndex({
    services,
    filters,
}: {
    services: Paginated<Service>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title="Services" />
            <div className="space-y-6">
                <PageHeader
                    title="Services"
                    description="Manage laundry services, pricing type, and variants."
                    createHref={ServiceController.create.url()}
                />
                <div className="flex flex-col gap-2 sm:flex-row">
                    <div className="flex-1">
                        <TextFilter
                            action={ServiceController.index.url()}
                            defaultValue={filters.search}
                            placeholder="Search services"
                        />
                    </div>
                    <Link
                        href="/services/copy"
                        className="inline-flex h-9 items-center justify-center rounded-md border px-3 text-sm"
                    >
                        Copy Catalog
                    </Link>
                </div>
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Service</th>
                                <th className="p-3">Outlet</th>
                                <th className="p-3">Category</th>
                                <th className="p-3">Pricing</th>
                                <th className="p-3">Variants</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {services.data.map((service) => (
                                <tr key={service.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {service.name}
                                    </td>
                                    <td className="p-3">
                                        {service.outlet?.name ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {service.service_category?.name ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {service.pricing_type}
                                    </td>
                                    <td className="p-3">
                                        {service.variants_count}
                                    </td>
                                    <td className="p-3">
                                        <StatusBadge
                                            active={service.is_active}
                                        />
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={`/services/${service.id}/variants`}
                                                className="inline-flex h-8 items-center rounded-md border px-3 text-xs"
                                            >
                                                Variants
                                            </Link>
                                            <EditButton
                                                href={ServiceController.edit.url(
                                                    service.id,
                                                )}
                                            />
                                            <Form
                                                {...ServiceController.toggleActive.form(
                                                    service.id,
                                                )}
                                            >
                                                <button className="h-8 rounded-md border px-3 text-xs">
                                                    Toggle
                                                </button>
                                            </Form>
                                            <DeleteButton
                                                action={ServiceController.destroy.url(
                                                    service.id,
                                                )}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {services.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No services found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={services.links} />
            </div>
        </>
    );
}
