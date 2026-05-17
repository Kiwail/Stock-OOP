<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход</title>
</head>
<body>
    <main>
        <h1>Вход</h1>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <label>
                Пароль
                <input type="password" name="password" required>
            </label>

            <label>
                <input type="checkbox" name="remember" value="1">
                Запомнить меня
            </label>

            <button type="submit">Войти</button>
        </form>

        <p>
            Нет аккаунта?
            <a href="{{ route('register') }}">Зарегистрироваться</a>
        </p>
    </main>
</body>
</html>
