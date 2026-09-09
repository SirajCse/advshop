<?php

namespace Botble\Base\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\AdminNotification;
use Botble\Base\Models\AdminNotificationQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    protected function clearNotificationCountCache(): void
    {
        cache()->forget('admin-notifications-count-' . Auth::guard()->id());
    }
    public function index(): BaseHttpResponse
    {
        $notificationsCount = AdminNotification::countUnread();

        /**
         * @var AdminNotificationQueryBuilder $adminQuery
         */
        $adminQuery = AdminNotification::query();

        $query = $adminQuery->hasPermission();

        $notifications = $query
            ->latest()
            ->paginate(10);

        return $this
            ->httpResponse()
            ->setData(view('core/base::notification.partials.content', compact('notifications', 'notificationsCount'))->render());
    }

    public function destroy(int|string $id): BaseHttpResponse
    {
        $notificationItem = AdminNotification::query()->findOrFail($id);
        $notificationItem->delete();

        $this->clearNotificationCountCache();

        /**
         * @var AdminNotificationQueryBuilder $adminQuery
         */
        $adminQuery = AdminNotification::query();

        /**
         * @var Builder $query
         */
        $query = $adminQuery->hasPermission();

        if (! $query->exists()) {
            return $this
                ->httpResponse()
                ->setData(view('core/base::notification.partials.content')->render());
        }

        return $this->httpResponse();
    }

    public function deleteAll(): BaseHttpResponse
    {
        AdminNotification::query()->delete();

        $this->clearNotificationCountCache();

        return $this->httpResponse();
    }

    public function read(int|string $id): RedirectResponse
    {
        /**
         * @var AdminNotification $notificationItem
         */
        $notificationItem = AdminNotification::query()->findOrFail($id);

        if ($notificationItem->read_at === null) {
            $notificationItem->markAsRead();
            $this->clearNotificationCountCache();
        }

        if (! $notificationItem->action_url || $notificationItem->action_url == '#') {
            return redirect()->back();
        }

        return redirect()->to(url($notificationItem->action_url));
    }

    public function readAll(): BaseHttpResponse
    {
        AdminNotification::query()
            ->whereNull('read_at')
            ->update([
                'read_at' => Carbon::now(),
            ]);

        $this->clearNotificationCountCache();

        return $this->httpResponse();
    }

    public function countUnread(): BaseHttpResponse
    {
        return $this
            ->httpResponse()
            ->setData(AdminNotification::countUnread());
    }
}
