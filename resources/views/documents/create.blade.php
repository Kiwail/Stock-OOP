@extends('layouts.app')

@section('title', $document->exists ? 'Labot melnrakstu' : 'Jauns dokuments')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $document->exists ? 'Labot melnrakstu #'.$document->id : 'Jauns dokuments' }}</h1>
            <p>Документ сохраняется как черновик. После проверки его можно подтвердить.</p>
        </div>
        <a class="button secondary" href="{{ route('documents.index') }}">Назад</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <ul class="flash err" style="list-style-position:inside;margin-bottom:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @include('documents._form')
        </div>
    </div>
@endsection
