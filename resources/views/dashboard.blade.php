<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panelis</title>
</head>
<body>
    <main>
        <h1>Lietotāja panelis</h1>

        <p>Jūs esat pieteicies kā {{ auth()->user()->name }}.</p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Iziet</button>
        </form>
    </main>
</body>
</html>
