import { Form, Head, Link } from '@inertiajs/react';
import ServiceCategoryController from '@/actions/App/Http/Controllers/ServiceCategoryController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { OutletOption, PageHeader } from '@/pages/master-data/shared';

type Category = {
    id: number;
    outlet_id: number;
    name: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export default function ServiceCategoryForm({
    category,
    outlets,
}: {
    category: Category | null;
    outlets: OutletOption[];
}) {
    const isEdit = category !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Category' : 'Create Category'} />
            <div className="space-y-6">
                <PageHeader
                    title={isEdit ? 'Edit Category' : 'Create Category'}
                    description="Manage service category data for selected outlet."
                />
                <Form
                    {...(isEdit
                        ? ServiceCategoryController.update.form(category.id)
                        : ServiceCategoryController.store.form())}
                    className="max-w-2xl space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <Field label="Outlet" error={errors.outlet_id}>
                                <select
                                    name="outlet_id"
                                    defaultValue={category?.outlet_id ?? ''}
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
                            <Field label="Name" error={errors.name}>
                                <input
                                    name="name"
                                    defaultValue={category?.name ?? ''}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    required
                                />
                            </Field>
                            <Field
                                label="Description"
                                error={errors.description}
                            >
                                <textarea
                                    name="description"
                                    defaultValue={category?.description ?? ''}
                                    className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                />
                            </Field>
                            <Field label="Sort Order" error={errors.sort_order}>
                                <input
                                    type="number"
                                    min="0"
                                    name="sort_order"
                                    defaultValue={category?.sort_order ?? 0}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    defaultChecked={category?.is_active ?? true}
                                />
                                Active
                            </label>
                            <div className="flex gap-2">
                                <Button disabled={processing}>Save</Button>
                                <Button asChild variant="outline">
                                    <Link
                                        href={ServiceCategoryController.index.url()}
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
