@php
    use App\Enums\DocumentType;
    use App\Support\StockStatus;
@endphp

<section class="panel" aria-label="Noliktavas sistēmas pārskats">
    <div class="panel-head">
        <div class="panel-title">
            <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3.5 20.5h17M5 20V8l7-4 7 4v12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 20v-7h8v7M7.5 9.5h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div>
                <strong>{{ $mainStock?->name ?? 'Noliktava' }}</strong>
                <span>
                    @if ($firma ?? null)
                        {{ $firma->name }} · atjaunināts tikko
                    @else
                        Nav datu — palaidiet migrate --seed
                    @endif
                </span>
            </div>
        </div>
        <span class="status">Aktīvs</span>
    </div>

    <div class="warehouse-map">
        <div class="shelves" aria-hidden="true">
            @foreach ([[false,true,false],[false,false,false],[true,false,false]] as $rows)
                <div class="shelf">
                    @foreach ($rows as $hot)
                        <div @class(['box-row', 'hot' => $hot])></div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="dock">Pieņemšana<br>un nosūtīšana</div>
    </div>

    <div class="table">
        @forelse ($balances as $balance)
            @php($status = StockStatus::forQuantity((float) $balance->cnt))
            <div class="row">
                <div class="cell">
                    {{ $balance->product->name }}
                    <small>SKU-{{ $balance->product_id }} · partija #{{ $balance->income_id }}</small>
                </div>
                <div class="cell">
                    {{ $balance->zoneLabel() }}
                    <small>FIFO {{ $balance->date_upd?->format('d.m.Y') }}</small>
                </div>
                <div class="cell">{{ rtrim(rtrim(number_format((float) $balance->cnt, 3, '.', ''), '0'), '.') }} {{ $balance->product->unitLabel() }}</div>
                <div class="cell"><span class="badge {{ $status[0] }}">{{ $status[1] }}</span></div>
            </div>
        @empty
            <div class="row">
                <div class="cell">Nav atlikumu — izveidojiet saņemšanas dokumentu.</div>
            </div>
        @endforelse
    </div>

    <div class="documents">
        @forelse ($recentDocuments as $doc)
            <a class="document" href="{{ auth()->check() ? route('documents.show', $doc) : route('login') }}">
                <strong>{{ $doc->typeEnum()->label() }} #{{ $doc->id }}</strong>
                <span>
                    @if ($doc->cancelled)
                        Atcelts
                    @elseif (! $doc->posted)
                        Gaida grāmatošanu
                    @elseif ($doc->type === DocumentType::Transfer->value)
                        {{ $doc->sourceStock?->name }} → {{ $doc->destinationStock?->name }}
                    @else
                        Izveidoja {{ $doc->operator?->name ?? 'operators' }}
                    @endif
                </span>
            </a>
        @empty
            <div class="document">
                <strong>Nav dokumentu</strong>
                <span>Izveidojiet pirmo dokumentu pēc pieslēgšanās</span>
            </div>
        @endforelse
    </div>
</section>
