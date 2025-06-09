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

    <style>
        body {
            margin: 0;
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .app-title {
            font-size: 1.8rem;
            color: #8b4513;
            margin: 0;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: #666;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #8b4513;
            background: rgba(139, 69, 19, 0.1);
        }

        .inline-form {
            margin: 0;
        }

        .inline-form button {
            background: none;
            border: none;
            font-family: inherit;
            font-size: inherit;
            cursor: pointer;
            padding: 0.5rem 1rem;
            color: #666;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .inline-form button:hover {
            color: #8b4513;
            background: rgba(139, 69, 19, 0.1);
        }

        .app-main {
            flex: 1;
            padding: 2rem 0;
        }
    </style>

    @yield('styles')
</head>
<body>
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