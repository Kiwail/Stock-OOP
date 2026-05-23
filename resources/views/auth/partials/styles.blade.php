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
        --red: #ff6b6b;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Instrument Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--text);
        background:
            linear-gradient(120deg, rgba(255, 138, 61, .12), transparent 28%),
            linear-gradient(180deg, #111722 0%, var(--bg) 52%, #090c11 100%);
    }

    a {
        color: inherit;
        text-decoration: none;
    }

    .auth-page {
        width: min(1060px, calc(100% - 32px));
        min-height: 100vh;
        margin: 0 auto;
        padding: 24px 0 36px;
        display: flex;
        flex-direction: column;
        gap: 42px;
    }

    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 20px;
    }

    .brand-mark {
        width: 40px;
        height: 40px;
        border: 1px solid rgba(255, 138, 61, .45);
        border-radius: 8px;
        background: linear-gradient(145deg, #26200f, #121821);
        display: grid;
        place-items: center;
        color: var(--accent);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .04);
    }

    .brand-mark svg,
    .auth-icon svg {
        width: 20px;
        height: 20px;
    }

    .top-link {
        min-height: 40px;
        padding: 0 16px;
        border: 1px solid var(--line);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text);
        background: rgba(255, 255, 255, .03);
        font-weight: 600;
        font-size: 14px;
    }

    .auth-shell {
        flex: 1;
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(390px, 460px);
        gap: 34px;
        align-items: center;
    }

    .auth-copy {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .eyebrow {
        width: fit-content;
        padding: 6px 10px;
        border: 1px solid rgba(255, 138, 61, .35);
        border-radius: 999px;
        color: #ffd2b5;
        background: rgba(255, 138, 61, .08);
        font-size: 13px;
        font-weight: 600;
    }

    h1 {
        margin: 0;
        max-width: 590px;
        font-size: clamp(38px, 6vw, 70px);
        line-height: .98;
        letter-spacing: 0;
    }

    .lead {
        max-width: 560px;
        margin: 0;
        color: var(--muted);
        font-size: 17px;
        line-height: 1.65;
    }

    .auth-points {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        max-width: 560px;
    }

    .auth-point {
        min-height: 86px;
        padding: 15px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: rgba(20, 26, 35, .72);
    }

    .auth-point strong,
    .auth-card-title strong {
        display: block;
        font-size: 15px;
    }

    .auth-point span,
    .auth-card-title span {
        display: block;
        margin-top: 5px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .auth-card {
        border: 1px solid var(--line);
        border-radius: 8px;
        background: rgba(14, 19, 27, .9);
        box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
        overflow: hidden;
    }

    .auth-card-head {
        min-height: 78px;
        padding: 18px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .01));
    }

    .auth-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        color: var(--accent);
        background: #202a37;
    }

    .auth-card-body {
        padding: 22px;
    }

    .errors {
        margin: 0 0 18px;
        padding: 12px 14px;
        border: 1px solid rgba(255, 107, 107, .45);
        border-radius: 8px;
        color: #ffd1d1;
        background: rgba(255, 107, 107, .1);
        list-style-position: inside;
        font-size: 14px;
        line-height: 1.55;
    }

    .auth-form {
        display: grid;
        gap: 15px;
    }

    .field {
        display: grid;
        gap: 8px;
        color: var(--text);
        font-size: 14px;
        font-weight: 600;
    }

    .field input {
        width: 100%;
        min-height: 46px;
        padding: 0 13px;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--text);
        background: var(--panel);
        font: inherit;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .field input:focus {
        border-color: rgba(255, 138, 61, .72);
        background: var(--panel-strong);
        box-shadow: 0 0 0 3px rgba(255, 138, 61, .14);
    }

    .check {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--muted);
        font-size: 14px;
        font-weight: 600;
    }

    .check input {
        width: 16px;
        height: 16px;
        accent-color: var(--accent);
    }

    .button {
        width: 100%;
        min-height: 46px;
        padding: 0 18px;
        border: 1px solid rgba(255, 138, 61, .6);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #15100b;
        background: linear-gradient(180deg, var(--accent), var(--accent-strong));
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    .auth-switch {
        margin: 18px 0 0;
        color: var(--muted);
        text-align: center;
        font-size: 14px;
    }

    .auth-switch a {
        color: #ffd2b5;
        font-weight: 700;
    }

    @media (max-width: 860px) {
        .auth-shell {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .auth-copy {
            padding-top: 8px;
        }
    }

    @media (max-width: 620px) {
        .auth-page {
            width: min(100% - 22px, 1060px);
            padding-top: 16px;
            gap: 28px;
        }

        .topbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .top-link {
            width: 100%;
        }

        .auth-points {
            grid-template-columns: 1fr;
        }

        .auth-card-body {
            padding: 18px;
        }
    }
</style>
