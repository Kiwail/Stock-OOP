<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация</title>
</head>
<body>
    <main>
        <h1>Регистрация</h1>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label>
                Имя
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>

            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label>
                Пароль
                <input type="password" name="password" required>
            </label>

            <label>
                Повторите пароль
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit">Создать аккаунт</button>
        </form>

        <p>
            Уже есть аккаунт?
            <a href="{{ route('login') }}">Войти</a>
        </p>
    </main>
</body>
</html>
