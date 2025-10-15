document.addEventListener("DOMContentLoaded", function () {
    const notifCountEl = document.getElementById('notificationsCount');
    const notifContentEl = document.getElementById('notificationsContent');
    const closeBtn = document.getElementById('closeModalBtn');
    const markAllBtn = document.getElementById('markAllReadBtn');
    const modal = document.getElementById('myModal');
    const api = window.NOTIFICATIONS_API;

    if (!api) return;

    window.NotificationBus = {
        emit(eventName, detail = {}) {
            document.dispatchEvent(new CustomEvent(eventName, { detail }));
        },
        on(eventName, callback) {
            document.addEventListener(eventName, callback);
        }
    };

    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    console.log(window.Echo);

    if (window.Echo) {
        window.Echo.private('admin.notifications')
            .listen('AdminNotificationCreated', (e) => {
                const notifCountEl = document.getElementById('notificationsCount');
                console.log(window.Echo);

                if (notifCountEl) {
                    const current = parseInt(notifCountEl.textContent || '0', 10);
                    notifCountEl.style.display = 'inline';
                    notifCountEl.textContent = current + 1;
                }
                console.log("📬 إشعار جديد وصل:");
                const notifContentEl = document.getElementById('notificationsContent');
                if (notifContentEl) {
                    const newNotif = `
                        <div class="notification-item unread" data-id="${e.id}">
                            <div class="title">${e.title}</div>
                            <div class="message text-muted small">${e.message}</div>
                            <div class="time text-secondary small">الآن</div>
                            <button class="btn btn-sm btn-outline-primary mark-read-btn mt-1" data-id="${e.id}">
                                تحديد كمقروء
                            </button>
                        </div>`;
                    notifContentEl.insertAdjacentHTML('afterbegin', newNotif);
                }

                try {
                    const audio = document.getElementById('notificationSound');
                    if (audio) {
                        audio.volume = 0.6;
                        audio.play().catch(() => {});
                    }
                } catch {}

                NotificationBus.emit('notifications:new', { notification: e });
            });
    }

    function fetchNotificationsCount() {
        fetch(api.countUrl)
            .then(res => res.json())
            .then(data => {
                if (notifCountEl) {
                    if (data.count > 0) {
                        notifCountEl.style.display = 'inline';
                        notifCountEl.textContent = data.count;
                    } else {
                        notifCountEl.style.display = 'none';
                    }
                }
                NotificationBus.emit('notifications:count', { count: data.count });
            })
            .catch(() => {
                if (notifCountEl) notifCountEl.style.display = 'none';
            });
    }

    window.openModal = function (event) {
        if (event) event.preventDefault();
        if (!modal) return;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (notifContentEl) {
            notifContentEl.innerHTML = `<div class="text-center text-muted p-3">جاري تحميل الإشعارات...</div>`;
            fetch(api.listUrl)
                .then(res => res.text())
                .then(html => {
                    notifContentEl.innerHTML = html;
                    attachMarkReadHandlers();
                    const notifications = Array.from(notifContentEl.querySelectorAll('.notification-item')).map(el => ({
                        id: el.dataset.id,
                        title: el.querySelector('.title')?.textContent,
                        message: el.querySelector('.message')?.textContent,
                        is_read: el.classList.contains('read')
                    }));
                    NotificationBus.emit('notifications:loaded', { notifications });
                })
                .catch(() => notifContentEl.innerHTML = `<div class="text-center text-danger p-3">فشل تحميل الإشعارات.</div>`);
        }
    };

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    window.markAsRead = function (id) {
        const csrf = getCsrf();
        return fetch(`/admin/notifications/mark-as-read/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(res => res.json());
    };

    function attachMarkReadHandlers() {
        notifContentEl.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.removeEventListener('click', onMarkClick);
            btn.addEventListener('click', onMarkClick);
        });
    }

    function onMarkClick(e) {
        const id = this.dataset.id;
        if (!id) return;

        markAsRead(id)
            .then(() => {
                const item = this.closest('.notification-item');
                if (item) item.classList.remove('unread');
                const current = parseInt(notifCountEl.textContent || '0', 10);
                const next = Math.max(0, current - 1);
                if (next > 0) {
                    notifCountEl.textContent = next;
                } else {
                    notifCountEl.style.display = 'none';
                }
                NotificationBus.emit('notifications:count', { count: next });
            })
            .catch(() => alert('حدث خطأ أثناء تمييز الإشعار كمقروء'));
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            const csrf = getCsrf();
            fetch(api.markReadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(() => {
                    notifContentEl.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
                    notifCountEl.style.display = 'none';
                    NotificationBus.emit('notifications:count', { count: 0 });
                })
                .catch(() => alert('فشل تمييز الكل كمقروء'));
        });
    }

    fetchNotificationsCount();
    setInterval(fetchNotificationsCount, 60000);
});

const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function handleNotificationClick(id, url) {
    if (!id) return;

    fetch(`/admin/notifications/mark-as-read/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
    })
        .then(res => res.json())
        .then(() => {
            const el = document.querySelector(`.notification-item[data-id='${id}']`);
            if (el) {
                el.classList.remove('unread');
                el.classList.add('read');
            }
            if (url) window.location.href = url;
        })
        .catch(err => console.error('Error marking notification:', err));
}

document.addEventListener("click", function enableSound() {
    const audio = document.getElementById("notif-sound");
    if (audio) {
        audio.muted = false;
        audio.volume = 0.0;
        audio.play().then(() => {
            console.log("🔊 تم تفعيل الصوت بنجاح بعد أول نقرة");
        }).catch(() => {});
    }
    document.removeEventListener("click", enableSound);
});
