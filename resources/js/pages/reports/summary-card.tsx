export function SummaryCard({
    label,
    value,
}: {
    label: string;
    value: string | number;
}) {
    return (
        <div className="rounded-[14px] border border-slate-200 bg-white p-5 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-bold text-slate-900">{value}</p>
        </div>
    );
}

export function ReportShell({
    title,
    description,
    children,
}: PropsWithChildren<{ title: string; description: string }>) {
    return (
        <div className="space-y-6">
            <header>
                <h1 className="text-[30px] leading-[38px] font-bold text-slate-900">
                    {title}
                </h1>
                <p className="text-sm leading-[22px] text-slate-500">
                    {description}
                </p>
            </header>
            {children}
        </div>
    );
}

export function formatRupiah(value: string | number | null | undefined) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}
import type { PropsWithChildren } from 'react';
