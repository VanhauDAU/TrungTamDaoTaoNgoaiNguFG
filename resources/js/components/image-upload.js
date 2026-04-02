const ICONS = {
    error: "fas fa-times-circle",
    success: "fas fa-check-circle",
    info: "fas fa-info-circle",
};

function initAllImageUploads() {
    document.querySelectorAll("[data-upload-component]").forEach(initImageUpload);
}

function initImageUpload(root) {
    if (root.dataset.uploadReady === "true") {
        return;
    }

    root.dataset.uploadReady = "true";

    const form = root.querySelector("[data-upload-form]");
    const input = root.querySelector("[data-upload-input]");
    const preview = root.querySelector("[data-upload-preview]");
    const dropzone = root.querySelector("[data-upload-dropzone]");
    const previewShell = root.querySelector(".ui-upload__preview-shell");
    const actions = root.querySelector("[data-upload-actions]");
    const confirmBtn = root.querySelector("[data-upload-confirm]");
    const cancelBtn = root.querySelector("[data-upload-cancel]");
    const selectedFileEl = root.querySelector("[data-upload-selected]");
    const feedback = root.querySelector("[data-upload-feedback]");
    const progressWrap = root.querySelector("[data-upload-progress-wrap]");
    const progressFill = root.querySelector("[data-upload-progress-fill]");
    const progressText = root.querySelector("[data-upload-progress-text]");
    const progressPct = root.querySelector("[data-upload-progress-pct]");

    if (!form || !input || !preview || !dropzone || !previewShell || !confirmBtn || !cancelBtn) {
        return;
    }

    const allowedTypes = csv(root.dataset.allowedTypes);
    const allowedLabel = root.dataset.allowedLabel || "JPG, PNG, GIF, WebP";
    const maxSize = Number(root.dataset.maxSize || 0);
    const maxSizeLabel = root.dataset.maxSizeLabel || formatSize(maxSize);
    const syncSelector = root.dataset.syncSelector || "";
    const responseUrlKey = root.dataset.responseUrlKey || "";

    let originalUrl = preview.getAttribute("src") || "";
    let previewUrl = null;
    let selectedFile = null;
    let isUploading = false;

    const setFeedback = (message, type = "info") => {
        if (!feedback) {
            return;
        }

        feedback.innerHTML = `<i class="${ICONS[type] ?? ICONS.info}"></i><span>${escapeHtml(message)}</span>`;
        feedback.classList.remove("d-none", "is-error", "is-success", "is-info");
        feedback.classList.add(`is-${type}`);
    };

    const clearFeedback = () => {
        if (!feedback) {
            return;
        }

        feedback.innerHTML = "";
        feedback.classList.add("d-none");
        feedback.classList.remove("is-error", "is-success", "is-info");
    };

    const setProgress = (percent, label) => {
        const clamped = Math.max(0, Math.min(100, percent));

        if (progressFill) {
            progressFill.style.width = `${clamped}%`;
        }
        if (progressText) {
            progressText.textContent = label ?? `Đang tải lên: ${clamped}%`;
        }
        if (progressPct) {
            progressPct.textContent = `${clamped}%`;
        }
    };

    const resetProgress = () => {
        setProgress(0, "Đang chuẩn bị...");
        progressWrap?.classList.add("d-none");
    };

    const revokePreviewUrl = () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    };

    const resetSelection = ({ restoreOriginal = true, keepFeedback = false } = {}) => {
        revokePreviewUrl();
        selectedFile = null;
        input.value = "";
        actions?.classList.add("d-none");
        selectedFileEl?.classList.add("d-none");
        if (selectedFileEl) {
            selectedFileEl.textContent = "";
        }
        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        previewShell.classList.remove("is-preview", "is-uploading");
        dropzone.classList.remove("is-dragover");
        resetProgress();

        if (restoreOriginal && originalUrl) {
            preview.setAttribute("src", originalUrl);
        }

        if (!keepFeedback) {
            clearFeedback();
        }
    };

    const syncPreviewTargets = (url) => {
        if (!url || !syncSelector) {
            return;
        }

        document.querySelectorAll(syncSelector).forEach((element) => {
            if (element instanceof HTMLImageElement) {
                element.src = url;
            }
        });
    };

    const emit = (eventName, detail = {}) => {
        root.dispatchEvent(new CustomEvent(eventName, { detail, bubbles: true }));
    };

    const handleFile = (file) => {
        resetProgress();
        clearFeedback();

        if (!file) {
            resetSelection();
            return;
        }

        if (allowedTypes.length > 0 && file.type && !allowedTypes.includes(file.type)) {
            resetSelection({ keepFeedback: true });
            setFeedback(`Ảnh không đúng định dạng. Chỉ chấp nhận ${allowedLabel}.`, "error");
            emit("upload:error", { message: "invalid_type", file });
            return;
        }

        if (maxSize > 0 && file.size > maxSize) {
            resetSelection({ keepFeedback: true });
            setFeedback(`Ảnh vượt quá giới hạn ${maxSizeLabel}. Vui lòng chọn ảnh nhỏ hơn.`, "error");
            emit("upload:error", { message: "file_too_large", file });
            return;
        }

        revokePreviewUrl();
        selectedFile = file;
        previewUrl = URL.createObjectURL(file);
        preview.setAttribute("src", previewUrl);
        previewShell.classList.add("is-preview");
        actions?.classList.remove("d-none");

        if (selectedFileEl) {
            selectedFileEl.textContent = `${file.name} (${formatSize(file.size)})`;
            selectedFileEl.classList.remove("d-none");
        }
    };

    const resolveResponseUrl = (response) => {
        const keys = [responseUrlKey, "avatarUrl", "file.url", "location", "url"].filter(Boolean);

        for (const key of keys) {
            const value = getByPath(response, key);

            if (typeof value === "string" && value.trim() !== "") {
                return value;
            }
        }

        return "";
    };

    const submitUpload = () => {
        if (isUploading || !selectedFile) {
            return;
        }

        isUploading = true;
        confirmBtn.disabled = true;
        cancelBtn.disabled = true;
        previewShell.classList.add("is-uploading");
        progressWrap?.classList.remove("d-none");
        setProgress(0, "Đang chuẩn bị tải lên...");
        setFeedback("Đang tải ảnh lên hệ thống...", "info");

        const xhr = new XMLHttpRequest();
        xhr.open(form.method || "POST", form.action, true);
        xhr.setRequestHeader("Accept", "application/json");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        xhr.upload.addEventListener("progress", (event) => {
            if (!event.lengthComputable) {
                return;
            }

            setProgress(Math.round((event.loaded / event.total) * 100), "Đang tải lên...");
        });

        xhr.onload = () => {
            isUploading = false;
            previewShell.classList.remove("is-uploading");

            let response = null;
            try {
                response = xhr.responseText ? JSON.parse(xhr.responseText) : null;
            } catch {
                response = null;
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                const resolvedUrl = resolveResponseUrl(response);

                setProgress(100, "Hoàn tất!");

                if (resolvedUrl) {
                    originalUrl = resolvedUrl;
                    preview.setAttribute("src", resolvedUrl);
                    syncPreviewTargets(resolvedUrl);
                }

                window.setTimeout(() => {
                    resetSelection({ restoreOriginal: false, keepFeedback: true });
                    setFeedback(response?.message || "Tải ảnh lên thành công!", "success");
                    emit("upload:success", { response, resolvedUrl });
                }, 500);

                return;
            }

            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            progressWrap?.classList.add("d-none");

            if (xhr.status === 422 && response?.errors) {
                const firstError = Object.values(response.errors).flat()[0] || "Dữ liệu ảnh chưa hợp lệ.";
                setFeedback(firstError, "error");
                emit("upload:error", { response, status: xhr.status });
                return;
            }

            setFeedback(response?.message || "Không thể tải ảnh lên lúc này. Vui lòng thử lại.", "error");
            emit("upload:error", { response, status: xhr.status });
        };

        xhr.onerror = () => {
            isUploading = false;
            previewShell.classList.remove("is-uploading");
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            progressWrap?.classList.add("d-none");
            setFeedback("Kết nối bị gián đoạn. Vui lòng kiểm tra mạng và thử lại.", "error");
            emit("upload:error", { message: "network_error" });
        };

        const formData = new FormData(form);
        formData.set(input.name, selectedFile);
        xhr.send(formData);
    };

    input.addEventListener("change", (event) => {
        handleFile(event.target.files?.[0] ?? null);
    });

    dropzone.addEventListener("click", (event) => {
        event.preventDefault();

        if (!isUploading) {
            input.click();
        }
    });

    cancelBtn.addEventListener("click", () => {
        if (!isUploading) {
            resetSelection();
        }
    });

    confirmBtn.addEventListener("click", submitUpload);

    form.addEventListener("submit", (event) => {
        if (!isUploading && selectedFile) {
            event.preventDefault();
            submitUpload();
        }
    });

    dropzone.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            input.click();
        }
    });

    ["dragenter", "dragover"].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            if (!isUploading) {
                dropzone.classList.add("is-dragover");
            }
        });
    });

    ["dragleave", "dragend"].forEach((eventName) => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove("is-dragover");
        });
    });

    dropzone.addEventListener("drop", (event) => {
        event.preventDefault();
        dropzone.classList.remove("is-dragover");

        if (isUploading) {
            return;
        }

        const file = event.dataTransfer?.files?.[0] ?? null;
        handleFile(file);
    });
}

function csv(value) {
    return (value || "")
        .split(",")
        .map((item) => item.trim())
        .filter(Boolean);
}

function getByPath(target, path) {
    if (!target || !path) {
        return null;
    }

    return path.split(".").reduce((carry, key) => {
        if (carry && typeof carry === "object" && key in carry) {
            return carry[key];
        }

        return null;
    }, target);
}

function formatSize(size) {
    if (!size) {
        return "0 B";
    }

    if (size < 1024) {
        return `${size} B`;
    }

    if (size < 1048576) {
        return `${(size / 1024).toFixed(1)} KB`;
    }

    return `${(size / 1048576).toFixed(2)} MB`;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAllImageUploads, { once: true });
} else {
    initAllImageUploads();
}
