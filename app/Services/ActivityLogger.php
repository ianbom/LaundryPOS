<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function log(Request $request, string $action, ?Model $subject = null, ?int $outletId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        ActivityLog::query()->create([
            'outlet_id' => $outletId,
            'user_id' => $request->user()?->id,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
