@extends('layout')

@section('content')
<div class="settings-container">
    <h1>設定</h1>
    <div class="settings-content">
        <div class="settings-section">
            <h2>プロフィール設定</h2>
            <form action="#" method="POST" class="settings-form">
                @csrf
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required>
                </div>
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" required>
                </div>
                <button type="submit" class="btn">更新</button>
            </form>
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

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 1.1rem;
    color: #333;
}

.form-group input {
    padding: 0.5rem;
    border: 2px solid #8b4513;
    border-radius: 0.5rem;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.btn {
    background: #4CAF50;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    align-self: flex-start;
}

.btn:hover {
    background: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
@endsection