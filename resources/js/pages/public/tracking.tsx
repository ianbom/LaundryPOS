import { Head } from '@inertiajs/react';
import { formatRupiah } from '@/pages/reports/summary-card';

type Tracking = {
    business: { name: string; logo_path: string | null };
    outlet: {
        name: string;
        address: string | null;
        phone: string | null;
        whatsapp_number: string | null;
    };
    customer: { name: string };
    order: {
        invoice_number: string;
        order_date: string | null;
        estimated_done_at: string | null;
        order_status: string;
        payment_status: string;
        grand_total: string;
    };
    items: {
        id: number;
        service_name: string;
        variant_name: string | null;
        quantity: string;
        unit: string;
        subtotal: string;
    }[];
    timeline: {
        id: number;
        new_status: string;
        notes: string | null;
        created_at: string | null;
    }[];
};

export default function PublicTracking({ tracking }: { tracking: Tracking }) {
    return (
        <>
            <Head title={`Tracking ${tracking.order.invoice_number}`} />
            <main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-900">
                <div className="mx-auto max-w-2xl space-y-4">
                    <section className="rounded-[14px] bg-blue-600 p-5 text-white shadow-[0_4px_12px_rgba(15,23,42,0.12)]">
                        <p className="text-sm opacity-80">
                            {tracking.business.name}
                        </p>
                        <h1 className="mt-2 text-2xl font-bold">
                            {tracking.order.order_status}
                        </h1>
                        <p className="mt-1 text-sm opacity-90">
                            Invoice {tracking.order.invoice_number}
                        </p>
                    </section>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-5">
                        <h2 className="font-bold">Ringkasan</h2>
                        <dl className="mt-3 grid gap-3 text-sm">
                            <div className="flex justify-between">
                                <dt>Customer</dt>
                                <dd className="font-semibold">
                                    {tracking.customer.name}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt>Payment</dt>
                                <dd className="font-semibold">
                                    {tracking.order.payment_status}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt>Total</dt>
                                <dd className="font-semibold">
                                    {formatRupiah(tracking.order.grand_total)}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-5">
                        <h2 className="font-bold">Timeline</h2>
                        <div className="mt-4 space-y-3">
                            {tracking.timeline.map((item) => (
                                <div
                                    key={item.id}
                                    className="border-l-2 border-blue-600 pl-3"
                                >
                                    <div className="font-semibold">
                                        {item.new_status}
                                    </div>
                                    <div className="text-xs text-slate-500">
                                        {item.created_at ?? '-'}
                                    </div>
                                    {item.notes && (
                                        <p className="mt-1 text-sm">
                                            {item.notes}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-5">
                        <h2 className="font-bold">Items</h2>
                        <div className="mt-3 divide-y">
                            {tracking.items.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex justify-between py-3 text-sm"
                                >
                                    <div>
                                        <div className="font-semibold">
                                            {item.service_name}
                                        </div>
                                        <div className="text-slate-500">
                                            {item.variant_name ?? '-'} ·{' '}
                                            {item.quantity} {item.unit}
                                        </div>
                                    </div>
                                    <div className="font-semibold">
                                        {formatRupiah(item.subtotal)}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-5">
                        <h2 className="font-bold">{tracking.outlet.name}</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {tracking.outlet.address ?? '-'}
                        </p>
                    </section>
                </div>
            </main>
        </>
    );
}
