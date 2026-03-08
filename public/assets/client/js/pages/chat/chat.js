(function () {
    const bootstrap = window.CHAT_BOOTSTRAP;
    const root = document.getElementById('chat-app');

    if (!bootstrap || !root) {
        return;
    }

    const state = {
        rooms: Array.isArray(bootstrap.rooms) ? bootstrap.rooms : [],
        selectedRoom: bootstrap.selectedRoom || null,
        messages: Array.isArray(bootstrap.initialMessages) ? bootstrap.initialMessages : [],
        submitting: false,
        roomQuery: '',
        mobileSidebarOpen: false,
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function endpoint(template, roomId) {
        return template.replace('__ROOM__', roomId);
    }

    function roomById(roomId) {
        return state.rooms.find((room) => Number(room.id) === Number(roomId)) || null;
    }

    function roomInitials(room) {
        const source = String(room?.name || room?.className || 'CH').trim();
        return source
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('') || 'CH';
    }

    function filteredRooms() {
        const query = state.roomQuery.trim().toLowerCase();
        if (!query) {
            return state.rooms;
        }

        return state.rooms.filter((room) => {
            const haystack = [
                room.name,
                room.className,
                room.courseName,
                room.teacherName,
                room.lastMessagePreview,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(query);
        });
    }

    function syncRoom(updatedRoom) {
        if (!updatedRoom) {
            return;
        }

        const index = state.rooms.findIndex((room) => Number(room.id) === Number(updatedRoom.id));
        if (index >= 0) {
            state.rooms[index] = { ...state.rooms[index], ...updatedRoom };
        } else {
            state.rooms.unshift(updatedRoom);
        }

        if (state.selectedRoom && Number(state.selectedRoom.id) === Number(updatedRoom.id)) {
            state.selectedRoom = { ...state.selectedRoom, ...updatedRoom };
        }
    }

    function setNotice(type, message) {
        const notice = document.getElementById('chat-inline-alert');
        if (!notice) {
            return;
        }

        if (!message) {
            notice.style.display = 'none';
            notice.textContent = '';
            notice.className = 'chat-inline-alert';
            return;
        }

        notice.style.display = 'block';
        notice.textContent = message;
        notice.className = `chat-inline-alert is-${type}`;
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': bootstrap.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message || 'Đã có lỗi xảy ra.');
            error.status = response.status;
            error.payload = data;
            throw error;
        }

        return data;
    }

    function updateRoomUrl(roomId) {
        const url = new URL(window.location.href);
        if (roomId) {
            url.searchParams.set('room', roomId);
        } else {
            url.searchParams.delete('room');
        }
        window.history.replaceState({}, '', url.toString());
    }

    function renderRoomList() {
        const rooms = filteredRooms();

        if (!state.rooms.length) {
            return `
                <div class="chat-room-empty">
                    <i class="fas fa-comments"></i>
                    <h4>Chưa có phòng chat</h4>
                    <p class="mb-0">Bạn chưa có lớp học phù hợp để tham gia chat.</p>
                </div>
            `;
        }

        if (!rooms.length) {
            return `
                <div class="chat-room-empty">
                    <i class="fas fa-search"></i>
                    <h4>Không tìm thấy phòng chat</h4>
                    <p class="mb-0">Thử tìm theo tên lớp, khóa học hoặc giáo viên.</p>
                </div>
            `;
        }

        return `
            <div class="chat-room-list">
                ${rooms.map((room) => `
                    <button type="button"
                        class="chat-room-item ${state.selectedRoom && Number(state.selectedRoom.id) === Number(room.id) ? 'is-active' : ''}"
                        data-room-id="${room.id}">
                        <div class="chat-room-row">
                            <div class="chat-room-avatar">${escapeHtml(roomInitials(room))}</div>
                            <div class="chat-room-content">
                                <div class="chat-room-top">
                                    <div class="chat-room-name">${escapeHtml(room.name)}</div>
                                    ${room.unreadCount > 0 ? `<span class="chat-room-badge">${room.unreadCount}</span>` : `<span class="chat-room-time">${escapeHtml(room.lastMessageAtLabel || '')}</span>`}
                                </div>
                                <div class="chat-room-course">${escapeHtml(room.courseName || room.className || '')}</div>
                                <div class="chat-room-preview">${escapeHtml(room.lastMessagePreview || 'Chưa có tin nhắn')}</div>
                                <div class="chat-room-meta">
                                    <span>${escapeHtml(room.teacherName || 'Chưa có giáo viên')}</span>
                                    <span class="chat-room-state">
                                        <i class="fas ${room.canAccess ? 'fa-lock-open' : 'fa-lock'}"></i>
                                        ${room.canAccess ? 'Đã tham gia' : 'Cần tham gia'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                `).join('')}
            </div>
        `;
    }

    function renderMessages() {
        if (!state.selectedRoom) {
            return `
                <div class="chat-message-empty">
                    <i class="fas fa-comments"></i>
                    <h4>Chọn một phòng chat</h4>
                    <p class="mb-0">Danh sách nhóm lớp của bạn sẽ hiển thị ở cột bên trái.</p>
                </div>
            `;
        }

        if (!state.selectedRoom.canAccess) {
            if (!state.selectedRoom.canJoin) {
                return `
                    <div class="chat-join-box">
                        <i class="fas fa-comments-slash"></i>
                        <h4>Phòng chat chưa mở</h4>
                        <p class="mb-0">
                            Bạn chưa thể vào nhóm <strong>${escapeHtml(state.selectedRoom.name)}</strong> ở giai đoạn hiện tại.
                        </p>
                    </div>
                `;
            }

            return `
                <div class="chat-join-box">
                    <i class="fas fa-user-plus"></i>
                    <h4>Tham gia nhóm chat lớp</h4>
                    <p class="mb-2">
                        Nhóm <strong>${escapeHtml(state.selectedRoom.name)}</strong> hiện chưa được bạn tham gia.
                    </p>
                    <form id="chat-join-form" class="chat-join-form">
                        <button type="submit" class="chat-join-btn">Tham gia nhóm chat</button>
                    </form>
                </div>
            `;
        }

        if (!state.messages.length) {
            return `
                <div class="chat-message-empty">
                    <i class="fas fa-paper-plane"></i>
                    <h4>Chưa có tin nhắn</h4>
                    <p class="mb-0">Hãy gửi tin nhắn đầu tiên để bắt đầu trao đổi trong lớp học.</p>
                </div>
            `;
        }

        return `
            <div class="chat-message-list">
                ${state.messages.map((message) => `
                    <div class="chat-message-row ${message.isMine ? 'is-mine' : ''}">
                        ${message.isMine ? '' : `<div class="chat-message-sender">${escapeHtml(message.senderName)}</div>`}
                        <div class="chat-message-bubble">
                            ${message.replyTo ? `
                                <div class="chat-reply-box">
                                    <div><strong>${escapeHtml(message.replyTo.senderName)}</strong></div>
                                    <div>${escapeHtml(message.replyTo.content)}</div>
                                </div>
                            ` : ''}
                            <div class="chat-message-text">${escapeHtml(message.content)}</div>
                            <div class="chat-message-time">${escapeHtml(message.sentAtLabel || '')}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function renderComposer() {
        if (!state.selectedRoom) {
            return '';
        }

        if (!state.selectedRoom.canAccess) {
            return '';
        }

        if (!state.selectedRoom.canSend) {
            return `
                <div class="chat-composer">
                    <div class="text-muted small">Bạn hiện không thể gửi tin nhắn trong nhóm chat này.</div>
                </div>
            `;
        }

        return `
            <div class="chat-composer">
                <form id="chat-send-form" class="chat-composer-form">
                    <div class="chat-composer-tools">
                        <button type="button" class="chat-tool-btn" title="Tệp đính kèm (sắp có)" disabled>
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="button" class="chat-tool-btn" title="Ảnh (sắp có)" disabled>
                            <i class="fas fa-image"></i>
                        </button>
                    </div>
                    <textarea id="chat-message-input" placeholder="Nhập tin nhắn cho lớp học của bạn..."></textarea>
                    <button type="submit" class="chat-send-btn" ${state.submitting ? 'disabled' : ''}>
                        <i class="fas fa-paper-plane me-1"></i> Gửi
                    </button>
                </form>
            </div>
        `;
    }

    function renderMainPanel() {
        const room = state.selectedRoom;

        return `
            <div class="chat-main">
                <div class="chat-main-header">
                    <div class="chat-main-header-row">
                        <button type="button" class="chat-mobile-rooms-btn" data-toggle-rooms>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="chat-main-avatar">${escapeHtml(room ? roomInitials(room) : 'CH')}</div>
                        <div class="chat-main-summary">
                            <h3 class="chat-main-title">${escapeHtml(room?.name || 'Chat lớp học')}</h3>
                            <p class="chat-main-subtitle">
                                ${room
                                    ? `${escapeHtml(room.className || '')}${room.courseName ? ` • ${escapeHtml(room.courseName)}` : ''} • GV: ${escapeHtml(room.teacherName || 'Chưa phân công')}`
                                    : 'Chọn một nhóm chat ở cột bên trái để bắt đầu.'}
                            </p>
                        </div>
                        ${room ? `<div class="chat-main-status ${room.canAccess ? 'is-live' : ''}">${room.canAccess ? 'Đang hoạt động' : 'Chưa tham gia'}</div>` : ''}
                    </div>
                </div>
                <div id="chat-inline-alert" class="chat-inline-alert"></div>
                <div id="chat-message-board" class="chat-message-board ${state.selectedRoom && state.selectedRoom.canAccess && state.messages.length ? 'has-messages' : ''}">${renderMessages()}</div>
                ${renderComposer()}
            </div>
        `;
    }

    function renderApp() {
        root.innerHTML = `
            <div class="chat-layout">
                <aside class="chat-sidebar ${state.mobileSidebarOpen ? 'is-open' : ''}">
                    <div class="chat-sidebar-header">
                        <h3 class="chat-sidebar-title">Phòng chat của tôi</h3>
                        <p class="chat-sidebar-note">Hiển thị các nhóm lớp học bạn có thể tham gia hoặc đã tham gia.</p>
                        <div class="chat-sidebar-search">
                            <i class="fas fa-search"></i>
                            <input
                                id="chat-room-search"
                                type="text"
                                placeholder="Tìm theo lớp, khóa học, giáo viên"
                                value="${escapeHtml(state.roomQuery)}"
                            >
                        </div>
                    </div>
                    ${renderRoomList()}
                </aside>
                ${state.mobileSidebarOpen ? '<button type="button" class="chat-sidebar-backdrop" data-close-rooms></button>' : ''}
                ${renderMainPanel()}
            </div>
        `;

        const board = document.getElementById('chat-message-board');
        if (board && state.selectedRoom && state.selectedRoom.canAccess) {
            board.scrollTop = board.scrollHeight;
        }
    }

    async function loadMessages(roomId) {
        const data = await requestJson(endpoint(bootstrap.endpoints.messages, roomId));
        syncRoom(data.room);
        state.selectedRoom = data.room;
        state.messages = Array.isArray(data.messages) ? data.messages : [];
        updateRoomUrl(roomId);
        renderApp();
    }

    async function selectRoom(roomId) {
        const room = roomById(roomId);
        if (!room) {
            return;
        }

        setNotice('', '');
        state.mobileSidebarOpen = false;
        state.selectedRoom = room;
        state.messages = [];
        renderApp();

        if (room.canAccess) {
            try {
                await loadMessages(roomId);
            } catch (error) {
                setNotice('error', error.payload?.message || error.message);
            }
        } else {
            updateRoomUrl(roomId);
        }
    }

    async function joinSelectedRoom() {
        if (!state.selectedRoom) {
            return;
        }

        try {
            const data = await requestJson(endpoint(bootstrap.endpoints.join, state.selectedRoom.id), {
                method: 'POST',
                body: JSON.stringify({}),
            });

            syncRoom(data.room);
            state.selectedRoom = data.room;
            setNotice('success', data.message || 'Tham gia nhóm chat thành công.');
            await loadMessages(state.selectedRoom.id);
        } catch (error) {
            setNotice('error', error.payload?.message || error.message);
        }
    }

    async function sendMessage() {
        const input = document.getElementById('chat-message-input');
        if (!input || !state.selectedRoom) {
            return;
        }

        const message = input.value.trim();
        if (!message || state.submitting) {
            return;
        }

        state.submitting = true;
        renderApp();
        const currentInput = document.getElementById('chat-message-input');
        if (currentInput) {
            currentInput.value = message;
            currentInput.focus();
        }

        try {
            const data = await requestJson(bootstrap.endpoints.send, {
                method: 'POST',
                body: JSON.stringify({
                    roomId: state.selectedRoom.id,
                    message,
                }),
            });

            state.messages.push(data.chatMessage);
            input.value = '';
            syncRoom(data.room);
            state.selectedRoom = data.room;
            state.selectedRoom.unreadCount = 0;
            setNotice('', '');
        } catch (error) {
            setNotice('error', error.payload?.message || error.message);
        } finally {
            state.submitting = false;
            renderApp();
        }
    }

    root.addEventListener('click', function (event) {
        const toggleRooms = event.target.closest('[data-toggle-rooms]');
        if (toggleRooms) {
            state.mobileSidebarOpen = !state.mobileSidebarOpen;
            renderApp();
            return;
        }

        const closeRooms = event.target.closest('[data-close-rooms]');
        if (closeRooms) {
            state.mobileSidebarOpen = false;
            renderApp();
            return;
        }

        const roomButton = event.target.closest('[data-room-id]');
        if (roomButton) {
            selectRoom(Number(roomButton.dataset.roomId));
        }
    });

    root.addEventListener('submit', function (event) {
        if (event.target.id === 'chat-join-form') {
            event.preventDefault();
            joinSelectedRoom();
        }

        if (event.target.id === 'chat-send-form') {
            event.preventDefault();
            sendMessage();
        }
    });

    root.addEventListener('input', function (event) {
        if (event.target.id === 'chat-room-search') {
            state.roomQuery = event.target.value || '';
            renderApp();
        }
    });

    root.addEventListener('keydown', function (event) {
        if (event.target.id === 'chat-message-input' && event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    renderApp();
})();
