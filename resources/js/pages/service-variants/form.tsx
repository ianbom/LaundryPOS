import { Form, Head, Link } from '@inertiajs/react';
import ServiceVariantController from '@/actions/App/Http/Controllers/ServiceVariantController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { PageHeader } from '@/pages/master-data/shared';

type Service = { id: number; name: string };
type Variant = {
    id: number;
    name: string;
    description: string | null;
    price: string;
    unit: string;
    min_quantity: string;
    estimated_duration_hours: number | null;
    is_express: boolean;
    is_active: boolean;
    sort_order: number;
};

const units = ['kg', 'item', 'set', 'm2', 'unit', 'custom'];

export default function ServiceVariantForm({
    service,
    variant,
}: {
    service: Service;
    variant: Variant | null;
}) {
    const isEdit = variant !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Variant' : 'Create Variant'} />
            <div className="space-y-6">
                <PageHeader
                    title={isEdit ? 'Edit Variant' : 'Create Variant'}
                    description={`Pricing variant for ${service.name}.`}
                />
                <Form
                    {...(isEdit
                        ? ServiceVariantController.update.form([
                              service.id,
                              variant.id,
                          ])
                        : ServiceVariantController.store.form(service.id))}
                    className="max-w-2xl space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <Field label="Name" error={errors.name}>
                                <input
                                    name="name"
                                    defaultValue={variant?.name ?? ''}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                />
                            </Field>
                            <Field label="Price" error={errors.price}>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    name="price"
                                    defaultValue={variant?.price ?? 0}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                />
                            </Field>
                            <Field label="Unit" error={errors.unit}>
                                <select
                                    name="unit"
                                    defaultValue={variant?.unit ?? 'kg'}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                >
                                    {units.map((unit) => (
                                        <option key={unit} value={unit}>
                                            {unit}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Minimum Quantity"
                                error={errors.min_quantity}
                            >
                                <input
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    name="min_quantity"
                                    defaultValue={variant?.min_quantity ?? 1}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                />
                            </Field>
                            <Field
                                label="Estimated Duration Hours"
                                error={errors.estimated_duration_hours}
                            >
                                <input
                                    type="number"
                                    min="1"
                                    name="estimated_duration_hours"
                                    defaultValue={
                                        variant?.estimated_duration_hours ?? ''
                                    }
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Description"
                                error={errors.description}
                            >
                                <textarea
                                    name="description"
                                    defaultValue={variant?.description ?? ''}
                                    className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                />
                            </Field>
                            <Field label="Sort Order" error={errors.sort_order}>
                                <input
                                    type="number"
                                    min="0"
                                    name="sort_order"
                                    defaultValue={variant?.sort_order ?? 0}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_express"
                                    value="1"
                                    defaultChecked={
                                        variant?.is_express ?? false
                                    }
                                />
                                Express
                            </label>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    defaultChecked={variant?.is_active ?? true}
                                />
                                Active
                            </label>
                            <p className="text-sm text-muted-foreground">
                                If customer quantity is lower than minimum
                                quantity, system charges minimum quantity.
                            </p>
                            <div className="flex gap-2">
                                <Button disabled={processing}>Save</Button>
                                <Button asChild variant="outline">
                                    <Link
                                        href={ServiceVariantController.index.url(
                                            service.id,
                                        )}
                                    >
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
