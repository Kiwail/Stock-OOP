<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

<style>
    :root {
        color-scheme: dark;
        --bg: #0d1117;
        --panel: #141a23;
        --panel-strong: #1b2430;
        --line: #293342;
        --text: #f4f7fb;
        --muted: #9ba8b8;
        --accent: #ff8a3d;
        --accent-strong: #ff6b1a;
        --green: #52d48f;
        --yellow: #f2c94c;
        --red: #ff6b6b;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Instrument Sans", system-ui, sans-serif;
        color: var(--text);
        background:
            linear-gradient(120deg, rgba(255, 138, 61, .1), transparent 28%),
            linear-gradient(180deg, #111722 0%, var(--bg) 52%, #090c11 100%);
    }

    a { color: inherit; text-decoration: none; }

    .app-shell {
        width: min(1240px, calc(100% - 32px));
        margin: 0 auto;
        padding: 20px 0 40px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .app-topbar {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 14px 16px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: rgba(14, 19, 27, .9);
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
        margin-right: auto;
    }

    .brand-mark {
        width: 36px;
        height: 36px;
        border: 1px solid rgba(255, 138, 61, .45);
        border-radius: 8px;
        display: grid;
        place-items: center;
        color: var(--accent);
        background: linear-gradient(145deg, #26200f, #121821);
    }

    .brand-mark svg { width: 18px; height: 18px; }

    .app-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .nav-link {
        min-height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--muted);
        border: 1px solid transparent;
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--text);
        border-color: var(--line);
        background: rgba(255, 255, 255, .04);
    }

    .nav-link-accent {
        color: #ffd2b5;
        border-color: rgba(255, 138, 61, .35);
        background: rgba(255, 138, 61, .1);
    }

    .app-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-meta strong { display: block; font-size: 13px; }
    .user-meta span { display: block; color: var(--muted); font-size: 11px; margin-top: 2px; }

    .button {
        min-height: 36px;
        padding: 0 14px;
        border: 1px solid rgba(255, 138, 61, .6);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #15100b;
        background: linear-gradient(180deg, var(--accent), var(--accent-strong));
        font: inherit;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
    }

    .button.ghost {
        color: var(--text);
        background: rgba(255, 255, 255, .03);
        border-color: var(--line);
    }

    .button.secondary {
        color: var(--text);
        background: var(--panel);
        border-color: var(--line);
    }

    .flash {
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
    }

    .flash.ok {
        border: 1px solid rgba(82, 212, 143, .45);
        background: rgba(82, 212, 143, .12);
        color: #c9f5dc;
    }

    .flash.err {
        border: 1px solid rgba(255, 107, 107, .45);
        background: rgba(255, 107, 107, .1);
        color: #ffd1d1;
    }

    .app-main { display: flex; flex-direction: column; gap: 18px; }

    .page-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-head h1 { margin: 0; font-size: clamp(28px, 4vw, 40px); }
    .page-head p { margin: 6px 0 0; color: var(--muted); font-size: 15px; }

    .subtabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .subtab {
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: var(--muted);
        background: rgba(20, 26, 35, .72);
        font-size: 13px;
        font-weight: 700;
    }

    .subtab:hover,
    .subtab.active {
        color: #15100b;
        border-color: rgba(255, 138, 61, .72);
        background: linear-gradient(180deg, var(--accent), var(--accent-strong));
    }

    .card {
        border: 1px solid var(--line);
        border-radius: 10px;
        background: rgba(14, 19, 27, .88);
        overflow: hidden;
    }

    .modal {
        width: min(1040px, calc(100% - 24px));
        max-height: min(88vh, 900px);
        padding: 0;
        border: 1px solid var(--line);
        border-radius: 10px;
        color: var(--text);
        background: var(--panel);
        box-shadow: 0 24px 90px rgba(0, 0, 0, .58);
    }

    .modal::backdrop {
        background: rgba(3, 6, 10, .72);
    }

    .modal-head {
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: rgba(255, 255, 255, .04);
    }

    .modal-head strong { display: block; font-size: 16px; }
    .modal-head span { display: block; margin-top: 4px; color: var(--muted); font-size: 13px; }
    .modal-body { padding: 18px; overflow: auto; }
    .modal .form-grid { max-width: none; }

    .document-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .document-detail-grid > div,
    .document-detail-comment {
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: rgba(255, 255, 255, .03);
    }

    .document-detail-grid span,
    .document-detail-comment span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
    }

    .document-detail-grid strong {
        display: block;
        margin-top: 6px;
        font-size: 14px;
    }

    .document-detail-comment { margin-bottom: 16px; }
    .document-detail-comment p { margin: 6px 0 0; }
    .document-detail-actions { margin-top: 16px; }

    .card-head {
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .card-body { padding: 18px; }

    .stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .stat {
        padding: 16px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: rgba(20, 26, 35, .72);
    }

    .stat span { display: block; color: var(--muted); font-size: 13px; }
    .stat strong { display: block; margin-top: 8px; font-size: 28px; }

    .table { width: 100%; border-collapse: collapse; }
    .table th,
    .table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--line);
        text-align: left;
        font-size: 14px;
    }

    .table th { color: var(--muted); font-weight: 600; font-size: 12px; }
    .table tr:last-child td { border-bottom: 0; }
    .table tr.clickable-row { cursor: pointer; }
    .table tr.clickable-row:hover td { background: rgba(255, 255, 255, .03); }
    .table small { display: block; color: var(--muted); margin-top: 3px; font-weight: 500; }

    .badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge.ok { color: #11301f; background: var(--green); }
    .badge.warn { color: #2d2408; background: var(--yellow); }
    .badge.low { color: #391111; background: var(--red); }
    .badge.muted { color: var(--muted); background: #202a37; }
    .badge.posted { color: #11301f; background: var(--green); }
    .badge.draft { color: #2d2408; background: var(--yellow); }

    .form-grid { display: grid; gap: 14px; max-width: 860px; }

    .filter-panel summary {
        width: max-content;
        list-style: none;
    }

    .filter-panel summary::-webkit-details-marker { display: none; }

    .filter-panel .filter-form { margin-top: 16px; }

    .field { display: grid; gap: 7px; font-size: 14px; font-weight: 600; }
    .field input,
    .field select,
    .field textarea {
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--text);
        background: var(--panel);
        font: inherit;
    }

    .field textarea { min-height: 90px; padding: 10px 12px; resize: vertical; }

    .line-row {
        display: grid;
        grid-template-columns: 1.6fr 0.9fr 0.7fr 0.9fr 0.7fr auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 10px;
    }

    .line-row .remove-line { min-height: 42px; }

    .line-total {
        color: #ffd2b5;
    }

    .actions { display: flex; gap: 10px; flex-wrap: wrap; }

    .workspace-grid {
        display: grid;
        grid-template-columns: minmax(0, .85fr) minmax(420px, 1.15fr);
        gap: 18px;
        align-items: start;
    }

    .panel,
    .panel-preview {
        border: 1px solid var(--line);
        border-radius: 10px;
        background: rgba(14, 19, 27, .88);
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
    }

    .panel-head,
    .panel-preview-head {
        min-height: 74px;
        padding: 18px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .01));
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .panel-title .icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: #202a37;
        color: var(--accent);
    }

    .panel-title strong { display: block; font-size: 15px; }
    .panel-title span { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; }

    .status {
        padding: 7px 10px;
        border-radius: 999px;
        color: #102016;
        background: var(--green);
        font-size: 12px;
        font-weight: 700;
    }

    .panel .table { border-top: 1px solid var(--line); }

    .panel .row {
        min-height: 68px;
        padding: 14px 18px;
        display: grid;
        grid-template-columns: 1.15fr .8fr .62fr .72fr;
        gap: 14px;
        align-items: center;
        border-bottom: 1px solid var(--line);
    }

    .panel .row:last-child { border-bottom: 0; }

    .panel .cell { font-size: 14px; font-weight: 600; }
    .panel .cell small { display: block; color: var(--muted); margin-top: 3px; font-weight: 500; }

    .panel .documents {
        padding: 18px;
        border-top: 1px solid var(--line);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        background: rgba(255, 255, 255, .02);
    }

    .panel .document {
        min-height: 92px;
        padding: 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel);
    }

    .panel .document strong { display: block; font-size: 14px; }
    .panel .document span { display: block; margin-top: 4px; color: var(--muted); font-size: 12px; }

    .badge.cancelled { color: #391111; background: #9ba8b8; }

    .panel-preview-head {
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .warehouse-map {
        padding: 16px 18px;
        display: grid;
        grid-template-columns: 1fr 130px;
        gap: 14px;
    }

    .shelves { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .shelf {
        min-height: 120px;
        padding: 10px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #111821;
        display: grid;
        align-content: end;
        gap: 7px;
    }

    .box-row {
        height: 18px;
        border-radius: 5px;
        background: repeating-linear-gradient(90deg, #2b3645 0 28px, #374555 28px 30px);
    }

    .box-row.hot {
        background: repeating-linear-gradient(90deg, #7a431f 0 28px, #965226 28px 30px);
    }

    .dock {
        min-height: 120px;
        border: 1px dashed rgba(255, 138, 61, .6);
        border-radius: 8px;
        background: rgba(255, 138, 61, .08);
        display: grid;
        place-items: center;
        text-align: center;
        color: #ffd2b5;
        font-size: 12px;
        font-weight: 700;
    }

    .doc-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 16px 18px;
        border-top: 1px solid var(--line);
    }

    .doc-card {
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel);
    }

    .doc-card strong { display: block; font-size: 14px; }
    .doc-card span { display: block; margin-top: 4px; color: var(--muted); font-size: 12px; }

    @media (max-width: 900px) {
        .workspace-grid,
        .stats,
        .document-detail-grid,
        .doc-cards,
        .line-row { grid-template-columns: 1fr; }
        .app-topbar { flex-direction: column; align-items: stretch; }
        .brand { margin-right: 0; }
    }
</style>
