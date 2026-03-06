/**
 * thong-bao-create.js
 * JavaScript cho trang Tạo Thông Báo mới (admin/thong-bao/tao-moi)
 * Chức năng: Wizard 3 bước, Quill editor sync, radio card đối tượng,
 *            AJAX preview recipients, build confirm step
 *
 * Yêu cầu biến toàn cục được inject bởi Blade:
 *   - window.RECIPIENTS_URL : route api.thong-bao.recipients
 *   - window.LOAI_LABELS    : ThongBao::loaiLabels()
 *   - window.UU_TIEN_LABELS : ThongBao::uuTienLabels()
 *   - window.DOI_TUONG_LABELS: ThongBao::doiTuongLabels()
 */

// ── Quill Editor ─────────────────────────────────────────────────────────────
const quill = new Quill('#quillEditor', {
    theme: 'snow',
    placeholder: 'Nhập nội dung thông báo…',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link'],
            ['clean'],
        ],
    },
});

const noiDungHidden = document.getElementById('noiDungHidden');

// Sync Quill → hidden textarea
quill.on('text-change', () => {
    noiDungHidden.value = quill.root.innerHTML;
});

// Preload old value (khi validation fail, quay lại)
if (noiDungHidden.value) {
    quill.root.innerHTML = noiDungHidden.value;
}

// ── Wizard State ─────────────────────────────────────────────────────────────
let currentStep    = 1;
let previewCount   = 0;
let currentDoiTuong = 0; // default: Tất cả

function updateComposeSummary() {
    const titleEl = document.getElementById('compose-title');
    const loaiEl = document.getElementById('compose-loai');
    const uuTienEl = document.getElementById('compose-uu-tien');
    const doiTuongEl = document.getElementById('compose-doi-tuong');
    const recipientEl = document.getElementById('compose-recipient');
    const pinEl = document.getElementById('compose-pin');
    const stepEl = document.getElementById('compose-step');

    const tieuDe = document.getElementById('tieuDe')?.value.trim();
    const loai = document.querySelector('[name=loaiGui]')?.value ?? 0;
    const uuTien = document.querySelector('[name=uuTien]')?.value ?? 0;
    const doiTuong = document.querySelector('[name=doiTuongGui]:checked')?.value ?? 0;
    const ghim = document.querySelector('[name=ghim]')?.checked;

    if (titleEl) titleEl.textContent = tieuDe || 'Chưa nhập';
    if (loaiEl) loaiEl.textContent = window.LOAI_LABELS[loai] ?? '—';
    if (uuTienEl) uuTienEl.textContent = window.UU_TIEN_LABELS[uuTien] ?? '—';
    if (doiTuongEl) doiTuongEl.textContent = window.DOI_TUONG_LABELS[doiTuong] ?? '—';
    if (recipientEl) recipientEl.textContent = previewCount;
    if (pinEl) pinEl.textContent = ghim ? 'Có' : 'Không';
    if (stepEl) stepEl.textContent = `${currentStep}/3`;
}

/**
 * Chuyển sang step N, validate từng bước
 */
function goStep(n) {
    // Validate step 1 trước khi sang step 2+
    if (n > 1) {
        const tieuDe  = document.getElementById('tieuDe').value.trim();
        const noiDung = quill.getText().trim();
        if (!tieuDe)  { Toast.fire({ icon: 'warning', title: 'Vui lòng nhập tiêu đề!' }); return; }
        if (!noiDung) { Toast.fire({ icon: 'warning', title: 'Vui lòng nhập nội dung!' }); return; }
        noiDungHidden.value = quill.root.innerHTML;
    }

    // Build bước xác nhận
    if (n === 3) buildConfirmStep();

    // Hiện panel tương ứng
    document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
    document.getElementById(`panel-${n}`).classList.add('active');

    // Cập nhật step dots và connectors
    for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById(`step-dot-${i}`);
        dot.classList.remove('active', 'done');
        if (i < n) dot.classList.add('done');
        else if (i === n) dot.classList.add('active');
    }
    for (let i = 1; i <= 2; i++) {
        document.getElementById(`conn-${i}`).classList.toggle('done', i < n);
    }
    currentStep = n;
    updateComposeSummary();
}
window.goStep = goStep;

/**
 * Điền dữ liệu vào bước 3 (Xác nhận)
 */
function buildConfirmStep() {
    const tieuDe   = document.getElementById('tieuDe').value;
    const loai     = document.querySelector('[name=loaiGui]').value;
    const uuTien   = document.querySelector('[name=uuTien]').value;
    const doiTuong = document.querySelector('[name=doiTuongGui]:checked')?.value ?? 0;

    document.getElementById('cf-tieu-de').textContent   = tieuDe;
    document.getElementById('cf-noi-dung').innerHTML    = quill.root.innerHTML;
    document.getElementById('cf-loai').textContent      = window.LOAI_LABELS[loai]      ?? '—';
    document.getElementById('cf-uu-tien').textContent   = window.UU_TIEN_LABELS[uuTien]  ?? '—';
    document.getElementById('cf-doi-tuong').textContent = window.DOI_TUONG_LABELS[doiTuong] ?? '—';
    document.getElementById('cf-count').textContent     = previewCount;
    updateComposeSummary();
}

