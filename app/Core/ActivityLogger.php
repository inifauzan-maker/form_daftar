<?php

namespace App\Core;

use App\Models\ActivityLog;
use Throwable;

class ActivityLogger
{
    public static function log(Request $request, string $action, string $description = '', array $metadata = [], ?int $userId = null): void
    {
        try {
            $logger = new ActivityLog();
            $logger->create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_path' => $request->path(),
                'request_method' => $request->method(),
            ]);
        } catch (Throwable $throwable) {
            if (config('app.debug')) {
                throw $throwable;
            }
        }
    }
}
