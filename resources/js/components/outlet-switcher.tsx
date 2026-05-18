import { Form, usePage } from '@inertiajs/react';
import { Store } from 'lucide-react';
import CurrentOutletController from '@/actions/App/Http/Controllers/CurrentOutletController';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Outlet } from '@/types';

export default function OutletSwitcher() {
    const { currentOutlet, outletOptions } = usePage().props as unknown as {
        currentOutlet: Outlet | null;
        outletOptions: Outlet[];
    };

    if (outletOptions.length === 0) {
        return null;
    }

    if (outletOptions.length === 1) {
        return (
            <div className="hidden h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 md:flex">
                <Store className="size-[18px]" />
                {currentOutlet?.name ?? outletOptions[0]?.name}
            </div>
        );
    }

    return (
        <Form {...CurrentOutletController.update.form()}>
            {({ processing, submit }) => (
                <Select
                    defaultValue={String(currentOutlet?.id ?? '')}
                    disabled={processing}
                    onValueChange={(value) => {
                        const input = document.querySelector<HTMLInputElement>(
                            'input[name="outlet_id"]',
                        );

                        if (input) {
                            input.value = value;
                            submit();
                        }
                    }}
                >
                    <input
                        type="hidden"
                        name="outlet_id"
                        defaultValue={currentOutlet?.id ?? ''}
                    />
                    <SelectTrigger className="hidden h-11 min-w-[210px] rounded-[10px] border-slate-200 bg-white md:flex">
                        <Store className="size-[18px]" />
                        <SelectValue placeholder="Select outlet" />
                    </SelectTrigger>
                    <SelectContent>
                        {outletOptions.map((outlet) => (
                            <SelectItem
                                key={outlet.id}
                                value={String(outlet.id)}
                            >
                                {outlet.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}
        </Form>
    );
}
