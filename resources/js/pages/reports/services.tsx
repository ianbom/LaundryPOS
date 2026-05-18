import { Head } from '@inertiajs/react';
import { formatRupiah, ReportShell } from './summary-card';

type ServiceRow = {
    service_name: string;
    variant_name: string | null;
    total_quantity: number;
    total_charged_quantity: number;
    total_orders: number;
    total_revenue: number;
};

export default function ServiceReport({ rows }: { rows: ServiceRow[] }) {
    return (
        <>
            <Head title="Service Report" />
            <ReportShell
                title="Service Report"
                description="Top paid services by quantity and revenue."
            >
                <div className="overflow-x-auto rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="border-b text-xs font-bold text-slate-900 uppercase">
                                <th className="px-4 py-3">Service</th>
                                <th>Variant</th>
                                <th>Quantity</th>
                                <th>Charged</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr
                                    key={`${row.service_name}-${row.variant_name}`}
                                    className="border-b hover:bg-slate-50"
                                >
                                    <td className="px-4 py-3 font-semibold">
                                        {row.service_name}
                                    </td>
                                    <td>{row.variant_name ?? '-'}</td>
                                    <td>{row.total_quantity}</td>
                                    <td>{row.total_charged_quantity}</td>
                                    <td>{row.total_orders}</td>
                                    <td>{formatRupiah(row.total_revenue)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </ReportShell>
        </>
    );
}
