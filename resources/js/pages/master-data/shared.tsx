import { Form, Link } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

export type OutletOption = {
    id: number;
    name: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
};

export function PageHeader({
    title,
    description,
    createHref,
}: {
    title: string;
    description: string;
    createHref?: string;
}) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 className="text-2xl font-semibold text-foreground">
                    {title}
                </h1>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
            {createHref && (
                <Button asChild>
                    <Link href={createHref}>
                        <Plus className="size-4" />
                        Create
                    </Link>
                </Button>
            )}
        </div>
    );
}

export function TextFilter({
    action,
    defaultValue,
    placeholder = 'Search',
}: {
    action: string;
    defaultValue?: string;
    placeholder?: string;
}) {
    return (
        <Form action={action} method="get" className="flex gap-2">
            <input
                name="search"
                defaultValue={defaultValue}
                placeholder={placeholder}
                className="h-9 w-full rounded-md border bg-background px-3 text-sm"
            />
            <Button variant="outline">Search</Button>
        </Form>
    );
}

export function Pagination({ links }: { links: PaginationLink[] }) {
    return (
        <div className="flex flex-wrap gap-2">
            {links.map((link, index) =>
                link.url ? (
                    <Button
                        key={`${link.label}-${index}`}
                        asChild
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                    >
                        <Link
                            href={link.url}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    </Button>
                ) : (
                    <Button
                        key={`${link.label}-${index}`}
                        variant="outline"
                        size="sm"
                        disabled
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </div>
    );
}

export function StatusBadge({ active }: { active: boolean }) {
    return (
        <span
            className={
                active
                    ? 'rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                    : 'rounded-full bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300'
            }
        >
            {active ? 'Active' : 'Inactive'}
        </span>
    );
}

export function EditButton({ href }: { href: string }) {
    return (
        <Button asChild size="sm" variant="outline">
            <Link href={href}>
                <Pencil className="size-4" />
                Edit
            </Link>
        </Button>
    );
}

export function DeleteButton({ action }: { action: string }) {
    return (
        <Form action={action} method="post">
            <input type="hidden" name="_method" value="delete" />
            <Button size="sm" variant="outline">
                <Trash2 className="size-4" />
                Delete
            </Button>
        </Form>
    );
}
