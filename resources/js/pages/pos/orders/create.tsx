import { Head, Link, useForm } from '@inertiajs/react';
import {
    Banknote,
    Plus,
    QrCode,
    Save,
    Trash2,
    WalletCards,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import POSOrderController from '@/actions/App/Http/Controllers/POSOrderController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

type Outlet = {
    id: number;
    name: string;
    address: string | null;
    whatsapp_number: string | null;
};

type Customer = {
    id: number;
    name: string;
    phone: string;
    whatsapp_number: string | null;
    address: string | null;
    notes: string | null;
};

type Variant = {
    id: number;
    service_id: number;
    name: string;
    price: string;
    unit: string;
    min_quantity: string;
};

type Service = {
    id: number;
    service_category_id: number;
    name: string;
    pricing_type: string;
    variants: Variant[];
};

type Category = {
    id: number;
    name: string;
    services: Service[];
};

type ItemInput = {
    service_variant_id: string;
    quantity: string;
    notes: string;
};

type FormPayload = {
    outlet_id: number;
    customer_id: string;
    customer: {
        name: string;
        phone: string;
        whatsapp_number: string;
        address: string;
        notes: string;
    };
    items: ItemInput[];
    discount_amount: string;
    additional_fee: string;
    delivery_fee: string;
    payment_method: 'qris' | 'cash';
    amount_paid: string;
    customer_notes: string;
    internal_notes: string;
};

type QrisIntent = {
    intent_id: number;
    status: string;
    amount: string;
    provider_order_id: string;
    qris_url: string | null;
    qris_string: string | null;
    payment_url: string | null;
    expired_at: string | null;
};

function money(value: number | string | null | undefined) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

export default function POSOrderCreate({
    outlet,
    customers,
    serviceCategories,
}: {
    outlet: Outlet;
    customers: Customer[];
    serviceCategories: Category[];
}) {
    const [customerSearch, setCustomerSearch] = useState('');
    const [qrisIntent, setQrisIntent] = useState<QrisIntent | null>(null);
    const [qrisError, setQrisError] = useState<string | null>(null);
    const [isGeneratingQris, setIsGeneratingQris] = useState(false);
    const [isCheckingQris, setIsCheckingQris] = useState(false);
    const variants = useMemo(
        () =>
            serviceCategories.flatMap((category) =>
                category.services.flatMap((service) =>
                    service.variants.map((variant) => ({
                        ...variant,
                        service,
                        category,
                    })),
                ),
            ),
        [serviceCategories],
    );

    const { data, setData, post, processing, errors } = useForm<FormPayload>({
        outlet_id: outlet.id,
        customer_id: '',
        customer: {
            name: '',
            phone: '',
            whatsapp_number: '',
            address: '',
            notes: '',
        },
        items: [{ service_variant_id: '', quantity: '1', notes: '' }],
        discount_amount: '0',
        additional_fee: '0',
        delivery_fee: '0',
        payment_method: 'qris',
        amount_paid: '',
        customer_notes: '',
        internal_notes: '',
    });

    const filteredCustomers = customers.filter((customer) => {
        const value =
            `${customer.name} ${customer.phone} ${customer.whatsapp_number ?? ''}`.toLowerCase();

        return value.includes(customerSearch.toLowerCase());
    });

    const previewItems = data.items.map((item) => {
        const variant = variants.find(
            (variant) => String(variant.id) === item.service_variant_id,
        );
        const quantity = Number(item.quantity || 0);
        const charged = Math.max(quantity, Number(variant?.min_quantity ?? 0));
        const subtotal = charged * Number(variant?.price ?? 0);

        return { item, variant, charged, subtotal };
    });

    const subtotal = previewItems.reduce((sum, item) => sum + item.subtotal, 0);
    const grandTotal = Math.max(
        subtotal -
            Number(data.discount_amount || 0) +
            Number(data.additional_fee || 0) +
            Number(data.delivery_fee || 0),
        0,
    );
    const amountPaid = Number(data.amount_paid || 0);
    const changeAmount = Math.max(amountPaid - grandTotal, 0);

    async function submit(event: FormEvent) {
        event.preventDefault();

        if (data.payment_method === 'qris') {
            await generateQris();
            return;
        }

        post(POSOrderController.store.url());
    }

    async function generateQris() {
        setIsGeneratingQris(true);
        setQrisError(null);

        try {
            const response = await fetch(POSOrderController.generateQris.url(), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
                body: JSON.stringify(data),
            });

            const payload = await response.json();

            if (!response.ok) {
                const validationMessage = Object.values(payload.errors ?? {})
                    .flat()
                    .join(' ');

                setQrisError(
                    payload.message || validationMessage || 'Gagal membuat QRIS.',
                );
                return;
            }

            setQrisIntent(payload);
        } catch (error) {
            setQrisError(
                error instanceof Error ? error.message : 'Gagal membuat QRIS.',
            );
        } finally {
            setIsGeneratingQris(false);
        }
    }

    async function checkQrisStatus() {
        if (!qrisIntent) {
            return;
        }

        setIsCheckingQris(true);
        setQrisError(null);

        try {
            const response = await fetch(
                POSOrderController.qrisStatus.url(qrisIntent.intent_id),
                {
                    headers: { Accept: 'application/json' },
                },
            );
            const payload = await response.json();

            if (payload.status === 'paid' && payload.order_url) {
                window.location.href = payload.order_url;
                return;
            }

            setQrisError('Pembayaran belum diterima Midtrans.');
        } finally {
            setIsCheckingQris(false);
        }
    }

    function updateItem(index: number, patch: Partial<ItemInput>) {
        setData(
            'items',
            data.items.map((item, itemIndex) =>
                itemIndex === index ? { ...item, ...patch } : item,
            ),
        );
    }

    return (
        <>
            <Head title="Create POS Order" />
            <form onSubmit={submit} className="space-y-6">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">
                            Create POS Order
                        </h1>
                        <p className="text-sm text-slate-500">
                            Catat pelanggan, item laundry, dan total transaksi.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={OrderController.index.url()}>
                                Orders
                            </Link>
                        </Button>
                        <Button
                            disabled={
                                processing ||
                                isGeneratingQris ||
                                (data.payment_method === 'qris' && !!qrisIntent)
                            }
                        >
                            <Save className="size-4" />
                            {data.payment_method === 'qris'
                                ? qrisIntent
                                    ? 'QRIS Sudah Dibuat'
                                    : 'Generate QRIS'
                                : 'Buat Order Tunai'}
                        </Button>
                    </div>
                </div>

                <Card className="rounded-[14px] border-slate-200 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <CardContent className="grid gap-3 pt-6 md:grid-cols-3">
                        <div>
                            <p className="text-xs font-semibold text-slate-500 uppercase">
                                Outlet Aktif
                            </p>
                            <p className="text-lg font-semibold text-slate-900">
                                {outlet.name}
                            </p>
                        </div>
                        <p className="text-sm text-slate-600">
                            {outlet.address ?? '-'}
                        </p>
                        <p className="text-sm text-slate-600">
                            WA: {outlet.whatsapp_number ?? '-'}
                        </p>
                    </CardContent>
                </Card>

                <div className="grid gap-6 xl:grid-cols-[1fr_360px]">
                    <div className="space-y-6">
                        <Card className="rounded-[14px] border-slate-200 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                            <CardHeader>
                                <CardTitle>Customer</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <Input
                                    value={customerSearch}
                                    onChange={(event) =>
                                        setCustomerSearch(event.target.value)
                                    }
                                    placeholder="Search customer"
                                />
                                <div className="grid gap-2 md:grid-cols-2">
                                    {filteredCustomers
                                        .slice(0, 6)
                                        .map((customer) => (
                                            <button
                                                type="button"
                                                key={customer.id}
                                                onClick={() =>
                                                    setData(
                                                        'customer_id',
                                                        String(customer.id),
                                                    )
                                                }
                                                className={
                                                    data.customer_id ===
                                                    String(customer.id)
                                                        ? 'rounded-lg border border-blue-500 bg-blue-50 p-3 text-left text-sm'
                                                        : 'rounded-lg border border-slate-200 p-3 text-left text-sm hover:bg-slate-50'
                                                }
                                            >
                                                <span className="block font-semibold">
                                                    {customer.name}
                                                </span>
                                                <span className="text-slate-500">
                                                    {customer.whatsapp_number ??
                                                        customer.phone}
                                                </span>
                                            </button>
                                        ))}
                                </div>
                                <div className="grid gap-3 md:grid-cols-2">
                                    <Input
                                        value={data.customer.name}
                                        onChange={(event) =>
                                            setData('customer', {
                                                ...data.customer,
                                                name: event.target.value,
                                            })
                                        }
                                        placeholder="Nama pelanggan"
                                    />
                                    <Input
                                        value={data.customer.phone}
                                        onChange={(event) =>
                                            setData('customer', {
                                                ...data.customer,
                                                phone: event.target.value,
                                            })
                                        }
                                        placeholder="Nomor HP"
                                    />
                                    <Input
                                        value={data.customer.whatsapp_number}
                                        onChange={(event) =>
                                            setData('customer', {
                                                ...data.customer,
                                                whatsapp_number:
                                                    event.target.value,
                                            })
                                        }
                                        placeholder="Nomor WhatsApp"
                                    />
                                    <Input
                                        value={data.customer.address}
                                        onChange={(event) =>
                                            setData('customer', {
                                                ...data.customer,
                                                address: event.target.value,
                                            })
                                        }
                                        placeholder="Alamat opsional"
                                    />
                                </div>
                                <Textarea
                                    value={data.customer.notes}
                                    onChange={(event) =>
                                        setData('customer', {
                                            ...data.customer,
                                            notes: event.target.value,
                                        })
                                    }
                                    placeholder="Catatan pelanggan"
                                />
                                <InputError message={errors.customer_id} />
                            </CardContent>
                        </Card>

                        <Card className="rounded-[14px] border-slate-200 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                            <CardHeader className="flex-row items-center justify-between">
                                <CardTitle>Order Items</CardTitle>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        setData('items', [
                                            ...data.items,
                                            {
                                                service_variant_id: '',
                                                quantity: '1',
                                                notes: '',
                                            },
                                        ])
                                    }
                                >
                                    <Plus className="size-4" />
                                    Add Item
                                </Button>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {data.items.map((item, index) => {
                                    const preview = previewItems[index];

                                    return (
                                        <div
                                            key={index}
                                            className="rounded-xl border border-slate-200 p-4"
                                        >
                                            <div className="grid gap-3 lg:grid-cols-[1fr_120px_120px_40px]">
                                                <select
                                                    value={
                                                        item.service_variant_id
                                                    }
                                                    onChange={(event) =>
                                                        updateItem(index, {
                                                            service_variant_id:
                                                                event.target
                                                                    .value,
                                                        })
                                                    }
                                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                                >
                                                    <option value="">
                                                        Pilih layanan
                                                    </option>
                                                    {variants.map((variant) => (
                                                        <option
                                                            key={variant.id}
                                                            value={variant.id}
                                                        >
                                                            {
                                                                variant.category
                                                                    .name
                                                            }{' '}
                                                            /{' '}
                                                            {
                                                                variant.service
                                                                    .name
                                                            }{' '}
                                                            / {variant.name}
                                                        </option>
                                                    ))}
                                                </select>
                                                <Input
                                                    value={item.quantity}
                                                    onChange={(event) =>
                                                        updateItem(index, {
                                                            quantity:
                                                                event.target
                                                                    .value,
                                                        })
                                                    }
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                />
                                                <div className="rounded-md bg-slate-50 px-3 py-2 text-sm">
                                                    {money(preview.subtotal)}
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="icon"
                                                    onClick={() =>
                                                        setData(
                                                            'items',
                                                            data.items.filter(
                                                                (
                                                                    _,
                                                                    itemIndex,
                                                                ) =>
                                                                    itemIndex !==
                                                                    index,
                                                            ),
                                                        )
                                                    }
                                                    disabled={
                                                        data.items.length === 1
                                                    }
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            </div>
                                            <div className="mt-3 grid gap-3 lg:grid-cols-[1fr_180px]">
                                                <Input
                                                    value={item.notes}
                                                    onChange={(event) =>
                                                        updateItem(index, {
                                                            notes: event.target
                                                                .value,
                                                        })
                                                    }
                                                    placeholder="Catatan item"
                                                />
                                                <p className="text-xs text-slate-500">
                                                    Unit:{' '}
                                                    {preview.variant?.unit ??
                                                        '-'}{' '}
                                                    | Min:{' '}
                                                    {preview.variant
                                                        ?.min_quantity ??
                                                        '-'}{' '}
                                                    | Charged:{' '}
                                                    {preview.charged || 0}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                                <InputError message={errors.items} />
                            </CardContent>
                        </Card>
                    </div>

                    <Card className="h-fit rounded-[14px] border-slate-200 shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                        <CardHeader>
                            <CardTitle>Order Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <AmountRow label="Subtotal" value={subtotal} />
                            <MoneyInput
                                label="Discount"
                                value={data.discount_amount}
                                onChange={(value) =>
                                    setData('discount_amount', value)
                                }
                            />
                            <MoneyInput
                                label="Additional fee"
                                value={data.additional_fee}
                                onChange={(value) =>
                                    setData('additional_fee', value)
                                }
                            />
                            <MoneyInput
                                label="Delivery fee"
                                value={data.delivery_fee}
                                onChange={(value) =>
                                    setData('delivery_fee', value)
                                }
                            />
                            <div className="rounded-xl bg-blue-50 p-4">
                                <p className="text-sm font-medium text-blue-700">
                                    Grand Total
                                </p>
                                <p className="text-3xl font-bold text-blue-700">
                                    {money(grandTotal)}
                                </p>
                            </div>

                            <div className="space-y-3 rounded-xl border border-slate-200 p-4">
                                <div>
                                    <p className="text-sm font-semibold text-slate-900">
                                        Metode Pembayaran
                                    </p>
                                    <p className="text-xs text-slate-500">
                                        Pilih sebelum order dibuat. QRIS membuat
                                        order setelah pembayaran sukses.
                                    </p>
                                </div>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQrisIntent(null);
                                            setQrisError(null);
                                            setData('payment_method', 'qris');
                                        }}
                                        className={
                                            data.payment_method === 'qris'
                                                ? 'rounded-lg border border-blue-500 bg-blue-50 p-3 text-left text-sm text-blue-700'
                                                : 'rounded-lg border border-slate-200 bg-white p-3 text-left text-sm text-slate-700 hover:bg-slate-50'
                                        }
                                    >
                                        <QrCode className="mb-2 size-5" />
                                        <span className="block font-semibold">
                                            QRIS Midtrans
                                        </span>
                                        <span className="text-xs">
                                            QR dinamis muncul sebelum order
                                            dibuat.
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setQrisIntent(null);
                                            setQrisError(null);
                                            setData('payment_method', 'cash');
                                        }}
                                        className={
                                            data.payment_method === 'cash'
                                                ? 'rounded-lg border border-green-500 bg-green-50 p-3 text-left text-sm text-green-700'
                                                : 'rounded-lg border border-slate-200 bg-white p-3 text-left text-sm text-slate-700 hover:bg-slate-50'
                                        }
                                    >
                                        <Banknote className="mb-2 size-5" />
                                        <span className="block font-semibold">
                                            Tunai
                                        </span>
                                        <span className="text-xs">
                                            Tidak membuat QRIS.
                                        </span>
                                    </button>
                                </div>
                                <InputError message={errors.payment_method} />
                            </div>

                            {data.payment_method === 'cash' && (
                                <div className="space-y-3 rounded-xl border border-green-200 bg-green-50 p-4">
                                    <MoneyInput
                                        label="Uang diterima"
                                        value={data.amount_paid}
                                        onChange={(value) =>
                                            setData('amount_paid', value)
                                        }
                                    />
                                    <AmountRow
                                        label="Kembalian"
                                        value={changeAmount}
                                    />
                                    <InputError message={errors.amount_paid} />
                                </div>
                            )}

                            {data.payment_method === 'qris' && !qrisIntent && (
                                <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
                                    <p className="font-semibold">
                                        QRIS dibuat sebelum order
                                    </p>
                                    <p className="mt-1 text-xs">
                                        Klik tombol untuk membuat QRIS. Order
                                        baru dibuat setelah Midtrans mengirim
                                        status paid.
                                    </p>
                                </div>
                            )}

                            {qrisIntent && (
                                <div className="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                                    <div>
                                        <p className="font-semibold">
                                            Scan QRIS untuk bayar
                                        </p>
                                        <p className="text-xs">
                                            Provider order:{' '}
                                            {qrisIntent.provider_order_id}
                                        </p>
                                    </div>
                                    {qrisIntent.qris_url && (
                                        <img
                                            src={qrisIntent.qris_url}
                                            alt="QRIS Midtrans"
                                            className="mx-auto size-56 rounded-lg border border-blue-200 bg-white object-contain p-2"
                                        />
                                    )}
                                    {qrisIntent.payment_url && (
                                        <a
                                            href={qrisIntent.payment_url}
                                            target="_blank"
                                            className="inline-flex font-semibold text-blue-700 underline"
                                        >
                                            Buka halaman pembayaran
                                        </a>
                                    )}
                                    <Button
                                        type="button"
                                        className="w-full"
                                        disabled={isCheckingQris}
                                        onClick={checkQrisStatus}
                                    >
                                        Cek Pembayaran & Buka Order
                                    </Button>
                                </div>
                            )}

                            {qrisError && (
                                <p className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {qrisError}
                                </p>
                            )}

                            <Textarea
                                value={data.customer_notes}
                                onChange={(event) =>
                                    setData(
                                        'customer_notes',
                                        event.target.value,
                                    )
                                }
                                placeholder="Catatan customer"
                            />
                            <Textarea
                                value={data.internal_notes}
                                onChange={(event) =>
                                    setData(
                                        'internal_notes',
                                        event.target.value,
                                    )
                                }
                                placeholder="Catatan internal"
                            />
                            <Button
                                className="w-full"
                                disabled={
                                    processing ||
                                    isGeneratingQris ||
                                    (data.payment_method === 'qris' &&
                                        !!qrisIntent)
                                }
                            >
                                <WalletCards className="size-4" />
                                {data.payment_method === 'qris'
                                    ? qrisIntent
                                        ? 'QRIS Sudah Dibuat'
                                        : 'Generate QRIS Sebelum Buat Order'
                                    : 'Buat Order & Konfirmasi Tunai'}
                            </Button>
                            <p className="text-xs text-slate-500">
                                WhatsApp receipt dikirim setelah pembayaran
                                berhasil: langsung untuk tunai, dan setelah
                                webhook Midtrans untuk QRIS.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </>
    );
}

function AmountRow({ label, value }: { label: string; value: number }) {
    return (
        <div className="flex items-center justify-between text-sm">
            <span className="text-slate-500">{label}</span>
            <span className="font-semibold text-slate-900">{money(value)}</span>
        </div>
    );
}

function MoneyInput({
    label,
    value,
    onChange,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
}) {
    return (
        <label className="grid gap-1 text-sm">
            <span className="font-medium text-slate-600">{label}</span>
            <Input
                type="number"
                min="0"
                step="100"
                value={value}
                onChange={(event) => onChange(event.target.value)}
            />
        </label>
    );
}
