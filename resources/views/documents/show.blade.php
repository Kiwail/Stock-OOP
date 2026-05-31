@extends('layouts.app')

@section('title', 'Dokuments #'.$document->id)

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $document->typeEnum()->label() }} #{{ $document->id }}</h1>
            <p>{{ $document->date_add?->format('d.m.Y H:i') }} · {{ $document->operator?->name }}</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('documents.index') }}">Atpakaļ</a>
            <a class="button secondary" href="{{ route('documents.print', $document) }}">Drukāt</a>
            @if (! $document->posted && ! $document->cancelled)
                <a class="button secondary" href="{{ route('documents.edit', $document) }}">Labot</a>
                <form method="POST" action="{{ route('documents.post', $document) }}" onsubmit="return confirm('Apstiprināt dokumentu? Atlikumi tiks mainīti.')">
                    @csrf
                    <button class="button" type="submit">Apstiprināt (grāmatot)</button>
                </form>
                <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Dzēst melnrakstu?')">
                    @csrf
                    @method('DELETE')
                    <button class="button ghost" type="submit">Dzēst melnrakstu</button>
                </form>
            @endif
            @if ($document->posted && ! $document->cancelled && auth()->user()->isAdmin())
                <form method="POST" action="{{ route('documents.cancel', $document) }}" onsubmit="return confirm('Atcelt dokumentu? Atlikumi tiks atgriezti.')">
                    @csrf
                    <button class="button ghost" type="submit">Atcelt dokumentu</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p>
                @if ($document->cancelled)
                    <span class="badge cancelled">Atcelts</span>
                @else
                    <span @class(['badge', 'posted' => $document->posted, 'draft' => ! $document->posted])>
                        {{ $document->posted ? 'Apstiprināts' : 'Melnraksts' }}
                    </span>
                @endif
            </p>
            @if ($document->sourceStock)
                <p><strong>Avota noliktava:</strong> {{ $document->sourceStock->name }}</p>
            @endif
            @if ($document->destinationStock)
                <p><strong>Mērķa noliktava:</strong> {{ $document->destinationStock->name }}</p>
            @endif
            @if ($document->recipientFirma)
                <p><strong>Saņēmēja uzņēmums:</strong> {{ $document->recipientFirma->name }}</p>
            @endif
            @if ($document->comment)
                <p><strong>Komentārs:</strong> {{ $document->comment }}</p>
            @endif
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Produkts</th>
                    <th>Zona</th>
                    <th>Daudzums</th>
                    <th>Cena</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->lines as $line)
                    <tr>
                        <td>{{ $line->product->name }}</td>
                        <td>{{ $line->zone ? 'Zona '.$line->zone : '—' }}</td>
                        <td>{{ (int) $line->cnt }} {{ $line->product->unitLabel() }}</td>
                        <td>{{ number_format($line->price, 2) }} € / gab. (kopā {{ number_format($line->price * $line->cnt, 2) }} €)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
