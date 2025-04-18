<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Greetr')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=comic-sans-ms:400" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Comic+Sans+MS&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>
<body style="margin: 0;">
    <div class="app-container">
        <header class="app-header">
            <div class="header-content">
                <h1 class="app-title">Greetr</h1>
                <nav class="nav-menu">
                    <a href="{{ route('home') }}" class="nav-link">ホーム</a>
                    <a href="{{ route('world') }}" class="nav-link">ワールド</a>
                    <a href="{{ route('setting') }}" class="nav-link">設定</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="nav-link">ログアウト</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="app-main">
            @yield('content')
        </main>
    </div>
</body>
</html>