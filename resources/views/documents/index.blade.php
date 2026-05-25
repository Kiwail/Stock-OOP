@extends('layouts.app')

@section('title', 'Dokumenti')

@section('content')
    <div class="page-head">
        <div>
            <h1>Dokumenti</h1>
            <p>Saņemšana, norakstīšana, pārvietošana un realizācija</p>
        </div>
        <a class="button" href="{{ route('documents.create') }}">Jauns dokuments</a>
    </div>

    <div class="card">
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
                        <td><a class="button secondary" href="{{ route('documents.show', $document) }}">Skatīt</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nav dokumentu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
