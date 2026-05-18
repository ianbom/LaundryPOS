import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Printer } from 'lucide-react';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import { Button } from '@/components/ui/button';

type Order = {
    id: number;
    invoice_number: string;
    order_date: string | null;
    subtotal: string;
    discount_amount: string;
    additional_fee: string;
    delivery_fee: string;
    grand_total: string;
    payment_status: string;
    customer: {
        name: string;
        phone: string;
        address: string | null;
    };
    outlet: {
        name: string;
        phone: string | null;
        address: string | null;
    };
    active_payment?: {
        method: string;
        provider: string;
        paid_at: string | null;
    } | null;
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
};

type BusinessSetting = {
    business_name: string;
    receipt_footer_text: string | null;
    terms_and_conditions: string | null;
};

function money(value: string | number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value));
}

export default function OrderInvoice({
    order,
    businessSettings,
    trackingUrl,
}: {
    order: Order;
    businessSettings: BusinessSetting;
    trackingUrl: string;
}) {
    return (
        <>
            <Head title={`Invoice ${order.invoice_number}`} />
            <div className="mx-auto max-w-4xl space-y-4">
                <div className="flex justify-between print:hidden">
                    <Button asChild variant="ghost">
                        <Link href={OrderController.show.url(order.id)}>
                            <ArrowLeft className="size-4" />
                            Back
                        </Link>
                    </Button>
                    <Button onClick={() => window.print()}>
                        <Printer className="size-4" />
                        Print
                    </Button>
                </div>

                <div className="rounded-[14px] border border-slate-200 bg-white p-8 shadow-[0_4px_12px_rgba(15,23,42,0.06)] print:border-0 print:shadow-none">
                    <div className="flex flex-col gap-6 border-b pb-6 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p className="text-2xl font-bold text-slate-900">
                                {businessSettings.business_name}
                            </p>
                            <p className="mt-1 text-sm text-slate-500">
                                {order.outlet.name}
                            </p>
                            <p className="text-sm text-slate-500">
                                {order.outlet.address ?? '-'}
                            </p>
                            <p className="text-sm text-slate-500">
                                {order.outlet.phone ?? '-'}
                            </p>
                        </div>
                        <div className="text-left sm:text-right">
                            <p className="text-sm font-semibold uppercase text-blue-600">
                                Invoice
                            </p>
                            <p className="text-xl font-bold text-slate-900">
                                {order.invoice_number}
                            </p>
                            <p className="text-sm text-slate-500">
                                {order.order_date ?? '-'}
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-6 border-b py-6 sm:grid-cols-2">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">
                                Bill To
                            </p>
                            <p className="mt-2 font-medium">
                                {order.customer.name}
                            </p>
                            <p className="text-sm text-slate-500">
                                {order.customer.phone}
                            </p>
                            <p className="text-sm text-slate-500">
                                {order.customer.address ?? '-'}
                            </p>
                        </div>
                        <div className="space-y-1 text-sm sm:text-right">
                            <p>
                                Payment:{' '}
                                <span className="font-semibold">
                                    {order.active_payment?.method ?? '-'}
                                </span>
                            </p>
                            <p>
                                Status:{' '}
                                <span className="font-semibold">
                                    {order.payment_status}
                                </span>
                            </p>
                            <p>Paid at: {order.active_payment?.paid_at ?? '-'}</p>
                        </div>
                    </div>

                    <table className="mt-6 w-full text-sm">
                        <thead className="border-b text-left text-xs font-bold uppercase text-slate-500">
                            <tr>
                                <th className="py-3">Item</th>
                                <th className="py-3">Qty</th>
                                <th className="py-3">Price</th>
                                <th className="py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {order.items.map((item) => (
                                <tr key={item.id} className="border-b">
                                    <td className="py-3">
                                        <p className="font-semibold">
                                            {item.service_name}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {item.variant_name}
                                        </p>
                                    </td>
                                    <td className="py-3">
                                        {item.charged_quantity} {item.unit}
                                    </td>
                                    <td className="py-3">
                                        {money(item.unit_price)}
                                    </td>
                                    <td className="py-3 text-right font-semibold">
                                        {money(item.subtotal)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    <div className="mt-6 flex justify-end">
                        <div className="w-full max-w-xs space-y-2 text-sm">
                            <TotalRow
                                label="Subtotal"
                                value={money(order.subtotal)}
                            />
                            <TotalRow
                                label="Discount"
                                value={money(order.discount_amount)}
                            />
                            <TotalRow
                                label="Additional fee"
                                value={money(order.additional_fee)}
                            />
                            <TotalRow
                                label="Delivery fee"
                                value={money(order.delivery_fee)}
                            />
                            <div className="flex justify-between border-t pt-3 text-lg font-bold">
                                <span>Grand Total</span>
                                <span>{money(order.grand_total)}</span>
                            </div>
                        </div>
                    </div>

                    <div className="mt-8 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                        <p>Tracking URL: {trackingUrl}</p>
                    </div>

                    <div className="mt-8 text-center text-sm text-slate-500">
                        <p>
                            {businessSettings.receipt_footer_text ??
                                'Terima kasih sudah menggunakan layanan kami.'}
                        </p>
                        {businessSettings.terms_and_conditions && (
                            <p className="mt-2">
                                {businessSettings.terms_and_conditions}
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

function TotalRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between">
            <span className="text-slate-500">{label}</span>
            <span className="font-semibold">{value}</span>
        </div>
    );
}
