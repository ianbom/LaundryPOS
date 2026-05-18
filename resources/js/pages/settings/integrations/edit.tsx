import { Form, Head } from '@inertiajs/react';
import IntegrationSettingController from '@/actions/App/Http/Controllers/Settings/IntegrationSettingController';
import { CheckboxField, TextField } from '@/components/phase2/form-controls';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Button } from '@/components/ui/button';
import type { IntegrationSettings } from '@/types';

export default function EditIntegrationSettings({
    integrationSettings,
}: {
    integrationSettings: IntegrationSettings;
}) {
    return (
        <>
            <Head title="Integration Settings" />

            <PageHeader
                title="Integration Settings"
                description="Configure Midtrans QRIS and WhatsApp provider credentials."
            />

            <Form
                {...IntegrationSettingController.update.form()}
                options={{ preserveScroll: true }}
                className="mt-6 space-y-6"
            >
                {({ processing, errors }) => (
                    <>
                        <PagePanel title="Midtrans Settings">
                            <div className="grid gap-4 md:grid-cols-2">
                                <TextField
                                    name="midtrans_server_key"
                                    label="Server key"
                                    placeholder={
                                        integrationSettings.midtrans_server_key_masked ??
                                        'Enter new server key'
                                    }
                                    error={errors.midtrans_server_key}
                                />
                                <TextField
                                    name="midtrans_client_key"
                                    label="Client key"
                                    placeholder={
                                        integrationSettings.midtrans_client_key_masked ??
                                        'Enter new client key'
                                    }
                                    error={errors.midtrans_client_key}
                                />
                                <TextField
                                    name="qris_expiry_minutes"
                                    label="QRIS expiry minutes"
                                    type="number"
                                    defaultValue={
                                        integrationSettings.qris_expiry_minutes
                                    }
                                    error={errors.qris_expiry_minutes}
                                    required
                                />
                                <CheckboxField
                                    name="midtrans_is_production"
                                    label="Use production environment"
                                    defaultChecked={
                                        integrationSettings.midtrans_is_production
                                    }
                                />
                            </div>
                        </PagePanel>

                        <PagePanel title="WhatsApp Settings">
                            <div className="grid gap-4 md:grid-cols-3">
                                <TextField
                                    name="whatsapp_provider"
                                    label="Provider"
                                    defaultValue={
                                        integrationSettings.whatsapp_provider ??
                                        'fonnte'
                                    }
                                    error={errors.whatsapp_provider}
                                />
                                <TextField
                                    name="whatsapp_api_key"
                                    label="API key"
                                    placeholder={
                                        integrationSettings.whatsapp_api_key_masked ??
                                        'Enter new API key'
                                    }
                                    error={errors.whatsapp_api_key}
                                />
                                <TextField
                                    name="whatsapp_sender_number"
                                    label="Sender number"
                                    defaultValue={
                                        integrationSettings.whatsapp_sender_number
                                    }
                                    error={errors.whatsapp_sender_number}
                                />
                            </div>
                        </PagePanel>

                        <div className="flex gap-3">
                            <Button disabled={processing}>
                                Save integrations
                            </Button>
                            <Button
                                type="submit"
                                formAction={IntegrationSettingController.testMidtrans.url()}
                                variant="outline"
                            >
                                Test Midtrans
                            </Button>
                            <Button
                                type="submit"
                                formAction={IntegrationSettingController.testWhatsapp.url()}
                                variant="outline"
                            >
                                Test WhatsApp
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}
