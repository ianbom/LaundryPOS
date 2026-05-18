import { Form, Head, Link } from '@inertiajs/react';
import WhatsAppTemplateController from '@/actions/App/Http/Controllers/Settings/WhatsAppTemplateController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { OutletOption, PageHeader } from '@/pages/master-data/shared';

type Template = {
    id: number;
    outlet_id: number | null;
    type: string;
    title: string;
    body: string;
    is_active: boolean;
};

const templateTypes = [
    'payment_receipt',
    'order_created',
    'order_processing',
    'order_ready',
    'order_completed',
    'payment_reminder',
    'custom',
];

const variables = [
    '{customer_name}',
    '{customer_phone}',
    '{invoice_number}',
    '{grand_total}',
    '{payment_method}',
    '{payment_status}',
    '{order_status}',
    '{tracking_url}',
    '{invoice_url}',
    '{outlet_name}',
    '{outlet_phone}',
    '{outlet_whatsapp}',
    '{outlet_address}',
    '{business_name}',
    '{estimated_done_at}',
    '{paid_at}',
];

export default function WhatsAppTemplateForm({
    template,
    outlets,
}: {
    template: Template | null;
    outlets: OutletOption[];
}) {
    const isEdit = template !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Template' : 'Create Template'} />
            <div className="space-y-6">
                <PageHeader
                    title={isEdit ? 'Edit Template' : 'Create Template'}
                    description="Configure message body with supported variables."
                />
                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <Form
                        {...(isEdit
                            ? WhatsAppTemplateController.update.form(
                                  template.id,
                              )
                            : WhatsAppTemplateController.store.form())}
                        className="space-y-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <Field label="Scope" error={errors.outlet_id}>
                                    <select
                                        name="outlet_id"
                                        defaultValue={template?.outlet_id ?? ''}
                                        className="h-9 rounded-md border bg-background px-3 text-sm"
                                    >
                                        <option value="">Global</option>
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
                                <Field label="Type" error={errors.type}>
                                    <select
                                        name="type"
                                        defaultValue={template?.type ?? ''}
                                        className="h-9 rounded-md border bg-background px-3 text-sm"
                                        required
                                    >
                                        <option value="">Select type</option>
                                        {templateTypes.map((type) => (
                                            <option key={type} value={type}>
                                                {type}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Title" error={errors.title}>
                                    <input
                                        name="title"
                                        defaultValue={template?.title ?? ''}
                                        className="h-9 rounded-md border bg-background px-3 text-sm"
                                        required
                                    />
                                </Field>
                                <Field label="Body" error={errors.body}>
                                    <textarea
                                        name="body"
                                        defaultValue={template?.body ?? ''}
                                        className="min-h-72 rounded-md border bg-background px-3 py-2 font-mono text-sm"
                                        required
                                    />
                                </Field>
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        defaultChecked={
                                            template?.is_active ?? true
                                        }
                                    />
                                    Active
                                </label>
                                <div className="flex gap-2">
                                    <Button disabled={processing}>Save</Button>
                                    <Button asChild variant="outline">
                                        <Link
                                            href={WhatsAppTemplateController.index.url()}
                                        >
                                            Cancel
                                        </Link>
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                    <aside className="rounded-lg border p-4">
                        <h2 className="text-sm font-medium">
                            Available Variables
                        </h2>
                        <div className="mt-3 flex flex-wrap gap-2">
                            {variables.map((variable) => (
                                <code
                                    key={variable}
                                    className="rounded bg-muted px-2 py-1 text-xs"
                                >
                                    {variable}
                                </code>
                            ))}
                        </div>
                    </aside>
                </div>
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
