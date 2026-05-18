import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    Banknote,
    CheckCircle2,
    ClipboardList,
    Droplets,
    Fan,
    MoreHorizontal,
    PackageCheck,
    Plus,
    Printer,
    QrCode,
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
import { index as ordersIndex, show as orderShow } from '@/routes/orders';

type Metric = {
    label: string;
    value: string;
    helper: string;
    helperAccent?: string;
    icon: LucideIcon;
    tone: 'green' | 'blue' | 'violet' | 'cyan' | 'amber';
};

type Status = {
    key: string;
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
    | 'ironing'
    | 'waiting'
    | 'drying'
    | 'unpaid'
    | 'qris'
    | 'cash';

type DashboardMetrics = {
    revenueToday: number;
    ordersToday: number;
    pendingPaymentOrders: number;
    processingOrders: number;
    readyToPickupOrders: number;
    completedOrdersToday: number;
    cashRevenueToday: number;
    qrisRevenueToday: number;
};

type RevenuePoint = {
    date: string;
    total: number;
};

type RecentOrder = {
    id: number;
    invoice_number: string;
    order_date: string | null;
    grand_total: string;
    payment_status: string;
    order_status: string;
    customer?: { name: string; phone: string | null } | null;
    active_payment?: {
        method: string;
        status: string;
        paid_at: string | null;
    } | null;
    items?: Array<{
        service_name: string;
        charged_quantity: string | number;
        unit: string;
    }>;
};

type DashboardProps = {
    metrics: DashboardMetrics;
    revenueChart: RevenuePoint[];
    orderStatusDistribution: Record<string, number>;
    paymentMethodDistribution: Record<string, number>;
    recentOrders: RecentOrder[];
};

const statusBlueprints: Omit<Status, 'value'>[] = [
    {
        key: 'waiting_payment',
        label: 'Waiting Payment',
        icon: Plus,
        tone: 'blue',
    },
    {
        key: 'processing',
        label: 'Processing',
        icon: WashingMachine,
        tone: 'blue',
    },
    { key: 'washing', label: 'Washing', icon: Droplets, tone: 'cyan' },
    { key: 'drying', label: 'Drying', icon: Fan, tone: 'amber' },
    { key: 'ironing', label: 'Ironing', icon: PackageCheck, tone: 'violet' },
    {
        key: 'ready_to_pickup',
        label: 'Ready for Pickup',
        icon: ShoppingBag,
        tone: 'cyan',
    },
    { key: 'completed', label: 'Completed', icon: CheckCircle2, tone: 'green' },
    { key: 'cancelled', label: 'Cancelled', icon: XCircle, tone: 'red' },
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
    waiting: 'bg-[#dbeafe] text-[#2563eb]',
    drying: 'bg-[#fef3c7] text-[#f59e0b]',
    unpaid: 'bg-[#f1f5f9] text-[#475569]',
    qris: 'bg-[#dbeafe] text-[#2563eb]',
    cash: 'bg-[#dcfce7] text-[#16a34a]',
};

function formatCurrency(value: number | string) {
    return new Intl.NumberFormat('id-ID', {
        currency: 'IDR',
        maximumFractionDigits: 0,
        style: 'currency',
    }).format(Number(value));
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

function titleCase(value: string) {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (match) => match.toUpperCase());
}

function badgeTone(value: string): BadgeTone {
    if (value === 'ready_to_pickup') {
        return 'ready';
    }

    if (value === 'waiting_payment') {
        return 'waiting';
    }

    if (value in badgeClasses) {
        return value as BadgeTone;
    }

    return 'pending';
}

function firstService(order: RecentOrder) {
    const item = order.items?.[0];

    if (!item) {
        return '-';
    }

    return item.service_name;
}

function firstQuantity(order: RecentOrder) {
    const item = order.items?.[0];

    if (!item) {
        return '-';
    }

    return `${item.charged_quantity} ${item.unit}`;
}

function metricCards(metrics: DashboardMetrics): Metric[] {
    return [
        {
            label: "Today's Revenue",
            value: formatCurrency(metrics.revenueToday),
            helper: `${formatCurrency(metrics.cashRevenueToday)} cash`,
            icon: Banknote,
            tone: 'green',
        },
        {
            label: "Today's Orders",
            value: String(metrics.ordersToday),
            helper: 'Orders created today',
            icon: ClipboardList,
            tone: 'blue',
        },
        {
            label: 'Processing Orders',
            value: String(metrics.processingOrders),
            helper: 'Orders in active workflow',
            icon: WashingMachine,
            tone: 'violet',
        },
        {
            label: 'Ready for Pickup',
            value: String(metrics.readyToPickupOrders),
            helper: 'Orders ready for customer',
            icon: ShoppingBag,
            tone: 'cyan',
        },
        {
            label: 'Pending Payments',
            value: String(metrics.pendingPaymentOrders),
            helper: 'Orders awaiting payment',
            helperAccent: String(metrics.pendingPaymentOrders),
            icon: Wallet,
            tone: 'amber',
        },
        {
            label: 'Completed Today',
            value: String(metrics.completedOrdersToday),
            helper: `${formatCurrency(metrics.qrisRevenueToday)} QRIS`,
            icon: CheckCircle2,
            tone: 'green',
        },
    ];
}

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
                            </span>{' '}
                            {metric.helper.replace(metric.helperAccent, '')}
                        </>
                    ) : (
                        metric.helper
                    )}
                </p>
            </div>
        </article>
    );
}

