@extends('layouts.app')

@section('title', 'Produkti')

@section('content')
    <div class="page-head">
        <div>
            <h1>Produkti</h1>
            <p>Preču katalogs ar cenām un mērvienībām</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('products.export') }}">CSV</a>
            <a class="button" href="{{ route('products.create') }}">Pievienot produktu</a>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Nosaukums</th>
                    <th>Iepirkuma cena</th>
                    <th>Realizācijas cena</th>
                    <th>Mērvienība</th>
                    @if (auth()->user()->isAdmin())
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->purchase_price, 2) }} €</td>
                        <td>{{ number_format($product->sale_price, 2) }} €</td>
                        <td>{{ $product->unitLabel() }}</td>
                        @if (auth()->user()->isAdmin())
                            <td class="actions">
                                <a class="button secondary" href="{{ route('products.edit', $product) }}">Labot</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Noņemt produktu?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button ghost" type="submit">Dzēst</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="5">Nav produktu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
