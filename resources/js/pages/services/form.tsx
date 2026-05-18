import { Form, Head, Link } from '@inertiajs/react';
import ServiceController from '@/actions/App/Http/Controllers/ServiceController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { OutletOption, PageHeader } from '@/pages/master-data/shared';

type CategoryOption = {
    id: number;
    outlet_id: number;
    name: string;
};

type Service = {
    id: number;
    outlet_id: number;
    service_category_id: number;
    name: string;
    description: string | null;
    pricing_type: string;
    sort_order: number;
    is_active: boolean;
};

const pricingTypes = [
    'per_kg',
    'per_item',
    'per_set',
    'per_m2',
    'fixed',
    'custom',
];

export default function ServiceForm({
    service,
    outlets,
    categories,
}: {
    service: Service | null;
    outlets: OutletOption[];
    categories: CategoryOption[];
}) {
    const isEdit = service !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Service' : 'Create Service'} />
            <div className="space-y-6">
                <PageHeader
                    title={isEdit ? 'Edit Service' : 'Create Service'}
                    description="Create outlet-scoped laundry services."
                />
                <Form
                    {...(isEdit
                        ? ServiceController.update.form(service.id)
                        : ServiceController.store.form())}
                    className="max-w-2xl space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <Field label="Outlet" error={errors.outlet_id}>
                                <select
                                    name="outlet_id"
                                    defaultValue={service?.outlet_id ?? ''}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                >
                                    <option value="">Select outlet</option>
                                    {outlets.map((outlet) => (
                                        <option
                                            key={outlet.id}
                                            value={outlet.id}
                                        >
                                            {outlet.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Category"
                                error={errors.service_category_id}
                            >
                                <select
                                    name="service_category_id"
                                    defaultValue={
                                        service?.service_category_id ?? ''
                                    }
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                >
                                    <option value="">Select category</option>
                                    {categories.map((category) => (
                                        <option
                                            key={category.id}
                                            value={category.id}
                                        >
                                            {category.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Name" error={errors.name}>
                                <input
                                    name="name"
                                    defaultValue={service?.name ?? ''}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                />
                            </Field>
                            <Field
                                label="Pricing Type"
                                error={errors.pricing_type}
                            >
                                <select
                                    name="pricing_type"
                                    defaultValue={service?.pricing_type ?? ''}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                >
                                    <option value="">Select pricing</option>
                                    {pricingTypes.map((type) => (
                                        <option key={type} value={type}>
                                            {type}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Description"
                                error={errors.description}
                            >
                                <textarea
                                    name="description"
                                    defaultValue={service?.description ?? ''}
                                    className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                />
                            </Field>
                            <Field label="Sort Order" error={errors.sort_order}>
                                <input
                                    type="number"
                                    min="0"
                                    name="sort_order"
                                    defaultValue={service?.sort_order ?? 0}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    defaultChecked={service?.is_active ?? true}
                                />
                                Active
                            </label>
                            <div className="flex gap-2">
                                <Button disabled={processing}>Save</Button>
                                <Button asChild variant="outline">
                                    <Link href={ServiceController.index.url()}>
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            {children}
            <InputError message={error} />
        </div>
    );
}
