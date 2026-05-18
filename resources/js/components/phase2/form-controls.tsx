import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type FieldProps = {
    name: string;
    label: string;
    defaultValue?: string | number | null;
    error?: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
};

export function TextField({
    name,
    label,
    defaultValue,
    error,
    type = 'text',
    placeholder,
    required,
}: FieldProps) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Input
                id={name}
                name={name}
                type={type}
                defaultValue={defaultValue ?? ''}
                placeholder={placeholder}
                required={required}
                className="h-11 rounded-xl border-slate-300 bg-white"
            />
            <InputError message={error} />
        </div>
    );
}

export function TextAreaField({
    name,
    label,
    defaultValue,
    error,
    placeholder,
}: FieldProps) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Textarea
                id={name}
                name={name}
                defaultValue={defaultValue ?? ''}
                placeholder={placeholder}
                className="min-h-24 rounded-xl border-slate-300 bg-white"
            />
            <InputError message={error} />
        </div>
    );
}

export function CheckboxField({
    name,
    label,
    defaultChecked,
}: {
    name: string;
    label: string;
    defaultChecked?: boolean;
}) {
    return (
        <label className="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700">
            <input
                type="checkbox"
                name={name}
                value="1"
                defaultChecked={defaultChecked}
                className="size-4 rounded border-slate-300 text-blue-600"
            />
            {label}
        </label>
    );
}
