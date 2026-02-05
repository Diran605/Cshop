<?php

namespace App\Support;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(string $action, mixed $subject = null, ?string $description = null, array $meta = [], ?int $branchId = null, ?int $userId = null): void
    {
        try {
            $subjectType = null;
            $subjectId = null;

            if (is_object($subject)) {
                $subjectType = get_class($subject);
                $subjectId = property_exists($subject, 'id') ? (int) ($subject->id ?? 0) : null;
                if ($subjectId !== null && $subjectId <= 0) {
                    $subjectId = null;
                }

                if ($branchId === null && property_exists($subject, 'branch_id')) {
                    $bid = (int) ($subject->branch_id ?? 0);
                    $branchId = $bid > 0 ? $bid : null;
                }
            } elseif (is_array($subject) && isset($subject['type'], $subject['id'])) {
                $subjectType = (string) $subject['type'];
                $subjectId = (int) $subject['id'];
            }

            if ($userId === null) {
                $userId = auth()->check() ? (int) auth()->id() : null;
            }

            ActivityLog::query()->create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => $description,
                'meta' => $meta,
                'ip_address' => request()?->ip(),
                'user_agent' => substr((string) (request()?->userAgent() ?? ''), 0, 255) ?: null,
            ]);
        } catch (\Throwable $e) {
            return;
        }
    }
}
