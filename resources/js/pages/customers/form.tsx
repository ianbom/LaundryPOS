import { Form, Head, Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { OutletOption, PageHeader } from '@/pages/master-data/shared';

type Customer = {
    id: number;
    outlet_id: number;
    name: string;
    phone: string;
    whatsapp_number: string | null;
    address: string | null;
    notes: string | null;
};

export default function CustomerForm({
    customer,
    outlets,
}: {
    customer: Customer | null;
    outlets: OutletOption[];
}) {
    const isEdit = customer !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Customer' : 'Create Customer'} />
            <div className="space-y-6">
                <PageHeader
                    title={isEdit ? 'Edit Customer' : 'Create Customer'}
                    description="Store customer identity and WhatsApp contact."
                />
                <Form
                    {...(isEdit
                        ? CustomerController.update.form(customer.id)
                        : CustomerController.store.form())}
                    className="max-w-2xl space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <Field label="Outlet" error={errors.outlet_id}>
                                <select
                                    name="outlet_id"
                                    defaultValue={customer?.outlet_id ?? ''}
                                    className="h-9 w-full rounded-md border bg-background px-3 text-sm"
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
                            <TextField
                                name="name"
                                label="Name"
                                defaultValue={customer?.name}
                                error={errors.name}
                                required
                            />
                            <TextField
                                name="phone"
                                label="Phone"
                                defaultValue={customer?.phone}
                                error={errors.phone}
                                required
                            />
                            <TextField
                                name="whatsapp_number"
                                label="WhatsApp"
                                defaultValue={customer?.whatsapp_number}
                                error={errors.whatsapp_number}
                            />
                            <TextareaField
                                name="address"
                                label="Address"
                                defaultValue={customer?.address}
                                error={errors.address}
                            />
                            <TextareaField
                                name="notes"
                                label="Notes"
                                defaultValue={customer?.notes}
                                error={errors.notes}
                            />
                            <div className="flex gap-2">
                                <Button disabled={processing}>Save</Button>
                                <Button asChild variant="outline">
                                    <Link href={CustomerController.index.url()}>
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

function TextField({
    name,
    label,
    defaultValue,
    error,
    required = false,
}: {
    name: string;
    label: string;
    defaultValue?: string | null;
    error?: string;
    required?: boolean;
}) {
    return (
        <Field label={label} error={error}>
            <input
                name={name}
                defaultValue={defaultValue ?? ''}
                required={required}
                className="h-9 rounded-md border bg-background px-3 text-sm"
            />
        </Field>
    );
}

function TextareaField({
    name,
    label,
    defaultValue,
    error,
}: {
    name: string;
    label: string;
    defaultValue?: string | null;
    error?: string;
}) {
    return (
        <Field label={label} error={error}>
            <textarea
                name={name}
                defaultValue={defaultValue ?? ''}
                className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
            />
        </Field>
    );
}
