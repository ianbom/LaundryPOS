import { Form, Head, Link } from '@inertiajs/react';
import OrderStatusController from '@/actions/App/Http/Controllers/OrderStatusController';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/pages/reports/summary-card';
import { index, invoice } from '@/routes/orders';

type StatusHistory = {
    id: number;
    old_status: string | null;
    new_status: string;
    notes: string | null;
    created_at: string | null;
    changed_by?: { name: string };
};

type OrderShow = {
    id: number;
    invoice_number: string;
    order_status: string;
    payment_status: string;
    order_date: string | null;
    estimated_done_at: string | null;
    grand_total: string;
    customer?: { name: string; phone: string; whatsapp_number: string | null };
    outlet?: { name: string; phone: string | null; address: string | null };
    items: {
        id: number;
        service_name: string;
        variant_name: string | null;
        quantity: string;
        charged_quantity: string;
        unit: string;
        unit_price: string;
        subtotal: string;
    }[];
    status_histories: StatusHistory[];
};

const statusOptions = [
    'waiting_payment',
    'received',
    'processing',
    'washing',
    'drying',
    'ironing',
    'ready_to_pickup',
    'completed',
    'cancelled',
];

export default function OrderShow({ order }: { order: OrderShow }) {
    return (
        <>
            <Head title={order.invoice_number} />
            <div className="space-y-6">
                <header className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-[30px] leading-[38px] font-bold text-slate-900">
                            {order.invoice_number}
                        </h1>
                        <p className="text-sm text-slate-500">
                            {order.customer?.name ?? '-'} ·{' '}
                            {formatRupiah(order.grand_total)}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back</Link>
                        </Button>
                        <Button asChild>
                            <Link href={invoice(order.id)}>Invoice</Link>
                        </Button>
                    </div>
                </header>

                <div className="grid gap-6 xl:grid-cols-[1fr_360px]">
                    <section className="rounded-[14px] border border-slate-200 bg-white p-6 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                        <h2 className="text-lg font-bold">Order Items</h2>
                        <div className="mt-4 overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead>
                                    <tr className="border-b text-xs font-bold uppercase">
                                        <th className="py-3">Service</th>
                                        <th>Qty</th>
                                        <th>Charged</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {order.items.map((item) => (
                                        <tr key={item.id} className="border-b">
                                            <td className="py-3">
                                                <div className="font-semibold">
                                                    {item.service_name}
                                                </div>
                                                <div className="text-xs text-slate-500">
                                                    {item.variant_name ?? '-'}
                                                </div>
                                            </td>
                                            <td>
                                                {item.quantity} {item.unit}
                                            </td>
                                            <td>
                                                {item.charged_quantity}{' '}
                                                {item.unit}
                                            </td>
                                            <td>
                                                {formatRupiah(item.unit_price)}
                                            </td>
                                            <td>
                                                {formatRupiah(item.subtotal)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-6 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                        <h2 className="text-lg font-bold">Update Status</h2>
                        <Form
                            {...OrderStatusController.update.form(order.id)}
                            options={{ preserveScroll: true }}
                            className="mt-4 space-y-3"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <select
                                        name="status"
                                        defaultValue={order.order_status}
                                        className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm"
                                    >
                                        {statusOptions.map((status) => (
                                            <option key={status} value={status}>
                                                {status}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.status && (
                                        <p className="text-sm text-red-600">
                                            {errors.status}
                                        </p>
                                    )}
                                    <textarea
                                        name="notes"
                                        placeholder="Notes"
                                        className="min-h-24 w-full rounded-xl border border-slate-300 p-3 text-sm"
                                    />
                                    <Button disabled={processing}>
                                        Save status
                                    </Button>
                                </>
                            )}
                        </Form>
                    </section>
                </div>

                <section className="rounded-[14px] border border-slate-200 bg-white p-6 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <h2 className="text-lg font-bold">Status Timeline</h2>
                    <div className="mt-4 space-y-3">
                        {order.status_histories.map((history) => (
                            <div
                                key={history.id}
                                className="rounded-xl border border-slate-200 p-4"
                            >
                                <div className="font-semibold text-slate-900">
                                    {history.old_status ?? 'created'} →{' '}
                                    {history.new_status}
                                </div>
                                <div className="text-sm text-slate-500">
                                    {history.created_at ?? '-'} ·{' '}
                                    {history.changed_by?.name ?? 'System'}
                                </div>
                                {history.notes && (
                                    <p className="mt-2 text-sm text-slate-700">
                                        {history.notes}
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}
