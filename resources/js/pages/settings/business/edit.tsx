import { Form, Head } from '@inertiajs/react';
import BusinessSettingController from '@/actions/App/Http/Controllers/Settings/BusinessSettingController';
import { TextAreaField, TextField } from '@/components/phase2/form-controls';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Button } from '@/components/ui/button';
import type { BusinessSetting } from '@/types';

export default function EditBusinessSettings({
    businessSetting,
}: {
    businessSetting: BusinessSetting;
}) {
    return (
        <>
            <Head title="Business Settings" />

            <PageHeader
                title="Business Settings"
                description="Manage identity, contact, receipt, and default QRIS configuration."
            />

            <Form
                {...BusinessSettingController.update.form()}
                options={{ preserveScroll: true }}
                encType="multipart/form-data"
                className="mt-6 space-y-6"
            >
                {({ processing, errors }) => (
                    <>
                        <PagePanel title="Business Identity">
                            <div className="grid gap-4 md:grid-cols-2">
                                <TextField
                                    name="business_name"
                                    label="Business name"
                                    defaultValue={businessSetting.business_name}
                                    error={errors.business_name}
                                    required
                                />
                                <TextField
                                    name="business_slug"
                                    label="Business slug"
                                    defaultValue={businessSetting.business_slug}
                                    error={errors.business_slug}
                                />
                                <TextField
                                    name="logo_path"
                                    label="Logo"
                                    type="file"
                                    error={errors.logo_path}
                                />
                                <TextField
                                    name="favicon_path"
                                    label="Favicon"
                                    type="file"
                                    error={errors.favicon_path}
                                />
                            </div>
                        </PagePanel>

                        <PagePanel title="Owner Information">
                            <div className="grid gap-4 md:grid-cols-3">
                                <TextField
                                    name="owner_name"
                                    label="Owner name"
                                    defaultValue={businessSetting.owner_name}
                                    error={errors.owner_name}
                                />
                                <TextField
                                    name="owner_phone"
                                    label="Owner phone"
                                    defaultValue={businessSetting.owner_phone}
                                    error={errors.owner_phone}
                                />
                                <TextField
                                    name="owner_email"
                                    label="Owner email"
                                    type="email"
                                    defaultValue={businessSetting.owner_email}
                                    error={errors.owner_email}
                                />
                            </div>
                        </PagePanel>

                        <PagePanel title="Default Contact">
                            <div className="grid gap-4 md:grid-cols-2">
                                <TextField
                                    name="default_phone"
                                    label="Default phone"
                                    defaultValue={businessSetting.default_phone}
                                    error={errors.default_phone}
                                />
                                <TextField
                                    name="default_whatsapp_number"
                                    label="Default WhatsApp"
                                    defaultValue={
                                        businessSetting.default_whatsapp_number
                                    }
                                    error={errors.default_whatsapp_number}
                                />
                                <TextField
                                    name="default_email"
                                    label="Default email"
                                    type="email"
                                    defaultValue={businessSetting.default_email}
                                    error={errors.default_email}
                                />
                                <TextField
                                    name="default_google_maps_url"
                                    label="Google Maps URL"
                                    defaultValue={
                                        businessSetting.default_google_maps_url
                                    }
                                    error={errors.default_google_maps_url}
                                />
                                <div className="md:col-span-2">
                                    <TextAreaField
                                        name="default_address"
                                        label="Default address"
                                        defaultValue={
                                            businessSetting.default_address
                                        }
                                        error={errors.default_address}
                                    />
                                </div>
                            </div>
                        </PagePanel>

                        <PagePanel title="Localization, Receipt, Terms">
                            <div className="grid gap-4 md:grid-cols-2">
                                <TextField
                                    name="timezone"
                                    label="Timezone"
                                    defaultValue={businessSetting.timezone}
                                    error={errors.timezone}
                                    required
                                />
                                <TextField
                                    name="currency"
                                    label="Currency"
                                    defaultValue={businessSetting.currency}
                                    error={errors.currency}
                                    required
                                />
                                <TextField
                                    name="qris_expiry_minutes"
                                    label="QRIS expiry minutes"
                                    type="number"
                                    defaultValue={
                                        businessSetting.qris_expiry_minutes
                                    }
                                    error={errors.qris_expiry_minutes}
                                    required
                                />
                                <TextAreaField
                                    name="receipt_footer_text"
                                    label="Receipt footer"
                                    defaultValue={
                                        businessSetting.receipt_footer_text
                                    }
                                    error={errors.receipt_footer_text}
                                />
                                <div className="md:col-span-2">
                                    <TextAreaField
                                        name="terms_and_conditions"
                                        label="Terms and conditions"
                                        defaultValue={
                                            businessSetting.terms_and_conditions
                                        }
                                        error={errors.terms_and_conditions}
                                    />
                                </div>
                            </div>
                        </PagePanel>

                        <Button disabled={processing}>Save settings</Button>
                    </>
                )}
            </Form>
        </>
    );
}
