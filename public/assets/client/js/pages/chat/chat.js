(function () {
    "use strict";

    const BS = window.CHAT_BOOTSTRAP;
    const root = document.getElementById("chat-app");
    const POLL_MS = 1500; // Poll mỗi 1.5 giây
    const ROOM_MS = 15000; // Làm mới sidebar mỗi 15 giây

    if (!BS || !root) return;

    // ─── State ────────────────────────────────────────────────────────────────
    const state = {
        rooms: Array.isArray(BS.rooms) ? BS.rooms : [],
        selectedRoom: BS.selectedRoom || null,
        messages: [], // array of message objects
        messageIds: new Set(), // fast dedup
        lastMessageId: 0,
        submitting: false,
        roomQuery: "",
        mobileSidebarOpen: false,
        messageDraft: "",
        messagesLoaded: false, // true after first AJAX load
    };

    let pollTimer = null;
    let roomTimer = null;
    let polling = false; // prevent concurrent polls

    // ─── Helpers ──────────────────────────────────────────────────────────────

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

    function roomById(id) {
        return state.rooms.find((r) => Number(r.id) === Number(id)) || null;
    }

    function roomInitials(room) {
        const s = String(room?.name || room?.className || "CH").trim();
        return (
            s
                .split(/\s+/)
                .slice(0, 2)
                .map((p) => p[0].toUpperCase())
                .join("") || "CH"
        );
    }

    function nearBottom() {
        const b = document.getElementById("chat-message-board");
        return !b || b.scrollHeight - b.scrollTop - b.clientHeight < 80;
    }

    function scrollToBottom() {
        const b = document.getElementById("chat-message-board");
        if (b) b.scrollTop = b.scrollHeight;
    }

    async function api(url, opts = {}) {
        const r = await fetch(url, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": BS.csrf,
                "X-Requested-With": "XMLHttpRequest",
                ...(opts.headers || {}),
            },
            ...opts,
        });
        const d = await r.json().catch(() => ({}));
        if (!r.ok) {
            const e = new Error(d.message || "Đã có lỗi xảy ra.");
            e.status = r.status;
            e.payload = d;
            throw e;
        }
        return d;
    }

    function setUrl(roomId) {
        const u = new URL(window.location.href);
        roomId
            ? u.searchParams.set("room", roomId)
            : u.searchParams.delete("room");
        window.history.replaceState({}, "", u.toString());
    }

    function notice(type, msg) {
        const el = document.getElementById("chat-inline-alert");
        if (!el) return;
        if (!msg) {
            el.style.display = "none";
            el.textContent = "";
            el.className = "chat-inline-alert";
            return;
        }
        el.style.display = "block";
        el.textContent = msg;
        el.className = `chat-inline-alert is-${type}`;
    }

    function syncRoomInList(updated) {
        if (!updated) return;
        const i = state.rooms.findIndex(
            (r) => Number(r.id) === Number(updated.id),
        );
        if (i >= 0) state.rooms[i] = { ...state.rooms[i], ...updated };
        else state.rooms.unshift(updated);
        if (
            state.selectedRoom &&
            Number(state.selectedRoom.id) === Number(updated.id)
        ) {
            state.selectedRoom = { ...state.selectedRoom, ...updated };
        }
    }

    function filteredRooms() {
        const q = state.roomQuery.trim().toLowerCase();
        if (!q) return state.rooms;
        return state.rooms.filter((r) =>
            [
                r.name,
                r.className,
                r.courseName,
                r.teacherName,
                r.lastMessagePreview,
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase()
                .includes(q),
        );
    }

    // ─── Message DOM helpers ──────────────────────────────────────────────────

    function buildMessageEl(msg) {
        const wrap = document.createElement("div");
        wrap.className = `chat-message-row${msg.isMine ? " is-mine" : ""}`;
        if (msg._pending) wrap.dataset.pending = msg.id;

        const replyHtml = msg.replyTo
            ? `
            <div class="chat-reply-box">
                <div><strong>${esc(msg.replyTo.senderName)}</strong></div>
                <div>${esc(msg.replyTo.content)}</div>
            </div>`
            : "";

        wrap.innerHTML = `
            ${msg.isMine ? "" : `<div class="chat-message-sender">${esc(msg.senderName)}</div>`}
            <div class="chat-message-bubble${msg._pending ? " is-pending" : ""}">
                ${replyHtml}
                <div class="chat-message-text">${esc(msg.content)}</div>
                <div class="chat-message-time">${msg._pending ? "Đang gửi..." : esc(msg.sentAtLabel || "")}</div>
            </div>`;
        return wrap;
    }

    /**
     * Chỉ append các tin nhắn MỚI vào message board — không re-render toàn bộ.
     * Trả về true nếu có thay đổi.
     */
    function appendNewMessages(msgs, opts = {}) {
        if (!Array.isArray(msgs) || !msgs.length) return false;

        const board = document.getElementById("chat-message-board");
        if (!board) return false;

        // Nếu board đang ở trạng thái empty/loading, re-render toàn bộ
        if (!board.querySelector(".chat-message-list")) {
            renderMessageBoard();
            return true;
        }

        const list = board.querySelector(".chat-message-list");
        if (!list) return false;

        let appended = false;
        const stick =
            opts.forceScrollToBottom !== undefined
                ? opts.forceScrollToBottom
                : nearBottom();

        msgs.forEach((msg) => {
            const msgId = Number(msg.id);
            if (state.messageIds.has(msgId)) return;
            state.messageIds.add(msgId);
            state.messages.push(msg);
            if (msgId > state.lastMessageId) state.lastMessageId = msgId;
            list.appendChild(buildMessageEl(msg));
            appended = true;
        });

        if (appended) {
            state.messages.sort((a, b) => Number(a.id) - Number(b.id));
            if (stick) scrollToBottom();
        }
        return appended;
    }

    /** Replace a pending optimistic bubble with the real one */
    function replacePendingMessage(pendingId, realMsg) {
        const board = document.getElementById("chat-message-board");
        if (!board) return;

        // Remove pending node
        const pendingEl = board.querySelector(
            `[data-pending="${CSS.escape(pendingId)}"]`,
        );
        if (pendingEl) pendingEl.remove();

        // Remove from state.messages
        state.messages = state.messages.filter((m) => m.id !== pendingId);

        // Add the real message
        const realId = Number(realMsg.id);
        if (!state.messageIds.has(realId)) {
            state.messageIds.add(realId);
            state.messages.push(realMsg);
            if (realId > state.lastMessageId) state.lastMessageId = realId;
            state.messages.sort((a, b) => Number(a.id) - Number(b.id));

            const list = board.querySelector(".chat-message-list");
            if (list) {
                list.appendChild(buildMessageEl(realMsg));
                scrollToBottom();
            }
        }
    }

    // ─── Full renders (only when structure changes) ───────────────────────────

    function renderRoomList() {
        const sidebar = root.querySelector(".chat-sidebar");
        if (!sidebar) return;
        // Only update the list portion
        let listWrap = sidebar.querySelector(".chat-rooms-wrap");
        if (!listWrap) return;

        const rooms = filteredRooms();
        if (!state.rooms.length) {
            listWrap.innerHTML = `<div class="chat-room-empty"><i class="fas fa-comments"></i><h4>Chưa có phòng chat</h4><p class="mb-0">Bạn chưa có lớp học phù hợp để tham gia chat.</p></div>`;
            return;
        }
        if (!rooms.length) {
            listWrap.innerHTML = `<div class="chat-room-empty"><i class="fas fa-search"></i><h4>Không tìm thấy phòng chat</h4><p class="mb-0">Thử tìm theo tên lớp, khóa học hoặc giáo viên.</p></div>`;
            return;
        }
        listWrap.innerHTML = `<div class="chat-room-list">${rooms
            .map(
                (room) => `
            <button type="button" class="chat-room-item ${state.selectedRoom && Number(state.selectedRoom.id) === Number(room.id) ? "is-active" : ""}" data-room-id="${room.id}">
                <div class="chat-room-row">
                    <div class="chat-room-avatar">${esc(roomInitials(room))}</div>
                    <div class="chat-room-content">
                        <div class="chat-room-top">
                            <div class="chat-room-name">${esc(room.name)}</div>
                            ${room.unreadCount > 0 ? `<span class="chat-room-badge">${room.unreadCount}</span>` : `<span class="chat-room-time">${esc(room.lastMessageAtLabel || "")}</span>`}
                        </div>
                        <div class="chat-room-course">${esc(room.courseName || room.className || "")}</div>
                        <div class="chat-room-preview">${esc(room.lastMessagePreview || "Chưa có tin nhắn")}</div>
                        <div class="chat-room-meta">
                            <span>${esc(room.teacherName || "Chưa có giáo viên")}</span>
                            <span class="chat-room-state"><i class="fas ${room.canAccess ? "fa-lock-open" : "fa-lock"}"></i> ${room.canAccess ? "Đã tham gia" : "Cần tham gia"}</span>
                        </div>
                    </div>
                </div>
            </button>`,
            )
            .join("")}</div>`;
    }

    function renderMessageBoard() {
        const board = document.getElementById("chat-message-board");
        if (!board) return;

        if (!state.selectedRoom) {
            board.innerHTML = `<div class="chat-message-empty"><i class="fas fa-comments"></i><h4>Chọn một phòng chat</h4><p class="mb-0">Danh sách nhóm lớp của bạn sẽ hiển thị ở cột bên trái.</p></div>`;
            return;
        }

        if (!state.selectedRoom.canAccess) {
            board.innerHTML = state.selectedRoom.canJoin
                ? `<div class="chat-join-box"><i class="fas fa-user-plus"></i><h4>Tham gia nhóm chat lớp</h4><p class="mb-2">Nhóm <strong>${esc(state.selectedRoom.name)}</strong> hiện chưa được bạn tham gia.</p><form id="chat-join-form" class="chat-join-form"><button type="submit" class="chat-join-btn">Tham gia nhóm chat</button></form></div>`
                : `<div class="chat-join-box"><i class="fas fa-comments-slash"></i><h4>Phòng chat chưa mở</h4><p class="mb-0">Bạn chưa thể vào nhóm <strong>${esc(state.selectedRoom.name)}</strong> ở giai đoạn hiện tại.</p></div>`;
            return;
        }

        if (!state.messagesLoaded) {
            board.innerHTML = `<div class="chat-message-empty"><i class="fas fa-spinner fa-spin"></i><h4>Đang tải tin nhắn...</h4></div>`;
            return;
        }

        if (!state.messages.length) {
            board.innerHTML = `<div class="chat-message-empty"><i class="fas fa-paper-plane"></i><h4>Chưa có tin nhắn</h4><p class="mb-0">Hãy gửi tin nhắn đầu tiên để bắt đầu trao đổi trong lớp học.</p></div>`;
            return;
        }

        board.className = `chat-message-board has-messages`;
        board.innerHTML = `<div class="chat-message-list">${state.messages.map((m) => buildMessageEl(m).outerHTML).join("")}</div>`;
    }

    function renderComposer() {
        const composerWrap = root.querySelector(".chat-composer-wrap");
        if (!composerWrap) return;

        if (!state.selectedRoom || !state.selectedRoom.canAccess) {
            composerWrap.innerHTML = "";
            return;
        }

        if (!state.selectedRoom.canSend) {
            composerWrap.innerHTML = `<div class="chat-composer"><div class="text-muted small">Bạn hiện không thể gửi tin nhắn trong nhóm chat này.</div></div>`;
            return;
        }

        // Only re-render if button disabled state changed (avoid textarea refocus)
        const existingBtn = composerWrap.querySelector(".chat-send-btn");
        if (existingBtn) {
            existingBtn.disabled = state.submitting;
            return;
        }

        composerWrap.innerHTML = `
            <div class="chat-composer">
                <form id="chat-send-form" class="chat-composer-form">
                    <div class="chat-composer-tools">
                        <button type="button" class="chat-tool-btn" title="Tệp đính kèm (sắp có)" disabled><i class="fas fa-paperclip"></i></button>
                        <button type="button" class="chat-tool-btn" title="Ảnh (sắp có)" disabled><i class="fas fa-image"></i></button>
                    </div>
                    <textarea id="chat-message-input" placeholder="Nhập tin nhắn cho lớp học của bạn..."></textarea>
                    <button type="submit" class="chat-send-btn" ${state.submitting ? "disabled" : ""}><i class="fas fa-paper-plane me-1"></i> Gửi</button>
                </form>
            </div>`;
    }

    /** Full initial render — called once or when switching rooms */
    function renderApp() {
        root.innerHTML = `
            <div class="chat-layout">
                <aside class="chat-sidebar ${state.mobileSidebarOpen ? "is-open" : ""}">
                    <div class="chat-sidebar-header">
                        <h3 class="chat-sidebar-title">Phòng chat của tôi</h3>
                        <p class="chat-sidebar-note">Hiển thị các nhóm lớp học bạn có thể tham gia hoặc đã tham gia.</p>
                        <div class="chat-sidebar-search"><i class="fas fa-search"></i>
                            <input id="chat-room-search" type="text" placeholder="Tìm theo lớp, khóa học, giáo viên" value="${esc(state.roomQuery)}">
                        </div>
                    </div>
                    <div class="chat-rooms-wrap"></div>
                </aside>
                ${state.mobileSidebarOpen ? '<button type="button" class="chat-sidebar-backdrop" data-close-rooms></button>' : ""}
                <div class="chat-main">
                    <div class="chat-main-header">
                        <div class="chat-main-header-row">
                            <button type="button" class="chat-mobile-rooms-btn" data-toggle-rooms><i class="fas fa-bars"></i></button>
                            <div class="chat-main-avatar">${esc(state.selectedRoom ? roomInitials(state.selectedRoom) : "CH")}</div>
                            <div class="chat-main-summary">
                                <h3 class="chat-main-title">${esc(state.selectedRoom?.name || "Chat lớp học")}</h3>
                                <p class="chat-main-subtitle">${
                                    state.selectedRoom
                                        ? `${esc(state.selectedRoom.className || "")}${state.selectedRoom.courseName ? ` • ${esc(state.selectedRoom.courseName)}` : ""} • GV: ${esc(state.selectedRoom.teacherName || "Chưa phân công")}`
                                        : "Chọn một nhóm chat ở cột bên trái để bắt đầu."
                                }</p>
                            </div>
                            ${state.selectedRoom ? `<div class="chat-main-status ${state.selectedRoom.canAccess ? "is-live" : ""}">${state.selectedRoom.canAccess ? "Đang hoạt động" : "Chưa tham gia"}</div>` : ""}
                        </div>
                    </div>
                    <div id="chat-inline-alert" class="chat-inline-alert"></div>
                    <div id="chat-message-board" class="chat-message-board"></div>
                    <div class="chat-composer-wrap"></div>
                </div>
            </div>`;

        renderRoomList();
        renderMessageBoard();
        renderComposer();
    }

    // ─── Polling ──────────────────────────────────────────────────────────────

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
                // No body, no heavy overhead
            })
                .then((r) => (r.ok ? r.json() : null))
                .catch(() => null);

            // Guard: room may have changed while we were waiting
            if (!state.selectedRoom || Number(state.selectedRoom.id) !== roomId)
                return;

            if (
                data &&
                data.status === "ok" &&
                Array.isArray(data.messages) &&
                data.messages.length
            ) {
                if (data.room) syncRoomInList(data.room);

                const stick = nearBottom();
                const appended = appendNewMessages(data.messages, {
                    forceScrollToBottom: stick,
                });
                if (appended) {
                    // Update unread count to 0 in sidebar silently
                    syncRoomInList({ ...data.room, unreadCount: 0 });
                    renderRoomList();
                    // Mark read silently
                    markReadSilently(roomId, state.lastMessageId);
                }
            }
        } catch (_) {
            // Network error — try again soon
        } finally {
            polling = false;
            schedulePoll();
        }
    }

    async function markReadSilently(roomId, lastId) {
        try {
            await fetch(ep(BS.endpoints.read, roomId), {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": BS.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ lastMessageId: lastId }),
            });
        } catch (_) {}
    }

    // ─── Room polling (sidebar) ───────────────────────────────────────────────

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
                const next = Array.isArray(data.rooms) ? data.rooms : [];
                const prev = JSON.stringify(
                    state.rooms.map((r) => ({
                        id: r.id,
                        unreadCount: r.unreadCount,
                        lastMessagePreview: r.lastMessagePreview,
                    })),
                );
                const nextS = JSON.stringify(
                    next.map((r) => ({
                        id: r.id,
                        unreadCount: r.unreadCount,
                        lastMessagePreview: r.lastMessagePreview,
                    })),
                );
                if (prev !== nextS) {
                    state.rooms = next;
                    if (state.selectedRoom) {
                        const refreshed = roomById(state.selectedRoom.id);
                        if (refreshed)
                            state.selectedRoom = {
                                ...state.selectedRoom,
                                ...refreshed,
                            };
                    }
                    renderRoomList();
                }
            } catch (_) {}
        }, ROOM_MS);
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    async function selectRoom(roomId) {
        const room = roomById(roomId);
        if (!room) return;

        stopPoll();
        notice("", "");

        state.mobileSidebarOpen = false;
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

                const msgs = Array.isArray(data.messages) ? data.messages : [];
                msgs.forEach((m) => {
                    const id = Number(m.id);
                    state.messageIds.add(id);
                    state.messages.push(m);
                    if (id > state.lastMessageId) state.lastMessageId = id;
                });

                renderMessageBoard();
                renderComposer();
                renderRoomList();
                scrollToBottom();
            } catch (err) {
                notice("error", err.payload?.message || err.message);
            }
        }

        schedulePoll(100); // First poll soon after load
    }

    async function joinRoom() {
        if (!state.selectedRoom) return;
        try {
            const data = await api(
                ep(BS.endpoints.join, state.selectedRoom.id),
                { method: "POST", body: JSON.stringify({}) },
            );
            syncRoomInList(data.room);
            state.selectedRoom = data.room;
            state.messagesLoaded = false;
            notice("success", data.message || "Tham gia nhóm chat thành công.");
            renderMessageBoard();
            renderComposer();
            await selectRoom(state.selectedRoom.id);
        } catch (err) {
            notice("error", err.payload?.message || err.message);
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

        // Disable send button
        const btn = root.querySelector(".chat-send-btn");
        if (btn) btn.disabled = true;

        // Optimistic bubble
        const pendingId = "p_" + Date.now();
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
            // First message — re-render board structure
            state.messages.push(optimistic);
            state.messagesLoaded = true;
            renderMessageBoard();
        } else {
            state.messages.push(optimistic);
            list.appendChild(buildMessageEl(optimistic));
        }
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
            notice("", "");
        } catch (err) {
            // Remove optimistic on failure, restore draft
            const list2 = board?.querySelector(".chat-message-list");
            const pendingEl = list2?.querySelector(
                `[data-pending="${CSS.escape(pendingId)}"]`,
            );
            if (pendingEl) pendingEl.remove();
            state.messages = state.messages.filter((m) => m.id !== pendingId);
            input.value = text;
            state.messageDraft = text;
            notice("error", err.payload?.message || err.message);
        } finally {
            state.submitting = false;
            if (btn) btn.disabled = false;
            input.focus();
        }
    }

    // ─── Events ───────────────────────────────────────────────────────────────

    root.addEventListener("click", (e) => {
        if (e.target.closest("[data-toggle-rooms]")) {
            state.mobileSidebarOpen = !state.mobileSidebarOpen;
            root.querySelector(".chat-sidebar")?.classList.toggle(
                "is-open",
                state.mobileSidebarOpen,
            );
            const backdrop = root.querySelector(".chat-sidebar-backdrop");
            if (state.mobileSidebarOpen && !backdrop) {
                const b = document.createElement("button");
                b.type = "button";
                b.className = "chat-sidebar-backdrop";
                b.dataset.closeRooms = "";
                root.querySelector(".chat-layout")?.insertBefore(
                    b,
                    root.querySelector(".chat-main"),
                );
            } else if (!state.mobileSidebarOpen && backdrop) {
                backdrop.remove();
            }
            return;
        }
        if (e.target.closest("[data-close-rooms]")) {
            state.mobileSidebarOpen = false;
            root.querySelector(".chat-sidebar")?.classList.remove("is-open");
            root.querySelector(".chat-sidebar-backdrop")?.remove();
            return;
        }
        const roomBtn = e.target.closest("[data-room-id]");
        if (roomBtn) selectRoom(Number(roomBtn.dataset.roomId));
    });

    root.addEventListener("submit", (e) => {
        if (e.target.id === "chat-join-form") {
            e.preventDefault();
            joinRoom();
        }
        if (e.target.id === "chat-send-form") {
            e.preventDefault();
            sendMessage();
        }
    });

    root.addEventListener("input", (e) => {
        if (e.target.id === "chat-message-input") {
            state.messageDraft = e.target.value || "";
        }
        if (e.target.id === "chat-room-search") {
            state.roomQuery = e.target.value || "";
            renderRoomList();
        }
    });

    root.addEventListener("keydown", (e) => {
        if (
            e.target.id === "chat-message-input" &&
            e.key === "Enter" &&
            !e.shiftKey
        ) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
            stopPoll();
        } else {
            schedulePoll(200);
        }
    });

    window.addEventListener("beforeunload", () => {
        stopPoll();
        stopRoomPoll();
    });

    // ─── Init ─────────────────────────────────────────────────────────────────

    renderApp();
    startRoomPoll();

    if (state.selectedRoom) {
        if (state.selectedRoom.canAccess) {
            // Load messages on init
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
                    const msgs = Array.isArray(data.messages)
                        ? data.messages
                        : [];
                    msgs.forEach((m) => {
                        const id = Number(m.id);
                        state.messageIds.add(id);
                        state.messages.push(m);
                        if (id > state.lastMessageId) state.lastMessageId = id;
                    });
                    renderMessageBoard();
                    renderComposer();
                    renderRoomList();
                    scrollToBottom();
                } catch (_) {}
                schedulePoll(200);
            })();
        } else {
            setUrl(state.selectedRoom.id);
        }
    }
})();
