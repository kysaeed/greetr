<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorldEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorldEntryController extends Controller
{
    /**
     * 今日のワールドエントリーを取得
     */
    public function getTodayEntry()
    {
        $user = Auth::user();
        $entry = $user->worldEntries()
            ->whereDate('created_at', today())
            ->first();

        return response()->json($entry);
    }

    /**
     * ワールドエントリーを作成
     */
    public function store(Request $request)
    {
        // 今日の日付のエントリーが既に存在するか確認
        $today = now()->format('Y-m-d');
        $existingEntry = auth()->user()->worldEntries()
            ->whereDate('date', $today)
            ->first();

        if ($existingEntry) {
            return response()->json([
                'success' => false,
                'message' => '今日は既にエントリー済みです'
            ], 400);
        }

        // バリデーション
        $validated = $request->validate([
            'date' => 'required|date',
            'daily_quests' => 'array'
        ]);

        // デイリークエストが未設定の場合は空配列を設定
        $dailyQuests = $validated['daily_quests'] ?? [];

        // 現在選択されているワールドを取得
        $selectedWorld = auth()->user()->worlds()
            ->wherePivot('is_selected', true)
            ->first();

        if (!$selectedWorld) {
            return response()->json([
                'success' => false,
                'message' => 'ワールドが選択されていません'
            ], 400);
        }

        // エントリーを作成
        $entry = auth()->user()->worldEntries()->create([
            'date' => $validated['date'],
            'daily_quests' => $dailyQuests,
            'world_id' => $selectedWorld->id,
            'coins_earned' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'エントリーが作成されました',
            'reward' => 100 // デフォルトの報酬
        ]);
    }

    /**
     * ユーザーの全てのワールドエントリーを取得
     */
    public function getUserEntries()
    {
        $user = Auth::user();
        $entries = $user->worldEntries()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($entries);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
