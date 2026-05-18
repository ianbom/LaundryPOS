import { Head } from '@inertiajs/react';
import type { Paginated } from '@/types';

type ActivityLog = {
    id: number;
    action: string;
    subject_type: string | null;
    subject_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    created_at: string;
    outlet?: { name: string };
    user?: { name: string };
};

export default function ActivityLogIndex({
    logs,
}: {
    logs: Paginated<ActivityLog>;
}) {
    return (
        <>
            <Head title="Activity Logs" />
            <div className="space-y-6">
                <header>
                    <h1 className="text-[30px] leading-[38px] font-bold text-slate-900">
                        Activity Logs
                    </h1>
                    <p className="text-sm leading-[22px] text-slate-500">
                        Important business actions across accessible outlets.
                    </p>
                </header>

                <div className="overflow-x-auto rounded-[14px] border border-slate-200 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]">
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="border-b text-xs font-bold text-slate-900 uppercase">
                                <th className="px-4 py-3">Action</th>
                                <th>User</th>
                                <th>Outlet</th>
                                <th>Subject</th>
                                <th>IP</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.data.map((log) => (
                                <tr
                                    key={log.id}
                                    className="border-b hover:bg-slate-50"
                                >
                                    <td className="px-4 py-3 font-semibold">
                                        {log.action}
                                    </td>
                                    <td>{log.user?.name ?? '-'}</td>
                                    <td>{log.outlet?.name ?? '-'}</td>
                                    <td>
                                        {log.subject_type ?? '-'} #
                                        {log.subject_id ?? '-'}
                                    </td>
                                    <td>{log.ip_address ?? '-'}</td>
                                    <td>{log.created_at}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
