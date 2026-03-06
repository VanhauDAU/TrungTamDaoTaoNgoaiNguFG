/**
 * edit.js
 * JavaScript cho trang Chỉnh sửa Thông Báo (admin/thong-bao/{id}/sua)
 * Chức năng: Quill editor, đồng bộ panel tóm tắt, preview file upload mới
 */

const quill = new Quill('#quillEditor', {
    theme: 'snow',
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
if (noiDungHidden.value) {
    quill.root.innerHTML = noiDungHidden.value;
}

quill.on('text-change', () => {
    noiDungHidden.value = quill.root.innerHTML;
});

let editSelectedFiles = [];

function formatSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    return (bytes / 1024).toFixed(0) + ' KB';
}

function getFileIcon(mime) {
    if (!mime) return { icon: 'fa-file', color: '#6b7280' };
    if (mime.startsWith('image/')) return { icon: 'fa-file-image', color: '#3b82f6' };
    if (mime === 'application/pdf') return { icon: 'fa-file-pdf', color: '#ef4444' };
    if (mime.includes('word') || mime.includes('doc')) return { icon: 'fa-file-word', color: '#2563eb' };
    if (mime.includes('sheet') || mime.includes('excel') || mime.includes('xls')) return { icon: 'fa-file-excel', color: '#16a34a' };
    if (mime.includes('presentation') || mime.includes('powerpoint')) return { icon: 'fa-file-powerpoint', color: '#ea580c' };
    if (mime.includes('zip') || mime.includes('rar') || mime.includes('archive')) return { icon: 'fa-file-archive', color: '#7c3aed' };
    return { icon: 'fa-file', color: '#9ca3af' };
}

function rebuildEditInput() {
    const dt = new DataTransfer();
    editSelectedFiles.forEach(f => dt.items.add(f));
    const input = document.getElementById('edit-tepDinhInput');
    if (input) input.files = dt.files;
}

function renderEditCards() {
    const list = document.getElementById('edit-file-list');
    if (!list) return;

    list.innerHTML = '';
    editSelectedFiles.forEach((file, idx) => {
        const card = document.createElement('div');
        card.className = 'nb-preview-card';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                card.innerHTML = `
                    <div class="npc-thumb">
                        <img src="${e.target.result}" alt="${file.name}">
                    </div>
                    <div class="npc-info">
                        <div class="npc-name" title="${file.name}">${file.name}</div>
                        <div class="npc-size">${formatSize(file.size)}</div>
                    </div>
                    <button type="button" class="npc-remove" onclick="removeEditFile(${idx})" title="Xóa">✕</button>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            const icon = getFileIcon(file.type);
            card.innerHTML = `
                <div class="npc-thumb npc-thumb-icon">
                    <i class="fas ${icon.icon}" style="color:${icon.color};font-size:2rem;"></i>
                </div>
                <div class="npc-info">
                    <div class="npc-name" title="${file.name}">${file.name}</div>
                    <div class="npc-size">${formatSize(file.size)}</div>
                </div>
                <button type="button" class="npc-remove" onclick="removeEditFile(${idx})" title="Xóa">✕</button>
            `;
        }

        list.appendChild(card);
    });
}

function previewEditFiles(fileList) {
    for (const file of fileList) {
        if (editSelectedFiles.length >= 5) {
            alert('Tối đa 5 file tải mới trong một lần cập nhật.');
            break;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" vượt quá 10MB.`);
            continue;
        }
        if (editSelectedFiles.some(f => f.name === file.name && f.size === file.size)) continue;
        editSelectedFiles.push(file);
    }
    rebuildEditInput();
    renderEditCards();
    updateEditSummary();
}
window.previewEditFiles = previewEditFiles;

function removeEditFile(idx) {
    editSelectedFiles.splice(idx, 1);
    rebuildEditInput();
    renderEditCards();
    updateEditSummary();
}
window.removeEditFile = removeEditFile;

function handleEditDrop(e) {
    e.preventDefault();
    const zone = document.getElementById('edit-dropzone');
    if (zone) zone.classList.remove('drag-over');
    previewEditFiles(e.dataTransfer.files);
}
window.handleEditDrop = handleEditDrop;

function updateEditSummary() {
    const title = document.getElementById('edit-tieu-de')?.value.trim() || 'Chưa nhập';
    const loai = document.getElementById('edit-loai')?.value ?? 0;
    const uuTien = document.getElementById('edit-uu-tien')?.value ?? 0;
    const ghim = document.getElementById('edit-ghim')?.checked;

    const delCount = document.querySelectorAll('.edit-delete-attachment:checked').length;
    const existingEl = document.getElementById('edit-existing-files');
    if (existingEl) {
        const total = Number(existingEl.dataset.total || existingEl.textContent || 0);
        existingEl.textContent = Math.max(total - delCount, 0);
    }

    const titleEl = document.getElementById('edit-summary-title');
    const loaiEl = document.getElementById('edit-summary-loai');
    const uuTienEl = document.getElementById('edit-summary-uu-tien');
    const pinEl = document.getElementById('edit-summary-pin');
    const newFilesEl = document.getElementById('edit-new-files');

    if (titleEl) titleEl.textContent = title;
    if (loaiEl) loaiEl.textContent = window.EDIT_LOAI_LABELS[loai] ?? '—';
    if (uuTienEl) uuTienEl.textContent = window.EDIT_UU_TIEN_LABELS[uuTien] ?? '—';
    if (pinEl) pinEl.textContent = ghim ? 'Có' : 'Không';
    if (newFilesEl) newFilesEl.textContent = editSelectedFiles.length;
}

document.addEventListener('DOMContentLoaded', () => {
    const existingEl = document.getElementById('edit-existing-files');
    if (existingEl) existingEl.dataset.total = existingEl.textContent.trim();

    document.getElementById('edit-tieu-de')?.addEventListener('input', updateEditSummary);
    document.getElementById('edit-loai')?.addEventListener('change', updateEditSummary);
    document.getElementById('edit-uu-tien')?.addEventListener('change', updateEditSummary);
    document.getElementById('edit-ghim')?.addEventListener('change', updateEditSummary);
    document.querySelectorAll('.edit-delete-attachment').forEach(el => el.addEventListener('change', updateEditSummary));

    updateEditSummary();
});
