/**
 * thong-bao.js — Client Notification System
 * File: public/assets/client/js/pages/thong-bao/thong-bao.js
 *
 * Chức năng:
 *  - Quản lý SSE (Server-Sent Events) với auto-reconnect
 *  - Fallback: polling mỗi 30s nếu SSE không khả dụng
 *  - Cập nhật số badge (header bell + account sidebar)
 *  - Hiển thị toast popup khi có thông báo mới
 *  - Dropdown bell: load AJAX, render, mark-read
 *  - Trang danh sách: filter, mark-read on click
 *
 * Yêu cầu window.* inject bởi script.blade.php:
 *   window.NB_SSE_URL      — route('api.thong-bao.stream')
 *   window.NB_DROPDOWN_URL — route('api.thong-bao.dropdown')
 *   window.NB_MARK_READ_URL — base url (sẽ append /{id}/da-doc)
 *   window.NB_MARK_ALL_URL  — route('api.thong-bao.mark-all-read')
 *   window.NB_PAGE_URL      — route('home.thong-bao.index')
 *   window.NB_IS_AUTH       — true nếu đã đăng nhập
 *   window.NB_CSRF          — CSRF token
 */

if (!window.NB_IS_AUTH) {
    // Không đăng nhập → không cần chạy gì
    throw new Error('NotificationSystem: User not authenticated, skipping.');
}

// ═══════════════════════════════════════════════════════════════
// CONSTANTS & STATE
// ═══════════════════════════════════════════════════════════════
const CSRF         = window.NB_CSRF;
const SSE_URL      = window.NB_SSE_URL;
const DROPDOWN_URL = window.NB_DROPDOWN_URL;
const MARK_ALL_URL = window.NB_MARK_ALL_URL;
const PAGE_URL     = window.NB_PAGE_URL;
const MARK_BASE    = window.NB_MARK_READ_URL;

const ICON_MAP = {
    0: { cls: 'icon-he-thong',  fa: 'fa-cog' },
    1: { cls: 'icon-hoc-tap',   fa: 'fa-graduation-cap' },
    2: { cls: 'icon-tai-chinh', fa: 'fa-wallet' },
    3: { cls: 'icon-su-kien',   fa: 'fa-calendar-alt' },
    4: { cls: 'icon-khan-cap',  fa: 'fa-exclamation-triangle' },
};

let eventSource     = null;
let pollingInterval = null;
let sseSupported    = typeof EventSource !== 'undefined';
let dropdownLoaded  = false;

// ═══════════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════════
function timeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return 'vừa xong';
    if (diff < 3600)  return Math.floor(diff / 60) + ' phút trước';
    if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
    return Math.floor(diff / 86400) + ' ngày trước';
}

function getIconConfig(loaiGui) {
    return ICON_MAP[loaiGui] ?? ICON_MAP[0];
}

