<?php

namespace App\Http\Controllers;

use App\Models\World;
use Illuminate\Http\Request;

class WorldController extends Controller
{
    /**
     * ワールド一覧ページを表示
     */
    public function index()
    {
        $user = auth()->user();
        $worlds = $user->worlds()->with('creator')->get();
        $selectedWorld = $user->worlds()->wherePivot('is_selected', true)->first();

        return view('world', [
            'worlds' => $worlds,
            'selectedWorld' => $selectedWorld
        ]);
    }

    /**
     * 新しいワールドを作成
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000'
        ]);

        $world = World::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => auth()->id()
        ]);

        // 選択済みワールドの有無を確認
        $hasSelectedWorld = auth()->user()->worlds()
            ->wherePivot('is_selected', true)
            ->exists();

        // 選択済みワールドがない場合は新規作成したワールドを選択状態にする
        auth()->user()->worlds()->syncWithoutDetaching([
            $world->id => ['is_selected' => !$hasSelectedWorld]
        ]);

        return redirect()->route('world.index')
            ->with('success', 'ワールドを作成しました。');
    }

    /**
     * ワールドを削除
     */
    public function destroy(World $world)
    {
        // 作成者のみ削除可能
        if ($world->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'このワールドを削除する権限がありません'
            ], 403);
        }

        $world->delete();

        return response()->json([
            'success' => true,
            'message' => 'ワールドを削除しました'
        ]);
    }

    /**
     * ワールドを選択
     */
    public function select(World $world)
    {
        // ユーザーがこのワールドにアクセス権を持っているか確認
        if (!$world->users()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'このワールドにアクセスする権限がありません'
            ], 403);
        }

        // 選択したワールドを選択状態にする
        // 他のワールドの選択状態は自動的に解除される
        auth()->user()->worlds()->syncWithoutDetaching([
            $world->id => ['is_selected' => true]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ワールドを選択しました'
        ]);
    }
}