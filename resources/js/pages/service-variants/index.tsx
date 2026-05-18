import { Form, Head, Link } from '@inertiajs/react';
import ServiceVariantController from '@/actions/App/Http/Controllers/ServiceVariantController';
import {
    DeleteButton,
    PageHeader,
    Paginated,
    Pagination,
    StatusBadge,
    TextFilter,
} from '@/pages/master-data/shared';

type Service = { id: number; name: string };
type Variant = {
    id: number;
    name: string;
    price: string;
    unit: string;
    min_quantity: string;
    estimated_duration_hours: number | null;
    is_express: boolean;
    is_active: boolean;
};

export default function ServiceVariantsIndex({
    service,
    variants,
    filters,
}: {
    service: Service;
    variants: Paginated<Variant>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title={`${service.name} Variants`} />
            <div className="space-y-6">
                <PageHeader
                    title={`${service.name} Variants`}
                    description="Manage price, duration, and minimum quantity."
                    createHref={ServiceVariantController.create.url(service.id)}
                />
                <TextFilter
                    action={ServiceVariantController.index.url(service.id)}
                    defaultValue={filters.search}
                    placeholder="Search variants"
                />
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Variant</th>
                                <th className="p-3">Price</th>
                                <th className="p-3">Unit</th>
                                <th className="p-3">Min Qty</th>
                                <th className="p-3">Duration</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {variants.data.map((variant) => (
                                <tr key={variant.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {variant.name}
                                        {variant.is_express && (
                                            <span className="ml-2 rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-700">
                                                Express
                                            </span>
                                        )}
                                    </td>
                                    <td className="p-3">{variant.price}</td>
                                    <td className="p-3">{variant.unit}</td>
                                    <td className="p-3">
                                        {variant.min_quantity}
                                    </td>
                                    <td className="p-3">
                                        {variant.estimated_duration_hours
                                            ? `${variant.estimated_duration_hours} h`
                                            : '-'}
                                    </td>
                                    <td className="p-3">
                                        <StatusBadge
                                            active={variant.is_active}
                                        />
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={ServiceVariantController.edit.url(
                                                    [service.id, variant.id],
                                                )}
                                                className="inline-flex h-8 items-center rounded-md border px-3 text-xs"
                                            >
                                                Edit
                                            </Link>
                                            <Form
                                                {...ServiceVariantController.toggleActive.form(
                                                    [service.id, variant.id],
                                                )}
                                            >
                                                <button className="h-8 rounded-md border px-3 text-xs">
                                                    Toggle
                                                </button>
                                            </Form>
                                            <DeleteButton
                                                action={ServiceVariantController.destroy.url(
                                                    [service.id, variant.id],
                                                )}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {variants.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No service variants found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={variants.links} />
            </div>
        </>
    );
}
