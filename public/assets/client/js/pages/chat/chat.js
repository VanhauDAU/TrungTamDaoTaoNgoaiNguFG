(function () {
    "use strict";

    const BS = window.CHAT_BOOTSTRAP;
    const root = document.getElementById("chat-app");
    const POLL_MS = 1500;
    const ROOM_MS = 15000;
    const MOBILE_BREAKPOINT = 991.98;

    if (!BS || !root) return;

    const state = {
        rooms: Array.isArray(BS.rooms) ? BS.rooms : [],
        selectedRoom: BS.selectedRoom || null,
        messages: [],
        messageIds: new Set(),
        lastMessageId: 0,
        submitting: false,
        roomQuery: "",
        roomFilter: "all",
        mobileSidebarOpen: false,
        mobileInfoOpen: false,
        messageDraft: "",
        messagesLoaded: false,
    };

    let pollTimer = null;
    let roomTimer = null;
    let polling = false;

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

    function messageOrderValue(message) {
        const numeric = Number(message?.id);
        return Number.isFinite(numeric) ? numeric : Number.MAX_SAFE_INTEGER;
    }

    function truncateText(value, limit = 120) {
        const text = String(value || "").trim();
        if (text.length <= limit) return text;
        return `${text.slice(0, limit - 1)}...`;
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

    function resizeComposerTextarea(textarea) {
        if (!textarea) return;
        textarea.style.height = "0px";
        textarea.style.height = `${Math.min(textarea.scrollHeight, 140)}px`;
    }

    async function api(url, opts = {}) {
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": BS.csrf,
                "X-Requested-With": "XMLHttpRequest",
                ...(opts.headers || {}),
            },
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

    function selectedRoomSubtitle(room) {
        if (!room) return "Chọn một nhóm chat để bắt đầu trao đổi với lớp học.";

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

        return [
            room.className || "Nhóm lớp học",
            room.courseName || "Chưa gắn khóa học",
            room.teacherName || "Chưa phân công",
        ];
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
                                        <div class="chat-room-avatar">${esc(roomInitials(room))}</div>
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
                                        <div class="chat-room-course">${esc(room.courseName || room.className || "Nhóm lớp học")}</div>
                                        <div class="chat-room-preview">${esc(room.lastMessagePreview || "Chưa có tin nhắn")}</div>
                                        <div class="chat-room-meta">
                                            <span>${esc(room.teacherName || "Chưa có giáo viên")}</span>
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

        header.innerHTML = `
            <div class="chat-main-header-row">
                <div class="chat-main-primary">
                    <button type="button" class="chat-mobile-rooms-btn" data-toggle-rooms aria-label="Mở danh sách phòng">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="chat-main-avatar-wrap">
                        <div class="chat-main-avatar">${esc(room ? roomInitials(room) : "CH")}</div>
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
                    <button type="button" class="chat-header-icon" disabled title="Đang cập nhật">
                        <i class="fas fa-phone"></i>
                    </button>
                    <button type="button" class="chat-header-icon" disabled title="Đang cập nhật">
                        <i class="fas fa-video"></i>
                    </button>
                    <button type="button" class="chat-header-icon ${state.mobileInfoOpen ? "is-active" : ""}" data-toggle-info title="Thông tin đoạn chat">
                        <i class="fas fa-circle-info"></i>
                    </button>
                </div>
            </div>`;
    }

    function buildMessageEl(message) {
        const wrap = document.createElement("div");
        wrap.className = `chat-message-row${message.isMine ? " is-mine" : ""}`;
        if (message._pending) wrap.dataset.pending = message.id;

        const replyHtml = message.replyTo
            ? `
                <div class="chat-reply-box">
                    <div><strong>${esc(message.replyTo.senderName)}</strong></div>
                    <div>${esc(message.replyTo.content)}</div>
                </div>`
            : "";

        wrap.innerHTML = `
            ${
                message.isMine
                    ? ""
                    : `<div class="chat-message-avatar-small">${esc(initialsFromText(message.senderName, "HV"))}</div>`
            }
            <div class="chat-message-stack">
                ${message.isMine ? "" : `<div class="chat-message-sender">${esc(message.senderName)}</div>`}
                <div class="chat-message-bubble${message._pending ? " is-pending" : ""}">
                    ${replyHtml}
                    <div class="chat-message-text">${esc(message.content)}</div>
                    <div class="chat-message-time">${message._pending ? "Đang gửi..." : esc(message.sentAtLabel || "")}</div>
                </div>
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
        board.innerHTML = `
            <div class="chat-message-list">
                ${state.messages
                    .map((message) => buildMessageEl(message).outerHTML)
                    .join("")}
            </div>`;
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
        const infoCards = [
            {
                icon: "fa-chalkboard",
                label: "Lớp học",
                value: room.className || "Nhóm lớp học",
            },
            {
                icon: "fa-book-open",
                label: "Khóa học",
                value: room.courseName || "Chưa gắn khóa học",
            },
            {
                icon: "fa-user-tie",
                label: "Giáo viên",
                value: room.teacherName || "Chưa phân công",
            },
            {
                icon: "fa-user-check",
                label: "Tham gia",
                value: roomMembershipText(room),
            },
            {
                icon: "fa-paper-plane",
                label: "Quyền gửi",
                value: roomSendText(room),
            },
            {
                icon: "fa-bell",
                label: "Tin chưa đọc",
                value: String(room.unreadCount || 0),
            },
            {
                icon: "fa-clock",
                label: "Hoạt động cuối",
                value: room.lastMessageAtLabel || "Chưa cập nhật",
            },
            {
                icon: "fa-layer-group",
                label: "Đang tải",
                value: `${snapshot.totalLoaded} tin`,
            },
        ];

        panel.innerHTML = `
            <div class="chat-info-hero">
                <div class="chat-info-avatar-wrap">
                    <div class="chat-info-avatar">${esc(roomInitials(room))}</div>
                    <span class="chat-info-avatar-dot ${room.canAccess ? "is-live" : ""}"></span>
                </div>
                <h4 class="chat-info-title">${esc(room.name)}</h4>
                <p class="chat-info-subtitle">${esc(selectedRoomSubtitle(room))}</p>
                <span class="chat-main-status ${roomStatusClass(room)}">${esc(roomStatusLabel(room))}</span>
            </div>

            <div class="chat-info-section">
                <div class="chat-info-section-head">
                    <h5>Thông tin đoạn chat</h5>
                </div>
                <div class="chat-info-grid">
                    ${infoCards
                        .map(
                            (item) => `
                                <div class="chat-info-card">
                                    <span class="chat-info-card-icon"><i class="fas ${item.icon}"></i></span>
                                    <div>
                                        <strong>${esc(item.value)}</strong>
                                        <span>${esc(item.label)}</span>
                                    </div>
                                </div>`,
                        )
                        .join("")}
                </div>
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
                    <button type="button" class="chat-info-action-btn" disabled>
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
                    <li>Nhập Enter để gửi nhanh, Shift + Enter để xuống dòng.</li>
                    <li>Bộ lọc bên trái giúp tách nhanh các đoạn chat chưa đọc và đã tham gia.</li>
                </ul>
            </div>`;
    }

    function renderComposer() {
        const composerWrap = root.querySelector(".chat-composer-wrap");
        if (!composerWrap) return;

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

        const existingBtn = composerWrap.querySelector(".chat-send-btn");
        if (existingBtn) {
            existingBtn.disabled = state.submitting;
            return;
        }

        composerWrap.innerHTML = `
            <div class="chat-composer">
                <form id="chat-send-form" class="chat-composer-form">
                    <div class="chat-composer-tools">
                        <button type="button" class="chat-tool-btn" title="Sắp có" disabled>
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="button" class="chat-tool-btn" title="Sắp có" disabled>
                            <i class="fas fa-image"></i>
                        </button>
                    </div>
                    <div class="chat-composer-field">
                        <textarea id="chat-message-input" placeholder="Nhập tin nhắn cho lớp học của bạn...">${esc(state.messageDraft)}</textarea>
                        <div class="chat-composer-note">Enter để gửi, Shift + Enter để xuống dòng</div>
                    </div>
                    <button type="submit" class="chat-send-btn" ${state.submitting ? "disabled" : ""}>
                        <i class="fas fa-paper-plane"></i>
                        <span>Gửi</span>
                    </button>
                </form>
            </div>`;

        resizeComposerTextarea(document.getElementById("chat-message-input"));
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
                if (data.room) {
                    syncRoomInList(data.room);
                    renderMainHeader();
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
                    })),
                );
                const nextSnapshot = JSON.stringify(
                    nextRooms.map((room) => ({
                        id: room.id,
                        unreadCount: room.unreadCount,
                        lastMessagePreview: room.lastMessagePreview,
                    })),
                );

                if (prevSnapshot !== nextSnapshot) {
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
                }
            } catch (_) {}
        }, ROOM_MS);
    }

    async function selectRoom(roomId) {
        const room = roomById(roomId);
        if (!room) return;

        stopPoll();
        notice("", "");

        state.mobileSidebarOpen = false;
        state.mobileInfoOpen = false;
        state.selectedRoom = room;
        state.messages = [];
        state.messageIds = new Set();
        state.lastMessageId = 0;
        state.messagesLoaded = false;
        state.messageDraft = "";

        renderApp();
        setUrl(roomId);

        if (room.canAccess) {
            try {
                const data = await api(ep(BS.endpoints.messages, roomId));

                if (
                    !state.selectedRoom ||
                    Number(state.selectedRoom.id) !== Number(roomId)
                )
                    return;

                syncRoomInList(data.room);
                state.selectedRoom = { ...state.selectedRoom, ...data.room };
                state.messagesLoaded = true;

                const messages = Array.isArray(data.messages)
                    ? data.messages
                    : [];
                messages.forEach((message) => {
                    const id = Number(message.id);
                    state.messageIds.add(id);
                    state.messages.push(message);
                    if (id > state.lastMessageId) state.lastMessageId = id;
                });

                renderMainHeader();
                renderMessageBoard();
                renderComposer();
                renderRoomList();
                renderInfoPanel();
                scrollToBottom();
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

    async function sendMessage() {
        const input = document.getElementById("chat-message-input");
        if (!input || !state.selectedRoom) return;

        const text = input.value.trim();
        if (!text || state.submitting) return;

        state.submitting = true;
        state.messageDraft = "";
        input.value = "";
        resizeComposerTextarea(input);

        const button = root.querySelector(".chat-send-btn");
        if (button) button.disabled = true;

        const pendingId = `p_${Date.now()}`;
        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");

        const optimistic = {
            id: pendingId,
            content: text,
            isMine: true,
            senderName: "Bạn",
            replyTo: null,
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
            const data = await api(BS.endpoints.send, {
                method: "POST",
                body: JSON.stringify({
                    roomId: state.selectedRoom.id,
                    message: text,
                }),
            });

            replacePendingMessage(pendingId, data.chatMessage);
            syncRoomInList({ ...data.room, unreadCount: 0 });
            state.selectedRoom = {
                ...state.selectedRoom,
                ...data.room,
                unreadCount: 0,
            };

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
            resizeComposerTextarea(input);
            renderInfoPanel();
            notice("error", error.payload?.message || error.message);
        } finally {
            state.submitting = false;
            if (button) button.disabled = false;
            input.focus();
        }
    }

    root.addEventListener("click", (event) => {
        if (event.target.closest("[data-toggle-rooms]")) {
            setMobilePanel("rooms", !state.mobileSidebarOpen);
            return;
        }

        if (event.target.closest("[data-toggle-info]")) {
            if (isMobileViewport())
                setMobilePanel("info", !state.mobileInfoOpen);
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
        }

        if (event.target.id === "chat-room-search") {
            state.roomQuery = event.target.value || "";
            renderRoomList();
        }
    });

    root.addEventListener("keydown", (event) => {
        if (
            event.target.id === "chat-message-input" &&
            event.key === "Enter" &&
            !event.shiftKey
        ) {
            event.preventDefault();
            sendMessage();
        }
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
    });

    renderApp();
    startRoomPoll();

    if (state.selectedRoom) {
        if (state.selectedRoom.canAccess) {
            (async () => {
                try {
                    const data = await api(
                        ep(BS.endpoints.messages, state.selectedRoom.id),
                    );

                    syncRoomInList(data.room);
                    state.selectedRoom = {
                        ...state.selectedRoom,
                        ...data.room,
                    };
                    state.messagesLoaded = true;

                    const messages = Array.isArray(data.messages)
                        ? data.messages
                        : [];
                    messages.forEach((message) => {
                        const id = Number(message.id);
                        state.messageIds.add(id);
                        state.messages.push(message);
                        if (id > state.lastMessageId) state.lastMessageId = id;
                    });

                    renderMainHeader();
                    renderMessageBoard();
                    renderComposer();
                    renderRoomList();
                    renderInfoPanel();
                    scrollToBottom();
                } catch (_) {}

                schedulePoll(200);
            })();
        } else {
            setUrl(state.selectedRoom.id);
        }
    }
})();
