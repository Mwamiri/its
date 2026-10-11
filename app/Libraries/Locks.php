<?php

namespace App\Libraries;

use App\Models\SettingModel;

/**
 * Record locking: while someone has an entry open for editing, others see who is editing
 * and cannot save over their work. A lock lapses after 15 minutes without activity
 * (the open form should renew it every 2 minutes via GET /its-lock/{entity}/{id}).
 *
 * Requires the record_locks table (see migration) and setting record_locking = 1.
 */
class Locks
{
    public const MINUTES = 15;

    public static function enabled(): bool
    {
        return SettingModel::get('record_locking', '0') === '1';
    }

    /**
     * Who else is editing this record right now: [user_id, name, since] or null.
     */
    public static function holder(string $entity, int $id, int $currentUserId): ?array
    {
        if (! self::enabled()) {
            return null;
        }
        $row = db_connect()->table('record_locks l')
            ->select('l.user_id, l.locked_at, u.name')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where(['l.entity' => $entity, 'l.entity_id' => $id])
            ->get()->getRowArray();
        if (! $row || (int) $row['user_id'] === $currentUserId || strtotime($row['locked_at']) < time() - self::MINUTES * 60) {
            return null;
        }
        return ['user_id' => (int) $row['user_id'], 'name' => $row['name'] ?: 'another user', 'since' => $row['locked_at']];
    }

    /**
     * Take or renew the lock for the current user. Returns the other holder when the lock is taken.
     */
    public static function acquire(string $entity, int $id, int $currentUserId, bool $takeOver = false): ?array
    {
        if (! self::enabled()) {
            return null;
        }
        if (! $takeOver && ($other = self::holder($entity, $id, $currentUserId))) {
            return $other;
        }
        $db = db_connect();
        $db->table('record_locks')->where(['entity' => $entity, 'entity_id' => $id])->delete();
        $db->table('record_locks')->insert(['entity' => $entity, 'entity_id' => $id, 'user_id' => $currentUserId, 'locked_at' => date('Y-m-d H:i:s')]);
        return null;
    }

    public static function release(string $entity, int $id, int $currentUserId): void
    {
        if (self::enabled()) {
            db_connect()->table('record_locks')->where(['entity' => $entity, 'entity_id' => $id, 'user_id' => $currentUserId])->delete();
        }
    }

    /** Clean up expired locks; call occasionally (e.g. from a cron command). */
    public static function purgeExpired(): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - self::MINUTES * 60);
        db_connect()->table('record_locks')->where('locked_at <', $cutoff)->delete();
        return db_connect()->affectedRows();
    }
}
