/**
 * show.js
 * JavaScript cho trang Chi tiết Thông Báo (admin/thong-bao/{id})
 * Chức năng: Chart.js doughnut tỉ lệ đọc, filter người nhận,
 *            toggle pin AJAX, delete confirm
 *
 * Yêu cầu biến toàn cục inject bởi Blade:
 *   - window.DA_DOC     : số người đã đọc
 *   - window.CHUA_DOC   : số người chưa đọc
 *   - window.THONG_BAO_ID
 */

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Doughnut chart (Chart.js) ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('readChart');
    if (ctx) {
        new window.Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [window.DA_DOC, window.CHUA_DOC],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0,
                }],
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend:  { display: false },
                    tooltip: { enabled: false },
                },
                animation: { animateRotate: true, duration: 900 },
            },
        });
    }
});

// ── Filter danh sách người nhận ───────────────────────────────────────────────
function filterRecipients(type, btn) {
    document.querySelectorAll('.rf-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.recipient-item').forEach(item => {
        const read = item.dataset.read === '1';
        item.style.display =
            type === 'all'    ? ''
          : type === 'read'   ? (read  ? '' : 'none')
          : type === 'unread' ? (!read ? '' : 'none')
          : '';
    });
}
window.filterRecipients = filterRecipients;

// ── Toggle pin ────────────────────────────────────────────────────────────────
async function togglePin(id) {
    const resp = await fetch(`/admin/thong-bao/${id}/ghim`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF },
    });
    const data = await resp.json();
    if (data.success) {
        Toast.fire({ icon: 'success', title: data.message });
        setTimeout(() => location.reload(), 900);
    }
}
window.togglePin = togglePin;

// ── Delete confirm ────────────────────────────────────────────────────────────
async function deleteThis() {
    const result = await Swal.fire({
        title: 'Xóa thông báo này?',
        text: 'Thao tác này không thể hoàn tác.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Huỷ',
    });
    if (result.isConfirmed) {
        document.getElementById('del-form').submit();
    }
}
window.deleteThis = deleteThis;
