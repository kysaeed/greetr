<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">ワールド一覧</h2>
                        <a href="{{ route('world.create') }}" class="btn btn-primary">
                            新規ワールド作成
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($worlds as $world)
                            <div class="card">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2">{{ $world->name }}</h3>
                                    <p class="text-gray-600 mb-4">{{ $world->description }}</p>
                                    <p class="text-sm text-gray-500">作成者: {{ $world->user->name }}</p>

                                    <div class="mt-4 flex justify-between items-center">
                                        <form action="{{ route('world.select', $world) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">
                                                選択
                                            </button>
                                        </form>
                                        <div class="flex space-x-2">
                                            @can('update', $world)
                                                <a href="{{ route('world.edit', $world) }}" class="btn btn-primary">
                                                    編集
                                                </a>
                                            @endcan
                                            @can('delete', $world)
                                                <form action="{{ route('world.destroy', $world) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">
                                                        削除
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>