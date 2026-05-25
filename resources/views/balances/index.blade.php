@extends('layouts.app')

@section('title', 'Atlikumi')

@section('content')
    <div class="page-head">
        <div>
            <h1>Atlikumi</h1>
            <p>Preču daudzums pa noliktavām un FIFO partijām</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <form method="GET" action="{{ route('balances.index') }}" class="actions">
                <label class="field" style="margin:0;">
                    Noliktava
                    <select name="stock_id" onchange="this.form.submit()">
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected($stockId == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
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
