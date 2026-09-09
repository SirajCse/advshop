<?php

namespace Botble\Base\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Support\Services\Cache\Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminNotification extends BaseModel
{
    use MassPrunable;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'title',
        'action_label',
        'action_url',
        'description',
        'permission',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'title' => SafeContent::class,
        'action_label' => SafeContent::class,
        'action_url' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    public function markAsRead(): void
    {
        $this->update([
            'read_at' => Carbon::now(),
        ]);
    }

    public function prunable(): Builder|BaseQueryBuilder
    {
        return static::query()->where('created_at', '<=', Carbon::now()->subMonth());
    }

    public static function countUnread(): int
    {
        /**
         * @var AdminNotificationQueryBuilder $adminQuery
         */
        $adminQuery = AdminNotification::query();

        /**
         * @var Builder $query
         */
        $query = $adminQuery->hasPermission();

        return $query
            ->whereNull('read_at')
            ->select('action_url')
            ->count();
    }

    public function isAbleToAccess(): bool
    {
        $user = Auth::guard()->user();

        return ! $this->permission || $user->isSuperUser() || $user->hasPermission($this->permission);
    }

    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            if ($notification->action_url) {
                $notification->action_url = str_replace(url(''), '', $notification->action_url);
            }
        });

        static::saved(function (): void {
            static::flushAdminNotificationCache();
        });

        static::deleted(function (): void {
            static::flushAdminNotificationCache();
        });
    }

    protected static function flushAdminNotificationCache(): void
    {
        Cache::make(static::class)->flush();

        try {
            DB::table('users')->pluck('id')->each(function ($id): void {
                cache()->forget('admin-notifications-count-' . $id);
            });
        } catch (Throwable) {
            // users table may be unavailable during install/migration
        }
    }

    public function newEloquentBuilder($query): AdminNotificationQueryBuilder
    {
        return new AdminNotificationQueryBuilder($query);
    }
}
