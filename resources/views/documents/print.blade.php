<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <title>Dokuments #{{ $document->id }}</title>
    <style>
        body { color: #111827; font-family: Arial, sans-serif; margin: 32px; }
        .head { align-items: flex-start; display: flex; justify-content: space-between; margin-bottom: 28px; }
        h1 { font-size: 26px; margin: 0 0 8px; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #d1d5db; padding: 10px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 12px; text-transform: uppercase; }
        .total { font-weight: 700; text-align: right; }
        .actions { margin-bottom: 20px; }
        .actions button { padding: 8px 14px; }
        @media print {
            .actions { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Drukāt</button>
    </div>

    <div class="head">
        <div>
            <h1>{{ $document->typeEnum()->label() }} #{{ $document->id }}</h1>
            <p>Statuss:
                @if ($document->cancelled)
                    Atcelts
                @else
                    {{ $document->posted ? 'Apstiprināts' : 'Melnraksts' }}
                @endif
            </p>
            <p>Datums: {{ $document->date_add?->format('d.m.Y H:i') }}</p>
            <p>Operators: {{ $document->operator?->name ?? '—' }}</p>
            @if ($document->recipientFirma)
                <p><strong>Saņēmēja uzņēmums:</strong> {{ $document->recipientFirma->name }}</p>
            @endif
        </div>
        <div>
            @if ($document->sourceStock)
                <p><strong>Avots:</strong> {{ $document->sourceStock->name }}</p>
            @endif
            @if ($document->destinationStock)
                <p><strong>Mērķis:</strong> {{ $document->destinationStock->name }}</p>
            @endif
        </div>
    </div>

    @if ($document->comment)
        <p><strong>Komentārs:</strong> {{ $document->comment }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Produkts</th>
                <th>Zona</th>
                <th>Daudzums</th>
                <th>Cena</th>
                <th>Kopā</th>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @foreach ($document->lines as $line)
                @php($lineTotal = (float) $line->price * (float) $line->cnt)
                @php($total += $lineTotal)
                <tr>
                    <td>{{ $line->product->name }}</td>
                    <td>{{ $line->zone ? 'Zona '.$line->zone : '—' }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $line->cnt, 3, '.', ''), '0'), '.') }} {{ $line->product->unitLabel() }}</td>
                    <td>{{ number_format((float) $line->price, 2) }} €</td>
                    <td>{{ number_format($lineTotal, 2) }} €</td>
                </tr>
            @endforeach
            <tr>
                <td class="total" colspan="4">Kopā</td>
                <td><strong>{{ number_format($total, 2) }} €</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
