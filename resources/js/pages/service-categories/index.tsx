import { Form, Head } from '@inertiajs/react';
import ServiceCategoryController from '@/actions/App/Http/Controllers/ServiceCategoryController';
import {
    DeleteButton,
    EditButton,
    PageHeader,
    Paginated,
    Pagination,
    StatusBadge,
    TextFilter,
} from '@/pages/master-data/shared';

type Category = {
    id: number;
    name: string;
    sort_order: number;
    is_active: boolean;
    services_count: number;
    outlet?: { name: string } | null;
};

export default function ServiceCategoriesIndex({
    categories,
    filters,
}: {
    categories: Paginated<Category>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title="Service Categories" />
            <div className="space-y-6">
                <PageHeader
                    title="Service Categories"
                    description="Group laundry services by outlet and type."
                    createHref={ServiceCategoryController.create.url()}
                />
                <TextFilter
                    action={ServiceCategoryController.index.url()}
                    defaultValue={filters.search}
                    placeholder="Search categories"
                />
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Name</th>
                                <th className="p-3">Outlet</th>
                                <th className="p-3">Services</th>
                                <th className="p-3">Sort</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {categories.data.map((category) => (
                                <tr key={category.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {category.name}
                                    </td>
                                    <td className="p-3">
                                        {category.outlet?.name ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {category.services_count}
                                    </td>
                                    <td className="p-3">
                                        {category.sort_order}
                                    </td>
                                    <td className="p-3">
                                        <StatusBadge
                                            active={category.is_active}
                                        />
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-2">
                                            <EditButton
                                                href={ServiceCategoryController.edit.url(
                                                    category.id,
                                                )}
                                            />
                                            <Form
                                                {...ServiceCategoryController.toggleActive.form(
                                                    category.id,
                                                )}
                                            >
                                                <button className="h-8 rounded-md border px-3 text-xs">
                                                    Toggle
                                                </button>
                                            </Form>
                                            <DeleteButton
                                                action={ServiceCategoryController.destroy.url(
                                                    category.id,
                                                )}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {categories.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No service categories found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={categories.links} />
            </div>
        </>
    );
}
