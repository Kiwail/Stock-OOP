<form class="form-grid document-form" method="POST" action="{{ $document->exists ? route('documents.update', $document) : route('documents.store') }}" id="doc-form">
    @csrf
    @if ($document->exists)
        @method('PUT')
    @endif

    <label class="field">
        Dokumenta tips
        @if (! $document->exists)
            <input type="hidden" name="type" value="{{ old('type', $document->type) }}">
        @endif
        <select name="{{ $document->exists ? 'type' : '' }}" id="doc-type" required @disabled(! $document->exists)>
            @foreach (\App\Enums\DocumentType::cases() as $type)
                @continue($type === \App\Enums\DocumentType::Sale && (int) old('type', $document->type) !== $type->value)
                <option value="{{ $type->value }}" @selected((int) old('type', $document->type) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </label>

    <label class="field">
        Operators
        <input type="text" value="{{ $currentOperator?->name }} ({{ $currentOperator?->email }})" disabled>
    </label>

    <label class="field" id="source-wrap">
        Avota noliktava
        <select name="source_stock_id" id="source-stock">
            <option value="">-</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((int) old('source_stock_id', $document->source_stock_id) === $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="field" id="dest-wrap">
        Mērķa noliktava
        <select name="destination_stock_id" id="dest-stock">
            <option value="">-</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((int) old('destination_stock_id', $document->destination_stock_id) === $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="field" id="recipient-firma-wrap">
        Saņēmēja uzņēmums
        <select name="recipient_firma_id" id="recipient-firma">
            <option value="">- izvēlieties -</option>
            @foreach ($recipientFirms as $firma)
                <option value="{{ $firma->id }}" @selected((int) old('recipient_firma_id', $document->recipient_firma_id) === $firma->id)>{{ $firma->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="field">
        Komentārs
        <textarea name="comment">{{ old('comment', $document->comment) }}</textarea>
    </label>

    <p id="stock-hint" class="flash" style="margin:0;display:none;"></p>

    <div>
        <strong>Dokumenta preces</strong>
        <div id="lines" style="margin-top:12px;">
            @foreach ($lineRows as $index => $row)
                <div class="line-row">
                    <label class="field">
                        Prece
                        <select name="lines[{{ $index }}][product_id]" class="line-product" data-selected="{{ $row['product_id'] ?? '' }}" required>
                            <option value="">- izvēlieties -</option>
                        </select>
                    </label>
                    <label class="field zone-field">
                        Zona
                        <input type="text" name="lines[{{ $index }}][zone]" placeholder="A-12" pattern="[A-Za-z]-\d{2}" value="{{ $row['zone'] ?? '' }}">
                    </label>
                    <label class="field">
                        Daudzums
                        <input type="number" step="1" min="1" name="lines[{{ $index }}][cnt]" class="line-cnt" value="{{ $row['cnt'] ?? 1 }}" required>
                    </label>
                    <label class="field">
                        Cena / gab.
                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][price]" class="line-price" placeholder="no kataloga" value="{{ $row['price'] ?? '' }}">
                    </label>
                    <label class="field">
                        Kopā
                        <output class="line-total" style="min-height:42px;display:flex;align-items:center;font-weight:700;">0.00</output>
                    </label>
                    <button class="button secondary remove-line" type="button">Dzēst</button>
                </div>
            @endforeach
        </div>
        <button class="button secondary" type="button" id="add-line" style="margin-top:10px;">Pievienot rindu</button>
    </div>

    <button class="button" type="submit">Saglabāt melnrakstu</button>
</form>

<script>
    const catalogProducts = @json($catalogProducts);
    const stockByWarehouse = @json($stockProducts);
    const typeSelect = document.getElementById('doc-type');
    const sourceWrap = document.getElementById('source-wrap');
    const destWrap = document.getElementById('dest-wrap');
    const sourceStock = document.getElementById('source-stock');
    const destStock = document.getElementById('dest-stock');
    const recipientFirmaWrap = document.getElementById('recipient-firma-wrap');
    const recipientFirma = document.getElementById('recipient-firma');
    const stockHint = document.getElementById('stock-hint');
    let lineIndex = {{ count($lineRows) }};

    function isIncome() {
        return Number(typeSelect.value) === 1;
    }

    function needsSourceStock() {
        return [2, 3, 4].includes(Number(typeSelect.value));
    }

    function productsForDocument() {
        if (isIncome()) {
            return catalogProducts;
        }

        const stockId = sourceStock.value;
        if (!stockId) {
            return [];
        }

        return stockByWarehouse[stockId] || [];
    }

    function syncStockFields() {
        const type = Number(typeSelect.value);
        const income = type === 1;
        const sale = type === 4;
        sourceWrap.style.display = [2, 3, 4].includes(type) ? 'grid' : 'none';
        destWrap.style.display = [1, 3].includes(type) ? 'grid' : 'none';
        recipientFirmaWrap.style.display = sale ? 'grid' : 'none';
        sourceStock.required = [2, 3, 4].includes(type);
        destStock.required = [1, 3].includes(type);
        recipientFirma.required = sale;

        document.querySelectorAll('.zone-field').forEach((field) => {
            field.style.display = income ? 'grid' : 'none';
            const input = field.querySelector('input');
            input.required = income;
        });

        if (needsSourceStock() && !sourceStock.value) {
            stockHint.style.display = 'block';
            stockHint.textContent = 'Vispirms izvēlieties avota noliktavu, tad parādīsies pieejamās preces.';
        } else if (!income && productsForDocument().length === 0) {
            stockHint.style.display = 'block';
            stockHint.textContent = 'Izvēlētajā noliktavā nav atlikumu.';
        } else {
            stockHint.style.display = 'none';
        }
    }

    function defaultUnitPrice(type, productId, rowProduct) {
        if (rowProduct && !isIncome()) {
            return Number(rowProduct.price) || 0;
        }

        const catalog = catalogProducts.find((p) => String(p.id) === String(productId));
        if (!catalog) return 0;
        return Number(type) === 4 ? catalog.sale : catalog.purchase;
    }

    function updateLineTotal(row) {
        const qty = parseInt(row.querySelector('.line-cnt')?.value, 10) || 0;
        const price = parseFloat(row.querySelector('.line-price')?.value) || 0;
        const total = row.querySelector('.line-total');
        if (total) {
            total.textContent = (qty * price).toFixed(2);
        }
    }

    function fillLinePrice(row) {
        const type = Number(typeSelect.value);
        const select = row.querySelector('.line-product');
        const productId = select?.value;
        const priceInput = row.querySelector('.line-price');
        const cntInput = row.querySelector('.line-cnt');
        if (!productId || !priceInput) return;

        const list = productsForDocument();
        const rowProduct = list.find((p) => String(p.id) === String(productId));

        if (!priceInput.value) {
            priceInput.value = defaultUnitPrice(type, productId, rowProduct).toFixed(2);
        }

        if (rowProduct && cntInput) {
            cntInput.max = rowProduct.qty;
        }

        updateLineTotal(row);
    }

    function rebuildProductSelects() {
        const list = productsForDocument();

        document.querySelectorAll('.line-row').forEach((row) => {
            const select = row.querySelector('.line-product');
            const previous = select.value || select.dataset.selected || '';
            select.innerHTML = '<option value="">- izvēlieties -</option>';

            list.forEach((product) => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = isIncome()
                    ? product.name
                    : `${product.name} (pieejams: ${product.qty} ${product.unit})`;
                option.dataset.qty = product.qty;
                option.dataset.price = product.price ?? '';
                select.appendChild(option);
            });

            if (list.some((p) => String(p.id) === String(previous))) {
                select.value = previous;
            } else {
                select.value = '';
            }
            delete select.dataset.selected;

            fillLinePrice(row);
        });
    }

    function bindLine(row) {
        row.querySelector('.line-product')?.addEventListener('change', () => {
            const price = row.querySelector('.line-price');
            if (price) {
                price.value = '';
            }
            fillLinePrice(row);
        });
        row.querySelector('.line-cnt')?.addEventListener('input', () => updateLineTotal(row));
        row.querySelector('.line-price')?.addEventListener('input', () => updateLineTotal(row));
        row.querySelector('.remove-line')?.addEventListener('click', () => removeLine(row));
        fillLinePrice(row);
    }

    function clearLine(row) {
        row.querySelectorAll('select, input').forEach((el) => {
            if (el.classList.contains('line-cnt')) {
                el.value = '1';
                el.removeAttribute('max');
                return;
            }

            el.value = '';
        });

        const total = row.querySelector('.line-total');
        if (total) {
            total.textContent = '0.00';
        }
    }

    function removeLine(row) {
        const rows = document.querySelectorAll('.line-row');
        if (rows.length === 1) {
            clearLine(row);
            return;
        }

        row.remove();
    }

    document.getElementById('lines').querySelectorAll('.line-row').forEach(bindLine);

    typeSelect.addEventListener('change', () => {
        syncStockFields();
        rebuildProductSelects();
    });

    sourceStock.addEventListener('change', () => {
        syncStockFields();
        rebuildProductSelects();
    });

    syncStockFields();
    rebuildProductSelects();

    document.getElementById('add-line').addEventListener('click', () => {
        const first = document.querySelector('.line-row');
        const clone = first.cloneNode(true);
        clone.querySelectorAll('select, input').forEach((el) => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\[\d+\]/, `[${lineIndex}]`));
            }
            if (el.classList.contains('line-cnt')) {
                el.value = '1';
                el.removeAttribute('max');
            }
            if (el.classList.contains('line-price')) {
                el.value = '';
            }
            if (el.classList.contains('line-product')) {
                el.value = '';
                delete el.dataset.selected;
            }
        });
        const total = clone.querySelector('.line-total');
        if (total) {
            total.textContent = '0.00';
        }
        document.getElementById('lines').appendChild(clone);
        bindLine(clone);
        rebuildProductSelects();
        lineIndex++;
    });
</script>
