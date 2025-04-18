@extends('layout')

@section('content')
<div class="world-container">
    <h1>ワールド</h1>
    <div class="world-content">
        <!-- ワールド追加フォーム -->
        <div class="world-section">
            <h2>ワールドを追加</h2>
            <form action="{{ route('world.store') }}" method="POST" class="world-form">
                @csrf
                <div class="form-group">
                    <label for="name">ワールド名</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">説明</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <button type="submit" class="btn">ワールドを作成</button>
            </form>
        </div>

        <!-- ワールド一覧 -->
        <div class="world-section">
            <h2>ワールド一覧</h2>
            <div class="world-list">
                @foreach($worlds as $world)
                <div class="world-item {{ $world->pivot->is_selected ? 'selected' : '' }}">
                    <div class="world-info">
                        <h3>{{ $world->name }}</h3>
                        <p>{{ $world->description }}</p>
                    </div>
                    <div class="world-actions">
                        @if(!$world->pivot->is_selected)
                        <form action="{{ route('world.select', $world) }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn-select">選択</button>
                        </form>
                        @else
                        <span class="selected-badge">選択中</span>
                        @endif
                        <form action="{{ route('world.destroy', $world) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete">削除</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.world-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.world-content {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 1rem;
    padding: 2rem;
    backdrop-filter: blur(5px);
}

.world-section {
    margin-bottom: 2rem;
}

.world-section h2 {
    font-size: 1.5rem;
    color: #8b4513;
    margin-bottom: 1rem;
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.world-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
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

.form-group input,
.form-group textarea {
    padding: 0.5rem;
    border: 2px solid #8b4513;
    border-radius: 0.5rem;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.world-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.world-item {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.world-item.selected {
    border: 2px solid #4CAF50;
    background: rgba(76, 175, 80, 0.1);
}

.world-info {
    flex: 1;
}

.world-info h3 {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 0.5rem;
}

.world-info p {
    color: #666;
}

.world-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.inline-form {
    margin: 0;
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

.btn-select {
    background: #2196F3;
}

.btn-select:hover {
    background: #0b7dda;
}

.btn-delete {
    background: #f44336;
}

.btn-delete:hover {
    background: #d32f2f;
}

.selected-badge {
    background: #4CAF50;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
}
</style>
@endsection