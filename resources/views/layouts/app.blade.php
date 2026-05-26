<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panelis') | In Stock</title>
    @include('partials.app-styles')
</head>
<body>
    <div class="app-shell">
        <header class="app-topbar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="In Stock">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="m4.5 8.8 7.5 4.3 7.5-4.3M12 13v6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                In Stock
            </a>

            <nav class="app-nav" aria-label="Galvenā navigācija">
                <a href="{{ route('dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard')])>Panelis</a>
                <a href="{{ route('products.index') }}" @class(['nav-link', 'active' => request()->routeIs('products.*')])>Produkti</a>
                <a href="{{ route('warehouses.index') }}" @class(['nav-link', 'active' => request()->routeIs('warehouses.*')])>Noliktavas</a>
                <a href="{{ route('balances.index') }}" @class(['nav-link', 'active' => request()->routeIs('balances.*')])>Atlikumi</a>
                <a href="{{ route('movements.index') }}" @class(['nav-link', 'active' => request()->routeIs('movements.*')])>Kustības</a>
                <a href="{{ route('documents.index') }}" @class(['nav-link', 'active' => request()->routeIs('documents.index', 'documents.show')])>Dokumenti</a>
                <a href="{{ route('documents.create') }}" @class(['nav-link', 'nav-link-accent', 'active' => request()->routeIs('documents.create')])>Jauns dokuments</a>
            </nav>

            <div class="app-user">
                <div class="user-meta">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ \App\Support\FirmaContext::firma()?->name ?? '—' }} · {{ \App\Support\FirmaContext::role()?->label() }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button ghost" type="submit">Iziet</button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="flash ok">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash err">{{ session('error') }}</div>
        @endif

        <main class="app-main">
            @yield('content')
        </main>
    </div>
</body>
</html>
