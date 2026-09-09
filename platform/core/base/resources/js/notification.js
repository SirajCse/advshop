$(() => {
    const $notification = $(document).find('#notification-sidebar')

    const setNotificationsBadge = (count) => {
        const $badges = $(document).find('.badge.notification-count')

        if (count > 0) {
            $badges.text(count).show()
        } else {
            $badges.text('0').hide()
        }
    }

    const updateNotificationsCount = () => {
        $httpClient
            .make()
            .get($notification.data('count-url'))
            .then(({ data }) => {
                setNotificationsBadge(data.data)
            })
    }

    const updateNotificationsContent = (url) => {
        $httpClient
            .make()
            .get(url || $notification.data('url'))
            .then(({ data }) => {
                $notification.find('.notification-content').html(data.data)
            })
    }

    const closeNotification = () => {
        $notification.offcanvas('hide')
    }

    $notification.on('hide.bs.offcanvas', () => {
        $('.offcanvas-backdrop').remove()
    })

    $(document).on('click', '.offcanvas-backdrop', function () {
        $(this).remove()

        closeNotification()
    })

    $notification
        .on('show.bs.offcanvas', () => {
            updateNotificationsContent()
            updateNotificationsCount()

            $('body').after(`<div class="offcanvas-backdrop"></div>`)
        })
        .on('click', '.mark-all-notifications-as-read', function (e) {
            e.preventDefault()

            setNotificationsBadge(0)

            $httpClient
                .make()
                .put($(this).data('url'))
                .then(() => {
                    updateNotificationsContent()
                })
        })
        .on('click', '.clear-notifications', function () {
            setNotificationsBadge(0)

            $httpClient
                .make()
                .delete($(this).data('url'))
                .then(() => {
                    closeNotification()
                })
        })
        .on('click', '.list-group-item .btn-delete-notification', function () {
            const $item = $(this).closest('.list-group-item')
            const isUnread = $item.hasClass('active')

            $httpClient
                .make()
                .delete($(this).data('url'))
                .then(() => {
                    $item.hide('slow', () => {
                        $item.remove()
                        updateNotificationsContent()
                    })

                    if (isUnread) {
                        const currentCount = parseInt($(document).find('.badge.notification-count').first().text(), 10) || 0
                        setNotificationsBadge(Math.max(0, currentCount - 1))
                    }
                })
        })
        .on('click', 'nav .btn-previous', function () {
            updateNotificationsContent($(this).data('url'))
        })
        .on('click', 'nav .btn-next', function () {
            updateNotificationsContent($(this).data('url'))
        })
})
