import { Head } from '@inertiajs/react';
import { formatRupiah, ReportShell } from './summary-card';

type CustomerRow = {
    id: number;
    name: string;
    phone: string;
    total_orders: number;
    total_spent: string;
    orders_max_order_date?: string | null;
};

export default function CustomerReport({ rows }: { rows: CustomerRow[] }) {
    return (
        <>
            <Head title="Customer Report" />
            <ReportShell
                title="Customer Report"
                description="Customer order frequency, spend, and last order."
            >
                <div className="overflow-x-auto rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="border-b text-xs font-bold text-slate-900 uppercase">
                                <th className="px-4 py-3">Customer</th>
                                <th>Phone</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Last Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr
                                    key={row.id}
                                    className="border-b hover:bg-slate-50"
                                >
                                    <td className="px-4 py-3 font-semibold">
                                        {row.name}
                                    </td>
                                    <td>{row.phone}</td>
                                    <td>{row.total_orders}</td>
                                    <td>{formatRupiah(row.total_spent)}</td>
                                    <td>{row.orders_max_order_date ?? '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </ReportShell>
        </>
    );
}
