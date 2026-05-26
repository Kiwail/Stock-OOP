@extends('layouts.app')

@section('title', $warehouse->exists ? 'Labot noliktavu' : 'Jauna noliktava')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $warehouse->exists ? 'Labot noliktavu' : 'Jauna noliktava' }}</h1>
        </div>
        <a class="button secondary" href="{{ route('warehouses.index') }}">Atpakaļ</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="form-grid" method="POST" action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}">
                @csrf
                @if ($warehouse->exists)
                    @method('PUT')
                @endif

                <label class="field">
                    Nosaukums
                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required>
                </label>

                <button class="button" type="submit">Saglabāt</button>
            </form>
        </div>
    </div>
@endsection