// ── Doi tuong radio cards ─────────────────────────────────────────────────────
/**
 * Chọn loại đối tượng → show sub-selector → fetch preview
 */
function selectDoiTuong(val, labelEl) {
    currentDoiTuong = parseInt(val);

    // Update selected CSS
    document.querySelectorAll('.doi-tuong-card').forEach(c => c.classList.remove('selected'));
    labelEl.classList.add('selected');

    // Ẩn tất cả sub-panels và remove name attribute (tránh submit thừa)
    const subSel = document.getElementById('subSelector');
    document.querySelectorAll('.ss-panel').forEach(p => {
        p.style.display = 'none';
        p.querySelectorAll('select').forEach(s => s.removeAttribute('name'));
    });

    // Map đối tượng → panel
    const panelMap = { 1: 'ss-lop', 2: 'ss-khoa', 3: 'ss-canhan', 4: 'ss-role' };
    const panelId  = panelMap[val];

    if (panelId) {
        subSel.style.display = 'block';
        const panel = document.getElementById(panelId);
        panel.style.display = 'block';
        // Gắn lại name
        const sel = panel.querySelector('select');
        sel.name = 'doiTuongId';
        sel.addEventListener('change', fetchRecipientPreview);
    } else {
        subSel.style.display = 'none';
    }

    fetchRecipientPreview();
    updateComposeSummary();
}
window.selectDoiTuong = selectDoiTuong;

// ── AJAX: Preview người nhận ──────────────────────────────────────────────────
async function fetchRecipientPreview() {
    const doiTuongGuiEl = document.querySelector('[name=doiTuongGui]:checked');
    if (!doiTuongGuiEl) return;

    const dtg  = doiTuongGuiEl.value;
    const dtEl = document.querySelector('.ss-panel:not([style*="none"]) select');
    const dtid = dtEl ? dtEl.value : '';

    const preview = document.getElementById('recipientPreview');
    preview.style.display = 'block';
    document.getElementById('previewBody').innerHTML =
        '<div class="preview-loading"><i class="fas fa-spinner fa-spin me-1"></i> Đang tải…</div>';

    let data;
    try {
        const resp = await fetch(`${window.RECIPIENTS_URL}?doiTuongGui=${dtg}&doiTuongId=${dtid}`);
        data = await resp.json();
    } catch (e) {
        document.getElementById('previewBody').innerHTML =
            '<div class="preview-loading">Không thể tải preview người nhận.</div>';
        previewCount = 0;
        updateComposeSummary();
        return;
    }

    previewCount = data.soNguoiNhan;
    document.getElementById('previewCount').textContent = previewCount;

    const body = document.getElementById('previewBody');

    if (!data.nguoiNhans.length) {
        body.innerHTML = '<div class="preview-loading">Không tìm thấy người nhận phù hợp.</div>';
        updateComposeSummary();
        return;
    }

    body.innerHTML = data.nguoiNhans.map(u => `
        <div class="preview-item">
            <div class="preview-avatar">${u.hoTen ? u.hoTen.charAt(0).toUpperCase() : '?'}</div>
            <div>
                <div class="preview-name">${u.hoTen}</div>
                <div class="preview-email">${u.email}</div>
            </div>
            <div class="preview-role">${u.role}</div>
        </div>
    `).join('');

    const more = document.getElementById('previewMore');
    if (data.soNguoiNhan > 20) {
        more.style.display = 'block';
        more.textContent   = `+${data.soNguoiNhan - 20} người nữa không hiển thị`;
    } else {
        more.style.display = 'none';
    }
    updateComposeSummary();
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Trigger preview cho đối tượng mặc định (Tất cả = 0)
    const defaultCard = document.querySelector('.doi-tuong-card.selected');
    if (defaultCard) fetchRecipientPreview();

    // Lắng nghe sub-selects
    ['sel-lop', 'sel-khoa', 'sel-canhan', 'sel-role'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', fetchRecipientPreview);
    });

    ['tieuDe', 'sel-lop', 'sel-khoa', 'sel-canhan', 'sel-role'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateComposeSummary);
    });

    document.querySelector('[name=loaiGui]')?.addEventListener('change', updateComposeSummary);
    document.querySelector('[name=uuTien]')?.addEventListener('change', updateComposeSummary);
    document.querySelector('[name=ghim]')?.addEventListener('change', updateComposeSummary);
    document.querySelectorAll('[name=doiTuongGui]').forEach(el => el.addEventListener('change', updateComposeSummary));

    updateComposeSummary();
});
