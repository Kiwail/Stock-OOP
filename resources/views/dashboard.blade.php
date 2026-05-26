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
                    <span>Krājumu vērtība</span>
                    <strong>{{ number_format($stats['stock_value'], 2, ',', ' ') }} €</strong>
                </div>
                <div class="stat">
                    <span>Atvērtie dokumenti</span>
                    <strong>{{ $stats['open_documents'] }}</strong>
                </div>
                <div class="stat">
                    <span>Maz atlikumu</span>
                    <strong>{{ $stats['low_stock_products'] }}</strong>
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
                    <a class="button secondary" href="{{ route('movements.index') }}">Kustības</a>
                    <a class="button secondary" href="{{ route('documents.index') }}">Dokumenti</a>
                    @if (auth()->user()->isAdmin())
                        <a class="button secondary" href="{{ route('warehouses.create') }}">Jauna noliktava</a>
                    @endif
                </div>
            </div>

            <div class="card" style="margin-top:18px;">
                <div class="card-head">
                    <strong>Atvērtie dokumenti pēc tipa</strong>
                </div>
                <div class="card-body">
                    <div class="stats">
                        @foreach ($stats['open_by_type'] as $type => $count)
                            <div class="stat">
                                <span>{{ $type }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:18px;">
                <div class="card-head">
                    <strong>Top preces pēc vērtības</strong>
                </div>
                <table class="table">
                    <tbody>
                        @forelse ($topProducts as $row)
                            <tr>
                                <td>{{ $row->product?->name }}</td>
                                <td>{{ number_format((float) $row->qty, 3, ',', ' ') }}</td>
                                <td>{{ number_format((float) $row->value, 2, ',', ' ') }} €</td>
                            </tr>
                        @empty
                            <tr><td>Nav atlikumu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top:18px;">
                <div class="card-head">
                    <strong>Pēdējie apstiprinātie dokumenti</strong>
                </div>
                <table class="table">
                    <tbody>
                        @forelse ($latestPostedDocuments as $document)
                            <tr>
                                <td><a href="{{ route('documents.show', $document) }}">#{{ $document->id }}</a></td>
                                <td>{{ $document->typeEnum()->label() }}</td>
                                <td>{{ $document->date_add?->format('d.m.Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td>Nav apstiprinātu dokumentu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @php($firma = \App\Support\FirmaContext::firma())
        @include('partials.stock-preview')
    </div>
@endsection
