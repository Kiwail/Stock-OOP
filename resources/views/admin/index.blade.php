@extends('layouts.app')

@section('title', 'Administrēšana')

@section('content')
    <div class="page-head">
        <div>
            <h1>Administrēšana</h1>
            <p>Pilna uzņēmuma pārvaldība: lietotāji, tiesības, preces, noliktavas un dokumenti.</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('products.create') }}">Jauna prece</a>
            <a class="button secondary" href="{{ route('warehouses.create') }}">Jauna noliktava</a>
            <a class="button" href="{{ route('documents.index') }}">Dokumenti</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <span>Lietotāji</span>
            <strong>{{ $stats['users'] }}</strong>
        </div>
        <div class="stat">
            <span>Administratori</span>
            <strong>{{ $stats['admins'] }}</strong>
        </div>
        <div class="stat">
            <span>Parastie lietotāji</span>
            <strong>{{ $stats['operators'] }}</strong>
        </div>
        <div class="stat">
            <span>Preces</span>
            <strong>{{ $stats['products'] }}</strong>
        </div>
        <div class="stat">
            <span>Noliktavas</span>
            <strong>{{ $stats['warehouses'] }}</strong>
        </div>
        <div class="stat">
            <span>Dokumenti</span>
            <strong>{{ $stats['documents'] }}</strong>
        </div>
        <div class="stat">
            <span>Melnraksti</span>
            <strong>{{ $stats['drafts'] }}</strong>
        </div>
    </div>

    <div class="admin-grid">
        <section class="card">
            <div class="card-head">
                <strong>Pārvaldības sadaļas</strong>
            </div>
            <div class="card-body admin-actions">
                <a class="admin-tile" href="{{ route('products.index') }}">
                    <strong>Preces</strong>
                    <span>Kataloga izveide, rediģēšana, arhivēšana un eksports.</span>
                </a>
                <a class="admin-tile" href="{{ route('warehouses.index') }}">
                    <strong>Noliktavas</strong>
                    <span>Noliktavu izveide un uzņēmuma noliktavu struktūras pārvaldība.</span>
                </a>
                <a class="admin-tile" href="{{ route('balances.index') }}">
                    <strong>Atlikumi</strong>
                    <span>Daudzuma, vērtības un zemu atlikumu kontrole.</span>
                </a>
                <a class="admin-tile" href="{{ route('movements.index') }}">
                    <strong>Kustības</strong>
                    <span>Operāciju vēsture un noliktavas izmaiņu audits.</span>
                </a>
                <a class="admin-tile" href="{{ route('documents.index') }}">
                    <strong>Dokumenti</strong>
                    <span>Saņemšana, norakstīšana, pārvietošana un realizācija.</span>
                </a>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <strong>Lietotāji un tiesības</strong>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Vārds</th>
                        <th>Email</th>
                        <th>Uzņēmumi</th>
                        <th>Loma</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php
                            $currentFirma = $user->firmas->firstWhere('id', \App\Support\FirmaContext::firmaId());
                            $managedFirma = $currentFirma ?? $user->firmas->first();
                            $role = $managedFirma?->pivot?->role;
                        @endphp
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                {{ $user->firmas->pluck('name')->join(', ') ?: '-' }}
                            </td>
                            <td>
                                @if ($managedFirma)
                                    <form method="POST" action="{{ route('admin.users.role', $user) }}" class="role-form">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="firma_id" value="{{ $managedFirma->id }}">
                                        <select name="role">
                                            @foreach (\App\Enums\UserRole::cases() as $case)
                                                <option value="{{ $case->value }}" @selected($role === $case->value)>{{ $case->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button class="button secondary" type="submit">Saglabāt</button>
                                    </form>
                                @else
                                    <span class="badge cancelled">Nav uzņēmuma</span>
                                @endif
                            </td>
                            <td>
                                @if ($role === \App\Enums\UserRole::Admin->value)
                                    <span class="badge posted">Admin</span>
                                @elseif ($role === \App\Enums\UserRole::Operator->value)
                                    <span class="badge draft">User</span>
                                @else
                                    <span class="badge cancelled">Nav pieejams</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
@endsection
