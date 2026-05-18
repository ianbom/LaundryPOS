import { Form, Head } from '@inertiajs/react';
import WhatsAppTemplateController from '@/actions/App/Http/Controllers/Settings/WhatsAppTemplateController';
import {
    DeleteButton,
    EditButton,
    PageHeader,
    Paginated,
    Pagination,
    StatusBadge,
    TextFilter,
} from '@/pages/master-data/shared';

type Template = {
    id: number;
    type: string;
    title: string;
    is_active: boolean;
    updated_at: string;
    outlet?: { name: string } | null;
};

export default function WhatsAppTemplatesIndex({
    templates,
    filters,
}: {
    templates: Paginated<Template>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title="WhatsApp Templates" />
            <div className="space-y-6">
                <PageHeader
                    title="WhatsApp Templates"
                    description="Manage notification templates and outlet overrides."
                    createHref={WhatsAppTemplateController.create.url()}
                />
                <TextFilter
                    action={WhatsAppTemplateController.index.url()}
                    defaultValue={filters.search}
                    placeholder="Search templates"
                />
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Title</th>
                                <th className="p-3">Type</th>
                                <th className="p-3">Scope</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {templates.data.map((template) => (
                                <tr key={template.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {template.title}
                                    </td>
                                    <td className="p-3">{template.type}</td>
                                    <td className="p-3">
                                        {template.outlet?.name ?? 'Global'}
                                    </td>
                                    <td className="p-3">
                                        <StatusBadge
                                            active={template.is_active}
                                        />
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-2">
                                            <EditButton
                                                href={WhatsAppTemplateController.edit.url(
                                                    template.id,
                                                )}
                                            />
                                            <Form
                                                {...WhatsAppTemplateController.toggleActive.form(
                                                    template.id,
                                                )}
                                            >
                                                <button className="h-8 rounded-md border px-3 text-xs">
                                                    Toggle
                                                </button>
                                            </Form>
                                            <DeleteButton
                                                action={WhatsAppTemplateController.destroy.url(
                                                    template.id,
                                                )}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {templates.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No WhatsApp templates found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={templates.links} />
            </div>
        </>
    );
}
