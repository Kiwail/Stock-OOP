@extends('layouts.app')

@section('title', 'Atlikumi')

@section('content')
    <div class="page-head">
        <div>
            <h1>Atlikumi</h1>
            <p>Preču daudzums pa noliktavām un FIFO partijām</p>
        </div>
        <a class="button secondary" href="{{ route('balances.export', request()->query()) }}">CSV</a>
    </div>

    <div class="card">
        <div class="card-head">
            <form method="GET" action="{{ route('balances.index') }}" class="form-grid">
                <label class="field" style="margin:0;">
                    Noliktava
                    <select name="stock_id">
                        <option value="">Visas</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) request('stock_id') === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field" style="margin:0;">
                    Produkts
                    <select name="product_id">
                        <option value="">Visi</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field" style="margin:0;">
                    Zona
                    <select name="zone">
                        <option value="">Visas</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone }}" @selected(request('zone') === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field" style="margin:0;">
                    Partija
                    <input type="number" name="income_id" min="1" value="{{ request('income_id') }}" placeholder="#ID">
                </label>
                <label class="field" style="margin:0;">
                    Tikai maz atlikumu
                    <select name="low_stock">
                        <option value="0">Nē</option>
                        <option value="1" @selected(request()->boolean('low_stock'))>Jā</option>
                    </select>
                </label>
                <div class="actions">
                    <button class="button" type="submit">Filtrēt</button>
                    <a class="button secondary" href="{{ route('balances.index') }}">Notīrīt</a>
                </div>
            </form>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Prece</th>
                    <th>Zona</th>
                    <th>Partija</th>
                    <th>Daudzums</th>
                    <th>Cena</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($balances as $balance)
                    <tr>
                        <td>{{ $balance->product->name }}<small>{{ $balance->stock->name }}</small></td>
                        <td>{{ $balance->zoneLabel() }}</td>
                        <td>#{{ $balance->income_id }}<small>FIFO {{ $balance->date_upd?->format('d.m.Y H:i') }}</small></td>
                        <td>{{ rtrim(rtrim(number_format((float) $balance->cnt, 3, '.', ''), '0'), '.') }} {{ $balance->product->unitLabel() }}</td>
                        <td>{{ number_format($balance->price, 2) }} €</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Šajā noliktavā nav atlikumu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
