<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reģistrācija | In Stock</title>
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

            <a class="top-link" href="{{ route('login') }}">Ieiet</a>
        </header>

        <main class="auth-shell">
            <section class="auth-copy" aria-labelledby="page-title">
                <div class="eyebrow">Jauns lietotājs</div>
                <h1 id="page-title">Piekļuves izveide noliktavai</h1>
                <p class="lead">
                    Pievienojiet darbinieka kontu, lai veidotu noliktavas dokumentus, kontrolētu atlikumus un strādātu ar preču partijām.
                </p>

                <div class="auth-points" aria-label="Konta nozīme">
                    <div class="auth-point">
                        <strong>Vienota loma</strong>
                        <span>Konts uzreiz ir gatavs pamata darbam uzskaites panelī.</span>
                    </div>
                    <div class="auth-point">
                        <strong>Ātrs starts</strong>
                        <span>Pēc reģistrācijas var pāriet uz darba ekrānu bez liekiem soļiem.</span>
                    </div>
                </div>
            </section>

            <section class="auth-card" aria-label="Reģistrācijas forma">
                <div class="auth-card-head">
                    <span class="auth-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M16 20v-1.8a4.2 4.2 0 0 0-4.2-4.2H7.2A4.2 4.2 0 0 0 3 18.2V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M9.5 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM18 8v6M21 11h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <div class="auth-card-title">
                        <strong>Reģistrēt kontu</strong>
                        <span>Aizpildiet jaunā lietotāja datus</span>
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

                    <form class="auth-form" method="POST" action="{{ route('register') }}">
                        @csrf

                        <label class="field">
                            Vārds
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                        </label>

                        <label class="field">
                            Email
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                        </label>

                        <label class="field">
                            Parole
                            <input type="password" name="password" required autocomplete="new-password">
                        </label>

                        <label class="field">
                            Atkārtojiet paroli
                            <input type="password" name="password_confirmation" required autocomplete="new-password">
                        </label>

                        <button class="button" type="submit">Izveidot kontu</button>
                    </form>

                    <p class="auth-switch">
                        Jau ir konts?
                        <a href="{{ route('login') }}">Ieiet</a>
                    </p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
