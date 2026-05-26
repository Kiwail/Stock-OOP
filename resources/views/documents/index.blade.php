@extends('layouts.app')

@section('title', 'Dokumenti')

@section('content')
    <div class="page-head">
        <div>
            <h1>Dokumenti</h1>
            <p>Saņemšana, norakstīšana, pārvietošana un realizācija</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('documents.export', request()->query()) }}">CSV</a>
            <a class="button" href="{{ route('documents.create') }}">Jauns dokuments</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" class="form-grid">
                <label class="field">
                    Tips
                    <select name="type">
                        <option value="">Visi</option>
                        @foreach (\App\Enums\DocumentType::cases() as $type)
                            <option value="{{ $type->value }}" @selected((int) request('type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Statuss
                    <select name="status">
                        <option value="">Visi</option>
                        <option value="draft" @selected(request('status') === 'draft')>Melnraksts</option>
                        <option value="posted" @selected(request('status') === 'posted')>Apstiprināts</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Atcelts</option>
                    </select>
                </label>
                <label class="field">
                    Avota noliktava
                    <select name="source_stock_id">
                        <option value="">Visas</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) request('source_stock_id') === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Mērķa noliktava
                    <select name="destination_stock_id">
                        <option value="">Visas</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) request('destination_stock_id') === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Operators
                    <select name="operator_id">
                        <option value="">Visi</option>
                        @foreach ($operators as $operator)
                            <option value="{{ $operator->id }}" @selected((int) request('operator_id') === $operator->id)>{{ $operator->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    No datuma
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </label>
                <label class="field">
                    Līdz datumam
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </label>
                <label class="field">
                    Komentārs
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Meklēt komentārā">
                </label>
                <div class="actions">
                    <button class="button" type="submit">Filtrēt</button>
                    <a class="button secondary" href="{{ route('documents.index') }}">Notīrīt</a>
                </div>
            </form>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tips</th>
                    <th>Datums</th>
                    <th>Noliktavas</th>
                    <th>Operators</th>
                    <th>Statuss</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>#{{ $document->id }}</td>
                        <td>{{ $document->typeEnum()->label() }}</td>
                        <td>{{ $document->date_add?->format('d.m.Y H:i') }}</td>
                        <td>
                            @if ($document->sourceStock)
                                <small>Avots: {{ $document->sourceStock->name }}</small>
                            @endif
                            @if ($document->destinationStock)
                                <small>Mērķis: {{ $document->destinationStock->name }}</small>
                            @endif
                        </td>
                        <td>{{ $document->operator?->name ?? '—' }}</td>
                        <td>
                            @if ($document->cancelled)
                                <span class="badge cancelled">Atcelts</span>
                            @else
                                <span @class(['badge', 'posted' => $document->posted, 'draft' => ! $document->posted])>
                                    {{ $document->posted ? 'Apstiprināts' : 'Melnraksts' }}
                                </span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="button secondary" href="{{ route('documents.show', $document) }}">Skatīt</a>
                            @if (! $document->posted && ! $document->cancelled)
                                <a class="button secondary" href="{{ route('documents.edit', $document) }}">Labot</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nav dokumentu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
