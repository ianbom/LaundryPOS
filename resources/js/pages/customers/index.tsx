import { Head } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import {
    DeleteButton,
    EditButton,
    PageHeader,
    Paginated,
    Pagination,
    TextFilter,
} from '@/pages/master-data/shared';

type Customer = {
    id: number;
    name: string;
    phone: string;
    whatsapp_number: string | null;
    total_orders: number;
    total_spent: string;
    outlet?: { name: string } | null;
};

export default function CustomersIndex({
    customers,
    filters,
}: {
    customers: Paginated<Customer>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title="Customers" />
            <div className="space-y-6">
                <PageHeader
                    title="Customers"
                    description="Manage laundry customers and contact details."
                    createHref={CustomerController.create.url()}
                />
                <TextFilter
                    action={CustomerController.index.url()}
                    defaultValue={filters.search}
                    placeholder="Search name, phone, WhatsApp"
                />
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Name</th>
                                <th className="p-3">Outlet</th>
                                <th className="p-3">Phone</th>
                                <th className="p-3">Orders</th>
                                <th className="p-3">Spent</th>
                                <th className="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {customers.data.map((customer) => (
                                <tr key={customer.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {customer.name}
                                    </td>
                                    <td className="p-3">
                                        {customer.outlet?.name ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {customer.whatsapp_number ??
                                            customer.phone}
                                    </td>
                                    <td className="p-3">
                                        {customer.total_orders}
                                    </td>
                                    <td className="p-3">
                                        {customer.total_spent}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-2">
                                            <EditButton
                                                href={CustomerController.edit.url(
                                                    customer.id,
                                                )}
                                            />
                                            <DeleteButton
                                                action={CustomerController.destroy.url(
                                                    customer.id,
                                                )}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {customers.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No customers found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={customers.links} />
            </div>
        </>
    );
}
