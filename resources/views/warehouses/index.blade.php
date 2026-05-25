@extends('layouts.app')

@section('title', 'Noliktavas')

@section('content')
    <div class="page-head">
        <div>
            <h1>Noliktavas</h1>
            <p>Firmas noliktavu saraksts</p>
        </div>
        @if (auth()->user()->isAdmin())
            <a class="button" href="{{ route('warehouses.create') }}">Pievienot noliktavu</a>
        @endif
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Nosaukums</th>
                    @if (auth()->user()->isAdmin())
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($warehouses as $warehouse)
                    <tr>
                        <td>{{ $warehouse->name }}</td>
                        @if (auth()->user()->isAdmin())
                            <td class="actions">
                                <a class="button secondary" href="{{ route('warehouses.edit', $warehouse) }}">Labot</a>
                                <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Noņemt noliktavu?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button ghost" type="submit">Dzēst</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="2">Nav noliktavu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
