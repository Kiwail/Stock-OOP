<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ieiet | In Stock</title>
    @include('auth.partials.styles')
</head>
<body>
    <div class="auth-page">
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

            <a class="top-link" href="{{ route('register') }}">Reģistrācija</a>
        </header>

        <main class="auth-shell">
            <section class="auth-copy" aria-labelledby="page-title">
                <div class="eyebrow">Noliktavas uzskaites panelis</div>
                <h1 id="page-title">Pieteikšanās darba maiņai</h1>
                <p class="lead">
                    Autorizējieties, lai turpinātu darbu ar atlikumiem, dokumentiem, FIFO partijām un preču kustību starp noliktavām.
                </p>

                <div class="auth-points" aria-label="Sistēmas iespējas">
                    <div class="auth-point">
                        <strong>Dokumenti</strong>
                        <span>Pieņemšana, pārvietošana un norakstīšana vienā darba plūsmā.</span>
                    </div>
                    <div class="auth-point">
                        <strong>Atlikumi</strong>
                        <span>Ātra piekļuve noliktavām, glabāšanas zonām un partijām.</span>
                    </div>
                </div>
            </section>

            <section class="auth-card" aria-label="Pieteikšanās forma">
                <div class="auth-card-head">
                    <span class="auth-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M15 3.8h3.2A1.8 1.8 0 0 1 20 5.6v12.8a1.8 1.8 0 0 1-1.8 1.8H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="m10 8 4 4-4 4M14 12H4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div class="auth-card-title">
                        <strong>Ieiet kontā</strong>
                        <span>Izmantojiet operatora email un paroli</span>
                    </div>
                </div>

                <div class="auth-card-body">
                    @if ($errors->any())
                        <ul class="errors">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('login') }}">
                        @csrf

                        <label class="field">
                            Email
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                        </label>

                        <label class="field">
                            Parole
                            <input type="password" name="password" required autocomplete="current-password">
                        </label>

                        <label class="check">
                            <input type="checkbox" name="remember" value="1">
                            Atcerēties mani
                        </label>

                        <button class="button" type="submit">Ieiet</button>
                    </form>

                    <p class="auth-switch">
                        Nav konta?
                        <a href="{{ route('register') }}">Reģistrēties</a>
                    </p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
