import { Head } from '@inertiajs/react';
import {
    ArrowRight,
    Banknote,
    CheckCircle2,
    ClipboardList,
    Droplets,
    Eye,
    Fan,
    MoreHorizontal,
    PackageCheck,
    Plus,
    Printer,
    QrCode,
    RefreshCcw,
    Search,
    Send,
    ShoppingBag,
    UserPlus,
    Wallet,
    WashingMachine,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { dashboard } from '@/routes';

type Metric = {
    label: string;
    value: string;
    helper: string;
    helperAccent?: string;
    icon: LucideIcon;
    tone: 'green' | 'blue' | 'violet' | 'cyan' | 'amber';
};

type Status = {
    label: string;
    value: string;
    icon: LucideIcon;
    tone: 'blue' | 'cyan' | 'amber' | 'violet' | 'green' | 'red';
};

type BadgeTone =
    | 'paid'
    | 'pending'
    | 'failed'
    | 'ready'
    | 'processing'
    | 'washing'
    | 'cancelled'
    | 'completed'
    | 'ironing';

type Order = {
    invoice: string;
    customer: string;
    phone: string;
    service: string;
    quantity: string;
    total: string;
    method: string;
    paymentStatus: string;
    paymentTone: BadgeTone;
    laundryStatus: string;
    laundryTone: BadgeTone;
    createdAt: string;
};

const metrics: Metric[] = [
    {
        label: "Today's Revenue",
        value: 'Rp 8.450.000',
        helper: '+8.2% from yesterday',
        icon: Banknote,
        tone: 'green',
    },
    {
        label: "Today's Orders",
        value: '56',
        helper: '+12.5% from yesterday',
        icon: ClipboardList,
        tone: 'blue',
    },
    {
        label: 'Active Orders',
        value: '128',
        helper: '3 orders awaiting confirmation',
        helperAccent: '3',
        icon: WashingMachine,
        tone: 'violet',
    },
    {
        label: 'Ready for Pickup',
        value: '42',
        helper: '+5 from yesterday',
        icon: ShoppingBag,
        tone: 'cyan',
    },
    {
        label: 'Pending Payments',
        value: 'Rp 2.125.000',
        helper: '12 orders pending payment',
        helperAccent: '12',
        icon: Wallet,
        tone: 'amber',
    },
    {
        label: 'Completed Orders',
        value: '74',
        helper: '+9.3% from yesterday',
        icon: CheckCircle2,
        tone: 'green',
    },
];

const statusItems: Status[] = [
    { label: 'New Order', value: '18', icon: Plus, tone: 'blue' },
    { label: 'Processing', value: '36', icon: WashingMachine, tone: 'blue' },
    { label: 'Washing', value: '28', icon: Droplets, tone: 'cyan' },
    { label: 'Drying', value: '22', icon: Fan, tone: 'amber' },
    { label: 'Ironing', value: '16', icon: PackageCheck, tone: 'violet' },
    { label: 'Ready for Pickup', value: '42', icon: ShoppingBag, tone: 'cyan' },
    { label: 'Completed', value: '74', icon: CheckCircle2, tone: 'green' },
    { label: 'Cancelled', value: '8', icon: XCircle, tone: 'red' },
];

const orders: Order[] = [
    {
        invoice: 'INV-2025-05010',
        customer: 'Budi Santoso',
        phone: '0812-3456-7890',
        service: 'Cuci Setrika',
        quantity: '5 Kg',
        total: 'Rp 75.000',
        method: 'QRIS',
        paymentStatus: 'Paid',
        paymentTone: 'paid',
        laundryStatus: 'Ready for Pickup',
        laundryTone: 'ready',
        createdAt: '10 May 2025, 09:30',
    },
    {
        invoice: 'INV-2025-05009',
        customer: 'Siti Nurhaliza',
        phone: '0813-2345-6789',
        service: 'Cuci Kering',
        quantity: '8 Kg',
        total: 'Rp 120.000',
        method: 'Transfer Bank',
        paymentStatus: 'Pending',
        paymentTone: 'pending',
        laundryStatus: 'Processing',
        laundryTone: 'processing',
        createdAt: '10 May 2025, 09:15',
    },
    {
        invoice: 'INV-2025-05008',
        customer: 'Andi Pratama',
        phone: '0812-9876-5432',
        service: 'Cuci Setrika + Lipat',
        quantity: '10 Kg',
        total: 'Rp 150.000',
        method: 'Cash',
        paymentStatus: 'Paid',
        paymentTone: 'paid',
        laundryStatus: 'Washing',
        laundryTone: 'washing',
        createdAt: '10 May 2025, 08:45',
    },
    {
        invoice: 'INV-2025-05007',
        customer: 'Dewi Lestari',
        phone: '0813-1111-2222',
        service: 'Cuci Kering',
        quantity: '6 Kg',
        total: 'Rp 90.000',
        method: 'QRIS',
        paymentStatus: 'Failed',
        paymentTone: 'failed',
        laundryStatus: 'Cancelled',
        laundryTone: 'cancelled',
        createdAt: '10 May 2025, 08:20',
    },
    {
        invoice: 'INV-2025-05006',
        customer: 'Hendra Wijaya',
        phone: '0812-2222-3333',
        service: 'Cuci Bed Cover',
        quantity: '1 Pcs',
        total: 'Rp 60.000',
        method: 'Cash',
        paymentStatus: 'Paid',
        paymentTone: 'paid',
        laundryStatus: 'Completed',
        laundryTone: 'completed',
        createdAt: '10 May 2025, 07:50',
    },
    {
        invoice: 'INV-2025-05005',
        customer: 'Rina Kartika',
        phone: '0813-4444-5555',
        service: 'Cuci Setrika',
        quantity: '4 Kg',
        total: 'Rp 60.000',
        method: 'Transfer Bank',
        paymentStatus: 'Pending',
        paymentTone: 'pending',
        laundryStatus: 'Ironing',
        laundryTone: 'ironing',
        createdAt: '10 May 2025, 07:30',
    },
];

const toneClasses = {
    green: {
        iconWrap: 'bg-[#dcfce7] text-[#16a34a]',
        status: 'border-[#bbf7d0] bg-[#f0fdf4]',
    },
    blue: {
        iconWrap: 'bg-[#dbeafe] text-[#2563eb]',
        status: 'border-[#bfdbfe] bg-[#eff6ff]',
    },
    violet: {
        iconWrap: 'bg-[#ede9fe] text-[#8b5cf6]',
        status: 'border-[#ddd6fe] bg-[#f5f3ff]',
    },
    cyan: {
        iconWrap: 'bg-[#cffafe] text-[#06b6d4]',
        status: 'border-[#a5f3fc] bg-[#ecfeff]',
    },
    amber: {
        iconWrap: 'bg-[#fef3c7] text-[#f59e0b]',
        status: 'border-[#fde68a] bg-[#fffbeb]',
    },
    red: {
        iconWrap: 'bg-[#fee2e2] text-[#ef4444]',
        status: 'border-[#fecaca] bg-[#fef2f2]',
    },
};

const badgeClasses: Record<BadgeTone, string> = {
    paid: 'bg-[#dcfce7] text-[#16a34a]',
    pending: 'bg-[#fef3c7] text-[#f59e0b]',
    failed: 'bg-[#fee2e2] text-[#ef4444]',
    ready: 'bg-[#cffafe] text-[#0891b2]',
    processing: 'bg-[#dbeafe] text-[#2563eb]',
    washing: 'bg-[#cffafe] text-[#0891b2]',
    cancelled: 'bg-[#fee2e2] text-[#dc2626]',
    completed: 'bg-[#dcfce7] text-[#15803d]',
    ironing: 'bg-[#ede9fe] text-[#7c3aed]',
};

function MetricCard({ metric }: { metric: Metric }) {
    const Icon = metric.icon;

    return (
        <article className="dashboard-card flex min-h-[126px] items-center gap-[18px] p-5">
            <div
                className={[
                    'grid size-[58px] shrink-0 place-items-center rounded-[14px]',
                    toneClasses[metric.tone].iconWrap,
                ].join(' ')}
            >
                <Icon className="size-[26px]" strokeWidth={2.2} />
            </div>
            <div className="min-w-0">
                <p className="mb-1.5 truncate text-sm leading-5 font-medium text-[#64748b]">
                    {metric.label}
                </p>
                <p className="mb-1.5 truncate text-[26px] leading-[34px] font-bold text-[#0f172a]">
                    {metric.value}
                </p>
                <p className="truncate text-xs leading-[18px] font-medium text-[#64748b]">
                    {metric.helperAccent ? (
                        <>
                            <span className="text-[#f59e0b]">
                                {metric.helperAccent}
                            </span>
                            {metric.helper.slice(metric.helperAccent.length)}
                        </>
                    ) : (
                        <>
                            <span className="text-[#16a34a]">↑ </span>
                            <span className="text-[#16a34a]">
                                {metric.helper.split(' ')[0]}
                            </span>{' '}
                            {metric.helper.split(' ').slice(1).join(' ')}
                        </>
                    )}
                </p>
            </div>
        </article>
    );
}

function RevenueChart() {
    const xLabels = [
        '21 Apr',
        '22 Apr',
        '23 Apr',
        '24 Apr',
        '25 Apr',
        '26 Apr',
        '27 Apr',
        '28 Apr',
        '29 Apr',
        '30 Apr',
        '1 May',
        '2 May',
        '3 May',
        '4 May',
        '5 May',
        '6 May',
        '7 May',
        '8 May',
        '9 May',
        '10 May',
    ];
    const yLabels = [
        'Rp 12M',
        'Rp 10M',
        'Rp 8M',
        'Rp 6M',
        'Rp 4M',
        'Rp 2M',
        'Rp 0',
    ];
    const points =
        '0,171 35,108 70,132 105,82 140,100 175,34 210,100 245,78 280,108 315,98 350,76 385,104 420,122 455,130 490,78 525,82 560,100 595,78 630,34 665,66 700,78';

    return (
        <section className="dashboard-card h-[305px] p-5">
            <div className="mb-4 flex items-center justify-between gap-4">
                <h2 className="text-lg leading-[26px] font-bold text-[#0f172a]">
                    Revenue Overview
                </h2>
                <div className="flex items-center gap-3">
                    <div className="hidden h-[34px] items-center rounded-lg border border-[#e2e8f0] bg-white p-0.5 md:flex">
                        {['Today', '7 Days', '30 Days', 'This Month'].map(
                            (label) => (
                                <button
                                    key={label}
                                    type="button"
                                    className={[
                                        'h-7 rounded-[7px] px-4 text-xs font-medium transition',
                                        label === '30 Days'
                                            ? 'bg-[#2563eb] text-white'
                                            : 'text-[#64748b] hover:bg-[#f8fafc]',
                                    ].join(' ')}
                                >
                                    {label}
                                </button>
                            ),
                        )}
                    </div>
                    <MoreHorizontal className="size-[18px] text-[#0f172a]" />
                </div>
            </div>
            <div className="grid grid-cols-[54px_minmax(0,1fr)] gap-2">
                <div className="grid h-[180px] content-between pt-0 text-right text-[11px] leading-none text-[#64748b]">
                    {yLabels.map((label) => (
                        <span key={label}>{label}</span>
                    ))}
                </div>
                <div className="relative h-[204px]">
                    <svg
                        viewBox="0 0 700 190"
                        className="h-[190px] w-full overflow-visible"
                        preserveAspectRatio="none"
                        aria-label="Revenue line chart"
                    >
                        <defs>
                            <linearGradient
                                id="revenueArea"
                                x1="0"
                                x2="0"
                                y1="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stopColor="rgba(37,99,235,0.16)"
                                />
                                <stop
                                    offset="100%"
                                    stopColor="rgba(37,99,235,0)"
                                />
                            </linearGradient>
                        </defs>
                        {[10, 37, 64, 91, 118, 145, 172].map((y) => (
                            <line
                                key={y}
                                x1="0"
                                x2="700"
                                y1={y}
                                y2={y}
                                stroke="#e2e8f0"
                                strokeDasharray="3 3"
                                strokeWidth="1"
                            />
                        ))}
                        <polygon
                            points={`${points} 700,180 0,180`}
                            fill="url(#revenueArea)"
                        />
                        <polyline
                            points={points}
                            fill="none"
                            stroke="#2563eb"
                            strokeWidth="2.2"
                            vectorEffect="non-scaling-stroke"
                        />
                        {points.split(' ').map((point) => {
                            const [cx, cy] = point.split(',');

                            return (
                                <circle
                                    key={point}
                                    cx={cx}
                                    cy={cy}
                                    r="4"
                                    fill="#ffffff"
                                    stroke="#2563eb"
                                    strokeWidth="2"
                                    vectorEffect="non-scaling-stroke"
                                />
                            );
                        })}
                    </svg>
                    <div className="grid grid-cols-10 text-[10px] text-[#64748b]">
                        {xLabels.map((label, index) => (
                            <span
                                key={label}
                                className={index % 2 === 0 ? '' : 'text-center'}
                            >
                                {label}
                            </span>
                        ))}
                    </div>
                </div>
            </div>
            <div className="mt-1 flex items-center justify-between text-xs font-medium text-[#334155]">
                <span className="flex items-center gap-2">
                    <span className="size-2.5 rounded-full bg-[#2563eb]" />
                    Revenue (IDR)
                </span>
                <span>Total Revenue: Rp 156.750.000</span>
            </div>
        </section>
    );
}

function StatusOverview() {
    return (
        <section className="dashboard-card h-[305px] p-[22px]">
            <h2 className="mb-5 text-lg leading-[26px] font-bold text-[#0f172a]">
                Laundry Status Overview
            </h2>
            <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
                {statusItems.map((status) => {
                    const Icon = status.icon;

                    return (
                        <article
                            key={status.label}
                            className={[
                                'flex h-[88px] items-center gap-3 rounded-xl border p-4',
                                toneClasses[status.tone].status,
                            ].join(' ')}
                        >
                            <div
                                className={[
                                    'grid size-[42px] shrink-0 place-items-center rounded-full',
                                    toneClasses[status.tone].iconWrap,
                                ].join(' ')}
                            >
                                <Icon
                                    className="size-[22px]"
                                    strokeWidth={2.1}
                                />
                            </div>
                            <div className="min-w-0">
                                <p className="truncate text-[13px] leading-[18px] font-semibold text-[#0f172a]">
                                    {status.label}
                                </p>
                                <p className="text-[28px] leading-[34px] font-bold text-[#0f172a]">
                                    {status.value}
                                </p>
                            </div>
                        </article>
                    );
                })}
            </div>
        </section>
    );
}

function QuickActions() {
    const actions = [
        { label: 'Create New Order', icon: Plus, primary: true },
        { label: 'Search Order', icon: Search },
        { label: 'Add Customer', icon: UserPlus },
        { label: 'Generate QRIS', icon: QrCode },
        { label: 'Print Invoice', icon: Printer },
        { label: 'Send WhatsApp Reminder', icon: Send, whatsapp: true },
    ];

    return (
        <section className="dashboard-card p-5 px-[26px]">
            <h2 className="mb-3.5 text-lg leading-[26px] font-bold text-[#0f172a]">
                Quick Actions
            </h2>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
                {actions.map((action) => {
                    const Icon = action.icon;

                    return (
                        <button
                            key={action.label}
                            type="button"
                            className={[
                                'flex h-[46px] items-center justify-center gap-2.5 rounded-lg border border-[#cbd5e1] bg-white px-3 text-[13px] font-medium text-[#334155] transition hover:border-[#94a3b8] hover:bg-[#f8fafc] active:translate-y-px',
                                action.primary
                                    ? 'font-bold text-[#2563eb]'
                                    : '',
                            ].join(' ')}
                        >
                            <Icon
                                className={[
                                    'size-[22px]',
                                    action.whatsapp
                                        ? 'text-[#22c55e]'
                                        : 'text-[#2563eb]',
                                ].join(' ')}
                                strokeWidth={2}
                            />
                            {action.label}
                        </button>
                    );
                })}
            </div>
        </section>
    );
}

function Badge({ tone, children }: { tone: BadgeTone; children: string }) {
    return (
        <span
            className={[
                'inline-flex h-[22px] items-center justify-center rounded-md px-3 text-xs leading-none font-bold',
                badgeClasses[tone],
            ].join(' ')}
        >
            {children}
        </span>
    );
}

function RecentOrders() {
    return (
        <section className="dashboard-card overflow-visible">
            <div className="flex items-center justify-between px-[26px] pt-[18px] pb-2.5">
                <h2 className="text-lg leading-[26px] font-bold text-[#0f172a]">
                    Recent Orders
                </h2>
                <button
                    type="button"
                    className="flex h-[34px] items-center gap-2 rounded-lg border border-[#e2e8f0] bg-white px-3.5 text-xs font-semibold text-[#334155] transition hover:bg-[#f8fafc]"
                >
                    View All Orders
                    <ArrowRight className="size-3.5" strokeWidth={2} />
                </button>
            </div>
            <div className="dashboard-table-scroll px-3">
                <table className="w-full border-collapse text-left">
                    <thead>
                        <tr className="h-[34px] border-y border-[#e2e8f0]">
                            {[
                                'Invoice',
                                'Customer',
                                'Service',
                                'Total',
                                'Payment Method',
                                'Payment Status',
                                'Laundry Status',
                                'Created At',
                                'Action',
                            ].map((header) => (
                                <th
                                    key={header}
                                    className="px-3.5 py-2 text-xs leading-4 font-bold text-[#0f172a]"
                                >
                                    {header}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {orders.map((order, index) => (
                            <tr
                                key={order.invoice}
                                className="relative h-[43px] border-b border-[#e2e8f0] bg-white transition hover:bg-[#f8fafc]"
                            >
                                <td className="px-3.5 py-2">
                                    <button
                                        type="button"
                                        className="text-[13px] leading-[18px] font-semibold text-[#2563eb] hover:text-[#1d4ed8] hover:underline"
                                    >
                                        {order.invoice}
                                    </button>
                                </td>
                                <td className="px-3.5 py-2">
                                    <p className="text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                        {order.customer}
                                    </p>
                                    <p className="text-xs leading-4 text-[#64748b]">
                                        {order.phone}
                                    </p>
                                </td>
                                <td className="px-3.5 py-2">
                                    <p className="text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                        {order.service}
                                    </p>
                                    <p className="text-xs leading-4 text-[#64748b]">
                                        {order.quantity}
                                    </p>
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {order.total}
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {order.method}
                                </td>
                                <td className="px-3.5 py-2">
                                    <Badge tone={order.paymentTone}>
                                        {order.paymentStatus}
                                    </Badge>
                                </td>
                                <td className="px-3.5 py-2">
                                    <Badge tone={order.laundryTone}>
                                        {order.laundryStatus}
                                    </Badge>
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {order.createdAt}
                                </td>
                                <td className="relative px-3.5 py-2">
                                    <button
                                        type="button"
                                        className="grid size-7 place-items-center rounded-md border border-[#e2e8f0] bg-white text-[#334155] transition hover:bg-[#f8fafc]"
                                        aria-label={`Open actions for ${order.invoice}`}
                                    >
                                        <MoreHorizontal
                                            className="size-4"
                                            strokeWidth={2}
                                        />
                                    </button>
                                    {index === orders.length - 1 && (
                                        <div className="absolute right-3 bottom-7 z-10 w-[170px] rounded-[10px] border border-[#e2e8f0] bg-white p-2 shadow-[0_12px_28px_rgba(15,23,42,0.14)]">
                                            {[
                                                {
                                                    label: 'View Detail',
                                                    icon: Eye,
                                                },
                                                {
                                                    label: 'Update Status',
                                                    icon: RefreshCcw,
                                                },
                                                {
                                                    label: 'Print Invoice',
                                                    icon: Printer,
                                                },
                                                {
                                                    label: 'Send WhatsApp',
                                                    icon: Send,
                                                },
                                                {
                                                    label: 'Cancel Order',
                                                    icon: XCircle,
                                                    danger: true,
                                                },
                                            ].map((item) => {
                                                const Icon = item.icon;

                                                return (
                                                    <button
                                                        key={item.label}
                                                        type="button"
                                                        className={[
                                                            'flex h-9 w-full items-center gap-2.5 rounded-[7px] px-2.5 text-[13px] font-medium transition',
                                                            item.danger
                                                                ? 'text-[#ef4444] hover:bg-[#fef2f2]'
                                                                : 'text-[#334155] hover:bg-[#f8fafc]',
                                                        ].join(' ')}
                                                    >
                                                        <Icon
                                                            className="size-4"
                                                            strokeWidth={1.8}
                                                        />
                                                        {item.label}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="flex h-11 items-center justify-between px-[26px]">
                <p className="text-xs text-[#64748b]">
                    Showing 1 to 6 of 6 orders
                </p>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        className="grid size-7 place-items-center rounded-md border border-[#e2e8f0] text-[#94a3b8]"
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        className="grid size-7 place-items-center rounded-md bg-[#2563eb] text-xs font-bold text-white"
                    >
                        1
                    </button>
                    <button
                        type="button"
                        className="grid size-7 place-items-center rounded-md border border-[#e2e8f0] text-[#94a3b8]"
                    >
                        ›
                    </button>
                </div>
            </div>
        </section>
    );
}

export default function Dashboard() {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="dashboard-page-scale space-y-4">
                <header>
                    <h1 className="text-[30px] leading-[38px] font-bold text-[#0f172a]">
                        Admin Dashboard
                    </h1>
                    <p className="text-sm leading-[22px] text-[#64748b]">
                        Monitor laundry operations, payments, orders, and staff
                        activity in real time.
                    </p>
                </header>

                <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                    {metrics.map((metric) => (
                        <MetricCard key={metric.label} metric={metric} />
                    ))}
                </section>

                <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.08fr_1fr]">
                    <RevenueChart />
                    <StatusOverview />
                </div>

                <QuickActions />
                <RecentOrders />
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
