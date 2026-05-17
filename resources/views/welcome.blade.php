<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>In Stock</title>

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

        .page {
            width: min(1180px, calc(100% - 32px));
            min-height: 100vh;
            margin: 0 auto;
            padding: 24px 0 36px;
            display: flex;
            flex-direction: column;
            gap: 26px;
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
        .icon svg {
            width: 20px;
            height: 20px;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .button {
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text);
            background: rgba(255, 255, 255, .03);
            font-weight: 600;
            font-size: 14px;
        }

        .button.primary {
            border-color: rgba(255, 138, 61, .6);
            background: linear-gradient(180deg, var(--accent), var(--accent-strong));
            color: #15100b;
        }

        .workspace {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, .92fr) minmax(420px, 1.08fr);
            gap: 22px;
            align-items: stretch;
        }

        .hero {
            padding: 34px 0 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
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
            margin: 20px 0 14px;
            max-width: 620px;
            font-size: clamp(42px, 7vw, 82px);
            line-height: .95;
            letter-spacing: 0;
        }

        .lead {
            max-width: 590px;
            margin: 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.65;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            min-height: 96px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(20, 26, 35, .72);
        }

        .stat span {
            display: block;
            color: var(--muted);
            font-size: 13px;
        }

        .stat strong {
            display: block;
            margin-top: 10px;
            font-size: 26px;
            letter-spacing: 0;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(14, 19, 27, .88);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
            overflow: hidden;
        }

        .panel-head {
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

        .icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #202a37;
            color: var(--accent);
        }

        .panel-title strong,
        .document strong {
            display: block;
            font-size: 15px;
        }

        .panel-title span,
        .document span,
        .cell small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .status {
            padding: 7px 10px;
            border-radius: 999px;
            color: #102016;
            background: var(--green);
            font-size: 12px;
            font-weight: 700;
        }

        .warehouse-map {
            padding: 18px;
            display: grid;
            grid-template-columns: 1fr 140px;
            gap: 16px;
        }

        .shelves {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .shelf {
            min-height: 148px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background:
                linear-gradient(90deg, transparent 0 48%, rgba(255, 255, 255, .04) 48% 52%, transparent 52%),
                #111821;
            display: grid;
            align-content: end;
            gap: 8px;
        }

        .box-row {
            height: 22px;
            border-radius: 5px;
            background: repeating-linear-gradient(90deg, #2b3645 0 32px, #374555 32px 34px);
        }

        .box-row.hot {
            background: repeating-linear-gradient(90deg, #7a431f 0 32px, #965226 32px 34px);
        }

        .dock {
            min-height: 148px;
            border: 1px dashed rgba(255, 138, 61, .6);
            border-radius: 8px;
            background: rgba(255, 138, 61, .08);
            display: grid;
            place-items: center;
            text-align: center;
            color: #ffd2b5;
            font-weight: 700;
        }

        .table {
            border-top: 1px solid var(--line);
        }

        .row {
            min-height: 68px;
            padding: 14px 18px;
            display: grid;
            grid-template-columns: 1.15fr .8fr .62fr .72fr;
            gap: 14px;
            align-items: center;
            border-bottom: 1px solid var(--line);
        }

        .row:last-child {
            border-bottom: 0;
        }

        .cell {
            min-width: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .cell small {
            font-weight: 500;
        }

        .badge {
            width: fit-content;
            padding: 6px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.ok {
            color: #11301f;
            background: var(--green);
        }

        .badge.warn {
            color: #2d2408;
            background: var(--yellow);
        }

        .badge.low {
            color: #391111;
            background: var(--red);
        }

        .documents {
            padding: 18px;
            border-top: 1px solid var(--line);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            background: rgba(255, 255, 255, .02);
        }

        .document {
            min-height: 92px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
        }

        @media (max-width: 900px) {
            .workspace,
            .warehouse-map,
            .documents {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 12px;
            }

            .row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 620px) {
            .page {
                width: min(100% - 22px, 1180px);
                padding-top: 16px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .nav,
            .button {
                width: 100%;
            }

            .stats,
            .shelves {
                grid-template-columns: 1fr;
            }

            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <a class="brand" href="{{ url('/') }}" aria-label="In Stock">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="m4.5 8.8 7.5 4.3 7.5-4.3M12 13v6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                In Stock
            </a>

            @if (Route::has('login'))
                <nav class="nav" aria-label="Navigācija">
                    @auth
                        <a class="button primary" href="{{ url('/dashboard') }}">Panelis</a>
                    @else
                        <a class="button" href="{{ route('login') }}">Ieiet</a>

                        @if (Route::has('register'))
                            <a class="button primary" href="{{ route('register') }}">Reģistrācija</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="workspace">
            <section class="hero" aria-labelledby="page-title">
                <div>
                    <div class="eyebrow">Noliktavas uzskaites sistēma</div>
                    <h1 id="page-title">In Stock</h1>
                    <p class="lead">
                        Krājumu, noliktavas dokumentu, FIFO partiju un preču kustības kontrole starp noliktavām vienā darba panelī.
                    </p>
                </div>

                <div class="stats" aria-label="Noliktavas kopsavilkums">
                    <div class="stat">
                        <span>Uzskaitē esošās preces</span>
                        <strong>1 284</strong>
                    </div>
                    <div class="stat">
                        <span>Atvērtie dokumenti</span>
                        <strong>18</strong>
                    </div>
                    <div class="stat">
                        <span>Noliktavas</span>
                        <strong>4</strong>
                    </div>
                </div>
            </section>

            <section class="panel" aria-label="Noliktavas sistēmas pārskats">
                <div class="panel-head">
                    <div class="panel-title">
                        <span class="icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M3.5 20.5h17M5 20V8l7-4 7 4v12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 20v-7h8v7M7.5 9.5h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <strong>Galvenā noliktava</strong>
                            <span>Riga DC-01 · atjaunināts tikko</span>
                        </div>
                    </div>
                    <span class="status">Aktīvs</span>
                </div>

                <div class="warehouse-map">
                    <div class="shelves" aria-hidden="true">
                        <div class="shelf">
                            <div class="box-row"></div>
                            <div class="box-row hot"></div>
                            <div class="box-row"></div>
                        </div>
                        <div class="shelf">
                            <div class="box-row"></div>
                            <div class="box-row"></div>
                            <div class="box-row"></div>
                        </div>
                        <div class="shelf">
                            <div class="box-row hot"></div>
                            <div class="box-row"></div>
                            <div class="box-row"></div>
                        </div>
                    </div>

                    <div class="dock">Pieņemšana<br>un nosūtīšana</div>
                </div>

                <div class="table">
                    <div class="row">
                        <div class="cell">UTP Cat.6 kabelis<small>SKU-1048 · partija #458</small></div>
                        <div class="cell">Zona A-12<small>FIFO 17.05.2026</small></div>
                        <div class="cell">240 gab.</div>
                        <div class="cell"><span class="badge ok">Normā</span></div>
                    </div>
                    <div class="row">
                        <div class="cell">Svītrkodu skeneris<small>SKU-2201 · partija #441</small></div>
                        <div class="cell">Zona B-04<small>FIFO 12.05.2026</small></div>
                        <div class="cell">15 gab.</div>
                        <div class="cell"><span class="badge warn">Pārbaudīt</span></div>
                    </div>
                    <div class="row">
                        <div class="cell">Iepakošanas lente<small>SKU-0312 · partija #463</small></div>
                        <div class="cell">Zona C-02<small>FIFO 16.05.2026</small></div>
                        <div class="cell">6 gab.</div>
                        <div class="cell"><span class="badge low">Maz</span></div>
                    </div>
                </div>

                <div class="documents">
                    <div class="document">
                        <strong>Saņemšana #1024</strong>
                        <span>Gaida grāmatošanu</span>
                    </div>
                    <div class="document">
                        <strong>Pārvietošana #317</strong>
                        <span>Noliktava A → Noliktava B</span>
                    </div>
                    <div class="document">
                        <strong>Norakstīšana #58</strong>
                        <span>Izveidoja operators</span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
