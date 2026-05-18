import { Form, Head, Link } from '@inertiajs/react';
import ServiceCopyController from '@/actions/App/Http/Controllers/ServiceCopyController';
import ServiceController from '@/actions/App/Http/Controllers/ServiceController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { OutletOption, PageHeader } from '@/pages/master-data/shared';

type CopyResult = Record<string, number> | null;

export default function ServiceCopy({
    outlets,
    result,
}: {
    outlets: OutletOption[];
    result: CopyResult;
}) {
    return (
        <>
            <Head title="Copy Services" />
            <div className="space-y-6">
                <PageHeader
                    title="Copy Services"
                    description="Copy category, service, and variant hierarchy between outlets."
                />
                <Form
                    {...ServiceCopyController.store.form()}
                    className="max-w-2xl space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <SelectOutlet
                                name="source_outlet_id"
                                label="Source Outlet"
                                outlets={outlets}
                                error={errors.source_outlet_id}
                            />
                            <SelectOutlet
                                name="target_outlet_id"
                                label="Target Outlet"
                                outlets={outlets}
                                error={errors.target_outlet_id}
                            />
                            <div className="grid gap-2">
                                <Label>Copy Mode</Label>
                                <select
                                    name="copy_mode"
                                    defaultValue="skip_existing"
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="skip_existing">
                                        Skip existing
                                    </option>
                                    <option value="duplicate_all">
                                        Duplicate all
                                    </option>
                                    <option value="overwrite_existing">
                                        Overwrite existing
                                    </option>
                                </select>
                                <InputError message={errors.copy_mode} />
                            </div>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="include_inactive"
                                    value="1"
                                />
                                Include inactive data
                            </label>
                            <div className="flex gap-2">
                                <Button disabled={processing}>Copy</Button>
                                <Button asChild variant="outline">
                                    <Link href={ServiceController.index.url()}>
                                        Back
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
                {result && (
                    <div className="rounded-lg border p-4">
                        <h2 className="font-medium">Last copy result</h2>
                        <dl className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                            {Object.entries(result).map(([key, value]) => (
                                <div
                                    key={key}
                                    className="flex justify-between gap-3"
                                >
                                    <dt className="text-muted-foreground">
                                        {key}
                                    </dt>
                                    <dd className="font-medium">{value}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                )}
            </div>
        </>
    );
}

function SelectOutlet({
    name,
    label,
    outlets,
    error,
}: {
    name: string;
    label: string;
    outlets: OutletOption[];
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <select
                name={name}
                className="h-9 rounded-md border bg-background px-3 text-sm"
                required
            >
                <option value="">Select outlet</option>
                {outlets.map((outlet) => (
                    <option key={outlet.id} value={outlet.id}>
                        {outlet.name}
                    </option>
                ))}
            </select>
            <InputError message={error} />
        </div>
    );
}
