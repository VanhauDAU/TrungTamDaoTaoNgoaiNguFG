<style>
    .report-ui {
        --report-panel: rgba(255, 255, 255, 0.94);
        --report-border: rgba(148, 163, 184, 0.18);
        --report-text: #0f172a;
        --report-muted: #64748b;
        --report-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
        --report-accent-soft: rgba(29, 78, 216, 0.1);
        color: var(--report-text);
    }

    .report-ui .report-shell,
    .report-ui .report-stat-grid,
    .report-ui .report-stack,
    .report-ui .report-list,
    .report-ui .report-criteria,
    .report-ui .report-timeline,
    .report-ui .report-filter-grid,
    .report-ui .report-form-grid,
    .report-ui .report-meta-grid,
    .report-ui .report-kpi-grid {
        display: grid;
        gap: 14px;
    }

    .report-ui .report-hero {
        position: relative;
        overflow: hidden;
        padding: 18px 20px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.55);
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 26%),
            radial-gradient(circle at left bottom, rgba(20, 184, 166, 0.12), transparent 28%),
            linear-gradient(135deg, #ffffff 0%, #f4f8fb 100%);
        box-shadow: var(--report-shadow);
    }

    .report-ui .report-hero__content,
    .report-ui .report-section-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(220px, 0.7fr);
        gap: 14px;
        align-items: start;
    }

    .report-ui .report-overline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.05);
        color: var(--report-muted);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .report-ui .report-title {
        margin: 8px 0 5px;
        font-size: clamp(1.3rem, 1.7vw, 1.75rem);
        line-height: 1.15;
        font-weight: 800;
    }

    .report-ui .report-subtitle {
        margin: 0;
        max-width: 680px;
        color: var(--report-muted);
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .report-ui .report-hero__aside {
        display: grid;
        gap: 8px;
        justify-items: end;
    }

    .report-ui .report-chip-wrap,
    .report-ui .report-actions,
    .report-ui .report-inline-list,
    .report-ui .report-row__top,
    .report-ui .report-row__bottom {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .report-ui .report-row__top,
    .report-ui .report-row__bottom {
        align-items: center;
        justify-content: space-between;
    }

    .report-ui .report-chip,
    .report-ui .report-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1;
    }

    .report-ui .report-chip {
        background: rgba(255, 255, 255, 0.76);
        border-color: rgba(148, 163, 184, 0.18);
        color: var(--report-text);
    }

    .report-ui .report-badge {
        background: #f8fafc;
        border-color: #dbe3ee;
        color: #334155;
    }

    .report-ui .report-badge--primary,
    .report-ui .report-stat--primary {
        background: rgba(15, 118, 110, 0.1);
        border-color: rgba(15, 118, 110, 0.14);
        color: #115e59;
    }

    .report-ui .report-badge--info,
    .report-ui .report-stat--info {
        background: rgba(29, 78, 216, 0.1);
        border-color: rgba(29, 78, 216, 0.12);
        color: #1d4ed8;
    }

    .report-ui .report-badge--warning,
    .report-ui .report-stat--warning {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .report-ui .report-badge--success,
    .report-ui .report-stat--success {
        background: rgba(22, 163, 74, 0.12);
        border-color: rgba(22, 163, 74, 0.14);
        color: #15803d;
    }

    .report-ui .report-badge--danger,
    .report-ui .report-stat--danger {
        background: rgba(225, 29, 72, 0.1);
        border-color: rgba(225, 29, 72, 0.14);
        color: #be123c;
    }

    .report-ui .report-panel {
        border: 1px solid var(--report-border);
        border-radius: 18px;
        background: var(--report-panel);
        backdrop-filter: blur(12px);
        box-shadow: var(--report-shadow);
        overflow: hidden;
    }

    .report-ui .report-panel__head,
    .report-ui .report-panel__body,
    .report-ui .report-panel__foot {
        padding: 14px 16px;
    }

    .report-ui .report-panel__head {
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(255, 255, 255, 0.7) 100%);
    }

    .report-ui .report-panel__head h5,
    .report-ui .report-panel__head h4 {
        margin: 0;
        font-weight: 800;
    }

    .report-ui .report-panel__head p {
        margin: 5px 0 0;
        color: var(--report-muted);
        font-size: 0.82rem;
    }

    .report-ui .report-stat-grid {
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
    }

    .report-ui .report-stat {
        min-height: 92px;
        padding: 13px;
        border-radius: 15px;
        border: 1px solid transparent;
        background: #fff;
    }

    .report-ui .report-stat__label {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .report-ui .report-stat__value {
        margin-top: 10px;
        font-size: clamp(1.3rem, 1.8vw, 1.8rem);
        font-weight: 800;
        line-height: 1;
    }

    .report-ui .report-stat__hint,
    .report-ui .report-persona span,
    .report-ui .report-meta,
    .report-ui .report-kv__label,
    .report-ui .report-note,
    .report-ui .report-empty p {
        color: var(--report-muted);
    }

    .report-ui .report-stat__hint {
        margin-top: 5px;
        font-size: 0.76rem;
    }

    .report-ui .report-filter-grid {
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        align-items: end;
    }

    .report-ui .report-form-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .report-ui .report-field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .report-ui .report-field .form-control,
    .report-ui .report-field .form-select,
    .report-ui .report-field textarea,
    .report-ui .report-field input,
    .report-ui .report-field select {
        min-height: 42px;
        padding: 10px 12px;
        border-radius: 13px;
        border-color: #d8e2ee;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: none;
    }

    .report-ui .report-field textarea {
        min-height: 104px;
        resize: vertical;
    }

    .report-ui .report-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 0 13px;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .report-ui .report-button:hover {
        transform: translateY(-1px);
    }

    .report-ui .report-button--primary {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: #fff;
        box-shadow: 0 10px 18px rgba(20, 184, 166, 0.18);
    }

    .report-ui .report-button--secondary {
        background: #fff;
        border-color: #d7e1ec;
        color: #334155;
    }

    .report-ui .report-button--soft {
        background: var(--report-accent-soft);
        color: #1d4ed8;
        border-color: rgba(29, 78, 216, 0.08);
    }

    .report-ui .report-button--danger {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }

    .report-ui .report-row {
        display: grid;
        gap: 10px;
        padding: 13px 14px;
        border-radius: 15px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .report-ui .report-persona {
        display: grid;
        gap: 3px;
    }

    .report-ui .report-persona strong {
        font-size: 0.94rem;
    }

    .report-ui .report-meta-grid {
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    }

    .report-ui .report-kv {
        padding: 10px 11px;
        border-radius: 13px;
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .report-ui .report-kv__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .report-ui .report-kv__value {
        margin-top: 5px;
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.45;
        color: var(--report-text);
    }

    .report-ui .report-progress {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #dfe7ef;
        overflow: hidden;
    }

    .report-ui .report-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    }

    .report-ui .report-empty {
        padding: 22px 16px;
        border-radius: 16px;
        border: 1px dashed #cbd5e1;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        text-align: center;
    }

    .report-ui .report-empty strong {
        display: block;
        margin-bottom: 6px;
        font-size: 0.98rem;
    }

    .report-ui .report-criterion {
        padding: 13px 14px;
        border-radius: 15px;
        border: 1px solid #dde6ef;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .report-ui .report-criterion__head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        align-items: start;
        margin-bottom: 10px;
    }

    .report-ui .report-criterion__title,
    .report-ui .report-timeline__title {
        font-weight: 800;
        color: var(--report-text);
    }

    .report-ui .report-criterion__code {
        margin-top: 3px;
        color: var(--report-muted);
        font-size: 0.78rem;
    }

    .report-ui .report-readonly {
        min-height: 42px;
        padding: 10px 12px;
        border-radius: 13px;
        border: 1px solid #dbe3ee;
        background: #f8fafc;
    }

    .report-ui .report-timeline__item {
        position: relative;
        padding-left: 20px;
    }

    .report-ui .report-timeline__item::before {
        content: "";
        position: absolute;
        top: 6px;
        left: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.1);
    }

    .report-ui .report-timeline__item::after {
        content: "";
        position: absolute;
        top: 18px;
        left: 4px;
        width: 1px;
        height: calc(100% + 10px);
        background: #d9e2ec;
    }

    .report-ui .report-timeline__item:last-child::after {
        display: none;
    }

    .report-ui .report-sticky {
        position: sticky;
        bottom: 12px;
        z-index: 10;
    }

    .report-ui .report-alert {
        padding: 12px 13px;
        border-radius: 14px;
        border: 1px solid #fde68a;
        background: #fff7d6;
        color: #92400e;
    }

    @media (max-width: 1200px) {
        .report-ui .report-hero__content,
        .report-ui .report-section-grid {
            grid-template-columns: 1fr;
        }

        .report-ui .report-hero__aside {
            justify-items: start;
        }
    }

    @media (max-width: 768px) {
        .report-ui .report-hero,
        .report-ui .report-panel__head,
        .report-ui .report-panel__body,
        .report-ui .report-panel__foot {
            padding: 12px;
        }

        .report-ui .report-title {
            font-size: 1.2rem;
        }
    }
</style>
