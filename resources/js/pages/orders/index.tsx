import { Form, Head, Link } from '@inertiajs/react';
import { Eye, Plus, Printer } from 'lucide-react';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import POSOrderController from '@/actions/App/Http/Controllers/POSOrderController';
import { Button } from '@/components/ui/button';
import { Paginated, Pagination } from '@/pages/master-data/shared';

type OrderRow = {
    id: number;
    invoice_number: string;
    order_date: string | null;
    grand_total: string;
    payment_status: string;
    order_status: string;
    customer?: { name: string; phone: string; whatsapp_number: string | null };
    outlet?: { name: string };
    creator?: { name: string };
    active_payment?: { method: string; provider: string; status: string } | null;
};

function money(value: string | number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value));
}

function statusClass(status: string) {
    if (['paid', 'completed'].includes(status)) {
        return 'bg-green-100 text-green-700';
    }

    if (['pending', 'waiting_payment'].includes(status)) {
        return 'bg-amber-100 text-amber-700';
    }

    if (['cancelled', 'failed', 'expired'].includes(status)) {
        return 'bg-red-100 text-red-700';
    }

    if (['ready_to_pickup'].includes(status)) {
        return 'bg-cyan-100 text-cyan-700';
    }

    return 'bg-blue-100 text-blue-700';
}

export default function OrdersIndex({
    orders,
    filters,
}: {
    orders: Paginated<OrderRow>;
    filters: { search?: string; payment_status?: string; order_status?: string };
}) {
    return (
        <>
            <Head title="Orders" />
            <div className="space-y-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">
                            Orders
                        </h1>
                        <p className="text-sm text-slate-500">
                            Monitor transaksi laundry, status pembayaran, dan
                            progress order.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={POSOrderController.index.url()}>
                            <Plus className="size-4" />
                            Create Order
                        </Link>
                    </Button>
                </div>

                <Form
                    action={OrderController.index.url()}
                    method="get"
                    className="grid gap-3 rounded-[14px] border border-slate-200 bg-white p-4 shadow-[0_4px_12px_rgba(15,23,42,0.06)] md:grid-cols-4"
                >
                    <input
                        name="search"
                        defaultValue={filters.search}
                        placeholder="Invoice, customer, phone"
                        className="h-9 rounded-md border px-3 text-sm"
                    />
                    <select
                        name="payment_status"
                        defaultValue={filters.payment_status ?? ''}
                        className="h-9 rounded-md border px-3 text-sm"
                    >
                        <option value="">All payment status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                    <select
                        name="order_status"
                        defaultValue={filters.order_status ?? ''}
                        className="h-9 rounded-md border px-3 text-sm"
                    >
                        <option value="">All order status</option>
                        <option value="waiting_payment">Waiting payment</option>
                        <option value="received">Received</option>
                        <option value="processing">Processing</option>
                        <option value="ready_to_pickup">Ready pickup</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <Button variant="outline">Filter</Button>
                </Form>

                <div className="overflow-hidden rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[1000px] text-sm">
                            <thead className="border-b text-left text-xs font-bold text-slate-900">
                                <tr>
                                    <th className="p-3">Invoice</th>
                                    <th className="p-3">Customer</th>
                                    <th className="p-3">Outlet</th>
                                    <th className="p-3">Date</th>
                                    <th className="p-3">Total</th>
                                    <th className="p-3">Payment</th>
                                    <th className="p-3">Laundry</th>
                                    <th className="p-3">Created By</th>
                                    <th className="p-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.data.map((order) => (
                                    <tr
                                        key={order.id}
                                        className="border-b hover:bg-slate-50"
                                    >
                                        <td className="p-3">
                                            <Link
                                                href={OrderController.show.url(
                                                    order.id,
                                                )}
                                                className="font-semibold text-blue-600"
                                            >
                                                {order.invoice_number}
                                            </Link>
                                        </td>
                                        <td className="p-3">
                                            <p className="font-medium">
                                                {order.customer?.name ?? '-'}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                {order.customer
                                                    ?.whatsapp_number ??
                                                    order.customer?.phone}
                                            </p>
                                        </td>
                                        <td className="p-3">
                                            {order.outlet?.name ?? '-'}
                                        </td>
                                        <td className="p-3">
                                            {order.order_date ?? '-'}
                                        </td>
                                        <td className="p-3 font-semibold">
                                            {money(order.grand_total)}
                                        </td>
                                        <td className="p-3">
                                            <span
                                                className={`rounded-md px-2 py-1 text-xs font-bold ${statusClass(order.payment_status)}`}
                                            >
                                                {order.payment_status}
                                            </span>
                                        </td>
                                        <td className="p-3">
                                            <span
                                                className={`rounded-md px-2 py-1 text-xs font-bold ${statusClass(order.order_status)}`}
                                            >
                                                {order.order_status}
                                            </span>
                                        </td>
                                        <td className="p-3">
                                            {order.creator?.name ?? '-'}
                                        </td>
                                        <td className="p-3">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={OrderController.show.url(
                                                            order.id,
                                                        )}
                                                    >
                                                        <Eye className="size-4" />
                                                        Detail
                                                    </Link>
                                                </Button>
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={OrderController.invoice.url(
                                                            order.id,
                                                        )}
                                                    >
                                                        <Printer className="size-4" />
                                                        Invoice
                                                    </Link>
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {orders.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={9}
                                            className="p-8 text-center text-slate-500"
                                        >
                                            No orders found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
                <Pagination links={orders.links} />
            </div>
        </>
    );
}
