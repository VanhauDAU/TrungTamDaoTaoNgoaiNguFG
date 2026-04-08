<style>
    .profile-page {
        display: grid;
        gap: 20px;
    }

    .profile-hero,
    .profile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .profile-hero {
        padding: 26px 28px;
        background: linear-gradient(140deg, #0f172a, #1d4ed8 55%, #0f766e);
        color: #fff;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .profile-hero h1 {
        margin: 0;
        font-size: 1.85rem;
        font-weight: 700;
    }

    .profile-hero p {
        margin: 10px 0 0;
        color: rgba(255, 255, 255, 0.82);
        max-width: 720px;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .profile-btn {
        border: 0;
        border-radius: 999px;
        padding: 11px 16px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .profile-btn-primary {
        background: #fff;
        color: #0f172a;
    }

    .profile-btn-ghost {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 20px;
    }

    .profile-card {
        overflow: hidden;
    }

    .profile-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .profile-card-header h2 {
        margin: 0;
        font-size: 1.06rem;
        color: #0f172a;
    }

    .profile-card-body {
        padding: 22px 24px;
    }

    .profile-kv {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .profile-kv-item {
        padding: 14px 16px;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .profile-kv-item strong {
        display: block;
        color: #0f172a;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .profile-kv-item span {
        color: #475569;
        word-break: break-word;
    }

    .profile-stack {
        display: grid;
        gap: 20px;
    }

    .profile-credential {
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(135deg, #fff7ed, #fffbeb);
        border: 1px solid #fdba74;
    }

    .profile-credential code {
        display: inline-block;
        padding: 6px 10px;
        margin-top: 6px;
        border-radius: 12px;
        background: #0f172a;
        color: #fff;
        font-size: 0.95rem;
    }

    .profile-credential-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .profile-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .profile-status-active {
        background: #ecfdf5;
        color: #047857;
    }

    .profile-status-locked {
        background: #fef2f2;
        color: #b91c1c;
    }

    .profile-doc-list,
    .profile-salary-history {
        display: grid;
        gap: 12px;
    }

    .profile-doc-item,
    .profile-salary-item {
        padding: 15px 16px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .profile-doc-head,
    .profile-salary-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: start;
        flex-wrap: wrap;
    }

    .profile-doc-meta,
    .profile-salary-meta {
        color: #64748b;
        font-size: 0.86rem;
        margin-top: 6px;
    }

    .profile-mini-form {
        display: grid;
        gap: 14px;
    }

    .profile-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .profile-mini-form label {
        display: block;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .profile-mini-form input,
    .profile-mini-form select,
    .profile-mini-form textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 11px 13px;
        background: #fff;
    }

    .profile-mini-form textarea {
        min-height: 110px;
        resize: vertical;
    }

    .profile-alert {
        padding: 14px 16px;
        border-radius: 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .profile-html {
        line-height: 1.7;
        color: #334155;
    }

    .profile-empty {
        color: #64748b;
        font-style: italic;
    }

    @media (max-width: 1024px) {

        .profile-grid,
        .profile-kv,
        .profile-mini-grid {
            grid-template-columns: 1fr;
        }

        .profile-hero,
        .profile-card-header,
        .profile-card-body {
            padding: 18px;
        }
    }

    /* ===== AVATAR ===== */
    .avatar-wrapper {
        position: relative;
        width: 84px;
        height: 84px;
        flex-shrink: 0;
        border-radius: 50%;
        cursor: pointer;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        <<<<<<< Updated upstream transition: transform 0.2s, box-shadow 0.2s;
    }

    .avatar-wrapper:hover {
        transform: scale(1.06);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
        =======>>>>>>>Stashed changes
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 50%;
    }

    .avatar-initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1, #0ea5e9);
        color: #fff;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -1px;
        user-select: none;
    }
</style>
