@extends('layouts.app')

@section('title', 'Panelis')

@section('content')
    <div class="page-head">
        <div>
            <h1>Darba panelis</h1>
            <p>Krājumu, dokumentu un noliktavu pārskats — {{ \App\Support\FirmaContext::firma()?->name }}</p>
        </div>
        <a class="button" href="{{ route('documents.create') }}">Jauns dokuments</a>
    </div>

    <div class="workspace-grid">
        <section>
            <div class="stats" style="margin-bottom: 18px;">
                <div class="stat">
                    <span>Uzskaitē esošās preces</span>
                    <strong>{{ number_format($stats['stock_qty'], 0, ',', ' ') }}</strong>
                </div>
                <div class="stat">
                    <span>Atvērtie dokumenti</span>
                    <strong>{{ $stats['open_documents'] }}</strong>
                </div>
                <div class="stat">
                    <span>Noliktavas</span>
                    <strong>{{ $stats['warehouses'] }}</strong>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <strong>Ātrās saites</strong>
                </div>
                <div class="card-body actions">
                    <a class="button secondary" href="{{ route('products.index') }}">Produkti</a>
                    <a class="button secondary" href="{{ route('balances.index') }}">Atlikumi</a>
                    <a class="button secondary" href="{{ route('documents.index') }}">Dokumenti</a>
                    @if (auth()->user()->isAdmin())
                        <a class="button secondary" href="{{ route('warehouses.create') }}">Jauna noliktava</a>
                    @endif
                </div>
            </div>
        </section>

        @php($firma = \App\Support\FirmaContext::firma())
        @include('partials.stock-preview')
    </div>
@endsection
