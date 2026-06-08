@extends('layouts.app')

@section('title', 'Dokumenti')

@section('content')
    @php
        $documentTabs = [
            \App\Enums\DocumentType::Income->value => 'Saņemšana',
            \App\Enums\DocumentType::Writeoff->value => 'Norakstīšana',
            \App\Enums\DocumentType::Transfer->value => 'Pārvietošana',
        ];
        $activeType = (int) request('type', \App\Enums\DocumentType::Income->value);
        if (! array_key_exists($activeType, $documentTabs)) {
            $activeType = \App\Enums\DocumentType::Income->value;
        }
    @endphp

    <div class="page-head">
        <div>
            <h1>Dokumenti</h1>
            <p>Saņemšana, norakstīšana un pārvietošana</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('documents.export', array_merge(request()->query(), ['type' => $activeType])) }}">CSV</a>
            <button class="button" type="button" id="open-document-modal">Jauns dokuments</button>
        </div>
    </div>

    <nav class="subtabs" aria-label="Dokumentu veidi">
        @foreach ($documentTabs as $typeValue => $typeLabel)
            <a
                href="{{ route('documents.index', array_merge(request()->except('page'), ['type' => $typeValue])) }}"
                @class(['subtab', 'active' => $activeType === $typeValue])
            >
                {{ $typeLabel }}
            </a>
        @endforeach
    </nav>

    <div class="card">
        <div class="card-body">
            <details class="filter-panel">
                <summary class="button secondary">Filtrēt</summary>
                <form method="GET" action="{{ route('documents.index') }}" class="form-grid filter-form">
                    <input type="hidden" name="type" value="{{ $activeType }}">
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
                        @foreach ($filterWarehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) request('source_stock_id') === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Mērķa noliktava
                    <select name="destination_stock_id">
                        <option value="">Visas</option>
                        @foreach ($filterWarehouses as $warehouse)
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
                    <button class="button" type="submit">Pielietot</button>
                    <a class="button secondary" href="{{ route('documents.index', ['type' => $activeType]) }}">Notīrīt</a>
                </div>
                </form>
            </details>
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
                    <tr class="clickable-row" data-modal-target="document-detail-{{ $document->id }}">
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
                            @if ($document->recipientFirma)
                                <small>Uzņēmums: {{ $document->recipientFirma->name }}</small>
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
                            <button class="button secondary open-detail-modal" type="button" data-modal-target="document-detail-{{ $document->id }}">Skatīt</button>
                            @if (! $document->posted && ! $document->cancelled && (int) $document->firma_id === (int) \App\Support\FirmaContext::firmaId())
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

    @foreach ($documents as $document)
        <dialog class="modal" id="document-detail-{{ $document->id }}">
            <div class="modal-head">
                <div>
                    <strong>{{ $document->typeEnum()->label() }} #{{ $document->id }}</strong>
                    <span>{{ $document->date_add?->format('d.m.Y H:i') }} · {{ $document->operator?->name ?? '-' }}</span>
                </div>
                <button class="button secondary close-detail-modal" type="button">Aizvērt</button>
            </div>
            <div class="modal-body">
                <div class="document-detail-grid">
                    <div>
                        <span>Statuss</span>
                        @if ($document->cancelled)
                            <strong><span class="badge cancelled">Atcelts</span></strong>
                        @else
                            <strong>
                                <span @class(['badge', 'posted' => $document->posted, 'draft' => ! $document->posted])>
                                    {{ $document->posted ? 'Apstiprināts' : 'Melnraksts' }}
                                </span>
                            </strong>
                        @endif
                    </div>
                    <div>
                        <span>Operators</span>
                        <strong>{{ $document->operator?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Avota noliktava</span>
                        <strong>{{ $document->sourceStock?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Mērķa noliktava</span>
                        <strong>{{ $document->destinationStock?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Saņēmēja uzņēmums</span>
                        <strong>{{ $document->recipientFirma?->name ?? '-' }}</strong>
                    </div>
                </div>

                @if ($document->comment)
                    <div class="document-detail-comment">
                        <span>Komentārs</span>
                        <p>{{ $document->comment }}</p>
                    </div>
                @endif

                <table class="table">
                    <thead>
                        <tr>
                            <th>Prece</th>
                            <th>Zona</th>
                            <th>Daudzums</th>
                            <th>Cena</th>
                            <th>Kopā</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($document->lines as $line)
                            <tr>
                                <td>{{ $line->product?->name }}</td>
                                <td>{{ $line->zone ? 'Zona '.$line->zone : '-' }}</td>
                                <td>{{ (int) $line->cnt }} {{ $line->product?->unitLabel() }}</td>
                                <td>{{ number_format($line->price, 2) }}</td>
                                <td>{{ number_format($line->price * $line->cnt, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="actions document-detail-actions">
                    <a class="button secondary" href="{{ route('documents.print', $document) }}">Drukāt</a>
                    @if (! $document->posted && ! $document->cancelled && (int) $document->firma_id === (int) \App\Support\FirmaContext::firmaId())
                        <a class="button secondary" href="{{ route('documents.edit', $document) }}">Labot</a>
                    @endif
                    <a class="button secondary" href="{{ route('documents.show', $document) }}">Atvērt lapu</a>
                </div>
            </div>
        </dialog>
    @endforeach

    <dialog class="modal" id="document-modal" data-open-on-load="{{ $errors->any() ? 'true' : 'false' }}">
        <div class="modal-head">
            <div>
                <strong>Jauns dokuments</strong>
                <span>Aizpildiet tipu, noliktavu, preces un saglabājiet dokumentu kā melnrakstu.</span>
            </div>
            <button class="button secondary" type="button" id="close-document-modal">Aizvērt</button>
        </div>
        <div class="modal-body">
            @if ($errors->any())
                <ul class="flash err" style="list-style-position:inside;margin-bottom:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @include('documents._form', ['document' => $createDocument])
        </div>
    </dialog>

    <script>
        const documentModal = document.getElementById('document-modal');
        if (documentModal?.dataset.openOnLoad === 'true') {
            documentModal.showModal();
        }
        document.getElementById('open-document-modal')?.addEventListener('click', () => documentModal.showModal());
        document.getElementById('close-document-modal')?.addEventListener('click', () => documentModal.close());
        documentModal?.addEventListener('click', (event) => {
            if (event.target === documentModal) {
                documentModal.close();
            }
        });

        function openDetailModal(id) {
            document.getElementById(id)?.showModal();
        }

        document.querySelectorAll('.clickable-row').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('a, button, form')) {
                    return;
                }

                openDetailModal(row.dataset.modalTarget);
            });
        });

        document.querySelectorAll('.open-detail-modal').forEach((button) => {
            button.addEventListener('click', () => openDetailModal(button.dataset.modalTarget));
        });

        document.querySelectorAll('[id^="document-detail-"]').forEach((modal) => {
            modal.querySelector('.close-detail-modal')?.addEventListener('click', () => modal.close());
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.close();
                }
            });
        });
    </script>
@endsection
