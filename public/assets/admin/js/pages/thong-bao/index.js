/**
 * thong-bao-index.js
 * JavaScript cho trang Danh sách Thông Báo (admin/thong-bao/index)
 * Chức năng: checkbox bulk-select, bulk-delete AJAX, single-delete, toggle-pin
 */

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Checkbox bulk select ─────────────────────────────────────────────────────
const checkAll  = document.getElementById('checkAll');
const btnBulk   = document.getElementById('btnBulkDelete');
const countSpan = document.getElementById('selectedCount');

function updateBulkBtn() {
    const checked = document.querySelectorAll('.cb-row:checked');
    countSpan.textContent = checked.length;
    btnBulk.disabled = checked.length === 0;
}

if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.cb-row').forEach(cb => (cb.checked = this.checked));
        updateBulkBtn();
    });
}
document.querySelectorAll('.cb-row').forEach(cb => cb.addEventListener('change', updateBulkBtn));

// ── Bulk delete ──────────────────────────────────────────────────────────────
if (btnBulk) {
    btnBulk.addEventListener('click', async function () {
        const ids = [...document.querySelectorAll('.cb-row:checked')].map(c => c.value);
        if (!ids.length) return;

        const result = await Swal.fire({
            title: `Chuyển ${ids.length} thông báo vào thùng rác?`,
            text: 'Bạn có thể khôi phục lại tại màn Thùng rác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Chuyển vào thùng rác',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Huỷ',
        });
        if (!result.isConfirmed) return;

        const resp = await fetch(BULK_DESTROY_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids }),
        });
        const data = await resp.json();
        if (data.success) {
            Toast.fire({ icon: 'success', title: data.message });
            setTimeout(() => location.reload(), 1200);
        }
    });
}

// ── Single delete ────────────────────────────────────────────────────────────
async function deleteSingle(id) {
    const result = await Swal.fire({
        title: 'Chuyển thông báo vào thùng rác?',
        text: 'Bạn có thể khôi phục lại tại màn Thùng rác.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Chuyển vào thùng rác',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Huỷ',
    });
    if (result.isConfirmed) {
        document.getElementById(`del-form-${id}`).submit();
    }
}
window.deleteSingle = deleteSingle;

// ── Duplicate to draft ─────────────────────────────────────────────────────
async function duplicateThongBao(id) {
    const result = await Swal.fire({
        title: 'Nhân bản thông báo này?',
        text: 'Hệ thống sẽ tạo một bản nháp mới để bạn chỉnh sửa.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Nhân bản',
        cancelButtonText: 'Huỷ',
    });
    if (result.isConfirmed) {
        document.getElementById(`dup-form-${id}`).submit();
    }
}
window.duplicateThongBao = duplicateThongBao;

// ── Send test to self ──────────────────────────────────────────────────────
async function sendTestThongBao(id) {
    const result = await Swal.fire({
        title: 'Gửi thử thông báo?',
        text: 'Thông báo sẽ được gửi cho chính tài khoản của bạn.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Gửi thử',
        cancelButtonText: 'Huỷ',
    });
    if (result.isConfirmed) {
        document.getElementById(`test-form-${id}`).submit();
    }
}
window.sendTestThongBao = sendTestThongBao;

// ── Toggle PIN ───────────────────────────────────────────────────────────────
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
