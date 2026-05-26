@extends('layouts.app')

@section('title', 'Kustību vēsture')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kustību vēsture</h1>
            <p>Grāmatoto dokumentu izmaiņas pa precēm, noliktavām, zonām un partijām</p>
        </div>
        <a class="button secondary" href="{{ route('movements.export', request()->query()) }}">CSV</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('movements.index') }}" class="form-grid">
                <label class="field">
                    Produkts
                    <select name="product_id">
                        <option value="">Visi</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Noliktava
                    <select name="stock_id">
                        <option value="">Visas</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) request('stock_id') === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Zona
                    <input type="text" name="zone" value="{{ request('zone') }}" placeholder="A-12">
                </label>
                <label class="field">
                    Dokuments
                    <input type="number" name="document_id" value="{{ request('document_id') }}" placeholder="#ID">
                </label>
                <label class="field">
                    Partija
                    <input type="number" name="income_id" value="{{ request('income_id') }}" placeholder="#ID">
                </label>
                <div class="actions">
                    <button class="button" type="submit">Filtrēt</button>
                    <a class="button secondary" href="{{ route('movements.index') }}">Notīrīt</a>
                </div>
            </form>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Dokuments</th>
                    <th>Datums</th>
                    <th>Prece</th>
                    <th>Noliktava</th>
                    <th>Zona</th>
                    <th>Partija</th>
                    <th>Izmaiņa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>
                            <a href="{{ route('documents.show', $movement->document) }}">#{{ $movement->document_id }}</a>
                            <small>{{ $movement->document?->typeEnum()->label() }}</small>
                        </td>
                        <td>{{ $movement->document?->date_add?->format('d.m.Y H:i') }}</td>
                        <td>{{ $movement->product?->name }}</td>
                        <td>{{ $movement->stock?->name }}</td>
                        <td>{{ $movement->zone }}</td>
                        <td>#{{ $movement->income_id }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $movement->cnt_delta, 3, '.', ''), '0'), '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Kustību nav.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
