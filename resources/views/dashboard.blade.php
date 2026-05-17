<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Панель</title>
</head>
<body>
    <main>
        <h1>Панель пользователя</h1>

        <p>Вы вошли как {{ auth()->user()->name }}.</p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Выйти</button>
        </form>
    </main>
</body>
</html>