// ═══════════════════════════════════════════════════════════════
// BADGE MANAGEMENT
// ═══════════════════════════════════════════════════════════════
function updateBadges(count) {
    // Header bell badge
    const bellBadge = document.getElementById('client-bell-badge');
    if (bellBadge) {
        if (count > 0) {
            bellBadge.textContent = count > 99 ? '99+' : count;
            bellBadge.classList.add('show');
        } else {
            bellBadge.classList.remove('show');
        }
    }

    // Sidebar account badge
    const sidebarBadge = document.getElementById('sidebar-nb-badge');
    if (sidebarBadge) {
        if (count > 0) {
            sidebarBadge.textContent = count > 99 ? '99+' : count;
            sidebarBadge.classList.add('show');
        } else {
            sidebarBadge.classList.remove('show');
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ═══════════════════════════════════════════════════════════════
let toastContainer = null;

function ensureToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'nb-toast-container';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

function showToast(notification) {
    const container = ensureToastContainer();
    const icon      = getIconConfig(notification.loaiGui);
    const isUrgent  = notification.uuTien === 2;
    const isImportant = notification.uuTien === 1;

    const toast = document.createElement('div');
    toast.className = `nb-toast${isUrgent ? ' khan-cap' : isImportant ? ' quan-trong' : ''}`;
    toast.innerHTML = `
        <div class="nb-toast-icon ${icon.cls}">
            <i class="fas ${icon.fa}"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div class="nb-toast-title">${notification.tieuDe}</div>
            <div class="nb-toast-preview">${notification.tomTat}</div>
            <div class="nb-toast-time">Vừa nhận</div>
        </div>
        <button class="nb-toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Click → navigate to notification page
    toast.addEventListener('click', (e) => {
        if (e.target.closest('.nb-toast-close')) return;
        markOneRead(notification.id);
        window.location.href = PAGE_URL;
    });

    container.appendChild(toast);

    // Auto remove after 6 seconds
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}

// ═══════════════════════════════════════════════════════════════
// DROPDOWN BELL
// ═══════════════════════════════════════════════════════════════
async function loadDropdown() {
    const listEl = document.getElementById('client-bell-list');
    if (!listEl) return;

    try {
        const resp = await fetch(DROPDOWN_URL);
        const data = await resp.json();

        updateBadges(data.unreadCount);

        if (!data.notifications.length) {
            listEl.innerHTML = `
                <div style="text-align:center;padding:2rem 1rem;color:#9ca3af;">
                    <i class="fas fa-bell-slash" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    Không có thông báo nào
                </div>`;
            return;
        }

        listEl.innerHTML = data.notifications.map(n => {
            const icon = getIconConfig(n.loaiGui);
            return `
                <a href="${PAGE_URL}"
                   class="list-group-item list-group-item-action p-3 border-0 noti-item${n.daDoc ? '' : ' unread'}"
                   onclick="handleDropdownClick(event, ${n.id})">
                    <div class="d-flex gap-3">
                        <div class="icon-circle ${icon.cls}" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas ${icon.fa}"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="noti-item-title">${n.tieuDe}</div>
                            <div class="noti-item-preview">${n.tomTat}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="noti-item-time">${timeAgo(n.ngayGui)}</span>
                                ${!n.daDoc ? '<span class="noti-dot"></span>' : ''}
                            </div>
                        </div>
                    </div>
                </a>`;
        }).join('');

        dropdownLoaded = true;
    } catch (e) {
        console.error('[NB] Dropdown load error', e);
    }
}

async function handleDropdownClick(e, id) {
    await markOneRead(id);
}
window.handleDropdownClick = handleDropdownClick;

async function markAllRead() {
    await fetch(MARK_ALL_URL, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF } });
    document.querySelectorAll('.noti-item.unread').forEach(el => el.classList.remove('unread'));
    document.querySelectorAll('.noti-dot').forEach(el => el.remove());
    updateBadges(0);
    // Update sidebar badge on list page
    document.querySelectorAll('.nb-item.unread').forEach(el => el.classList.remove('unread'));
}
window.markAllRead = markAllRead;

async function markOneRead(id) {
    await fetch(`${MARK_BASE}/${id}/da-doc`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF },
    });
}

// ═══════════════════════════════════════════════════════════════
// SSE CONNECTION — DISABLED for php artisan serve compatibility
// php artisan serve = PHP built-in single-threaded server.
// SSE stream holds the single thread, blocking ALL other requests.
// Use polling instead. Switch to SSE only with Apache/Nginx + php-fpm.
// ═══════════════════════════════════════════════════════════════
function connectSSE() {
    // Không dùng SSE với development server (single-threaded)
    // Fallback ngay sang polling
    console.log('[NB] SSE disabled (dev server), using polling');
    startPolling();
}

// ═══════════════════════════════════════════════════════════════
// FALLBACK: POLLING
// ═══════════════════════════════════════════════════════════════
let lastKnownCount = -1;

async function fetchUnreadCount() {
    try {
        const resp = await fetch(window.NB_UNREAD_URL);
        const data = await resp.json();
        updateBadges(data.count);

        if (lastKnownCount !== -1 && data.count > lastKnownCount) {
            // Có thông báo mới → load dropdown để lấy dữ liệu mới
            loadDropdown();
        }
        lastKnownCount = data.count;
    } catch (e) {
        console.error('[NB] fetchUnreadCount error', e);
    }
}

function startPolling() {
    if (pollingInterval) return;
    console.log('[NB] Starting polling fallback (60s)');
    fetchUnreadCount(); // Ngay lập tức
    pollingInterval = setInterval(fetchUnreadCount, 60000); // 60s là đủ cho notifications
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// ═══════════════════════════════════════════════════════════════
// BELL DROPDOWN TOGGLE (Client header)
// ═══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const bellBtn      = document.getElementById('client-bell-btn');
    const bellDropdown = document.getElementById('client-bell-dropdown');
    const markAllBtn   = document.getElementById('client-mark-all-btn');

    if (bellBtn && bellDropdown) {
        // Sử dụng event chuẩn của Bootstrap 5 thay vì tự toggle class
        bellBtn.addEventListener('show.bs.dropdown', () => {
            loadDropdown();
        });

        // Vẫn giữ close click outside phòng trường hợp click trong dropdown bị đóng
        bellDropdown.addEventListener('click', (e) => {
            // Ngăn dropdown tự đóng khi click bên trong (trừ khi click thẻ a/button)
            if (!e.target.closest('a') && !e.target.closest('button')) {
                e.stopPropagation();
            }
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', async () => {
            await markAllRead();
        });
    }

    // ── On notification list page: mark read on click ──
    document.querySelectorAll('.nb-item[data-id]').forEach(item => {
        item.addEventListener('click', async () => {
            const id = item.dataset.id;
            if (item.dataset.read === '0') {
                await markOneRead(id);
                item.classList.remove('unread');
                item.dataset.read = '1';
                const dot = item.querySelector('.nb-unread-dot');
                if (dot) dot.remove();
                fetchUnreadCount();
            }
        });
    });

    // ── Start SSE / polling ──
    connectSSE();

    // Initial badge load
    fetchUnreadCount();
});
