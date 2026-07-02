<?php
declare(strict_types=1);

namespace App\Core;

/** Audit trail: who, when, what changed, from where. */
class Audit
{
    public static function log(
        string $action,
        string $entity,
        ?string $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        // Never persist secrets in the audit trail.
        foreach (['password', 'password_hash', 'two_factor_code', 'api_key'] as $secret) {
            unset($oldValues[$secret], $newValues[$secret]);
        }
        Database::insert('audit_logs', [
            'user_id'    => Auth::id(),
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }
}
