import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { transactions } from '@/routes/reports';
import { formatRupiah, ReportShell } from './summary-card';

type TransactionRow = {
    id: number;
    invoice_number: string;
    order_date: string | null;
    payment_status: string;
    order_status: string;
    subtotal: string;
    discount_amount: string;
    additional_fee: string;
    delivery_fee: string;
    grand_total: string;
    outlet?: { name: string };
    customer?: { name: string; phone: string };
    active_payment?: { method: string; paid_at: string | null };
    creator?: { name: string };
};

export default function TransactionReport({
    rows,
}: {
    rows: TransactionRow[];
}) {
    return (
        <>
            <Head title="Transaction Report" />
            <ReportShell
                title="Transaction Report"
                description="Audit order transactions, payment state, and totals by outlet."
            >
                <div className="flex justify-end">
                    <Button asChild>
                        <Link href={transactions({ query: { export: 'csv' } })}>
                            Export CSV
                        </Link>
                    </Button>
                </div>
                <div className="overflow-x-auto rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="border-b text-xs font-bold text-slate-900 uppercase">
                                <th className="px-4 py-3">Invoice</th>
                                <th>Outlet</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr
                                    key={row.id}
                                    className="border-b hover:bg-slate-50"
                                >
                                    <td className="px-4 py-3 font-semibold text-blue-600">
                                        {row.invoice_number}
                                    </td>
                                    <td>{row.outlet?.name ?? '-'}</td>
                                    <td>
                                        <div>{row.customer?.name ?? '-'}</div>
                                        <div className="text-xs text-slate-500">
                                            {row.customer?.phone ?? '-'}
                                        </div>
                                    </td>
                                    <td className="capitalize">
                                        {row.active_payment?.method ?? '-'}
                                    </td>
                                    <td>
                                        {row.payment_status} /{' '}
                                        {row.order_status}
                                    </td>
                                    <td>{formatRupiah(row.grand_total)}</td>
                                    <td>{row.creator?.name ?? '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </ReportShell>
        </>
    );
}
