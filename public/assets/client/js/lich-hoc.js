/* ============================================================
   LỊCH HỌC - JavaScript
   File: public/assets/client/js/lich-hoc.js
   ============================================================ */

(function () {
    'use strict';

    /* ── WEEK NAVIGATION ─────────────────────────────────── */

    /**
     * Chuyển chuỗi date ISO (YYYY-MM-DD) thành đối tượng Date
     */
    function parseDate(str) {
        const [y, m, d] = str.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    /**
     * Format đối tượng Date thành chuỗi YYYY-MM-DD
     */
    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    /**
     * Thêm số ngày vào date và trả về date mới
     */
    function addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    /**
     * Điều hướng tuần: negative = tuần trước, positive = tuần sau
     */
    function navigateWeek(offset) {
        const currentDateStr = document.getElementById('base-date').value;
        const currentDate    = parseDate(currentDateStr);
        const newDate        = addDays(currentDate, offset * 7);
        const newDateStr     = formatDate(newDate);
        const url            = new URL(window.location.href);
        url.searchParams.set('tuan', newDateStr);
        window.location.href = url.toString();
    }

    /**
     * Quay về tuần hiện tại (xóa param tuan)
     */
    function goToday() {
        const url = new URL(window.location.href);
        url.searchParams.delete('tuan');
        window.location.href = url.toString();
    }

    /* ── MODAL CHI TIẾT ──────────────────────────────────── */

    function showLessonModal(card) {
        const d = card.dataset;
        const modal = document.getElementById('lessonModal');

        // Tên lớp / khóa học
        modal.querySelector('#modal-ten-lop').textContent   = d.tenLop   || '—';
        modal.querySelector('#modal-khoa-hoc').textContent  = d.khoaHoc  || '—';
        modal.querySelector('#modal-ngay-hoc').textContent  = d.ngayHoc  || '—';
        modal.querySelector('#modal-ca-hoc').textContent    = d.caHoc    || '—';
        modal.querySelector('#modal-phong-hoc').textContent = d.phong    || '—';
        modal.querySelector('#modal-giao-vien').textContent = d.giaoVien || '—';
        modal.querySelector('#modal-co-so').textContent     = d.coSo     || '—';
        modal.querySelector('#modal-ghi-chu').textContent   = d.ghiChu   || '(Không có)';

        // Badge trạng thái buổi học
        const badge = modal.querySelector('#modal-type-badge');
        const statusClass = d.statusClass || 'sap-dien-ra';
        const statusLabel = d.statusLabel || 'Sắp diễn ra';
        badge.className = `modal-badge ${statusClass}`;
        badge.textContent = statusLabel;

        // Trạng thái chi tiết
        const status = modal.querySelector('#modal-status');
        if (d.statusKey === 'da-huy') {
            status.innerHTML = '<i class="fas fa-ban text-danger"></i> Buổi học đã hủy';
        } else if (d.statusKey === 'doi-lich') {
            status.innerHTML = '<i class="fas fa-calendar-xmark text-warning"></i> Buổi học đã được đổi lịch';
        } else if (d.statusKey === 'dang-dien-ra') {
            status.innerHTML = '<i class="fas fa-person-chalkboard text-primary"></i> Buổi học đang diễn ra';
        } else if (d.daDiemDanh === '1') {
            status.innerHTML = '<i class="fas fa-clipboard-check text-info"></i> Buổi học đã được điểm danh';
        } else if (d.daHoanThanh === '1') {
            status.innerHTML = '<i class="fas fa-check-circle text-success"></i> Đã hoàn thành';
        } else {
            status.innerHTML = '<i class="fas fa-clock text-warning"></i> Buổi học sắp diễn ra';
        }

        const bsModal = bootstrap.Modal.getOrCreate(modal);
        bsModal.show();
    }

    /* ── KHỞI TẠO ────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        // Nút điều hướng tuần
        const btnPrev  = document.getElementById('btn-prev-week');
        const btnNext  = document.getElementById('btn-next-week');
        const btnToday = document.getElementById('btn-today');

        if (btnPrev)  btnPrev.addEventListener('click',  function () { navigateWeek(-1); });
        if (btnNext)  btnNext.addEventListener('click',  function () { navigateWeek(1);  });
        if (btnToday) btnToday.addEventListener('click', goToday);

        // Click vào card buổi học → mở modal
        document.querySelectorAll('.lesson-card[data-ten-lop]').forEach(function (card) {
            card.addEventListener('click', function () {
                showLessonModal(this);
            });

            // Hiệu ứng ripple khi click
            card.addEventListener('mousedown', function (e) {
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position:absolute;width:8px;height:8px;border-radius:50%;
                    background:rgba(255,255,255,.35);pointer-events:none;
                    left:${e.offsetX - 4}px;top:${e.offsetY - 4}px;
                    animation:ripple-expand .5s ease-out forwards;
                `;
                this.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 500);
            });
        });

        // Highlight cột ngày hôm nay
        const todayStr = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        document.querySelectorAll('th[data-date]').forEach(function (th) {
            if (th.dataset.date === todayStr) {
                th.classList.add('today');
            }
        });
    });

    // CSS for ripple animation (inject once)
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
        @keyframes ripple-expand {
            from { transform: scale(1); opacity: .8; }
            to   { transform: scale(16); opacity: 0; }
        }
    `;
    document.head.appendChild(rippleStyle);
})();
