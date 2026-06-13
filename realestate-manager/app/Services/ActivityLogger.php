<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null, ?int $clientId = null)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'client_id' => $clientId ?? self::resolveClientId($model),
                'action' => $action,
                'loggable_type' => get_class($model),
                'loggable_id' => $model->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to write activity log: ' . $e->getMessage());
        }
    }

    public static function logCreate(Model $model, ?int $clientId = null)
    {
        self::log('create', $model, null, $model->toArray(), $clientId);
    }

    public static function logUpdate(Model $model, array $oldValues, ?int $clientId = null)
    {
        // Only log if values actually changed
        $newValues = $model->toArray();
        $changes = array_diff_assoc($newValues, $oldValues);
        
        // Remove timestamps from changes comparison
        unset($changes['updated_at'], $changes['created_at']);

        if (!empty($changes)) {
            self::log('update', $model, $oldValues, $newValues, $clientId);
        }
    }

    public static function logDelete(Model $model, $clientId = null, ?string $reason = null)
    {
        if (is_string($clientId)) {
            $reason = $clientId;
            $clientId = null;
        }

        $oldValues = $model->toArray();
        if ($reason) {
            $oldValues['delete_reason'] = $reason;
        }

        self::log('delete', $model, $oldValues, null, $clientId);
    }

    public static function logRestore(Model $model, ?int $clientId = null)
    {
        self::log('restore', $model, null, $model->toArray(), $clientId);
    }

    protected static function resolveClientId(Model $model): ?int
    {
        if ($model instanceof \App\Models\Client) {
            return $model->id;
        }

        if (isset($model->client_id)) {
            return $model->client_id;
        }

        return null;
    }
}
