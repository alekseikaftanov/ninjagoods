<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_name',
        'action',
        'entity_type',
        'entity_name',
        'entity_id',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public static function logAction(string $action, string $entityType, string $entityName, ?int $entityId = null, ?array $changes = null, ?string $ipAddress = null): void
    {
        self::create([
            'admin_name' => 'Admin',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_name' => $entityName,
            'entity_id' => $entityId,
            'changes' => $changes,
            'ip_address' => $ipAddress,
        ]);
    }
}
