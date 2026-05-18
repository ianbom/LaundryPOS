import { Form, Link } from '@inertiajs/react';
import OutletController from '@/actions/App/Http/Controllers/OutletController';
import {
    CheckboxField,
    TextAreaField,
    TextField,
} from '@/components/phase2/form-controls';
import { PagePanel } from '@/components/phase2/page-panel';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/outlets';
import type { Outlet } from '@/types';

export default function OutletForm({ outlet }: { outlet?: Outlet }) {
    const action = outlet
        ? OutletController.update.form(outlet.id)
        : OutletController.store.form();

    return (
        <Form {...action} options={{ preserveScroll: true }}>
            {({ processing, errors }) => (
                <PagePanel title="Outlet Information">
                    <div className="grid gap-4 md:grid-cols-2">
                        <TextField
                            name="name"
                            label="Outlet name"
                            defaultValue={outlet?.name}
                            error={errors.name}
                            required
                        />
                        <TextField
                            name="code"
                            label="Code"
                            defaultValue={outlet?.code}
                            error={errors.code}
                        />
                        <TextField
                            name="slug"
                            label="Slug"
                            defaultValue={outlet?.slug}
                            error={errors.slug}
                            required
                        />
                        <TextField
                            name="phone"
                            label="Phone"
                            defaultValue={outlet?.phone}
                            error={errors.phone}
                        />
                        <TextField
                            name="whatsapp_number"
                            label="WhatsApp"
                            defaultValue={outlet?.whatsapp_number}
                            error={errors.whatsapp_number}
                        />
                        <TextField
                            name="email"
                            label="Email"
                            type="email"
                            defaultValue={outlet?.email}
                            error={errors.email}
                        />
                        <TextField
                            name="google_maps_url"
                            label="Google Maps URL"
                            defaultValue={outlet?.google_maps_url}
                            error={errors.google_maps_url}
                        />
                        <div className="flex items-end gap-3">
                            <CheckboxField
                                name="is_main"
                                label="Main outlet"
                                defaultChecked={outlet?.is_main}
                            />
                            <CheckboxField
                                name="is_active"
                                label="Active"
                                defaultChecked={outlet?.is_active ?? true}
                            />
                        </div>
                        <div className="md:col-span-2">
                            <TextAreaField
                                name="address"
                                label="Address"
                                defaultValue={outlet?.address}
                                error={errors.address}
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex gap-3">
                        <Button disabled={processing}>
                            {outlet ? 'Update outlet' : 'Create outlet'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={index()}>Cancel</Link>
                        </Button>
                    </div>
                </PagePanel>
            )}
        </Form>
    );
}
