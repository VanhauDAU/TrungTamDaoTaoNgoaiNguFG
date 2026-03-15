<style>
    .staff-page {
        display: grid;
        gap: 20px;
    }

    .staff-header,
    .staff-card,
    .staff-footer {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .staff-header {
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        background: linear-gradient(135deg, #0f766e, #1d4ed8);
        color: #fff;
    }

    .staff-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .staff-header p {
        margin: 8px 0 0;
        color: rgba(255, 255, 255, 0.8);
    }

    .staff-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .staff-btn {
        border: 0;
        border-radius: 999px;
        padding: 11px 18px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s ease;
    }

    .staff-btn-primary {
        background: #f8fafc;
        color: #0f172a;
    }

    .staff-btn-secondary {
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .staff-btn-muted {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #dbeafe;
    }

    .staff-card {
        overflow: hidden;
    }

    .staff-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .staff-card-header h2 {
        margin: 0;
        font-size: 1.08rem;
        color: #0f172a;
    }

    .staff-card-header p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 0.94rem;
    }

    .staff-card-body {
        padding: 24px;
    }

    .staff-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .staff-field-full {
        grid-column: 1 / -1;
    }

    .staff-label {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.94rem;
        font-weight: 600;
        color: #1e293b;
    }

    .staff-hint {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 500;
    }

    .staff-required {
        color: #dc2626;
    }

    .staff-control,
    .staff-control textarea,
    .staff-control select,
    .staff-control input {
        width: 100%;
    }

    .staff-control input,
    .staff-control select,
    .staff-control textarea {
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.94rem;
        color: #0f172a;
        background: #fff;
    }

    .staff-control input[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    .staff-control textarea {
        min-height: 118px;
        resize: vertical;
    }

    .staff-error-list {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
        border-radius: 18px;
        padding: 18px 22px;
    }

    .staff-error-list ul {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .staff-error {
        display: block;
        color: #dc2626;
        font-size: 0.84rem;
        margin-top: 6px;
    }

    .staff-banner {
        border-radius: 16px;
        padding: 16px 18px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .staff-banner strong {
        display: block;
        margin-bottom: 6px;
    }

    .staff-footer {
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }

    .staff-footer-meta {
        color: #64748b;
        font-size: 0.9rem;
    }

    .staff-inline-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .staff-detail-lines {
        display: grid;
        gap: 12px;
    }

    .staff-detail-row {
        display: grid;
        grid-template-columns: 1.2fr 1.4fr 1fr 1.6fr auto;
        gap: 10px;
        align-items: start;
    }

    .staff-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
        background: #ecfeff;
        color: #0f766e;
    }

    .staff-hidden {
        display: none;
    }

    .staff-cascade-preview {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px dashed #93c5fd;
    }

    @media (max-width: 992px) {
        .staff-grid,
        .staff-inline-grid,
        .staff-detail-row {
            grid-template-columns: 1fr;
        }

        .staff-header {
            padding: 20px;
        }

        .staff-card-body,
        .staff-card-header,
        .staff-footer {
            padding: 18px;
        }
    }
</style>
