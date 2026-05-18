import { Head, Link } from '@inertiajs/react';
import ServiceController from '@/actions/App/Http/Controllers/ServiceController';
import { Button } from '@/components/ui/button';
import { PageHeader, StatusBadge } from '@/pages/master-data/shared';

type Service = {
    id: number;
    name: string;
    description: string | null;
    pricing_type: string;
    is_active: boolean;
    outlet?: { name: string } | null;
    service_category?: { name: string } | null;
    variants: Array<{
        id: number;
        name: string;
        price: string;
        unit: string;
        min_quantity: string;
        is_active: boolean;
    }>;
};

export default function ServiceShow({ service }: { service: Service }) {
    return (
        <>
            <Head title={service.name} />
            <div className="space-y-6">
                <PageHeader
                    title={service.name}
                    description="Service detail and variant summary."
                />
                <div className="grid gap-3 text-sm sm:grid-cols-2">
                    <Info label="Outlet" value={service.outlet?.name ?? '-'} />
                    <Info
                        label="Category"
                        value={service.service_category?.name ?? '-'}
                    />
                    <Info label="Pricing" value={service.pricing_type} />
                    <div className="space-y-1">
                        <div className="text-muted-foreground">Status</div>
                        <StatusBadge active={service.is_active} />
                    </div>
                </div>
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3">Variant</th>
                                <th className="p-3">Price</th>
                                <th className="p-3">Unit</th>
                                <th className="p-3">Min Qty</th>
                                <th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {service.variants.map((variant) => (
                                <tr key={variant.id} className="border-t">
                                    <td className="p-3 font-medium">
                                        {variant.name}
                                    </td>
                                    <td className="p-3">{variant.price}</td>
                                    <td className="p-3">{variant.unit}</td>
                                    <td className="p-3">
                                        {variant.min_quantity}
                                    </td>
                                    <td className="p-3">
                                        <StatusBadge
                                            active={variant.is_active}
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Button asChild variant="outline">
                    <Link href={ServiceController.index.url()}>Back</Link>
                </Button>
            </div>
        </>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div className="space-y-1">
            <div className="text-muted-foreground">{label}</div>
            <div className="font-medium">{value}</div>
        </div>
    );
}
