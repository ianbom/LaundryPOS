import { Head } from '@inertiajs/react';
import { formatRupiah, ReportShell, SummaryCard } from './summary-card';

type RevenueMetrics = {
    total_revenue: number;
    total_cash_revenue: number;
    total_qris_revenue: number;
    total_orders_paid: number;
    average_order_value: number;
};

export default function RevenueReport({
    metrics,
}: {
    metrics: RevenueMetrics;
}) {
    return (
        <>
            <Head title="Revenue Report" />
            <ReportShell
                title="Revenue Report"
                description="Paid revenue summary across accessible outlets."
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <SummaryCard
                        label="Total Revenue"
                        value={formatRupiah(metrics.total_revenue)}
                    />
                    <SummaryCard
                        label="Cash Revenue"
                        value={formatRupiah(metrics.total_cash_revenue)}
                    />
                    <SummaryCard
                        label="QRIS Revenue"
                        value={formatRupiah(metrics.total_qris_revenue)}
                    />
                    <SummaryCard
                        label="Paid Orders"
                        value={metrics.total_orders_paid}
                    />
                    <SummaryCard
                        label="Average Order"
                        value={formatRupiah(metrics.average_order_value)}
                    />
                </div>
            </ReportShell>
        </>
    );
}
