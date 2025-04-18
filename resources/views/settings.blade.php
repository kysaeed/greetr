@extends('layouts.app')

@section('content')
<div class="settings-container">
    <h1>設定</h1>
    <div class="settings-content">
        <div class="settings-section">
            <h2>アカウント設定</h2>
            <div class="settings-item">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" value="{{ Auth::user()->name }}" disabled>
            </div>
            <div class="settings-item">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" value="{{ Auth::user()->email }}" disabled>
            </div>
        </div>
        <div class="settings-section">
            <h2>ゲーム設定</h2>
            <div class="settings-item">
                <label for="sound">サウンド</label>
                <input type="checkbox" id="sound" checked>
            </div>
            <div class="settings-item">
                <label for="notifications">通知</label>
                <input type="checkbox" id="notifications" checked>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.settings-content {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 1rem;
    padding: 2rem;
    backdrop-filter: blur(5px);
}

.settings-section {
    margin-bottom: 2rem;
}

.settings-section h2 {
    font-size: 1.5rem;
    color: #8b4513;
    margin-bottom: 1rem;
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.settings-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.settings-item label {
    font-size: 1.1rem;
    color: #333;
}

.settings-item input[type="text"],
.settings-item input[type="email"] {
    padding: 0.5rem;
    border: 2px solid #8b4513;
    border-radius: 0.5rem;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.settings-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
</style>
@endsection