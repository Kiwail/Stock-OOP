@extends('layouts.app')

@section('title', $product->exists ? 'Labot produktu' : 'Jauns produkts')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $product->exists ? 'Labot produktu' : 'Jauns produkts' }}</h1>
        </div>
        <a class="button secondary" href="{{ route('products.index') }}">Atpakaļ</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <ul class="flash err" style="list-style-position:inside;margin-bottom:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form class="form-grid" method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <label class="field">
                    Nosaukums
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                </label>

                <label class="field">
                    Iepirkuma cena (€)
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" required>
                </label>

                <label class="field">
                    Realizācijas cena (€)
                    <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? 0) }}" required>
                </label>

                <label class="field">
                    Mērvienība
                    <select name="unit" required>
                        @foreach ([1 => 'gab.', 2 => 'kg', 3 => 'l'] as $value => $label)
                            <option value="{{ $value }}" @selected((int) old('unit', $product->unit ?? 1) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <button class="button" type="submit">Saglabāt</button>
            </form>
        </div>
    </div>
@endsection
