(function () {
    "use strict";

    const BS = window.CHAT_BOOTSTRAP;
    const root = document.getElementById("chat-app");
    const POLL_MS = 1500;
    const ROOM_MS = 15000;
    const MOBILE_BREAKPOINT = 991.98;
    const REACTION_EMOJIS = Array.isArray(BS?.reactionEmojis)
        ? BS.reactionEmojis
        : ["👍", "❤️", "😂", "😮", "😢", "🔥", "😡"];
    const COMPOSER_EMOJIS = Array.isArray(BS?.composerEmojis)
        ? BS.composerEmojis
        : [
              "😀",
              "😁",
              "😂",
              "🤣",
              "😊",
              "😍",
              "😘",
              "😎",
              "🤔",
              "😮",
              "😢",
              "😭",
              "😡",
              "👍",
              "👎",
              "👏",
              "🙏",
              "❤️",
              "💔",
              "🔥",
              "🎉",
              "🌟",
              "💯",
              "🤝",
              "👌",
              "🙌",
              "🥳",
              "😴",
              "🤯",
              "🤗",
          ];

    if (!BS || !root) return;

    const state = {
        rooms: Array.isArray(BS.rooms) ? BS.rooms : [],
        selectedRoom: BS.selectedRoom || null,
        messages: [],
        messageIds: new Set(),
        lastMessageId: 0,
        hasOlderMessages: false,
        loadingOlderMessages: false,
        submitting: false,
        roomQuery: "",
        roomFilter: "all",
        mobileSidebarOpen: false,
        mobileInfoOpen: false,
        messageDraft: "",
        messageSearchOpen: false,
        messageSearchQuery: "",
        messageSearchResults: [],
        messageSearchLoading: false,
        draftAttachments: [],
        messagesLoaded: false,
        unreadMarkerMessageId: null,
        highlightedMessageId: null,
        replyingTo: null,
        openMessageMenuId: null,
        openReactionPickerId: null,
        openReceiptDetailsMessageId: null,
        composerEmojiOpen: false,
        typingUsers: [],
        roomMembers: [],
        roomMembersLoading: false,
    };

    let pollTimer = null;
    let roomTimer = null;
    let polling = false;
    let messageSearchTimer = null;
    let typingStartTimer = null;
    let typingStopTimer = null;
    let typingActive = false;
    let highlightTimer = null;

    function esc(v) {
        return String(v ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function ep(tpl, id) {
        return tpl.replace("__ROOM__", id);
    }

    function isMobileViewport() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }

    function roomById(id) {
        return (
            state.rooms.find((room) => Number(room.id) === Number(id)) || null
        );
    }

    function initialsFromText(value, fallback = "CH") {
        const parts = String(value || "")
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2);

        if (!parts.length) return fallback;

        return (
            parts
                .map((part) => part.charAt(0).toUpperCase())
                .join("")
                .slice(0, 2) || fallback
        );
    }

    function roomInitials(room) {
        return initialsFromText(
            room?.name || room?.className || room?.courseName,
            "CH",
        );
    }

    function avatarInnerHtml(name, avatarUrl, fallback = "CH") {
        if (avatarUrl) {
            return `<img src="${esc(avatarUrl)}" alt="${esc(name || fallback)}" class="chat-avatar-image">`;
        }

        return esc(initialsFromText(name, fallback));
    }

    function latestMineReceiptMessageId() {
        for (let index = state.messages.length - 1; index >= 0; index -= 1) {
            const message = state.messages[index];

            if (!message || message._pending || message.isSystem) continue;

            return message.isMine ? Number(message.id) : null;
        }

        return null;
    }

    function receiptSummaryHtml(receipt, messageId) {
        if (!receipt || !receipt.statusLabel) return "";

        const isOpen =
            Number(state.openReceiptDetailsMessageId) === Number(messageId);

        return `
            <div class="chat-receipt-wrap">
                <button
                    type="button"
                    class="chat-receipt-summary${isOpen ? " is-open" : ""}"
                    data-toggle-receipt-details="${messageId}"
                    aria-label="Xem chi tiết trạng thái tin nhắn"
                >
                    <span class="chat-receipt-label">${esc(receipt.statusLabel)}</span>
                </button>
                ${receiptDetailsHtml(receipt, messageId)}
            </div>`;
    }

    function receiptDetailsSectionHtml(title, users) {
        const items = Array.isArray(users) ? users : [];

        return `
            <div class="chat-receipt-section">
                <div class="chat-receipt-section-title">${esc(title)}</div>
                ${
                    items.length
                        ? items
                              .map(
                                  (user) => `
                                    <div class="chat-receipt-user">
                                        <span class="chat-receipt-user-avatar">
                                            ${avatarInnerHtml(user.name, user.avatarUrl, "TV")}
                                        </span>
                                        <span class="chat-receipt-user-body">
                                            <strong>${esc(user.name || "Người dùng")}</strong>
                                            <span>${esc(user.atLabel || "Vừa xong")}</span>
                                        </span>
                                    </div>`,
                              )
                              .join("")
                        : `<div class="chat-receipt-empty">Chưa có</div>`
                }
            </div>`;
    }

    function receiptDetailsHtml(receipt, messageId) {
        if (
            !receipt ||
            Number(state.openReceiptDetailsMessageId) !== Number(messageId)
        ) {
            return "";
        }

        return `
            <div class="chat-receipt-popover" data-receipt-details="${messageId}">
                ${receiptDetailsSectionHtml("Đã gửi", receipt.sentBy)}
                ${receiptDetailsSectionHtml("Đã nhận", receipt.deliveredUsers)}
                ${receiptDetailsSectionHtml("Đã xem", receipt.seenUsers)}
            </div>`;
    }

    function messageOrderValue(message) {
        const numeric = Number(message?.id);
        return Number.isFinite(numeric) ? numeric : Number.MAX_SAFE_INTEGER;
    }

    function truncateText(value, limit = 120) {
        const text = String(value || "").trim();
        if (text.length <= limit) return text;
        return `${text.slice(0, limit - 1)}...`;
    }

    function typingSummaryText() {
        const users = Array.isArray(state.typingUsers) ? state.typingUsers : [];

        if (!users.length) return "";
        if (users.length === 1) return `${users[0].name} đang nhập...`;
        if (users.length === 2)
            return `${users[0].name} và ${users[1].name} đang nhập...`;

        return `${users[0].name} và ${users.length - 1} người khác đang nhập...`;
    }

    function messageSearchResultsHtml() {
        if (!state.messageSearchOpen) return "";

        const query = String(state.messageSearchQuery || "").trim();
        if (!query) return "";

        const results = Array.isArray(state.messageSearchResults)
            ? state.messageSearchResults
            : [];

        return `
            <div class="chat-search-results">
                <div class="chat-search-results-head">
                    <strong>Kết quả trong đoạn chat</strong>
                    <button type="button" class="chat-search-results-close" data-clear-message-search aria-label="Đóng tìm kiếm">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                ${
                    state.messageSearchLoading
                        ? `<div class="chat-search-results-empty">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Đang tìm tin nhắn...</span>
                           </div>`
                        : results.length
                          ? `<div class="chat-search-results-list">
                                    ${results
                                        .map(
                                            (message) => `
                                                <button
                                                    type="button"
                                                    class="chat-search-result-item"
                                                    data-jump-message="${message.id}"
                                                >
                                                    <span class="chat-search-result-top">
                                                        <strong>${esc(message.senderName || "Người dùng")}</strong>
                                                        <span>${esc(message.sentAtLabel || "")}</span>
                                                    </span>
                                                    <span class="chat-search-result-text">${esc(truncateText(message.content || (message.attachments?.length ? (message.attachments[0].isImage ? "[Ảnh đính kèm]" : "[Tệp đính kèm]") : ""), 110) || "Tin nhắn đính kèm")}</span>
                                                </button>`,
                                        )
                                        .join("")}
                             </div>`
                          : `<div class="chat-search-results-empty">
                                <i class="fas fa-magnifying-glass"></i>
                                <span>Không tìm thấy tin phù hợp với "${esc(query)}".</span>
                             </div>`
                }
            </div>`;
    }

    function nearBottom() {
        const board = document.getElementById("chat-message-board");
        return (
            !board ||
            board.scrollHeight - board.scrollTop - board.clientHeight < 80
        );
    }

    function scrollToBottom() {
        const board = document.getElementById("chat-message-board");
        if (board) board.scrollTop = board.scrollHeight;
    }

    function scrollToUnreadMarker() {
        const board = document.getElementById("chat-message-board");
        if (!board) return false;

        const divider = board.querySelector(".chat-unread-divider");
        if (divider) {
            divider.scrollIntoView({ behavior: "smooth", block: "start" });
            return true;
        }

        const unreadMessageId = firstUnreadIncomingMessageId();
        if (!unreadMessageId) return false;

        const targetMessage = board.querySelector(
            `[data-message-id="${CSS.escape(String(unreadMessageId))}"]`,
        );

        if (!targetMessage) return false;

        targetMessage.scrollIntoView({ behavior: "smooth", block: "start" });
        return true;
    }

    function resizeComposerTextarea(textarea) {
        if (!textarea) return;
        textarea.style.height = "0px";
        textarea.style.height = `${Math.min(textarea.scrollHeight, 140)}px`;
    }

    function formatFileSize(bytes) {
        const size = Number(bytes) || 0;
        if (size < 1024) return `${size} B`;
        if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
        return `${(size / (1024 * 1024)).toFixed(1)} MB`;
    }

    function isImageMime(mime) {
        return String(mime || "").startsWith("image/");
    }

    function releaseDraftAttachment(attachment) {
        if (attachment?.previewUrl?.startsWith("blob:")) {
            URL.revokeObjectURL(attachment.previewUrl);
        }
    }

    function clearDraftAttachments() {
        state.draftAttachments.forEach(releaseDraftAttachment);
        state.draftAttachments = [];
    }

    function normalizeDraftFiles(fileList) {
        return Array.from(fileList || [])
            .filter(Boolean)
            .map((file) => ({
                id: `${file.name}_${file.size}_${file.lastModified}_${Math.random().toString(36).slice(2, 8)}`,
                file,
                name: file.name,
                size: file.size,
                mime: file.type || "application/octet-stream",
                isImage: isImageMime(file.type),
                previewUrl: isImageMime(file.type)
                    ? URL.createObjectURL(file)
                    : null,
            }));
    }

    function addDraftFiles(fileList) {
        const incoming = normalizeDraftFiles(fileList);
        if (!incoming.length) return;

        const existingKeys = new Set(
            state.draftAttachments.map(
                (item) =>
                    `${item.name}_${item.size}_${item.file?.lastModified || 0}`,
            ),
        );

        incoming.forEach((item) => {
            const key = `${item.name}_${item.size}_${item.file?.lastModified || 0}`;
            if (existingKeys.has(key)) {
                releaseDraftAttachment(item);
                return;
            }

            state.draftAttachments.push(item);
            existingKeys.add(key);
        });

        renderComposer();
    }

    function removeDraftAttachment(attachmentId) {
        const index = state.draftAttachments.findIndex(
            (item) => item.id === attachmentId,
        );

        if (index < 0) return;

        releaseDraftAttachment(state.draftAttachments[index]);
        state.draftAttachments.splice(index, 1);
        renderComposer();
    }

    async function api(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;

        const response = await fetch(url, {
            headers: (() => {
                const headers = {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": BS.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                    ...(opts.headers || {}),
                };

                if (!isFormData) headers["Content-Type"] = "application/json";

                return headers;
            })(),
            ...opts,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message || "Đã có lỗi xảy ra.");
            error.status = response.status;
            error.payload = data;
            throw error;
        }

        return data;
    }

    function setUrl(roomId) {
        const nextUrl = new URL(window.location.href);
        if (roomId) nextUrl.searchParams.set("room", roomId);
        else nextUrl.searchParams.delete("room");
        window.history.replaceState({}, "", nextUrl.toString());
    }

    function notice(type, message) {
        const el = document.getElementById("chat-inline-alert");
        if (!el) return;

        if (!message) {
            el.style.display = "none";
            el.textContent = "";
            el.className = "chat-inline-alert";
            return;
        }

        el.style.display = "block";
        el.textContent = message;
        el.className = `chat-inline-alert is-${type}`;
    }

    function roomStats() {
        return {
            total: state.rooms.length,
            unread: state.rooms.filter((room) => Number(room.unreadCount) > 0)
                .length,
            active: state.rooms.filter((room) => room.canAccess).length,
        };
    }

    function roomStatusLabel(room) {
        if (!room) return "Chưa chọn phòng";
        if (room.canAccess) return "Đang hoạt động";
        if (room.canJoin) return "Sẵn sàng tham gia";
        return "Chưa mở";
    }

    function roomStatusClass(room) {
        if (!room) return "is-idle";
        if (room.canAccess) return "is-live";
        if (room.canJoin) return "is-ready";
        return "is-locked";
    }

    function roomMembershipText(room) {
        if (!room) return "Chưa tham gia";
        if (room.canAccess) return "Đã tham gia";
        if (room.canJoin) return "Có thể tham gia";
        return "Chờ kích hoạt";
    }

    function roomSendText(room) {
        if (!room) return "Chưa chọn phòng";
        if (room.canSend) return "Có thể gửi tin nhắn";
        if (room.canAccess) return "Chỉ xem tin nhắn";
        return "Chưa thể gửi";
    }

    function roomSecondaryTitle(room) {
        if (!room) return "Nhóm lớp học";

        if (room.type === "direct") {
            return (
                room.directContextClassName ||
                room.directContextLabel ||
                room.className ||
                "Đoạn chat riêng"
            );
        }

        return room.courseName || room.className || "Nhóm lớp học";
    }

    function roomMetaCaption(room) {
        if (!room) return "Chưa có thông tin";

        if (room.type === "direct") {
            return room.directContextLabel || "Đoạn chat riêng";
        }

        return room.teacherName || "Chưa có giáo viên";
    }

    function selectedRoomSubtitle(room) {
        if (!room) return "Chọn một nhóm chat để bắt đầu trao đổi với lớp học.";

        if (room.type === "direct") {
            return [
                room.directContextLabel || "Đoạn chat riêng",
                room.teacherName || null,
            ]
                .filter(Boolean)
                .join(" • ");
        }

        return [
            room.className,
            room.courseName,
            room.teacherName ? `GV: ${room.teacherName}` : null,
        ]
            .filter(Boolean)
            .join(" • ");
    }

    function roomMetaChips(room) {
        if (!room) return [];

        if (room.type === "direct") {
            return [
                room.directContextClassName || "Đoạn chat riêng",
                room.directContextCourseName || room.directContextLabel || "Kết nối lớp học",
                room.directPeerName || room.name,
            ];
        }

        return [
            room.className || "Nhóm lớp học",
            room.courseName || "Chưa gắn khóa học",
            room.teacherName || "Chưa phân công",
        ];
    }

    function findMessageById(messageId) {
        return (
            state.messages.find(
                (message) => String(message.id) === String(messageId),
            ) || null
        );
    }

    function reactionLabel(reaction) {
        const count = Number(reaction?.count) || 0;
        const label = count > 1 ? `${reaction.emoji} ${count}` : reaction.emoji;
        if (reaction?.reactedByMe) return `${label} · Bạn đã thả cảm xúc`;
        return label;
    }

    function reactionUsersText(reaction) {
        const names = Array.isArray(reaction?.userNames) ? reaction.userNames : [];
        if (!names.length) return reactionLabel(reaction);
        return `${reactionLabel(reaction)}\n${names.join(", ")}`;
    }

    function attachmentListHtml(attachments, { compact = false } = {}) {
        if (!Array.isArray(attachments) || !attachments.length) return "";

        const listClass = compact
            ? "chat-message-attachments is-compact"
            : "chat-message-attachments";

        return `
            <div class="${listClass}">
                ${attachments
                    .map((attachment) => {
                        if (attachment.isImage) {
                            return `
                                <a
                                    href="${esc(attachment.url || attachment.previewUrl || "#")}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="chat-message-attachment-image"
                                >
                                    <img src="${esc(attachment.thumbnailUrl || attachment.url || attachment.previewUrl || "")}" alt="${esc(attachment.name || "Ảnh đính kèm")}">
                                </a>`;
                        }

                        return `
                            <a
                                href="${esc(attachment.downloadUrl || attachment.url || "#")}"
                                target="_blank"
                                rel="noopener noreferrer"
                                download="${esc(attachment.name || "tep-dinh-kem")}"
                                class="chat-message-attachment-file"
                            >
                                <span class="chat-message-attachment-file-icon"><i class="fas fa-file-arrow-down"></i></span>
                                <span class="chat-message-attachment-file-body">
                                    <strong>${esc(attachment.name || "Tệp đính kèm")}</strong>
                                    <span>${esc(formatFileSize(attachment.size))}</span>
                                </span>
                            </a>`;
                    })
                    .join("")}
            </div>`;
    }

    function draftAttachmentHtml() {
        if (!state.draftAttachments.length) return "";

        return `
            <div class="chat-composer-attachments">
                ${state.draftAttachments
                    .map((attachment) => {
                        if (attachment.isImage) {
                            return `
                                <div class="chat-composer-attachment chat-composer-attachment-image">
                                    <img src="${esc(attachment.previewUrl || "")}" alt="${esc(attachment.name)}">
                                    <button
                                        type="button"
                                        class="chat-composer-attachment-remove"
                                        data-remove-draft-attachment="${esc(attachment.id)}"
                                        aria-label="Bỏ tệp ${esc(attachment.name)}"
                                    >
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>`;
                        }

                        return `
                            <div class="chat-composer-attachment">
                                <div class="chat-composer-attachment-file">
                                    <span class="chat-composer-attachment-icon"><i class="fas fa-paperclip"></i></span>
                                    <span class="chat-composer-attachment-body">
                                        <strong>${esc(attachment.name)}</strong>
                                        <span>${esc(formatFileSize(attachment.size))}</span>
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="chat-composer-attachment-remove"
                                    data-remove-draft-attachment="${esc(attachment.id)}"
                                    aria-label="Bỏ tệp ${esc(attachment.name)}"
                                >
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>`;
                    })
                    .join("")}
            </div>`;
    }

    function closeMessageMenu() {
        if (state.openMessageMenuId === null) return;
        state.openMessageMenuId = null;
        root.querySelectorAll(
            ".chat-message-menu-btn.is-open, .chat-message-menu.is-open",
        ).forEach((el) => el.classList.remove("is-open"));
    }

    function closeReactionPicker() {
        if (state.openReactionPickerId === null) return;
        state.openReactionPickerId = null;
        root.querySelectorAll(
            ".chat-message-reaction-btn.is-open, .chat-message-reaction-popover.is-open",
        ).forEach((el) => el.classList.remove("is-open"));
    }

    function toggleReactionPicker(messageId) {
        const nextId =
            state.openReactionPickerId === Number(messageId)
                ? null
                : Number(messageId);

        state.openReactionPickerId = nextId;
        closeMessageMenu();
        closeReceiptDetails();
        closeComposerEmojiPicker();

        root.querySelectorAll("[data-message-reaction-picker]").forEach(
            (popover) => {
                const isOpen =
                    Number(popover.dataset.messageReactionPicker) === nextId;
                popover.classList.toggle("is-open", isOpen);
            },
        );

        root.querySelectorAll("[data-message-reaction-btn]").forEach(
            (button) => {
                const isOpen =
                    Number(button.dataset.messageReactionBtn) === nextId;
                button.classList.toggle("is-open", isOpen);
            },
        );
    }

    function closeReceiptDetails() {
        if (state.openReceiptDetailsMessageId === null) return;
        state.openReceiptDetailsMessageId = null;
        root.querySelectorAll(
            ".chat-receipt-summary.is-open, .chat-receipt-popover.is-open",
        ).forEach((el) => el.classList.remove("is-open"));
    }

    function toggleReceiptDetails(messageId) {
        const nextId =
            state.openReceiptDetailsMessageId === Number(messageId)
                ? null
                : Number(messageId);

        state.openReceiptDetailsMessageId = nextId;
        closeMessageMenu();
        closeReactionPicker();
        closeComposerEmojiPicker();

        root.querySelectorAll("[data-toggle-receipt-details]").forEach(
            (button) => {
                button.classList.toggle(
                    "is-open",
                    Number(button.dataset.toggleReceiptDetails) === nextId,
                );
            },
        );

        root.querySelectorAll("[data-receipt-details]").forEach((popover) => {
            popover.classList.toggle(
                "is-open",
                Number(popover.dataset.receiptDetails) === nextId,
            );
        });
    }

    function closeComposerEmojiPicker() {
        if (!state.composerEmojiOpen) return;
        state.composerEmojiOpen = false;

        root.querySelectorAll(
            ".chat-composer-emoji-btn.is-open, .chat-composer-emoji-picker.is-open",
        ).forEach((el) => el.classList.remove("is-open"));
    }

    function toggleComposerEmojiPicker() {
        state.composerEmojiOpen = !state.composerEmojiOpen;
        closeMessageMenu();
        closeReactionPicker();
        closeReceiptDetails();

        root.querySelectorAll("[data-toggle-composer-emoji]").forEach(
            (button) => {
                button.classList.toggle("is-open", state.composerEmojiOpen);
            },
        );

        root.querySelectorAll(".chat-composer-emoji-picker").forEach(
            (picker) => {
                picker.classList.toggle("is-open", state.composerEmojiOpen);
            },
        );
    }

    function toggleMessageMenu(messageId) {
        const nextId =
            state.openMessageMenuId === Number(messageId)
                ? null
                : Number(messageId);
        state.openMessageMenuId = nextId;
        closeReactionPicker();
        closeReceiptDetails();
        closeComposerEmojiPicker();

        root.querySelectorAll("[data-message-menu-id]").forEach((menu) => {
            const isOpen = Number(menu.dataset.messageMenuId) === nextId;
            menu.classList.toggle("is-open", isOpen);
        });

        root.querySelectorAll("[data-message-menu-btn]").forEach((button) => {
            const isOpen = Number(button.dataset.messageMenuBtn) === nextId;
            button.classList.toggle("is-open", isOpen);
        });
    }

    function setReplyingTo(message) {
        state.replyingTo = message
            ? {
                  id: message.id,
                  senderName: message.senderName,
                  content: message.content,
                  isRecalled: Boolean(message.isRecalled),
              }
            : null;

        closeMessageMenu();
        closeReactionPicker();
        closeComposerEmojiPicker();
        renderComposer();
        document.getElementById("chat-message-input")?.focus();
    }

    function syncMessagesState(messages) {
        state.messages = [];
        state.messageIds = new Set();
        state.lastMessageId = 0;

        messages.forEach((message) => {
            state.messages.push(message);

            const id = Number(message.id);
            if (Number.isFinite(id)) {
                state.messageIds.add(id);
                if (id > state.lastMessageId) state.lastMessageId = id;
            }
        });
    }

    function replaceMessageInState(updatedMessage) {
        const index = state.messages.findIndex(
            (message) => Number(message.id) === Number(updatedMessage.id),
        );

        if (index < 0) return false;

        state.messages[index] = {
            ...state.messages[index],
            ...updatedMessage,
        };

        if (
            state.replyingTo &&
            Number(state.replyingTo.id) === Number(updatedMessage.id)
        ) {
            state.replyingTo = {
                id: updatedMessage.id,
                senderName: updatedMessage.senderName,
                content: updatedMessage.content,
                isRecalled: Boolean(updatedMessage.isRecalled),
            };
        }

        return true;
    }

    function firstUnreadIncomingMessageId() {
        const markerId = Number(state.unreadMarkerMessageId);
        if (!Number.isFinite(markerId) || markerId <= 0) return null;

        const target = state.messages.find(
            (message) =>
                !message._pending &&
                !message.isMine &&
                !message.isSystem &&
                Number(message.id) > markerId,
        );

        return target ? Number(target.id) : null;
    }

    function syncTypingUsers(users) {
        const typingUsers = Array.isArray(users) ? users : [];
        state.typingUsers = typingUsers;

        if (!state.roomMembers.length) return;

        const typingIds = new Set(
            typingUsers.map((user) => Number(user.id)).filter(Number.isFinite),
        );

        state.roomMembers = state.roomMembers.map((member) => ({
            ...member,
            isTyping: typingIds.has(Number(member.id)),
        }));
    }

    async function sendTypingState(roomId, typing) {
        if (!roomId) return;

        try {
            const data = await api(ep(BS.endpoints.typing, roomId), {
                method: "POST",
                body: JSON.stringify({ typing }),
            });

            if (
                state.selectedRoom &&
                Number(state.selectedRoom.id) === Number(roomId)
            ) {
                syncTypingUsers(data.typingUsers);
                renderMainHeader();
                updateComposerTypingNote();
                renderInfoPanel();
            }
        } catch (_) {}
    }

    function stopTyping(roomId = state.selectedRoom?.id) {
        clearTimeout(typingStartTimer);
        clearTimeout(typingStopTimer);

        if (!typingActive || !roomId) {
            typingActive = false;
            return;
        }

        typingActive = false;
        sendTypingState(roomId, false);
    }

    function scheduleTypingHeartbeat() {
        if (
            !state.selectedRoom ||
            !state.selectedRoom.canAccess ||
            !state.selectedRoom.canSend
        ) {
            return;
        }

        const roomId = Number(state.selectedRoom.id);
        clearTimeout(typingStartTimer);
        clearTimeout(typingStopTimer);

        typingStartTimer = setTimeout(() => {
            if (!typingActive) {
                typingActive = true;
                sendTypingState(roomId, true);
            }
        }, 180);

        typingStopTimer = setTimeout(() => {
            stopTyping(roomId);
        }, 2400);
    }

    function clearMessageSearch() {
        state.messageSearchOpen = false;
        state.messageSearchQuery = "";
        state.messageSearchResults = [];
        state.messageSearchLoading = false;
        clearTimeout(messageSearchTimer);
        renderMainHeader();
    }

    function toggleMessageSearch(forceOpen = null) {
        const nextOpen =
            typeof forceOpen === "boolean"
                ? forceOpen
                : !state.messageSearchOpen;

        if (!nextOpen) {
            clearMessageSearch();
            return;
        }

        state.messageSearchOpen = true;
        renderMainHeader();
        document.getElementById("chat-message-search")?.focus();
    }

    async function performMessageSearch(query) {
        const keyword = String(query || "").trim();
        state.messageSearchQuery = keyword;

        clearTimeout(messageSearchTimer);

        if (!state.selectedRoom || !state.selectedRoom.canAccess || keyword.length < 2) {
            state.messageSearchResults = [];
            state.messageSearchLoading = false;
            renderMainHeader();
            return;
        }

        state.messageSearchLoading = true;
        renderMainHeader();

        const roomId = Number(state.selectedRoom.id);
        messageSearchTimer = setTimeout(async () => {
            try {
                const url = new URL(
                    ep(BS.endpoints.search, roomId),
                    window.location.origin,
                );
                url.searchParams.set("q", keyword);

                const data = await api(url.toString(), {
                    headers: { "Cache-Control": "no-cache" },
                });

                if (
                    !state.selectedRoom ||
                    Number(state.selectedRoom.id) !== roomId ||
                    state.messageSearchQuery !== keyword
                ) {
                    return;
                }

                state.messageSearchResults = Array.isArray(data.matches)
                    ? data.matches
                    : [];
            } catch (_) {
                if (state.messageSearchQuery === keyword) {
                    state.messageSearchResults = [];
                }
            } finally {
                if (state.messageSearchQuery === keyword) {
                    state.messageSearchLoading = false;
                    renderMainHeader();
                }
            }
        }, 220);
    }

    function highlightMessage(messageId) {
        const row = root.querySelector(
            `[data-message-id="${CSS.escape(String(messageId))}"]`,
        );
        if (!row) return false;

        clearTimeout(highlightTimer);
        root.querySelectorAll(".chat-message-row.is-highlighted").forEach((el) => {
            el.classList.remove("is-highlighted");
        });

        row.classList.add("is-highlighted");
        row.scrollIntoView({ behavior: "smooth", block: "center" });
        highlightTimer = setTimeout(() => {
            row.classList.remove("is-highlighted");
        }, 2200);

        return true;
    }

    async function jumpToMessage(messageId) {
        if (!messageId || !state.selectedRoom || !state.selectedRoom.canAccess) {
            return;
        }

        let found = highlightMessage(messageId);

        while (!found && state.hasOlderMessages) {
            const previousCount = state.messages.length;
            await loadOlderMessages();
            found = highlightMessage(messageId);

            if (state.messages.length === previousCount && !state.hasOlderMessages) {
                break;
            }
        }

        if (!found) {
            notice("error", "Không tìm thấy tin nhắn gốc trong lịch sử hiện có.");
        }
    }

    async function loadRoomMembers(roomId) {
        if (!roomId) {
            state.roomMembers = [];
            state.roomMembersLoading = false;
            renderInfoPanel();
            return [];
        }

        state.roomMembersLoading = true;
        renderInfoPanel();

        try {
            const data = await api(ep(BS.endpoints.members, roomId), {
                headers: { "Cache-Control": "no-cache" },
            });

            if (
                !state.selectedRoom ||
                Number(state.selectedRoom.id) !== Number(roomId)
            ) {
                return [];
            }

            state.roomMembers = Array.isArray(data.members) ? data.members : [];
            syncTypingUsers(state.typingUsers);
            return state.roomMembers;
        } finally {
            state.roomMembersLoading = false;
            renderInfoPanel();
        }
    }

    function syncRoomInList(updated) {
        if (!updated) return;

        const index = state.rooms.findIndex(
            (room) => Number(room.id) === Number(updated.id),
        );

        if (index >= 0)
            state.rooms[index] = { ...state.rooms[index], ...updated };
        else state.rooms.unshift(updated);

        if (
            state.selectedRoom &&
            Number(state.selectedRoom.id) === Number(updated.id)
        ) {
            state.selectedRoom = { ...state.selectedRoom, ...updated };
        }
    }

    function filteredRooms() {
        const query = state.roomQuery.trim().toLowerCase();

        return state.rooms.filter((room) => {
            if (state.roomFilter === "unread" && Number(room.unreadCount) <= 0)
                return false;
            if (state.roomFilter === "active" && !room.canAccess) return false;

            if (!query) return true;

            return [
                room.name,
                room.className,
                room.courseName,
                room.teacherName,
                room.lastMessagePreview,
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase()
                .includes(query);
        });
    }

    function messageSnapshot() {
        const committedMessages = state.messages.filter(
            (message) => !message._pending,
        );
        const mine = committedMessages.filter(
            (message) => message.isMine,
        ).length;
        const others = committedMessages.length - mine;
        const lastMessage = committedMessages.at(-1) || null;

        return {
            totalLoaded: committedMessages.length,
            mine,
            others,
            lastMessage,
            preview:
                truncateText(
                    lastMessage?.content ||
                        (Array.isArray(lastMessage?.attachments) &&
                        lastMessage.attachments.length
                            ? lastMessage.attachments[0]?.isImage
                                ? "[Ảnh đính kèm]"
                                : "[Tệp đính kèm]"
                            : "") ||
                        state.selectedRoom?.lastMessagePreview ||
                        "Chưa có tin nhắn",
                    130,
                ) || "Chưa có tin nhắn",
        };
    }

    function renderSidebarMeta() {
        const stats = roomStats();

        const totalEl = document.getElementById("chat-filter-total");
        const unreadEl = document.getElementById("chat-filter-unread");
        const activeEl = document.getElementById("chat-filter-active");
        const summaryTotal = document.getElementById("chat-summary-total");
        const summaryUnread = document.getElementById("chat-summary-unread");
        const summaryActive = document.getElementById("chat-summary-active");

        if (totalEl) totalEl.textContent = stats.total;
        if (unreadEl) unreadEl.textContent = stats.unread;
        if (activeEl) activeEl.textContent = stats.active;
        if (summaryTotal) summaryTotal.textContent = stats.total;
        if (summaryUnread) summaryUnread.textContent = stats.unread;
        if (summaryActive) summaryActive.textContent = stats.active;

        root.querySelectorAll("[data-room-filter]").forEach((button) => {
            button.classList.toggle(
                "is-active",
                button.dataset.roomFilter === state.roomFilter,
            );
        });
    }

    function renderRoomList() {
        const listWrap = root.querySelector(".chat-rooms-wrap");
        if (!listWrap) return;

        renderSidebarMeta();

        const rooms = filteredRooms();

        if (!state.rooms.length) {
            listWrap.innerHTML = `
                <div class="chat-room-empty">
                    <i class="fas fa-comments"></i>
                    <h4>Chưa có phòng chat</h4>
                    <p>Bạn chưa có lớp học phù hợp để tham gia trao đổi.</p>
                </div>`;
            return;
        }

        if (!rooms.length) {
            listWrap.innerHTML = `
                <div class="chat-room-empty">
                    <i class="fas fa-search"></i>
                    <h4>Không tìm thấy kết quả</h4>
                    <p>Thử tìm theo tên lớp, khóa học hoặc giáo viên.</p>
                </div>`;
            return;
        }

        listWrap.innerHTML = `
            <div class="chat-room-list">
                ${rooms
                    .map((room) => {
                        const isActive =
                            state.selectedRoom &&
                            Number(state.selectedRoom.id) === Number(room.id);

                        return `
                            <button type="button" class="chat-room-item ${isActive ? "is-active" : ""}" data-room-id="${room.id}">
                                <div class="chat-room-row">
                                    <div class="chat-room-avatar-wrap">
                                        <div class="chat-room-avatar">${avatarInnerHtml(room.name, room.avatarUrl, "CH")}</div>
                                        <span class="chat-room-dot ${room.canAccess ? "is-live" : ""}"></span>
                                    </div>
                                    <div class="chat-room-content">
                                        <div class="chat-room-top">
                                            <div class="chat-room-name">${esc(room.name)}</div>
                                            ${
                                                Number(room.unreadCount) > 0
                                                    ? `<span class="chat-room-badge">${room.unreadCount}</span>`
                                                    : `<span class="chat-room-time">${esc(room.lastMessageAtLabel || "Mới")}</span>`
                                            }
                                        </div>
                                        <div class="chat-room-course">${esc(roomSecondaryTitle(room))}</div>
                                        <div class="chat-room-preview">${esc(room.lastMessagePreview || "Chưa có tin nhắn")}</div>
                                        <div class="chat-room-meta">
                                            <span>${esc(roomMetaCaption(room))}</span>
                                            <span class="chat-room-state ${roomStatusClass(room)}">
                                                <i class="fas ${room.canAccess ? "fa-circle-check" : room.canJoin ? "fa-user-plus" : "fa-lock"}"></i>
                                                ${esc(roomMembershipText(room))}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>`;
                    })
                    .join("")}
            </div>`;
    }

    function renderMainHeader() {
        const header = document.getElementById("chat-main-header");
        if (!header) return;

        const room = state.selectedRoom;
        const chips = roomMetaChips(room);
        const typingText = typingSummaryText();
        const activeElement = document.activeElement;
        const searchWasFocused = activeElement?.id === "chat-message-search";
        const searchSelectionStart = searchWasFocused
            ? activeElement.selectionStart
            : null;
        const searchSelectionEnd = searchWasFocused
            ? activeElement.selectionEnd
            : null;

        header.innerHTML = `
            <div class="chat-main-header-row">
                <div class="chat-main-primary">
                    <button type="button" class="chat-mobile-rooms-btn" data-toggle-rooms aria-label="Mở danh sách phòng">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="chat-main-avatar-wrap">
                        <div class="chat-main-avatar">${room ? avatarInnerHtml(room.name, room.avatarUrl, "CH") : "CH"}</div>
                        <span class="chat-main-avatar-dot ${room && room.canAccess ? "is-live" : ""}"></span>
                    </div>
                    <div class="chat-main-summary">
                        <div class="chat-main-title-row">
                            <h3 class="chat-main-title">${esc(room?.name || "Chat lớp học")}</h3>
                            ${
                                room
                                    ? `<span class="chat-main-status ${roomStatusClass(room)}">${esc(roomStatusLabel(room))}</span>`
                                    : ""
                            }
                        </div>
                        <p class="chat-main-subtitle">${esc(selectedRoomSubtitle(room))}</p>
                        ${
                            chips.length
                                ? `<div class="chat-main-tags">
                                        ${chips
                                            .map(
                                                (chip) =>
                                                    `<span class="chat-main-pill">${esc(chip)}</span>`,
                                            )
                                            .join("")}
                                   </div>`
                                : ""
                        }
                    </div>
                </div>
                <div class="chat-main-actions">
                    <button type="button" class="chat-header-icon ${state.messageSearchOpen ? "is-active" : ""}" data-toggle-message-search title="Tìm kiếm trong đoạn chat">
                        <i class="fas fa-magnifying-glass"></i>
                    </button>
                    <button type="button" class="chat-header-icon ${state.mobileInfoOpen ? "is-active" : ""}" data-toggle-info title="Thông tin đoạn chat">
                        <i class="fas fa-circle-info"></i>
                    </button>
                </div>
            </div>
            ${
                room && room.canAccess
                    ? `
                       ${
                           state.messageSearchOpen
                               ? `<div class="chat-search-panel">
                                        <label class="chat-main-search" aria-label="Tìm kiếm trong đoạn chat">
                                            <i class="fas fa-magnifying-glass"></i>
                                            <input
                                                id="chat-message-search"
                                                type="search"
                                                placeholder="Tìm theo nội dung, người gửi hoặc tên tệp"
                                                value="${esc(state.messageSearchQuery)}"
                                            >
                                            ${
                                                state.messageSearchQuery
                                                    ? `<button type="button" class="chat-main-search-clear" data-clear-message-search aria-label="Xóa tìm kiếm">
                                                            <i class="fas fa-xmark"></i>
                                                       </button>`
                                                    : ""
                                            }
                                        </label>
                                  </div>`
                               : ""
                       }
                       ${messageSearchResultsHtml()}`
                    : ""
            }`;

        if (searchWasFocused && state.messageSearchOpen) {
            const nextSearch = document.getElementById("chat-message-search");
            if (nextSearch) {
                nextSearch.focus();
                if (
                    Number.isInteger(searchSelectionStart) &&
                    Number.isInteger(searchSelectionEnd)
                ) {
                    nextSearch.setSelectionRange(
                        searchSelectionStart,
                        searchSelectionEnd,
                    );
                }
            }
        }
    }

    function buildMessageEl(message) {
        const wrap = document.createElement("div");
        wrap.className = `chat-message-row${message.isMine ? " is-mine" : ""}${message.isSystem ? " is-system" : ""}${Number(state.highlightedMessageId) === Number(message.id) ? " is-highlighted" : ""}`;
        if (message._pending) wrap.dataset.pending = message.id;
        if (!message._pending) wrap.dataset.messageId = message.id;

        if (message.isSystem) {
            wrap.innerHTML = `
                <div class="chat-system-message">
                    <i class="fas fa-circle-info"></i>
                    <span>${esc(message.content || "Tin nhắn hệ thống")}</span>
                </div>`;

            return wrap;
        }

        const replyHtml = message.replyTo
            ? `
                <button
                    type="button"
                    class="chat-reply-box${message.replyTo.isRecalled ? " is-recalled" : ""}"
                    data-jump-message="${message.replyTo.id}"
                >
                    <div><strong>${esc(message.replyTo.senderName)}</strong></div>
                    <div>${esc(message.replyTo.content)}</div>
                </button>`
            : "";
        const attachmentsHtml = attachmentListHtml(message.attachments);

        const canShowMenu = !message._pending;
        const isMenuOpen =
            canShowMenu &&
            Number(state.openMessageMenuId) === Number(message.id);
        const canReact = canShowMenu && !message.isRecalled;
        const isReactionPickerOpen =
            canReact &&
            Number(state.openReactionPickerId) === Number(message.id);
        const latestReceiptId = latestMineReceiptMessageId();
        const shouldShowReceipt =
            message.isMine &&
            !message._pending &&
            Number(message.id) === Number(latestReceiptId);
        const reactionsHtml =
            Array.isArray(message.reactions) && message.reactions.length
                ? `<div class="chat-message-reactions">
                        ${message.reactions
                            .map(
                                (reaction) => `
                                    <button
                                        type="button"
                                        class="chat-message-reaction-pill${reaction.reactedByMe ? " is-active" : ""}"
                                        data-toggle-reaction="${message.id}"
                                        data-emoji="${esc(reaction.emoji)}"
                                        title="${esc(reactionUsersText(reaction))}"
                                        aria-label="${esc(reactionUsersText(reaction))}"
                                    >
                                        <span class="chat-message-reaction-pill-emoji">${esc(reaction.emoji)}</span>
                                        <span class="chat-message-reaction-pill-count">${esc(reaction.count)}</span>
                                    </button>`,
                            )
                            .join("")}
                   </div>`
                : "";
        const menuHtml = canShowMenu
            ? `
                <div class="chat-message-tools">
                    ${
                        canReact
                            ? `<div class="chat-message-reaction-wrap">
                                    <button
                                        type="button"
                                        class="chat-message-reaction-btn ${isReactionPickerOpen ? "is-open" : ""}"
                                        data-message-reaction-btn="${message.id}"
                                        aria-label="Thả cảm xúc"
                                    >
                                        <i class="fas fa-smile"></i>
                                    </button>
                                    <div
                                        class="chat-message-reaction-popover ${isReactionPickerOpen ? "is-open" : ""}"
                                        data-message-reaction-picker="${message.id}"
                                    >
                                        ${REACTION_EMOJIS.map(
                                            (emoji) => `
                                                <button
                                                    type="button"
                                                    class="chat-reaction-emoji-btn"
                                                    data-picker-reaction="${message.id}"
                                                    data-emoji="${esc(emoji)}"
                                                    aria-label="Thả cảm xúc ${esc(emoji)}"
                                                >
                                                    ${esc(emoji)}
                                                </button>`,
                                        ).join("")}
                                    </div>
                               </div>`
                            : ""
                    }
                    <button
                        type="button"
                        class="chat-message-menu-btn ${isMenuOpen ? "is-open" : ""}"
                        data-message-menu-btn="${message.id}"
                        aria-label="Tùy chọn tin nhắn"
                    >
                        <i class="fas fa-ellipsis"></i>
                    </button>
                    <div class="chat-message-menu ${isMenuOpen ? "is-open" : ""}" data-message-menu-id="${message.id}">
                        <button type="button" class="chat-message-menu-item" data-message-action="reply" data-message-id="${message.id}">
                            <i class="fas fa-reply"></i>
                            <span>Trả lời</span>
                        </button>
                        ${
                            message.canDeleteForMe
                                ? `<button type="button" class="chat-message-menu-item" data-message-action="delete-for-me" data-message-id="${message.id}">
                                        <i class="fas fa-eye-slash"></i>
                                        <span>Xóa phía tôi</span>
                                   </button>`
                                : ""
                        }
                        ${
                            message.canRecall
                                ? `<button type="button" class="chat-message-menu-item is-danger" data-message-action="recall" data-message-id="${message.id}">
                                        <i class="fas fa-rotate-left"></i>
                                        <span>Thu hồi</span>
                                   </button>`
                                : ""
                        }
                    </div>
                </div>`
            : "";

        wrap.innerHTML = `
            ${
                message.isMine
                    ? ""
                    : `<div class="chat-message-avatar-small">${avatarInnerHtml(message.senderName, message.senderAvatarUrl, "HV")}</div>`
            }
            <div class="chat-message-stack">
                ${message.isMine ? "" : `<div class="chat-message-sender">${esc(message.senderName)}</div>`}
                <div class="chat-message-bubble-wrap">
                    <div class="chat-message-bubble${message._pending ? " is-pending" : ""}${message.isRecalled ? " is-recalled" : ""}">
                        ${replyHtml}
                        ${attachmentsHtml}
                        ${
                            String(message.content || "").trim()
                                ? `<div class="chat-message-text">${esc(message.content)}</div>`
                                : ""
                        }
                        <div class="chat-message-meta-row">
                            <div class="chat-message-time">${message._pending ? "Đang gửi..." : esc(message.sentAtLabel || "")}</div>
                        </div>
                    </div>
                    ${menuHtml}
                </div>
                ${
                    shouldShowReceipt
                        ? `<div class="chat-message-receipt-line">${receiptSummaryHtml(message.receipt, message.id)}</div>`
                        : ""
                }
                ${reactionsHtml}
            </div>`;

        return wrap;
    }

    function renderMessageBoard() {
        const board = document.getElementById("chat-message-board");
        if (!board) return;

        board.className = "chat-message-board";

        if (!state.selectedRoom) {
            board.innerHTML = `
                <div class="chat-message-empty">
                    <i class="fas fa-comments"></i>
                    <h4>Chọn một phòng chat</h4>
                    <p>Danh sách nhóm lớp học của bạn sẽ hiển thị ở cột bên trái.</p>
                </div>`;
            return;
        }

        if (!state.selectedRoom.canAccess) {
            board.innerHTML = state.selectedRoom.canJoin
                ? `
                    <div class="chat-join-box">
                        <i class="fas fa-user-plus"></i>
                        <h4>Tham gia nhóm chat lớp</h4>
                        <p>Bạn có thể vào nhóm <strong>${esc(state.selectedRoom.name)}</strong> để nhận thông báo và trao đổi với lớp học.</p>
                        <form id="chat-join-form" class="chat-join-form">
                            <button type="submit" class="chat-join-btn">Tham gia ngay</button>
                        </form>
                    </div>`
                : `
                    <div class="chat-join-box">
                        <i class="fas fa-comments-slash"></i>
                        <h4>Phòng chat chưa mở</h4>
                        <p>Bạn chưa thể truy cập nhóm <strong>${esc(state.selectedRoom.name)}</strong> ở giai đoạn hiện tại.</p>
                    </div>`;
            return;
        }

        if (!state.messagesLoaded) {
            board.innerHTML = `
                <div class="chat-message-empty">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h4>Đang tải tin nhắn...</h4>
                    <p>Hệ thống đang đồng bộ đoạn hội thoại mới nhất.</p>
                </div>`;
            return;
        }

        if (!state.messages.length) {
            board.innerHTML = `
                <div class="chat-message-empty">
                    <i class="fas fa-paper-plane"></i>
                    <h4>Chưa có tin nhắn</h4>
                    <p>Hãy gửi tin nhắn đầu tiên để bắt đầu trao đổi trong lớp học.</p>
                </div>`;
            return;
        }

        board.classList.add("has-messages");
        const unreadMessageId = firstUnreadIncomingMessageId();
        board.innerHTML = `
            ${
                state.hasOlderMessages
                    ? `<div class="chat-history-status${state.loadingOlderMessages ? " is-loading" : ""}">
                            <i class="fas ${state.loadingOlderMessages ? "fa-spinner fa-spin" : "fa-clock-rotate-left"}"></i>
                            <span>${state.loadingOlderMessages ? "Đang tải thêm tin nhắn..." : "Cuộn lên để xem lịch sử cũ hơn"}</span>
                       </div>`
                    : ""
            }
            <div class="chat-message-list">
                ${state.messages
                    .map((message) => {
                        const divider =
                            unreadMessageId &&
                            Number(message.id) === Number(unreadMessageId)
                                ? `<div class="chat-unread-divider">
                                        <span>Tin chưa đọc</span>
                                   </div>`
                                : "";

                        return `${divider}${buildMessageEl(message).outerHTML}`;
                    })
                    .join("")}
            </div>`;

        syncHistoryStatus();
    }

    function syncHistoryStatus() {
        const status = root.querySelector(".chat-history-status");
        if (!status) return;

        status.classList.toggle("is-loading", state.loadingOlderMessages);
        status.innerHTML = `
            <i class="fas ${state.loadingOlderMessages ? "fa-spinner fa-spin" : "fa-clock-rotate-left"}"></i>
            <span>${state.loadingOlderMessages ? "Đang tải thêm tin nhắn..." : "Cuộn lên để xem lịch sử cũ hơn"}</span>`;
    }

    function renderInfoPanel() {
        const panel = document.getElementById("chat-info-panel");
        if (!panel) return;

        const room = state.selectedRoom;

        if (!room) {
            panel.innerHTML = `
                <div class="chat-info-empty">
                    <i class="fas fa-circle-info"></i>
                    <h4>Thông tin đoạn chat</h4>
                    <p>Chọn một phòng chat để xem chi tiết lớp học, trạng thái tham gia và hoạt động gần đây.</p>
                </div>`;
            return;
        }

        const snapshot = messageSnapshot();
        const membersHtml = state.roomMembersLoading
            ? `<div class="chat-members-empty">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Đang tải thành viên đoạn chat...</p>
               </div>`
            : state.roomMembers.length
              ? `<div class="chat-members-list">
                    ${state.roomMembers
                        .map(
                            (member) => `
                                <button
                                    type="button"
                                    class="chat-member-item${member.isMe ? " is-me" : ""}"
                                    data-open-direct="${member.id}"
                                    ${member.canDirect ? "" : "disabled"}
                                >
                                    <span class="chat-member-avatar">${avatarInnerHtml(member.name, member.avatarUrl, member.initials || "TV")}</span>
                                    <span class="chat-member-body">
                                        <strong>${esc(member.name)}</strong>
                                        <span class="chat-member-meta">
                                            <span>${esc(member.isMe ? "Bạn" : member.roleLabel || "Thành viên")}</span>
                                            <span class="chat-member-status ${member.isOnline ? "is-online" : ""}${member.isTyping ? " is-typing" : ""}">
                                                <i class="fas fa-circle"></i>
                                                ${esc(member.isTyping ? "Đang nhập..." : member.presenceLabel || "Chưa hoạt động gần đây")}
                                            </span>
                                        </span>
                                    </span>
                                    <span class="chat-member-action">
                                        ${
                                            member.isMe
                                                ? "Bạn"
                                                : member.canDirect
                                                  ? "Nhắn riêng"
                                                  : "Không khả dụng"
                                        }
                                    </span>
                                </button>`,
                        )
                        .join("")}
                 </div>`
              : `<div class="chat-members-empty">
                    <i class="fas fa-users"></i>
                    <p>Chưa có thành viên khả dụng trong đoạn chat này.</p>
                 </div>`;

        panel.innerHTML = `
            <div class="chat-info-hero">
                <div class="chat-info-avatar-wrap">
                    <div class="chat-info-avatar">${avatarInnerHtml(room.name, room.avatarUrl, "CH")}</div>
                    <span class="chat-info-avatar-dot ${room.canAccess ? "is-live" : ""}"></span>
                </div>
                <h4 class="chat-info-title">${esc(room.name)}</h4>
                <p class="chat-info-subtitle">${esc(selectedRoomSubtitle(room))}</p>
                <span class="chat-main-status ${roomStatusClass(room)}">${esc(roomStatusLabel(room))}</span>
            </div>

            <div class="chat-info-section">
                <div class="chat-info-section-head">
                    <h5>Thành viên đoạn chat</h5>
                </div>
                ${membersHtml}
            </div>

            <div class="chat-info-section">
                <div class="chat-info-section-head">
                    <h5>Tùy chọn nhanh</h5>
                </div>
                <div class="chat-info-actions">
                    <button type="button" class="chat-info-action-btn" disabled>
                        <i class="fas fa-image"></i>
                        <span>Tập tin</span>
                    </button>
                    <button type="button" class="chat-info-action-btn" disabled>
                        <i class="fas fa-bell-slash"></i>
                        <span>Thông báo</span>
                    </button>
                    <button type="button" class="chat-info-action-btn" data-focus-message-search>
                        <i class="fas fa-magnifying-glass"></i>
                        <span>Tìm kiếm</span>
                    </button>
                </div>
            </div>

            <div class="chat-info-section">
                <div class="chat-info-section-head">
                    <h5>Tổng quan hội thoại</h5>
                </div>
                <div class="chat-info-highlight">
                    <span class="chat-info-highlight-label">Xem nhanh tin nhắn gần nhất</span>
                    <p>${esc(snapshot.preview)}</p>
                </div>
                <div class="chat-info-mini-grid">
                    <div class="chat-info-mini-card">
                        <strong>${snapshot.totalLoaded}</strong>
                        <span>Tin đã tải</span>
                    </div>
                    <div class="chat-info-mini-card">
                        <strong>${snapshot.mine}</strong>
                        <span>Tin của bạn</span>
                    </div>
                    <div class="chat-info-mini-card">
                        <strong>${snapshot.others}</strong>
                        <span>Từ thành viên khác</span>
                    </div>
                </div>
            </div>

            <div class="chat-info-section">
                <div class="chat-info-section-head">
                    <h5>Gợi ý sử dụng</h5>
                </div>
                <ul class="chat-info-tips">
                    <li>Danh sách phòng và đoạn chat đều cuộn trong khung riêng, trang sẽ không cuộn bên ngoài.</li>
                    <li>Bộ lọc bên trái giúp tách nhanh các đoạn chat chưa đọc và đã tham gia.</li>
                </ul>
            </div>`;
    }

    function renderComposer() {
        const composerWrap = root.querySelector(".chat-composer-wrap");
        if (!composerWrap) return;
        const activeElement = document.activeElement;
        const inputWasFocused = activeElement?.id === "chat-message-input";
        const inputSelectionStart = inputWasFocused
            ? activeElement.selectionStart
            : null;
        const inputSelectionEnd = inputWasFocused
            ? activeElement.selectionEnd
            : null;

        if (!state.selectedRoom || !state.selectedRoom.canAccess) {
            composerWrap.innerHTML = "";
            return;
        }

        if (!state.selectedRoom.canSend) {
            composerWrap.innerHTML = `
                <div class="chat-composer-disabled">
                    <i class="fas fa-lock"></i>
                    <span>Bạn hiện không thể gửi tin nhắn trong nhóm chat này.</span>
                </div>`;
            return;
        }

        composerWrap.innerHTML = `
            <div class="chat-composer">
                <form id="chat-send-form" class="chat-composer-form">
                    <input
                        id="chat-attachment-input"
                        type="file"
                        class="chat-composer-file-input"
                        data-chat-attachment-input
                        multiple
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,image/*"
                    >
                    <div class="chat-composer-tools">
                        <button type="button" class="chat-tool-btn" data-open-file-picker title="Đính kèm tệp">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="button" class="chat-tool-btn" data-open-file-picker title="Chọn ảnh hoặc tệp">
                            <i class="fas fa-image"></i>
                        </button>
                    </div>
                    <div class="chat-composer-field">
                        ${
                            state.replyingTo
                                ? `<div class="chat-composer-reply">
                                        <div class="chat-composer-reply-body">
                                            <span class="chat-composer-reply-label">Đang trả lời ${esc(state.replyingTo.senderName)}</span>
                                            <p>${esc(state.replyingTo.content)}</p>
                                        </div>
                                        <button type="button" class="chat-composer-reply-close" data-cancel-reply aria-label="Hủy trả lời">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                   </div>`
                                : ""
                        }
                        ${draftAttachmentHtml()}
                        <div class="chat-composer-input-shell">
                            <textarea id="chat-message-input" placeholder="Nhập tin nhắn cho lớp học của bạn...">${esc(state.messageDraft)}</textarea>
                            <div class="chat-composer-emoji">
                                <button
                                    type="button"
                                    class="chat-composer-emoji-btn ${state.composerEmojiOpen ? "is-open" : ""}"
                                    data-toggle-composer-emoji
                                    aria-label="Thêm cảm xúc"
                                >
                                    <i class="fas fa-smile"></i>
                                </button>
                                <div class="chat-composer-emoji-picker ${state.composerEmojiOpen ? "is-open" : ""}">
                                    ${COMPOSER_EMOJIS.map(
                                        (emoji) => `
                                            <button
                                                type="button"
                                                class="chat-reaction-emoji-btn"
                                                data-composer-emoji="${esc(emoji)}"
                                                aria-label="Chèn cảm xúc ${esc(emoji)}"
                                            >
                                                ${esc(emoji)}
                                            </button>`,
                                    ).join("")}
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="chat-send-btn" ${state.submitting ? "disabled" : ""}>
                        <i class="fas fa-paper-plane"></i>
                        <span>${state.submitting ? "Đang gửi" : "Gửi"}</span>
                    </button>
                </form>
            </div>`;

        const nextInput = document.getElementById("chat-message-input");
        resizeComposerTextarea(nextInput);

        if (inputWasFocused && nextInput) {
            nextInput.focus();
            if (
                Number.isInteger(inputSelectionStart) &&
                Number.isInteger(inputSelectionEnd)
            ) {
                nextInput.setSelectionRange(
                    inputSelectionStart,
                    inputSelectionEnd,
                );
            }
        }
    }

    function updateComposerTypingNote() {
        const note = root.querySelector(".chat-composer-note");
        if (!note) return;
        note.textContent =
            typingSummaryText() ||
            "Enter để gửi nhanh, Shift + Enter để xuống dòng.";
    }

    function updateMobilePanels() {
        const sidebar = root.querySelector(".chat-sidebar");
        const infoPanel = root.querySelector(".chat-info-panel");
        const headerInfoButton = root.querySelector("[data-toggle-info]");

        if (!isMobileViewport()) {
            state.mobileSidebarOpen = false;
            state.mobileInfoOpen = false;
        }

        sidebar?.classList.toggle("is-open", state.mobileSidebarOpen);
        infoPanel?.classList.toggle("is-open", state.mobileInfoOpen);
        headerInfoButton?.classList.toggle("is-active", state.mobileInfoOpen);

        const layout = root.querySelector(".chat-layout");
        const existingBackdrop = root.querySelector(".chat-panel-backdrop");
        const shouldShowBackdrop =
            isMobileViewport() &&
            (state.mobileSidebarOpen || state.mobileInfoOpen);

        if (shouldShowBackdrop && !existingBackdrop && layout) {
            const backdrop = document.createElement("button");
            backdrop.type = "button";
            backdrop.className = "chat-panel-backdrop";
            backdrop.dataset.closePanels = "1";
            layout.appendChild(backdrop);
        } else if (!shouldShowBackdrop && existingBackdrop) {
            existingBackdrop.remove();
        }
    }

    function setMobilePanel(panel, open) {
        if (!isMobileViewport()) return;

        if (panel === "rooms") {
            state.mobileSidebarOpen = open;
            if (open) state.mobileInfoOpen = false;
        }

        if (panel === "info") {
            state.mobileInfoOpen = open;
            if (open) state.mobileSidebarOpen = false;
        }

        updateMobilePanels();
    }

    function closeMobilePanels() {
        state.mobileSidebarOpen = false;
        state.mobileInfoOpen = false;
        updateMobilePanels();
    }

    function renderApp() {
        root.innerHTML = `
            <div class="chat-layout">
                <aside class="chat-sidebar">
                    <div class="chat-sidebar-header">
                        <div class="chat-sidebar-headline">
                            <div>
                                <h3 class="chat-sidebar-title">Đoạn chat</h3>
                                <p class="chat-sidebar-note">Trao đổi nhanh với lớp học, giáo viên và thông báo mới nhất.</p>
                            </div>
                        </div>

                        <div class="chat-sidebar-search">
                            <i class="fas fa-search"></i>
                            <input id="chat-room-search" type="text" placeholder="Tìm theo lớp, khóa học, giáo viên" value="${esc(state.roomQuery)}">
                        </div>

                        <div class="chat-sidebar-filters">
                            <button type="button" class="chat-filter-pill" data-room-filter="all">
                                Tất cả
                                <span id="chat-filter-total">0</span>
                            </button>
                            <button type="button" class="chat-filter-pill" data-room-filter="unread">
                                Chưa đọc
                                <span id="chat-filter-unread">0</span>
                            </button>
                            <button type="button" class="chat-filter-pill" data-room-filter="active">
                                Đang tham gia
                                <span id="chat-filter-active">0</span>
                            </button>
                        </div>
                    </div>

                    <div class="chat-rooms-wrap"></div>
                </aside>

                <section class="chat-main">
                    <div id="chat-main-header" class="chat-main-header"></div>
                    <div id="chat-inline-alert" class="chat-inline-alert"></div>
                    <div id="chat-message-board" class="chat-message-board"></div>
                    <div class="chat-composer-wrap"></div>
                </section>

                <aside class="chat-info-panel">
                    <div id="chat-info-panel" class="chat-info-scroll"></div>
                </aside>
            </div>`;

        renderMainHeader();
        renderRoomList();
        renderMessageBoard();
        renderComposer();
        renderInfoPanel();
        updateMobilePanels();
    }

    async function loadSelectedRoomMessages(roomId, opts = {}) {
        const { preservePosition = false, before = null } = opts;
        const board = document.getElementById("chat-message-board");
        const previousHeight =
            before && board ? board.scrollHeight : 0;
        const previousTop = before && board ? board.scrollTop : 0;
        const distanceFromBottom = board
            ? Math.max(
                  0,
                  board.scrollHeight - board.scrollTop - board.clientHeight,
              )
            : 0;
        const url = new URL(ep(BS.endpoints.messages, roomId), window.location.origin);
        if (before) url.searchParams.set("before", before);

        const data = await api(url.toString());

        if (
            !state.selectedRoom ||
            Number(state.selectedRoom.id) !== Number(roomId)
        ) {
            return null;
        }

        syncRoomInList(data.room);
        state.selectedRoom = { ...state.selectedRoom, ...data.room };
        state.messagesLoaded = true;
        if (!before) {
            state.unreadMarkerMessageId = data.readMarkerId
                ? Number(data.readMarkerId)
                : null;
        }

        const messages = Array.isArray(data.messages) ? data.messages : [];
        state.hasOlderMessages = Boolean(data.hasMore);

        if (before) {
            mergeOlderMessagesIntoState(messages);
        } else {
            syncMessagesState(messages);
        }

        renderMainHeader();
        renderMessageBoard();
        renderComposer();
        renderRoomList();
        renderInfoPanel();

        if (!before) {
            await loadRoomMembers(roomId);
        }

        const nextBoard = document.getElementById("chat-message-board");
        if (before) {
            if (nextBoard) {
                nextBoard.scrollTop =
                    nextBoard.scrollHeight - previousHeight + previousTop;
            }
            return data;
        }

        if (preservePosition && nextBoard && distanceFromBottom > 80) {
            nextBoard.scrollTop = Math.max(
                0,
                nextBoard.scrollHeight -
                    nextBoard.clientHeight -
                    distanceFromBottom,
            );
        } else if (!preservePosition && state.unreadMarkerMessageId) {
            if (!scrollToUnreadMarker()) {
                scrollToBottom();
            }
        } else {
            scrollToBottom();
        }

        return data;
    }

    function mergeOlderMessagesIntoState(messages) {
        if (!Array.isArray(messages) || !messages.length) return false;
        let prepended = false;

        messages
            .slice()
            .forEach((message) => {
                const messageId = Number(message.id);
                if (state.messageIds.has(messageId)) return;

                state.messageIds.add(messageId);
                state.messages.unshift(message);
                prepended = true;
            });

        if (!prepended) return false;

        state.messages.sort(
            (a, b) => messageOrderValue(a) - messageOrderValue(b),
        );

        return true;
    }

    function appendNewMessages(messages, opts = {}) {
        if (!Array.isArray(messages) || !messages.length) return false;

        const board = document.getElementById("chat-message-board");
        if (!board) return false;

        if (!board.querySelector(".chat-message-list")) {
            renderMessageBoard();
            renderInfoPanel();
            return true;
        }

        const list = board.querySelector(".chat-message-list");
        if (!list) return false;

        let appended = false;
        const stickToBottom =
            opts.forceScrollToBottom !== undefined
                ? opts.forceScrollToBottom
                : nearBottom();

        messages.forEach((message) => {
            const messageId = Number(message.id);
            if (state.messageIds.has(messageId)) return;

            state.messageIds.add(messageId);
            state.messages.push(message);

            if (messageId > state.lastMessageId)
                state.lastMessageId = messageId;

            list.appendChild(buildMessageEl(message));
            appended = true;
        });

        if (appended) {
            state.messages.sort(
                (a, b) => messageOrderValue(a) - messageOrderValue(b),
            );
            if (stickToBottom) scrollToBottom();
            renderInfoPanel();
        }

        return appended;
    }

    function replacePendingMessage(pendingId, realMessage) {
        const board = document.getElementById("chat-message-board");
        if (!board) return;

        const pendingEl = board.querySelector(
            `[data-pending="${CSS.escape(pendingId)}"]`,
        );
        if (pendingEl) pendingEl.remove();

        state.messages = state.messages.filter(
            (message) => message.id !== pendingId,
        );

        const realId = Number(realMessage.id);
        if (!state.messageIds.has(realId)) {
            state.messageIds.add(realId);
            state.messages.push(realMessage);
            if (realId > state.lastMessageId) state.lastMessageId = realId;
            state.messages.sort(
                (a, b) => messageOrderValue(a) - messageOrderValue(b),
            );

            const list = board.querySelector(".chat-message-list");
            if (list) {
                list.appendChild(buildMessageEl(realMessage));
                scrollToBottom();
            }
        }

        renderInfoPanel();
    }

    async function loadOlderMessages() {
        if (
            state.loadingOlderMessages ||
            !state.selectedRoom ||
            !state.selectedRoom.canAccess ||
            !state.messages.length ||
            !state.hasOlderMessages
        ) {
            return;
        }

        state.loadingOlderMessages = true;
        renderMessageBoard();

        try {
            const oldestMessage = state.messages[0];
            await loadSelectedRoomMessages(state.selectedRoom.id, {
                before: oldestMessage?.id || null,
            });
        } catch (_) {
        } finally {
            state.loadingOlderMessages = false;
            syncHistoryStatus();
        }
    }

    function stopPoll() {
        if (pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    }

    function schedulePoll(delay = POLL_MS) {
        stopPoll();

        if (
            document.hidden ||
            !state.selectedRoom ||
            !state.selectedRoom.canAccess
        )
            return;

        pollTimer = setTimeout(doPoll, delay);
    }

    async function doPoll() {
        if (
            polling ||
            !state.selectedRoom ||
            !state.selectedRoom.canAccess ||
            document.hidden
        ) {
            schedulePoll();
            return;
        }

        polling = true;
        const roomId = Number(state.selectedRoom.id);
        const after = state.lastMessageId;
        const previousUpdatedAt = state.selectedRoom.updatedAt || null;

        try {
            const url = new URL(BS.endpoints.poll, window.location.origin);
            url.searchParams.set("room", roomId);
            url.searchParams.set("after", after);

            const data = await fetch(url.toString(), {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": BS.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
            })
                .then((response) => (response.ok ? response.json() : null))
                .catch(() => null);

            if (!state.selectedRoom || Number(state.selectedRoom.id) !== roomId)
                return;

            if (data && data.status === "ok") {
                let roomChanged = false;

                if (data.room) {
                    roomChanged =
                        Boolean(previousUpdatedAt) &&
                        Boolean(data.room.updatedAt) &&
                        previousUpdatedAt !== data.room.updatedAt;
                    syncRoomInList(data.room);
                    renderMainHeader();
                    renderInfoPanel();
                }

                if (Array.isArray(data.typingUsers)) {
                    syncTypingUsers(data.typingUsers);
                    renderMainHeader();
                    updateComposerTypingNote();
                    renderInfoPanel();
                }

                if (Array.isArray(data.messages) && data.messages.length) {
                    const stick = nearBottom();
                    const appended = appendNewMessages(data.messages, {
                        forceScrollToBottom: stick,
                    });

                    if (appended) {
                        syncRoomInList({ ...data.room, unreadCount: 0 });
                        renderRoomList();
                        renderMainHeader();
                        renderInfoPanel();
                        markReadSilently(roomId, state.lastMessageId);
                    }
                } else if (roomChanged) {
                    await loadSelectedRoomMessages(roomId, {
                        preservePosition: true,
                    });
                }
            }
        } catch (_) {
        } finally {
            polling = false;
            schedulePoll();
        }
    }

    async function markReadSilently(roomId, lastMessageId) {
        try {
            await fetch(ep(BS.endpoints.read, roomId), {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": BS.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ lastMessageId }),
            });
        } catch (_) {}
    }

    function stopRoomPoll() {
        if (roomTimer) {
            clearInterval(roomTimer);
            roomTimer = null;
        }
    }

    function startRoomPoll() {
        stopRoomPoll();

        roomTimer = setInterval(async () => {
            if (document.hidden) return;

            try {
                const data = await api(BS.endpoints.rooms, {
                    headers: { "Cache-Control": "no-cache" },
                });

                const nextRooms = Array.isArray(data.rooms) ? data.rooms : [];
                const prevSnapshot = JSON.stringify(
                    state.rooms.map((room) => ({
                        id: room.id,
                        unreadCount: room.unreadCount,
                        lastMessagePreview: room.lastMessagePreview,
                        updatedAt: room.updatedAt,
                    })),
                );
                const nextSnapshot = JSON.stringify(
                    nextRooms.map((room) => ({
                        id: room.id,
                        unreadCount: room.unreadCount,
                        lastMessagePreview: room.lastMessagePreview,
                        updatedAt: room.updatedAt,
                    })),
                );

                if (prevSnapshot !== nextSnapshot) {
                    const previousSelectedRoom = state.selectedRoom
                        ? { ...state.selectedRoom }
                        : null;
                    state.rooms = nextRooms;

                    if (state.selectedRoom) {
                        const refreshed = roomById(state.selectedRoom.id);
                        if (refreshed)
                            state.selectedRoom = {
                                ...state.selectedRoom,
                                ...refreshed,
                            };
                    }

                    renderRoomList();
                    renderMainHeader();
                    renderInfoPanel();

                    if (
                        previousSelectedRoom &&
                        state.selectedRoom &&
                        previousSelectedRoom.updatedAt !==
                            state.selectedRoom.updatedAt &&
                        state.selectedRoom.canAccess
                    ) {
                        await loadSelectedRoomMessages(state.selectedRoom.id, {
                            preservePosition: true,
                        });
                    }
                }

                if (state.selectedRoom && state.selectedRoom.canAccess) {
                    await loadRoomMembers(state.selectedRoom.id);
                }
            } catch (_) {}
        }, ROOM_MS);
    }

    async function selectRoom(roomId) {
        const room = roomById(roomId);
        if (!room) return;

        stopTyping();
        stopPoll();
        notice("", "");

        state.mobileSidebarOpen = false;
        state.mobileInfoOpen = false;
        state.selectedRoom = room;
        state.messages = [];
        state.messageIds = new Set();
        state.lastMessageId = 0;
        state.hasOlderMessages = false;
        state.loadingOlderMessages = false;
        state.messagesLoaded = false;
        state.messageSearchOpen = false;
        state.messageSearchQuery = "";
        state.messageSearchResults = [];
        state.messageSearchLoading = false;
        state.messageDraft = "";
        clearDraftAttachments();
        state.unreadMarkerMessageId = null;
        state.highlightedMessageId = null;
        state.replyingTo = null;
        state.openMessageMenuId = null;
        state.openReactionPickerId = null;
        state.openReceiptDetailsMessageId = null;
        state.composerEmojiOpen = false;
        state.typingUsers = [];
        state.roomMembers = [];
        state.roomMembersLoading = false;

        renderApp();
        setUrl(roomId);

        if (room.canAccess) {
            try {
                await loadSelectedRoomMessages(roomId);
            } catch (error) {
                notice("error", error.payload?.message || error.message);
            }
        }

        schedulePoll(100);
    }

    async function joinRoom() {
        if (!state.selectedRoom) return;

        try {
            const data = await api(
                ep(BS.endpoints.join, state.selectedRoom.id),
                {
                    method: "POST",
                    body: JSON.stringify({}),
                },
            );

            syncRoomInList(data.room);
            state.selectedRoom = data.room;
            state.messagesLoaded = false;

            notice("success", data.message || "Tham gia nhóm chat thành công.");
            renderMainHeader();
            renderMessageBoard();
            renderComposer();
            renderRoomList();
            renderInfoPanel();

            await selectRoom(state.selectedRoom.id);
        } catch (error) {
            notice("error", error.payload?.message || error.message);
        }
    }

    async function recallMessage(messageId) {
        if (!state.selectedRoom || !messageId) return;

        const targetMessage = findMessageById(messageId);
        if (!targetMessage || !targetMessage.canRecall || state.submitting)
            return;

        try {
            closeMessageMenu();

            const data = await api(
                BS.endpoints.recall.replace("__MESSAGE__", messageId),
                {
                    method: "POST",
                    body: JSON.stringify({
                        roomId: state.selectedRoom.id,
                    }),
                },
            );

            replaceMessageInState(data.chatMessage);
            syncRoomInList({ ...data.room, unreadCount: 0 });
            state.selectedRoom = {
                ...state.selectedRoom,
                ...data.room,
                unreadCount: 0,
            };

            renderMessageBoard();
            renderComposer();
            renderRoomList();
            renderMainHeader();
            renderInfoPanel();
            notice("success", data.message || "Đã thu hồi tin nhắn.");
        } catch (error) {
            notice("error", error.payload?.message || error.message);
        }
    }

    async function deleteMessageForMe(messageId) {
        if (!state.selectedRoom || !messageId || state.submitting) return;

        const targetMessage = findMessageById(messageId);
        if (!targetMessage || targetMessage._pending || !targetMessage.canDeleteForMe)
            return;

        try {
            closeMessageMenu();

            const data = await api(
                BS.endpoints.deleteForMe.replace("__MESSAGE__", messageId),
                {
                    method: "POST",
                    body: JSON.stringify({
                        roomId: state.selectedRoom.id,
                    }),
                },
            );

            state.messages = state.messages.filter(
                (message) => Number(message.id) !== Number(messageId),
            );
            state.messageIds.delete(Number(messageId));

            if (
                state.replyingTo &&
                Number(state.replyingTo.id) === Number(messageId)
            ) {
                state.replyingTo = null;
            }

            syncRoomInList(data.room);
            state.selectedRoom = {
                ...state.selectedRoom,
                ...data.room,
            };

            renderMessageBoard();
            renderComposer();
            renderRoomList();
            renderMainHeader();
            renderInfoPanel();
            notice(
                "success",
                data.message || "Đã xóa tin nhắn khỏi chế độ xem của bạn.",
            );
        } catch (error) {
            notice("error", error.payload?.message || error.message);
        }
    }

    async function openDirectConversation(targetUserId) {
        if (!targetUserId || state.submitting) return;

        const member = state.roomMembers.find(
            (item) => Number(item.id) === Number(targetUserId),
        );
        if (!member || member.isMe || !member.canDirect) return;

        try {
            const data = await api(BS.endpoints.direct, {
                method: "POST",
                body: JSON.stringify({
                    targetUserId,
                }),
            });

            syncRoomInList(data.room);
            renderRoomList();
            await selectRoom(data.room.id);
            notice("success", data.message || "Đã mở đoạn chat riêng.");
        } catch (error) {
            notice("error", error.payload?.message || error.message);
        }
    }

    function insertComposerEmoji(emoji) {
        if (!emoji) return;

        const input = document.getElementById("chat-message-input");
        if (!input) return;

        const currentValue = input.value || "";
        const start = Number.isInteger(input.selectionStart)
            ? input.selectionStart
            : currentValue.length;
        const end = Number.isInteger(input.selectionEnd)
            ? input.selectionEnd
            : start;
        const nextValue =
            currentValue.slice(0, start) + emoji + currentValue.slice(end);
        const nextCursor = start + emoji.length;

        input.value = nextValue;
        state.messageDraft = nextValue;
        closeComposerEmojiPicker();
        resizeComposerTextarea(input);
        input.focus();
        input.setSelectionRange(nextCursor, nextCursor);
    }

    async function toggleReaction(messageId, emoji) {
        if (!state.selectedRoom || !messageId || !emoji) return;

        const targetMessage = findMessageById(messageId);
        if (
            !targetMessage ||
            targetMessage._pending ||
            targetMessage.isRecalled
        ) {
            return;
        }

        try {
            closeMessageMenu();
            closeReactionPicker();

            const data = await api(
                BS.endpoints.react.replace("__MESSAGE__", messageId),
                {
                    method: "POST",
                    body: JSON.stringify({
                        roomId: state.selectedRoom.id,
                        emoji,
                    }),
                },
            );

            replaceMessageInState(data.chatMessage);
            syncRoomInList({ ...data.room, unreadCount: 0 });
            state.selectedRoom = {
                ...state.selectedRoom,
                ...data.room,
                unreadCount: 0,
            };

            renderMessageBoard();
            renderRoomList();
            renderMainHeader();
            renderInfoPanel();
            notice("", "");
        } catch (error) {
            notice("error", error.payload?.message || error.message);
        }
    }

    async function sendMessage() {
        const input = document.getElementById("chat-message-input");
        if (!input || !state.selectedRoom) return;

        const text = input.value.trim();
        const attachments = state.draftAttachments.map((item) => ({ ...item }));
        if ((!text && !attachments.length) || state.submitting) return;
        const replyTo = state.replyingTo ? { ...state.replyingTo } : null;

        stopTyping(state.selectedRoom.id);
        state.submitting = true;
        state.composerEmojiOpen = false;
        state.messageDraft = "";
        state.draftAttachments = [];
        input.value = "";
        resizeComposerTextarea(input);

        const pendingId = `p_${Date.now()}`;
        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");

        const optimistic = {
            id: pendingId,
            content: text,
            isMine: true,
            senderName: "Bạn",
            replyTo,
            attachments: attachments.map((attachment) => ({
                id: attachment.id,
                name: attachment.name,
                size: attachment.size,
                mime: attachment.mime,
                isImage: attachment.isImage,
                previewUrl: attachment.previewUrl,
                url: attachment.previewUrl,
                thumbnailUrl: attachment.previewUrl,
                downloadUrl: attachment.previewUrl,
            })),
            sentAtLabel: `${pad(now.getHours())}:${pad(now.getMinutes())} ${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()}`,
            _pending: true,
        };

        const board = document.getElementById("chat-message-board");
        let list = board?.querySelector(".chat-message-list");

        if (!list) {
            state.messages.push(optimistic);
            state.messagesLoaded = true;
            renderMessageBoard();
            list = board?.querySelector(".chat-message-list");
        } else {
            state.messages.push(optimistic);
            list.appendChild(buildMessageEl(optimistic));
        }

        renderInfoPanel();
        scrollToBottom();
        input.focus();

        try {
            const formData = new FormData();
            formData.append("roomId", state.selectedRoom.id);
            formData.append("message", text);
            if (replyTo?.id) formData.append("replyToMessageId", replyTo.id);
            attachments.forEach((attachment) => {
                formData.append("attachments[]", attachment.file);
            });

            const data = await api(BS.endpoints.send, {
                method: "POST",
                body: formData,
            });

            replacePendingMessage(pendingId, data.chatMessage);
            syncRoomInList({ ...data.room, unreadCount: 0 });
            state.selectedRoom = {
                ...state.selectedRoom,
                ...data.room,
                unreadCount: 0,
            };
            state.replyingTo = null;
            attachments.forEach(releaseDraftAttachment);

            renderMessageBoard();
            renderComposer();
            renderRoomList();
            renderMainHeader();
            renderInfoPanel();
            notice("", "");
        } catch (error) {
            const pendingEl = board?.querySelector(
                `[data-pending="${CSS.escape(pendingId)}"]`,
            );
            if (pendingEl) pendingEl.remove();

            state.messages = state.messages.filter(
                (message) => message.id !== pendingId,
            );

            if (!state.messages.length) renderMessageBoard();

            input.value = text;
            state.messageDraft = text;
            state.draftAttachments = attachments;
            resizeComposerTextarea(input);
            renderComposer();
            renderInfoPanel();
            notice("error", error.payload?.message || error.message);
        } finally {
            state.submitting = false;
            renderComposer();
            document.getElementById("chat-message-input")?.focus();
        }
    }

    root.addEventListener("click", (event) => {
        if (
            !event.target.closest(".chat-message-tools") &&
            !event.target.closest(".chat-message-menu")
        ) {
            closeMessageMenu();
        }

        if (
            !event.target.closest(".chat-message-reaction-wrap") &&
            !event.target.closest(".chat-message-reactions")
        ) {
            closeReactionPicker();
        }

        if (
            !event.target.closest(".chat-receipt-wrap") &&
            !event.target.closest(".chat-receipt-popover")
        ) {
            closeReceiptDetails();
        }

        if (
            !event.target.closest(".chat-composer-emoji") &&
            !event.target.closest("[data-toggle-composer-emoji]")
        ) {
            closeComposerEmojiPicker();
        }

        if (event.target.closest("[data-toggle-rooms]")) {
            setMobilePanel("rooms", !state.mobileSidebarOpen);
            return;
        }

        if (event.target.closest("[data-toggle-info]")) {
            if (isMobileViewport())
                setMobilePanel("info", !state.mobileInfoOpen);
            return;
        }

        if (event.target.closest("[data-toggle-message-search]")) {
            toggleMessageSearch();
            return;
        }

        if (event.target.closest("[data-close-panels]")) {
            closeMobilePanels();
            return;
        }

        const filterButton = event.target.closest("[data-room-filter]");
        if (filterButton) {
            state.roomFilter = filterButton.dataset.roomFilter || "all";
            renderRoomList();
            return;
        }

        const directButton = event.target.closest("[data-open-direct]");
        if (directButton) {
            openDirectConversation(Number(directButton.dataset.openDirect));
            return;
        }

        if (event.target.closest("[data-cancel-reply]")) {
            setReplyingTo(null);
            return;
        }

        if (event.target.closest("[data-focus-message-search]")) {
            toggleMessageSearch(true);
            return;
        }

        if (event.target.closest("[data-clear-message-search]")) {
            clearMessageSearch();
            return;
        }

        const jumpMessageButton = event.target.closest("[data-jump-message]");
        if (jumpMessageButton) {
            const messageId = Number(jumpMessageButton.dataset.jumpMessage);
            if (messageId) {
                clearMessageSearch();
                jumpToMessage(messageId);
            }
            return;
        }

        if (event.target.closest("[data-open-file-picker]")) {
            document.getElementById("chat-attachment-input")?.click();
            return;
        }

        const removeDraftButton = event.target.closest(
            "[data-remove-draft-attachment]",
        );
        if (removeDraftButton) {
            removeDraftAttachment(
                removeDraftButton.dataset.removeDraftAttachment || "",
            );
            return;
        }

        if (event.target.closest("[data-toggle-composer-emoji]")) {
            toggleComposerEmojiPicker();
            return;
        }

        const composerEmojiButton = event.target.closest("[data-composer-emoji]");
        if (composerEmojiButton) {
            insertComposerEmoji(composerEmojiButton.dataset.composerEmoji || "");
            return;
        }

        const messageReactionButton = event.target.closest(
            "[data-message-reaction-btn]",
        );
        if (messageReactionButton) {
            toggleReactionPicker(messageReactionButton.dataset.messageReactionBtn);
            return;
        }

        const pickerReactionButton = event.target.closest("[data-picker-reaction]");
        if (pickerReactionButton) {
            toggleReaction(
                Number(pickerReactionButton.dataset.pickerReaction),
                pickerReactionButton.dataset.emoji || "",
            );
            return;
        }

        const reactionPill = event.target.closest("[data-toggle-reaction]");
        if (reactionPill) {
            toggleReaction(
                Number(reactionPill.dataset.toggleReaction),
                reactionPill.dataset.emoji || "",
            );
            return;
        }

        const receiptButton = event.target.closest("[data-toggle-receipt-details]");
        if (receiptButton) {
            toggleReceiptDetails(receiptButton.dataset.toggleReceiptDetails);
            return;
        }

        const messageMenuButton = event.target.closest(
            "[data-message-menu-btn]",
        );
        if (messageMenuButton) {
            toggleMessageMenu(messageMenuButton.dataset.messageMenuBtn);
            return;
        }

        const messageAction = event.target.closest("[data-message-action]");
        if (messageAction) {
            const action = messageAction.dataset.messageAction;
            const messageId = Number(messageAction.dataset.messageId);
            const message = findMessageById(messageId);

            if (action === "reply" && message) {
                setReplyingTo(message);
                return;
            }

            if (action === "recall") {
                recallMessage(messageId);
                return;
            }

            if (action === "delete-for-me") {
                deleteMessageForMe(messageId);
                return;
            }
        }

        const roomButton = event.target.closest("[data-room-id]");
        if (roomButton) selectRoom(Number(roomButton.dataset.roomId));
    });

    root.addEventListener("submit", (event) => {
        if (event.target.id === "chat-join-form") {
            event.preventDefault();
            joinRoom();
        }

        if (event.target.id === "chat-send-form") {
            event.preventDefault();
            sendMessage();
        }
    });

    root.addEventListener("input", (event) => {
        if (event.target.id === "chat-message-input") {
            state.messageDraft = event.target.value || "";
            resizeComposerTextarea(event.target);
            scheduleTypingHeartbeat();
        }

        if (event.target.id === "chat-room-search") {
            state.roomQuery = event.target.value || "";
            renderRoomList();
        }

        if (event.target.id === "chat-message-search") {
            performMessageSearch(event.target.value || "");
        }

        if (event.target.id === "chat-attachment-input") {
            addDraftFiles(event.target.files);
            event.target.value = "";
        }
    });

    root.addEventListener(
        "scroll",
        (event) => {
            if (
                event.target.id === "chat-message-board" &&
                event.target.scrollTop <= 80
            ) {
                loadOlderMessages();
            }
        },
        true,
    );

    root.addEventListener("keydown", (event) => {
        if (
            event.target.id === "chat-message-input" &&
            event.key === "Enter" &&
            !event.shiftKey
        ) {
            event.preventDefault();
            sendMessage();
        }

        if (event.key === "Escape") {
            if (state.openMessageMenuId !== null) closeMessageMenu();
            else if (state.openReactionPickerId !== null) closeReactionPicker();
            else if (state.openReceiptDetailsMessageId !== null)
                closeReceiptDetails();
            else if (state.composerEmojiOpen) closeComposerEmojiPicker();
            else if (state.replyingTo) setReplyingTo(null);
        }
    });

    root.addEventListener(
        "focusout",
        (event) => {
            if (event.target.id === "chat-message-input") {
                stopTyping();
            }
        },
        true,
    );

    document.addEventListener("click", (event) => {
        if (!root.contains(event.target)) {
            closeMessageMenu();
            closeReactionPicker();
            closeReceiptDetails();
            closeComposerEmojiPicker();
        }
    });

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopTyping();
    });

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopPoll();
        else schedulePoll(200);
    });

    window.addEventListener("resize", () => {
        updateMobilePanels();
    });

    window.addEventListener("beforeunload", () => {
        stopPoll();
        stopRoomPoll();
        clearDraftAttachments();
    });

    renderApp();
    startRoomPoll();

    if (state.selectedRoom) {
        if (state.selectedRoom.canAccess) {
            (async () => {
                try {
                    await loadSelectedRoomMessages(state.selectedRoom.id);
                } catch (_) {}

                schedulePoll(200);
            })();
        } else {
            setUrl(state.selectedRoom.id);
        }
    }
})();
