import type { PropsWithChildren, ReactNode } from 'react';

export function PagePanel({
    children,
    title,
    actions,
}: PropsWithChildren<{ title?: string; actions?: ReactNode }>) {
    return (
        <section className="rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
            {(title || actions) && (
                <div className="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
                    {title && (
                        <h2 className="text-lg font-bold text-slate-900">
                            {title}
                        </h2>
                    )}
                    {actions}
                </div>
            )}
            <div className="p-6">{children}</div>
        </section>
    );
}

export function PageHeader({
    title,
    description,
    actions,
}: {
    title: string;
    description: string;
    actions?: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 className="text-[30px] leading-[38px] font-bold text-slate-900">
                    {title}
                </h1>
                <p className="mt-1 text-sm leading-6 text-slate-500">
                    {description}
                </p>
            </div>
            {actions}
        </div>
    );
}
