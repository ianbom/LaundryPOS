import { Head, Link } from '@inertiajs/react';
import { Edit } from 'lucide-react';
import { PageHeader, PagePanel } from '@/components/phase2/page-panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit, index } from '@/routes/outlets';
import type { Outlet } from '@/types';

export default function ShowOutlet({ outlet }: { outlet: Outlet }) {
    return (
        <>
            <Head title={outlet.name} />
            <PageHeader
                title={outlet.name}
                description="Outlet detail and contact information."
                actions={
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back</Link>
                        </Button>
                        <Button asChild>
                            <Link href={edit(outlet.id)}>
                                <Edit className="size-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                }
            />
            <div className="mt-6">
                <PagePanel title="Details">
                    <dl className="grid gap-4 text-sm md:grid-cols-2">
                        {[
                            ['Code', outlet.code ?? '-'],
                            ['Phone', outlet.phone ?? '-'],
                            ['WhatsApp', outlet.whatsapp_number ?? '-'],
                            ['Email', outlet.email ?? '-'],
                            ['Address', outlet.address ?? '-'],
                        ].map(([label, value]) => (
                            <div key={label}>
                                <dt className="font-semibold text-slate-500">
                                    {label}
                                </dt>
                                <dd className="mt-1 text-slate-900">{value}</dd>
                            </div>
                        ))}
                        <div>
                            <dt className="font-semibold text-slate-500">
                                Status
                            </dt>
                            <dd className="mt-1 flex gap-2">
                                {outlet.is_main && <Badge>Main</Badge>}
                                <Badge
                                    variant={
                                        outlet.is_active
                                            ? 'default'
                                            : 'secondary'
                                    }
                                >
                                    {outlet.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </PagePanel>
            </div>
        </>
    );
}
