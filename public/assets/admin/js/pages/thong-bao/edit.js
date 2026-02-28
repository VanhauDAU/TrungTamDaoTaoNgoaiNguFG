/**
 * edit.js
 * JavaScript cho trang Chỉnh sửa Thông Báo (admin/thong-bao/{id}/sua)
 * Chức năng: Quill rich-text editor với preload nội dung cũ
 */

// ── Quill Editor ─────────────────────────────────────────────────────────────
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

// Preload nội dung từ server
if (noiDungHidden.value) {
    quill.root.innerHTML = noiDungHidden.value;
}

// Sync Quill → hidden textarea khi thay đổi
quill.on('text-change', () => {
    noiDungHidden.value = quill.root.innerHTML;
});