function RevenueChart({ points }: { points: RevenuePoint[] }) {
    const totals = points.map((point) => Number(point.total));
    const maxTotal = Math.max(...totals, 1);
    const chartPoints = points
        .map((point, index) => {
            const x =
                points.length <= 1 ? 0 : (700 / (points.length - 1)) * index;
            const y = 172 - (Number(point.total) / maxTotal) * 138;

            return `${x.toFixed(1)},${y.toFixed(1)}`;
        })
        .join(' ');

    return (
        <section className="dashboard-card h-[305px] p-5">
            <div className="mb-4 flex items-center justify-between gap-4">
                <h2 className="text-lg leading-[26px] font-bold text-[#0f172a]">
                    Revenue Overview
                </h2>
                <span className="rounded-lg border border-[#e2e8f0] px-3 py-1 text-xs font-semibold text-[#334155]">
                    Last 7 days
                </span>
            </div>
            <div className="grid grid-cols-[54px_minmax(0,1fr)] gap-2">
                <div className="grid h-[180px] content-between pt-0 text-right text-[11px] leading-none text-[#64748b]">
                    {[1, 0.8, 0.6, 0.4, 0.2, 0].map((ratio) => (
                        <span key={ratio}>
                            {formatCurrency(maxTotal * ratio)}
                        </span>
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
                            points={`${chartPoints} 700,180 0,180`}
                            fill="url(#revenueArea)"
                        />
                        <polyline
                            points={chartPoints}
                            fill="none"
                            stroke="#2563eb"
                            strokeWidth="2.2"
                            vectorEffect="non-scaling-stroke"
                        />
                        {chartPoints.split(' ').map((point) => {
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
                    <div className="grid grid-cols-7 text-[10px] text-[#64748b]">
                        {points.map((point) => (
                            <span key={point.date} className="truncate">
                                {new Intl.DateTimeFormat('id-ID', {
                                    day: 'numeric',
                                    month: 'short',
                                }).format(new Date(point.date))}
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
                <span>
                    Total Revenue:{' '}
                    {formatCurrency(
                        totals.reduce((total, value) => total + value, 0),
                    )}
                </span>
            </div>
        </section>
    );
}

function StatusOverview({
    distribution,
}: {
    distribution: Record<string, number>;
}) {
    const statuses = statusBlueprints.map((status) => ({
        ...status,
        value: String(distribution[status.key] ?? 0),
    }));

    return (
        <section className="dashboard-card h-[305px] p-[22px]">
            <h2 className="mb-5 text-lg leading-[26px] font-bold text-[#0f172a]">
                Laundry Status Overview
            </h2>
            <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
                {statuses.map((status) => {
                    const Icon = status.icon;

                    return (
                        <article
                            key={status.key}
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
        {
            label: 'Create New Order',
            icon: Plus,
            href: '/pos/orders/create',
            primary: true,
        },
        { label: 'Search Order', icon: Search, href: ordersIndex() },
        { label: 'Add Customer', icon: UserPlus, href: '/customers' },
        { label: 'Generate QRIS', icon: QrCode, href: ordersIndex() },
        { label: 'Print Invoice', icon: Printer, href: ordersIndex() },
        {
            label: 'Send WhatsApp Reminder',
            icon: Send,
            href: ordersIndex(),
            whatsapp: true,
        },
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
                        <Link
                            key={action.label}
                            href={action.href}
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
                        </Link>
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

function RecentOrders({ orders }: { orders: RecentOrder[] }) {
    return (
        <section className="dashboard-card overflow-visible">
            <div className="flex items-center justify-between px-[26px] pt-[18px] pb-2.5">
                <h2 className="text-lg leading-[26px] font-bold text-[#0f172a]">
                    Recent Orders
                </h2>
                <Link
                    href={ordersIndex()}
                    className="flex h-[34px] items-center gap-2 rounded-lg border border-[#e2e8f0] bg-white px-3.5 text-xs font-semibold text-[#334155] transition hover:bg-[#f8fafc]"
                >
                    View All Orders
                    <ArrowRight className="size-3.5" strokeWidth={2} />
                </Link>
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
                        {orders.map((order) => (
                            <tr
                                key={order.id}
                                className="relative h-[43px] border-b border-[#e2e8f0] bg-white transition hover:bg-[#f8fafc]"
                            >
                                <td className="px-3.5 py-2">
                                    <Link
                                        href={orderShow(order.id)}
                                        className="text-[13px] leading-[18px] font-semibold text-[#2563eb] hover:text-[#1d4ed8] hover:underline"
                                    >
                                        {order.invoice_number}
                                    </Link>
                                </td>
                                <td className="px-3.5 py-2">
                                    <p className="text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                        {order.customer?.name ?? '-'}
                                    </p>
                                    <p className="text-xs leading-4 text-[#64748b]">
                                        {order.customer?.phone ?? '-'}
                                    </p>
                                </td>
                                <td className="px-3.5 py-2">
                                    <p className="text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                        {firstService(order)}
                                    </p>
                                    <p className="text-xs leading-4 text-[#64748b]">
                                        {firstQuantity(order)}
                                    </p>
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {formatCurrency(order.grand_total)}
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {titleCase(
                                        order.active_payment?.method ?? '-',
                                    )}
                                </td>
                                <td className="px-3.5 py-2">
                                    <Badge
                                        tone={badgeTone(order.payment_status)}
                                    >
                                        {titleCase(order.payment_status)}
                                    </Badge>
                                </td>
                                <td className="px-3.5 py-2">
                                    <Badge tone={badgeTone(order.order_status)}>
                                        {titleCase(order.order_status)}
                                    </Badge>
                                </td>
                                <td className="px-3.5 py-2 text-[13px] leading-[18px] font-medium text-[#0f172a]">
                                    {formatDate(order.order_date)}
                                </td>
                                <td className="relative px-3.5 py-2">
                                    <Link
                                        href={orderShow(order.id)}
                                        className="grid size-7 place-items-center rounded-md border border-[#e2e8f0] bg-white text-[#334155] transition hover:bg-[#f8fafc]"
                                        aria-label={`Open ${order.invoice_number}`}
                                    >
                                        <MoreHorizontal
                                            className="size-4"
                                            strokeWidth={2}
                                        />
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {orders.length === 0 && (
                            <tr>
                                <td
                                    colSpan={9}
                                    className="px-3.5 py-8 text-center text-sm text-[#64748b]"
                                >
                                    No recent orders yet.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <div className="flex h-11 items-center justify-between px-[26px]">
                <p className="text-xs text-[#64748b]">
                    Showing {orders.length} recent orders
                </p>
            </div>
        </section>
    );
}

export default function Dashboard({
    metrics,
    revenueChart,
    orderStatusDistribution,
    recentOrders,
}: DashboardProps) {
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
                    {metricCards(metrics).map((metric) => (
                        <MetricCard key={metric.label} metric={metric} />
                    ))}
                </section>

                <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.08fr_1fr]">
                    <RevenueChart points={revenueChart} />
                    <StatusOverview distribution={orderStatusDistribution} />
                </div>

                <QuickActions />
                <RecentOrders orders={recentOrders} />
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
