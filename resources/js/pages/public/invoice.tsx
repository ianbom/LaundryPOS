import { Head, Link } from '@inertiajs/react';
import { MessageCircle, Printer, ReceiptText } from 'lucide-react';
import { Button } from '@/components/ui/button';

type PublicInvoiceProps = {
    invoice: {
        business: {
            name: string;
            logo_path: string | null;
            receipt_footer_text: string | null;
            terms_and_conditions: string | null;
        };
        outlet: {
            name: string;
            address: string | null;
            phone: string | null;
            whatsapp_number: string | null;
        };
        customer: { name: string; phone: string };
        order: {
            invoice_number: string;
            order_date: string | null;
            subtotal: string;
            discount_amount: string;
            additional_fee: string;
            delivery_fee: string;
            grand_total: string;
            payment_status: string;
            tracking_token: string;
        };
        payment: {
            method: string;
            status: string;
            paid_at: string | null;
        } | null;
        items: InvoiceItem[];
        tracking_url: string;
    };
};

type InvoiceItem = {
    id: number;
    service_name: string;
    variant_name: string | null;
    unit: string;
    charged_quantity: string;
    unit_price: string;
    subtotal: string;
};

export default function PublicInvoice({ invoice }: PublicInvoiceProps) {
    const whatsappPhone =
        invoice.outlet.whatsapp_number ?? invoice.outlet.phone ?? '';

    return (
        <>
            <Head title={`Invoice ${invoice.order.invoice_number}`} />
            <main className="min-h-screen bg-slate-50 px-4 py-5 text-slate-900 sm:px-6 sm:py-8 print:bg-white print:p-0">
                <div className="mx-auto max-w-3xl">
                    <div className="mb-4 flex flex-wrap gap-2 print:hidden">
                        <Button onClick={() => window.print()}>
                            <Printer className="size-4" />
                            Print Invoice
                        </Button>
                        <Button asChild variant="outline">
                            <Link
                                href={`/track/${invoice.order.tracking_token}`}
                            >
                                Tracking
                            </Link>
                        </Button>
                        {whatsappPhone && (
                            <Button asChild variant="outline">
                                <a
                                    href={`https://wa.me/${normalizePhone(whatsappPhone)}`}
                                >
                                    <MessageCircle className="size-4" />
                                    WhatsApp Outlet
                                </a>
                            </Button>
                        )}
                    </div>

                    <section className="rounded-[14px] border border-slate-200 bg-white p-6 shadow-[0_4px_12px_rgba(15,23,42,0.06)] print:rounded-none print:border-0 print:shadow-none">
                        <header className="flex items-start justify-between gap-5 border-b border-slate-200 pb-5">
                            <div className="flex gap-3">
                                <div className="flex size-12 items-center justify-center rounded-[14px] bg-blue-600 text-white">
                                    <ReceiptText className="size-6" />
                                </div>
                                <div>
                                    <h1 className="text-xl font-bold">
                                        {invoice.business.name}
                                    </h1>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {invoice.outlet.name}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {invoice.outlet.address ?? '-'}
                                    </p>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-xs font-bold text-slate-500 uppercase">
                                    Invoice
                                </p>
                                <p className="mt-1 text-lg font-bold text-blue-600">
                                    {invoice.order.invoice_number}
                                </p>
                            </div>
                        </header>

                        <section className="grid gap-4 border-b border-slate-200 py-5 sm:grid-cols-2">
                            <Info
                                label="Customer"
                                value={invoice.customer.name}
                            />
                            <Info
                                label="Phone"
                                value={invoice.customer.phone}
                            />
                            <Info
                                label="Order Date"
                                value={formatDate(invoice.order.order_date)}
                            />
                            <Info
                                label="Payment"
                                value={`${invoice.payment?.method?.toUpperCase() ?? '-'} · ${statusLabel(invoice.order.payment_status)}`}
                            />
                        </section>

                        <section className="py-5">
                            <h2 className="text-sm font-bold text-slate-500 uppercase">
                                Order Items
                            </h2>
                            <div className="mt-3 overflow-hidden rounded-[10px] border border-slate-200">
                                <table className="w-full text-sm">
                                    <thead className="bg-slate-50 text-left">
                                        <tr>
                                            <th className="p-3">Layanan</th>
                                            <th className="p-3 text-right">
                                                Qty
                                            </th>
                                            <th className="p-3 text-right">
                                                Harga
                                            </th>
                                            <th className="p-3 text-right">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {invoice.items.map((item) => (
                                            <tr
                                                key={item.id}
                                                className="border-t border-slate-200"
                                            >
                                                <td className="p-3">
                                                    <p className="font-semibold">
                                                        {item.service_name}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        {item.variant_name ??
                                                            '-'}
                                                    </p>
                                                </td>
                                                <td className="p-3 text-right">
                                                    {item.charged_quantity}{' '}
                                                    {item.unit}
                                                </td>
                                                <td className="p-3 text-right">
                                                    {formatRupiah(
                                                        item.unit_price,
                                                    )}
                                                </td>
                                                <td className="p-3 text-right font-semibold">
                                                    {formatRupiah(
                                                        item.subtotal,
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section className="ml-auto w-full max-w-sm space-y-2 border-t border-slate-200 pt-5 text-sm">
                            <AmountRow
                                label="Subtotal"
                                value={invoice.order.subtotal}
                            />
                            <AmountRow
                                label="Diskon"
                                value={invoice.order.discount_amount}
                            />
                            <AmountRow
                                label="Biaya tambahan"
                                value={invoice.order.additional_fee}
                            />
                            <AmountRow
                                label="Delivery"
                                value={invoice.order.delivery_fee}
                            />
                            <div className="flex justify-between border-t border-slate-200 pt-3 text-lg font-bold">
                                <span>Grand Total</span>
                                <span>
                                    {formatRupiah(invoice.order.grand_total)}
                                </span>
                            </div>
                        </section>

                        <footer className="mt-8 space-y-3 text-center text-sm text-slate-500">
                            {invoice.business.receipt_footer_text && (
                                <p>{invoice.business.receipt_footer_text}</p>
                            )}
                            {invoice.business.terms_and_conditions && (
                                <p>{invoice.business.terms_and_conditions}</p>
                            )}
                            <p>Tracking: {invoice.tracking_url}</p>
                        </footer>
                    </section>
                </div>
            </main>
        </>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-xs font-bold text-slate-500 uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-semibold">{value}</p>
        </div>
    );
}

function AmountRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between">
            <span className="text-slate-500">{label}</span>
            <span className="font-semibold">{formatRupiah(value)}</span>
        </div>
    );
}

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatRupiah(value: string) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value));
}

function statusLabel(value: string) {
    return value
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function normalizePhone(value: string) {
    const phone = value.replace(/[^0-9+]/g, '').replace(/^\+/, '');

    return phone.startsWith('0') ? `62${phone.slice(1)}` : phone;
}
